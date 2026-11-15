<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\Registration;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewRegistrationNotification;
use App\Mail\RegistrationConfirmation;
use App\Exports\RegistrationsExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Conference;
use App\Http\Controllers\Admin\ConferenceController;
use Barryvdh\DomPDF\Facade\Pdf;

// ---- Public site: Slovak (default, no prefix) and English (/en) ----
Route::middleware('locale:sk')->group(__DIR__ . '/public.php');
Route::middleware('locale:en')->prefix('en')->name('en.')->group(__DIR__ . '/public.php');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

Route::middleware(['auth', 'admin', 'admin.conference', 'locale:sk'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function (Request $request) {
        $sortable = [
            'name' => 'name',
            'email' => 'email',
            // UI: "Typ účasti" (Aktívna/Pasívna)
            'participation_type' => 'participation_type',
            // UI: "Forma účasti" (Online/Prezenčne)
            'online_participation' => 'online_participation',
            'institution' => 'institution',
            'created_at' => 'created_at',
        ];

        $q = trim((string) $request->query('q', ''));

        // New select filters
        $participationTypeFilter = (string) $request->query('participation_type', '');
        $onlineParticipationFilter = (string) $request->query('online_participation', '');

        // Backward compatibility for old checkbox URLs
        if ($participationTypeFilter === '' && $request->boolean('only_active')) {
            $participationTypeFilter = 'active';
        }
        if ($onlineParticipationFilter === '' && $request->boolean('only_in_person')) {
            $onlineParticipationFilter = 'in_person';
        }

        $sort = (string) $request->query('sort', 'created_at');
        $direction = strtolower((string) $request->query('direction', 'desc'));

        if (!array_key_exists($sort, $sortable)) {
            $sort = 'created_at';
        }
        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        // breaks are programme items, not participants
        $registrationsQuery = Registration::query()->where('participation_type', '!=', 'break');

        if ($q !== '') {
            // Basic text search across name/email/institution
            $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q) . '%';
            $registrationsQuery->where(function ($query) use ($like) {
                $query->where('name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('institution', 'like', $like);
            });
        }

        // Select filters
        if ($participationTypeFilter === 'active') {
            // Active = presentation
            $registrationsQuery->where('participation_type', 'presentation');
        } elseif ($participationTypeFilter === 'passive') {
            // Passive = passive
            $registrationsQuery->where('participation_type', 'passive');
        }

        if ($onlineParticipationFilter === 'online') {
            $registrationsQuery->where('online_participation', true);
        } elseif ($onlineParticipationFilter === 'in_person') {
            $registrationsQuery->where('online_participation', false);
        }

        // Review state of talks (implies active participation)
        $reviewFilter = (string) $request->query('review', '');
        if (array_key_exists($reviewFilter, Registration::REVIEW_LABELS)) {
            $registrationsQuery->where('participation_type', 'presentation');
            $reviewFilter === Registration::REVIEW_PENDING
                ? $registrationsQuery->where(fn ($q) => $q->whereNull('review_status')->orWhere('review_status', Registration::REVIEW_PENDING))
                : $registrationsQuery->where('review_status', $reviewFilter);
        } else {
            $reviewFilter = '';
        }

        $registrations = $registrationsQuery
            ->orderBy($sortable[$sort], $direction)
            ->paginate(10)
            ->withQueryString();

        return view('admin.dashboard', compact(
            'registrations',
            'sort',
            'direction',
            'q',
            'participationTypeFilter',
            'onlineParticipationFilter',
            'reviewFilter'
        ));
    })->name('dashboard');

    Route::get('/export', function (Request $request) {
        $sortable = [
            'name' => 'name',
            'email' => 'email',
            'participation_type' => 'participation_type',
            'online_participation' => 'online_participation',
            'institution' => 'institution',
            'created_at' => 'created_at',
        ];

        $q = trim((string) $request->query('q', ''));

        // New select filters
        $participationTypeFilter = (string) $request->query('participation_type', '');
        $onlineParticipationFilter = (string) $request->query('online_participation', '');

        // Backward compatibility for old checkbox URLs
        if ($participationTypeFilter === '' && $request->boolean('only_active')) {
            $participationTypeFilter = 'active';
        }
        if ($onlineParticipationFilter === '' && $request->boolean('only_in_person')) {
            $onlineParticipationFilter = 'in_person';
        }

        $sort = (string) $request->query('sort', 'created_at');
        $direction = strtolower((string) $request->query('direction', 'desc'));

        if (!array_key_exists($sort, $sortable)) {
            $sort = 'created_at';
        }
        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        // breaks are programme items, not participants
        $registrationsQuery = Registration::query()->where('participation_type', '!=', 'break');

        if ($q !== '') {
            $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q) . '%';
            $registrationsQuery->where(function ($query) use ($like) {
                $query->where('name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('institution', 'like', $like);
            });
        }

        if ($participationTypeFilter === 'active') {
            $registrationsQuery->where('participation_type', 'presentation');
        } elseif ($participationTypeFilter === 'passive') {
            $registrationsQuery->where('participation_type', 'passive');
        }

        if ($onlineParticipationFilter === 'online') {
            $registrationsQuery->where('online_participation', true);
        } elseif ($onlineParticipationFilter === 'in_person') {
            $registrationsQuery->where('online_participation', false);
        }

        // Review state of talks (implies active participation)
        $reviewFilter = (string) $request->query('review', '');
        if (array_key_exists($reviewFilter, Registration::REVIEW_LABELS)) {
            $registrationsQuery->where('participation_type', 'presentation');
            $reviewFilter === Registration::REVIEW_PENDING
                ? $registrationsQuery->where(fn ($q) => $q->whereNull('review_status')->orWhere('review_status', Registration::REVIEW_PENDING))
                : $registrationsQuery->where('review_status', $reviewFilter);
        } else {
            $reviewFilter = '';
        }

        $registrationsQuery->orderBy($sortable[$sort], $direction);

        $fileName = 'registrations_' . Conference::current()->year . '_' . local_now()->format('Y-m-d_His') . '.xlsx';
        return Excel::download(new RegistrationsExport($registrationsQuery), $fileName);
    })->name('registrations.export');

    Route::get('/attendance-pdf', function (Request $request) {
        $q = trim((string) $request->query('q', ''));

        // New select filters
        $participationTypeFilter = (string) $request->query('participation_type', '');
        $onlineParticipationFilter = (string) $request->query('online_participation', '');

        // Backward compatibility for old checkbox URLs
        if ($participationTypeFilter === '' && $request->boolean('only_active')) {
            $participationTypeFilter = 'active';
        }
        if ($onlineParticipationFilter === '' && $request->boolean('only_in_person')) {
            $onlineParticipationFilter = 'in_person';
        }

        // breaks are programme items, not participants
        $registrationsQuery = Registration::query()->where('participation_type', '!=', 'break');

        if ($q !== '') {
            $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q) . '%';
            $registrationsQuery->where(function ($query) use ($like) {
                $query->where('name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('institution', 'like', $like);
            });
        }

        if ($participationTypeFilter === 'active') {
            $registrationsQuery->where('participation_type', 'presentation');
        } elseif ($participationTypeFilter === 'passive') {
            $registrationsQuery->where('participation_type', 'passive');
        }

        if ($onlineParticipationFilter === 'online') {
            $registrationsQuery->where('online_participation', true);
        } elseif ($onlineParticipationFilter === 'in_person') {
            $registrationsQuery->where('online_participation', false);
        }

        // Review state of talks (implies active participation)
        $reviewFilter = (string) $request->query('review', '');
        if (array_key_exists($reviewFilter, Registration::REVIEW_LABELS)) {
            $registrationsQuery->where('participation_type', 'presentation');
            $reviewFilter === Registration::REVIEW_PENDING
                ? $registrationsQuery->where(fn ($q) => $q->whereNull('review_status')->orWhere('review_status', Registration::REVIEW_PENDING))
                : $registrationsQuery->where('review_status', $reviewFilter);
        } else {
            $reviewFilter = '';
        }

        $registrations = $registrationsQuery->get();

        $extractSortKey = static function ($r): array {
            $name = trim((string) ($r->name ?? ''));
            $parts = preg_split('/\s+/u', $name) ?: [];
            $parts = array_values(array_filter($parts, fn ($p) => $p !== ''));

            $first = $parts[0] ?? '';
            $last = $parts[1] ?? $first;

            return [$last, $first, $name];
        };

        // Slovak locale-aware sorting (diacritics)
        if (class_exists('Collator')) {
            try {
                $collator = new \Collator('sk_SK');
                // Keep diacritics significant; primary strength can ignore accents, so use tertiary.
                $collator->setStrength(\Collator::TERTIARY);

                $arr = $registrations->all();
                usort($arr, static function ($a, $b) use ($extractSortKey, $collator) {
                    [$al, $af, $an] = $extractSortKey($a);
                    [$bl, $bf, $bn] = $extractSortKey($b);

                    $c = $collator->compare($al, $bl);
                    if ($c !== 0) return $c;

                    $c = $collator->compare($af, $bf);
                    if ($c !== 0) return $c;

                    return $collator->compare($an, $bn);
                });
                $registrations = collect($arr);
            } catch (\Throwable $e) {
                // Fallback below
            }
        }

        // Fallback (best-effort) if intl is not available
        if (!$registrations instanceof \Illuminate\Support\Collection) {
            $registrations = collect($registrations);
        }
        if (!class_exists('Collator')) {
            $registrations = $registrations
                ->sortBy(function ($r) use ($extractSortKey) {
                    [$last, $first, $name] = $extractSortKey($r);
                    return mb_strtolower($last . '|' . $first . '|' . $name, 'UTF-8');
                })
                ->values();
        }

        $fileName = 'prezencna_listina_' . Conference::current()->year . '_' . local_now()->format('Y-m-d_His') . '.pdf';

        $pdf = Pdf::loadView('admin.attendance-pdf', compact(
            'registrations',
            'q',
            'participationTypeFilter',
            'onlineParticipationFilter'
        ))->setPaper('a4', 'portrait');

        return $pdf->download($fileName);
    })->name('registrations.attendance_pdf');

    // ---- Review of submitted talks: the author is informed by e-mail ----
    $notifyAuthor = function (Registration $registration, \Illuminate\Mail\Mailable $mail): string {
        try {
            Mail::to($registration->email, $registration->name)->locale($registration->locale ?: 'sk')->send($mail);

            return " E-mail autorovi ({$registration->email}) bol odoslaný.";
        } catch (\Throwable $e) {
            report($e);

            return ' E-mail autorovi sa nepodarilo odoslať, kontaktujte prosím autora priamo.';
        }
    };

    Route::post('/registration/{registration}/approve', function (Registration $registration) use ($notifyAuthor) {
        abort_unless($registration->isTalk(), 404);
        $registration->approve();

        $mailNote = $notifyAuthor($registration, new \App\Mail\TalkApproved($registration, Conference::current()));

        return back()->with('success', "Príspevok „{$registration->title}“ bol schválený a pridaný na koniec programu." . $mailNote);
    })->name('registration.approve');

    Route::post('/registration/{registration}/reject', function (Request $request, Registration $registration) use ($notifyAuthor) {
        abort_unless($registration->isTalk(), 404);

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'review_comment' => ['required', 'string', 'min:10', 'max:2000'],
        ], [
            'review_comment.required' => 'Odmietnutie potrebuje zdôvodnenie pre autora.',
            'review_comment.min' => 'Zdôvodnenie pre autora musí mať aspoň 10 znakov.',
            'review_comment.max' => 'Zdôvodnenie pre autora môže mať najviac 2000 znakov.',
        ]);
        if ($validator->fails()) {
            return back()->with('error', "Príspevok „{$registration->title}“ nebol odmietnutý: " . $validator->errors()->first());
        }

        $registration->reject((string) $request->input('review_comment'));

        $mailNote = $notifyAuthor($registration, new \App\Mail\TalkRejected($registration, Conference::current()));

        return back()->with('success', "Príspevok „{$registration->title}“ bol odmietnutý a odstránený z programu." . $mailNote);
    })->name('registration.reject');

    Route::get('/registration/{registration}', function (Registration $registration) {
        return view('admin.registration-show', compact('registration'));
    })->name('registration.show');

    Route::get('/program', function () {
        $registrations = \App\Models\Registration::query()
            ->inProgram()
            ->orderByRaw('time_start is null')
            ->orderBy('time_start')
            ->orderBy('id')
            ->get();

        return view('admin.program', compact('registrations'));
    })->name('program');

    // Programme of the working edition as a PDF (also for archived editions: it changes nothing)
    Route::get('/program/pdf', function () {
        $conference = Conference::current();
        $registrations = \App\Models\Registration::query()
            ->inProgram()
            ->orderByRaw('program_order is null')
            ->orderBy('program_order')
            ->orderBy('id')
            ->get();

        return Pdf::loadView('admin.program-pdf', compact('registrations', 'conference'))
            ->setPaper('a4', 'landscape')
            ->download('program_' . $conference->year . '.pdf');
    })->name('program.pdf');

    // ---- Presentations (príspevky) CRUD (create/store) ----
    Route::get('/program/create', function () {
        return view('admin.program-create');
    })->name('program.create');

    Route::post('/program', function (Request $request) {
        $data = $request->validate([
            'title_before' => ['nullable', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'title_after' => ['nullable', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:100'],
            'institution' => ['required', 'string', 'max:255'],
            'online_participation' => ['required', 'in:0,1'],
            'time_start' => ['required', 'date_format:H:i'],
            'title' => ['required', 'string', 'max:255'],
            'abstract' => ['nullable', 'string'],
            'keywords' => ['nullable', 'string', 'max:255'],
            'block' => ['nullable', 'in:' . implode(',', array_keys(Registration::BLOCKS))],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $data['online_participation'] = (bool) ((int) $data['online_participation']);

        $data['participation_type'] = 'presentation';

        // Allowed: matches existing program item start OR is strictly after last item end
        $items = \App\Models\Registration::query()
            ->inProgram()
            ->whereNotNull('time_start')
            ->orderByRaw('program_order is null')
            ->orderBy('program_order')
            ->orderBy('id')
            ->get(['id', 'time_start', 'duration_minutes', 'program_order']);

        $allowedStarts = $items
            ->pluck('time_start')
            ->filter()
            ->map(fn ($t) => \Illuminate\Support\Carbon::parse($t)->format('H:i'))
            ->unique()
            ->values()
            ->all();

        $start = (string) $data['time_start'];

        $isSlotStart = in_array($start, $allowedStarts, true);

        $isAfterLastEnd = false;
        if ($items->isNotEmpty()) {
            $last = $items->last();
            $lastStart = $last->time_start ? \Illuminate\Support\Carbon::parse($last->time_start) : null;
            $lastDur = (int) ($last->duration_minutes ?? 0);
            if ($lastStart) {
                $lastEnd = $lastStart->copy();
                if ($lastDur > 0) {
                    $lastEnd->addMinutes($lastDur);
                }
                // times are consecutive, so a new item can only start exactly where the programme ends
                $programEnd = $lastEnd->format('H:i');
                $isAfterLastEnd = $start === $programEnd;
            }
        } else {
            // empty programme: it starts at the edition's start time
            $programEnd = \Illuminate\Support\Carbon::parse(Conference::current()->startTimeLabel())->format('H:i');
            $isAfterLastEnd = $start === $programEnd;
        }

        if (!$isSlotStart && !$isAfterLastEnd) {
            return back()
                ->withErrors(['time_start' => 'Čas začiatku príspevku musí byť zhodný so začiatkom niektorej položky programu alebo s koncom programu. Povolené časy: ' . implode(', ', array_unique(array_merge($allowedStarts, isset($programEnd) ? [$programEnd] : [])))])
                ->withInput();
        }

        $data['participation_type'] = 'presentation';
        // Talks added by the organisers are part of the programme right away
        $data['review_status'] = Registration::REVIEW_APPROVED;
        // online_participation comes from the form (already cast to bool above)

        // Assign duration if conference has default
        $conference = Conference::current();
        if ($conference && empty($data['duration_minutes'])) {
            $data['duration_minutes'] = (int) ($conference->presentation_minutes ?: 15);
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($data, $start, $items, $isSlotStart) {
            // Determine insert position
            if ($isSlotStart) {
                // Insert before the first item that starts at $start
                $target = $items->first(fn ($it) => $it->time_start && \Illuminate\Support\Carbon::parse($it->time_start)->format('H:i') === $start);
                $insertPos = $target ? (int) ($target->program_order ?? 1) : ((int) ($items->max('program_order') ?? 0) + 1);
            } else {
                // After last item
                $insertPos = (int) ($items->max('program_order') ?? 0) + 1;
            }

            // Make room
            \App\Models\Registration::query()
                ->inProgram()
                ->whereNotNull('program_order')
                ->where('program_order', '>=', $insertPos)
                ->increment('program_order');

            $data['program_order'] = $insertPos;
            $data['time_start'] = $start;

            \App\Models\Registration::create($data);

            // Recalculate times from the beginning (same algorithm as reorder)
            $sorted = \App\Models\Registration::query()
                ->inProgram()
                ->orderByRaw('program_order is null')
                ->orderBy('program_order')
                ->orderBy('id')
                ->get(['id', 'duration_minutes']);

            $conference = Conference::current();
            $programStart = trim((string) ($conference?->start_time ?? '09:00'));
            if ($programStart === '') {
                $programStart = '09:00';
            }

            try {
                $cursor = \Illuminate\Support\Carbon::parse($programStart);
            } catch (\Exception $e) {
                $cursor = \Illuminate\Support\Carbon::createFromFormat('H:i', '09:00');
            }

            foreach ($sorted as $item) {
                $t = $cursor->format('H:i');
                \App\Models\Registration::where('id', $item->id)->update(['time_start' => $t]);

                $dur = (int) ($item->duration_minutes ?? 0);
                if ($dur > 0) {
                    $cursor->addMinutes($dur);
                }
            }
        });

        return redirect()->route('admin.program')->with('success', 'Príspevok bol pridaný.');
    })->name('program.store');

    // ---- Breaks (prestávky) CRUD ----
    Route::get('/breaks/create', function () {
        $conference = Conference::current();

        return view('admin.break-create', compact('conference'));
    })->name('breaks.create');

    Route::post('/breaks', function (Request $request) {
        $data = $request->validate([
            'time_start' => ['required', 'date_format:H:i'],
            'title' => ['required', 'string', 'max:255'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:480'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $start = (string) $data['time_start'];
        $breakMinutes = (int) $data['duration_minutes'];

        $exists = \App\Models\Registration::query()
            ->inProgram()
            ->whereNotNull('time_start')
            ->where('time_start', $start)
            ->exists();

        if (!$exists) {
            // Provide allowed times to the user
            $allowed = \App\Models\Registration::query()
                ->inProgram()
                ->whereNotNull('time_start')
                ->orderByRaw('program_order is null')
                ->orderBy('program_order')
                ->pluck('time_start')
                ->map(fn ($t) => \Illuminate\Support\Carbon::parse($t)->format('H:i'))
                ->unique()
                ->values()
                ->all();

            return back()
                ->withErrors(['time_start' => 'Čas prestávky musí byť zhodný so začiatkom niektorého príspevku v programe. Povolené časy: ' . implode(', ', $allowed)])
                ->withInput();
        }

        $data['participation_type'] = 'break';
        $data['online_participation'] = true;
        $data['name'] = '—';
        $data['email'] = 'break@local';
        $data['institution'] = '—';

        // Determine insert position by time
        $programBefore = \App\Models\Registration::query()
            ->inProgram()
            ->orderByRaw('program_order is null')
            ->orderBy('program_order')
            ->orderBy('id')
            ->get(['id', 'program_order', 'time_start']);

        $insertPos = 1;
        foreach ($programBefore as $row) {
            $t = $row->time_start ? \Illuminate\Support\Carbon::parse($row->time_start)->format('H:i') : null;
            if ($t !== null && $t < $start) {
                $insertPos = max($insertPos, ((int) ($row->program_order ?? 0)) + 1);
            }
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($insertPos, $data, $start, $breakMinutes) {
            \App\Models\Registration::query()
                ->inProgram()
                ->whereNotNull('program_order')
                ->where('program_order', '>=', $insertPos)
                ->increment('program_order');

            $data['program_order'] = $insertPos;
            $break = \App\Models\Registration::create($data);
            $break->time_start = $start;
            $break->duration_minutes = $breakMinutes;
            $break->save();

            // Re-load program in new order
            $programAfter = \App\Models\Registration::query()
                ->inProgram()
                ->orderByRaw('program_order is null')
                ->orderBy('program_order')
                ->orderBy('id')
                ->get();

            $idx = $programAfter->search(fn ($r) => (int) $r->id === (int) $break->id);
            if ($idx === false) return;

            $cursor = \Illuminate\Support\Carbon::parse($start)->addMinutes($breakMinutes);

            for ($i = $idx + 1; $i < $programAfter->count(); $i++) {
                $item = $programAfter[$i];
                $item->time_start = $cursor->format('H:i');
                $item->save();

                $dur = (int) ($item->duration_minutes ?? 0);
                if ($dur > 0) {
                    $cursor->addMinutes($dur);
                }
            }
        });

        return redirect()->route('admin.program')->with('success', 'Prestávka bola pridaná.');
    })->name('breaks.store');

    Route::get('/breaks/{break}/edit', function (\App\Models\Registration $break) {
        if ($break->participation_type !== 'break') {
            abort(404);
        }

        return view('admin.break-edit', compact('break'));
    })->name('breaks.edit');

    Route::put('/breaks/{break}', function (Request $request, \App\Models\Registration $break) {
        if ($break->participation_type !== 'break') {
            abort(404);
        }

        $data = $request->validate([
            'time_start' => ['required', 'date_format:H:i'],
            'title' => ['required', 'string', 'max:255'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:480'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $start = (string) $data['time_start'];
        $breakMinutes = (int) $data['duration_minutes'];

        // Check if the time exists in ANY program item (including this break itself)
        $exists = \App\Models\Registration::query()
            ->inProgram()
            ->whereNotNull('time_start')
            ->where('time_start', $start)
            ->exists();

        if (!$exists) {
            $allowed = \App\Models\Registration::query()
                ->inProgram()
                ->whereNotNull('time_start')
                ->orderByRaw('program_order is null')
                ->orderBy('program_order')
                ->pluck('time_start')
                ->map(fn ($t) => \Illuminate\Support\Carbon::parse($t)->format('H:i'))
                ->unique()
                ->values()
                ->all();

            return back()
                ->withErrors(['time_start' => 'Čas prestávky musí byť zhodný so začiatkom niektorého príspevku v programe. Povolené časy: ' . implode(', ', $allowed)])
                ->withInput();
        }

        // The chosen time is the start of a programme item: the break goes right before
        // that item. Then the whole programme is renumbered and its times recalculated,
        // so nothing overlaps and no gap is left at the break's old place.
        $others = \App\Models\Registration::query()
            ->inProgram()
            ->where('id', '!=', $break->id)
            ->orderByRaw('program_order is null')
            ->orderBy('program_order')
            ->orderBy('id')
            ->get(['id', 'program_order', 'time_start'])
            ->values();

        $target = $others->search(fn ($row) => $row->time_start
            && \Illuminate\Support\Carbon::parse($row->time_start)->format('H:i') === $start);

        if ($target === false) {
            // the break keeps its own start time: it stays where it is
            $target = $others->filter(fn ($row) => (int) $row->program_order < (int) $break->program_order)->count();
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($break, $data, $breakMinutes, $others, $target) {
            $order = $others->pluck('id')->all();
            array_splice($order, $target, 0, [$break->id]);

            foreach ($order as $index => $id) {
                \App\Models\Registration::whereKey($id)->update(['program_order' => $index + 1]);
            }

            $break->fill($data);
            $break->duration_minutes = $breakMinutes;
            $break->program_order = $target + 1;
            $break->save();

            \App\Models\Registration::recalculateProgramTimes();
        });

        return redirect()->route('admin.program')->with('success', 'Prestávka bola upravená.');
    })->name('breaks.update');

    Route::get('/registration/{registration}/edit', function (\App\Models\Registration $registration) {
        return view('admin.registration-edit', compact('registration'));
    })->name('registration.edit');

    Route::put('/registration/{registration}', function (Request $request, \App\Models\Registration $registration) {
        $data = $request->validate([
            'title_before' => ['nullable', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'title_after' => ['nullable', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:100'],
            'institution' => ['required', 'string', 'max:255'],
            'online_participation' => ['nullable', 'in:0,1'],
            'time_start' => ['nullable', 'date_format:H:i'],
            'title' => ['nullable', 'string', 'max:255'],
            'abstract' => ['nullable', 'string'],
            'keywords' => ['nullable', 'string', 'max:255'],
            'block' => ['nullable', \Illuminate\Validation\Rule::in(array_merge(array_keys(Registration::BLOCKS), array_filter([$registration->block])))],
        ]);

        if (array_key_exists('online_participation', $data)) {
            $data['online_participation'] = (bool) ((int) $data['online_participation']);
        }

        // Extra rules only for program items
        if ($registration->participation_type === 'presentation' && $registration->reviewStatus() === Registration::REVIEW_APPROVED) {
            $newStart = (string) ($data['time_start'] ?? '');
            if ($newStart === '') {
                return back()->withErrors(['time_start' => 'Čas začiatku je povinný.'])->withInput();
            }

            $items = \App\Models\Registration::query()
                ->inProgram()
                ->whereNotNull('time_start')
                ->orderByRaw('program_order is null')
                ->orderBy('program_order')
                ->orderBy('id')
                ->get(['id', 'time_start', 'duration_minutes', 'program_order']);

            $allowedStarts = $items
                ->pluck('time_start')
                ->filter()
                ->map(fn ($t) => \Illuminate\Support\Carbon::parse($t)->format('H:i'))
                ->unique()
                ->values()
                ->all();

            $isSlotStart = in_array($newStart, $allowedStarts, true);

            $isAfterLastEnd = false;
            if ($items->isNotEmpty()) {
                $last = $items->last();
                $lastStart = $last->time_start ? \Illuminate\Support\Carbon::parse($last->time_start) : null;
                $lastDur = (int) ($last->duration_minutes ?? 0);
                if ($lastStart) {
                    $lastEnd = $lastStart->copy();
                    if ($lastDur > 0) {
                        $lastEnd->addMinutes($lastDur);
                    }
                    // times are consecutive, so a new item can only start exactly where the programme ends
                $programEnd = $lastEnd->format('H:i');
                $isAfterLastEnd = $newStart === $programEnd;
                }
            } else {
                $programEnd = \Illuminate\Support\Carbon::parse(Conference::current()->startTimeLabel())->format('H:i');
                $isAfterLastEnd = $newStart === $programEnd;
            }

            if (!$isSlotStart && !$isAfterLastEnd) {
                return back()
                    ->withErrors(['time_start' => 'Čas začiatku príspevku musí byť zhodný so začiatkom niektorej položky programu alebo s koncom programu. Povolené časy: ' . implode(', ', array_unique(array_merge($allowedStarts, isset($programEnd) ? [$programEnd] : [])))])
                    ->withInput();
            }

            // If slot start, reposition program_order to that slot
            \Illuminate\Support\Facades\DB::transaction(function () use ($registration, $data, $items, $newStart, $isSlotStart) {
                $registration->fill($data);

                if ($isSlotStart) {
                    $target = $items->first(fn ($it) => (int) $it->id !== (int) $registration->id && $it->time_start && \Illuminate\Support\Carbon::parse($it->time_start)->format('H:i') === $newStart);
                    if ($target) {
                        $oldOrder = (int) ($registration->program_order ?? 0);
                        $newOrder = (int) ($target->program_order ?? $oldOrder);

                        if ($oldOrder && $newOrder && $oldOrder !== $newOrder) {
                            // remove from old position
                            \App\Models\Registration::query()
                                ->inProgram()
                                ->whereNotNull('program_order')
                                ->where('program_order', '>', $oldOrder)
                                ->decrement('program_order');

                            // make room
                            \App\Models\Registration::query()
                                ->inProgram()
                                ->whereNotNull('program_order')
                                ->where('program_order', '>=', $newOrder)
                                ->increment('program_order');

                            $registration->program_order = $newOrder;
                        }
                    }
                } else {
                    // after last => move to end
                    $max = \App\Models\Registration::query()
                        ->inProgram()
                        ->max('program_order');
                    $registration->program_order = (int) ($max ?? 0) + 1;
                }

                $registration->time_start = $newStart;
                $registration->save();

                // Recalculate whole program from start
                $sorted = \App\Models\Registration::query()
                    ->inProgram()
                    ->orderByRaw('program_order is null')
                    ->orderBy('program_order')
                    ->orderBy('id')
                    ->get(['id', 'duration_minutes']);

                $conference = Conference::current();
                $programStart = trim((string) ($conference?->start_time ?? '09:00'));
                if ($programStart === '') {
                    $programStart = '09:00';
                }

                $cursor = \Illuminate\Support\Carbon::parse($programStart);
                foreach ($sorted as $item) {
                    $t = $cursor->format('H:i');
                    \App\Models\Registration::where('id', $item->id)->update(['time_start' => $t]);

                    $dur = (int) ($item->duration_minutes ?? 0);
                    if ($dur > 0) {
                        $cursor->addMinutes($dur);
                    }
                }
            });

            $return = (string) $request->query('return', 'program');
            $redirectRoute = $return === 'dashboard' ? 'admin.dashboard' : 'admin.program';
            return redirect()->route($redirectRoute)->with('success', 'Registrácia bola upravená.');
        }

        // Non-program items: keep existing behavior
        $registration->fill($data);
        $registration->save();

        $return = (string) $request->query('return', 'program');
        $redirectRoute = $return === 'dashboard' ? 'admin.dashboard' : 'admin.program';

        return redirect()->route($redirectRoute)->with('success', 'Registrácia bola upravená.');
    })->name('registration.update');

    Route::delete('/program/{registration}', function (\App\Models\Registration $registration) {
        if (!in_array($registration->participation_type, ['presentation', 'break'], true)) {
            abort(404);
        }

        $deletedType = $registration->participation_type;
        $startFrom = $registration->time_start;
        $deletedOrder = $registration->program_order;

        \Illuminate\Support\Facades\DB::transaction(function () use ($registration, $deletedOrder, $startFrom) {
            $registration->delete();

            // Close gap in program_order
            if ($deletedOrder !== null) {
                \App\Models\Registration::query()
                    ->inProgram()
                    ->whereNotNull('program_order')
                    ->where('program_order', '>', (int) $deletedOrder)
                    ->decrement('program_order');
            }

            // Recalculate times for all items after the deleted one
            if ($startFrom) {
                $items = \App\Models\Registration::query()
                    ->inProgram()
                    ->orderByRaw('program_order is null')
                    ->orderBy('program_order')
                    ->orderBy('id')
                    ->get();

                // Find first item that now sits at the deleted position (or next one)
                $index = false;
                if ($deletedOrder !== null) {
                    $index = $items->search(fn ($it) => (int) ($it->program_order ?? 0) === (int) $deletedOrder);
                }
                if ($index === false) {
                    // fallback: recalc nothing if we can't locate, or if list empty
                    return;
                }

                $cursor = \Illuminate\Support\Carbon::parse($startFrom);

                for ($i = $index; $i < $items->count(); $i++) {
                    $item = $items[$i];
                    $item->time_start = $cursor->format('H:i');
                    $item->save();

                    $dur = (int) ($item->duration_minutes ?? 0);
                    if ($dur > 0) {
                        $cursor->addMinutes($dur);
                    }
                }
            }
        });

        return redirect()
            ->route('admin.program')
            ->with('success', $deletedType === 'break' ? 'Prestávka bola vymazaná.' : 'Príspevok bol vymazaný.');
    })->name('program.destroy');

    Route::post('/program/reorder', function (Request $request) {
        $payload = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        $ids = array_values(array_unique($payload['ids']));

        // Require that ids represent the FULL program list
        $allIds = \App\Models\Registration::query()
            ->inProgram()
            ->orderByRaw('program_order is null')
            ->orderBy('program_order')
            ->orderBy('id')
            ->pluck('id')
            ->map(fn ($v) => (int) $v)
            ->all();

        sort($allIds);
        $reqIds = $ids;
        sort($reqIds);

        if ($allIds !== $reqIds) {
            return response()->json(['ok' => false, 'error' => 'Program list incomplete'], 422);
        }

        // 1) Ulož nové poradie
        foreach ($ids as $index => $id) {
            \App\Models\Registration::where('id', $id)->update(['program_order' => $index + 1]);
        }

        // 2) Načítaj položky v novom poradí
        $items = \App\Models\Registration::query()
            ->inProgram()
            ->orderByRaw('program_order is null')
            ->orderBy('program_order')
            ->orderBy('id')
            ->get(['id', 'duration_minutes']);

        // 3) Štart od Conference.start_time
        $conference = Conference::current();
        $start = trim((string) ($conference?->start_time ?? '09:00'));
        if ($start === '') {
            $start = '09:00';
        }

        try {
            $cursor = \Illuminate\Support\Carbon::parse($start);
        } catch (\Exception $e) {
            $cursor = \Illuminate\Support\Carbon::createFromFormat('H:i', '09:00');
        }

        // 4) Prepočítaj všetky časy a ulož
        $updatedTimes = [];
        foreach ($items as $item) {
            $timeStr = $cursor->format('H:i');
            \App\Models\Registration::where('id', $item->id)->update(['time_start' => $timeStr]);
            $updatedTimes[(int) $item->id] = $timeStr;

            $dur = (int) ($item->duration_minutes ?? 0);
            if ($dur > 0) {
                $cursor->addMinutes($dur);
            }
        }

        // 5) Vráť časy ajaxom
        return response()->json(['ok' => true, 'times' => $updatedTimes]);
    })->name('program.reorder');

    Route::get('/program/duration', function () {
        $conference = Conference::current();

        return view('admin.program-duration', compact('conference'));
    })->name('program.duration');

    Route::post('/program/duration', function (Request $request) {
        $payload = $request->validate([
            'start_time' => ['required', 'date_format:H:i'],
            'presentation_minutes' => ['required', 'integer', 'min:1', 'max:480'],
            'break_minutes' => ['nullable', 'integer', 'min:0', 'max:480'],
            'apply_to_breaks' => ['sometimes', 'boolean'],
        ]);

        $conference = Conference::current();

        $conference->start_time = (string) $payload['start_time'];
        $conference->presentation_minutes = (int) $payload['presentation_minutes'];
        $conference->break_minutes = (int) ($payload['break_minutes'] ?? 0);
        $conference->save();

        \Illuminate\Support\Facades\DB::transaction(function () use ($conference, $request) {
            // every talk of the edition gets the new length, also those still waiting for review
            \App\Models\Registration::query()
                ->where('participation_type', 'presentation')
                ->update(['duration_minutes' => (int) $conference->presentation_minutes]);

            // breaks keep their own length (e.g. a longer lunch) unless asked otherwise
            if ($request->boolean('apply_to_breaks')) {
                \App\Models\Registration::query()
                    ->where('participation_type', 'break')
                    ->update(['duration_minutes' => (int) $conference->break_minutes]);
            }

            \App\Models\Registration::recalculateProgramTimes();
        });

        return redirect()->route('admin.program')->with('success', 'Časy programu boli prepočítané.');
    })->name('program.duration.apply');

    Route::post('/program/{registration}/duration', function (Request $request, \App\Models\Registration $registration) {
        if (!in_array($registration->participation_type, ['presentation', 'break'], true)) {
            abort(404);
        }

        $payload = $request->validate([
            'duration_minutes' => ['required', 'integer', 'min:0', 'max:480'],
        ]);

        // only programme items (breaks, approved talks) have a length that moves other items
        if (! \App\Models\Registration::query()->inProgram()->whereKey($registration->id)->exists()) {
            abort(422, 'Položka nie je v programe.');
        }

        $registration->duration_minutes = (int) $payload['duration_minutes'];
        $registration->save();

        \App\Models\Registration::recalculateProgramTimes();

        $updatedTimes = \App\Models\Registration::query()->inProgram()->get(['id', 'time_start'])
            ->mapWithKeys(fn ($item) => [(int) $item->id => \Illuminate\Support\Carbon::parse($item->time_start)->format('H:i')])
            ->all();

        return response()->json(['ok' => true, 'times' => $updatedTimes]);
    })->name('program.duration.update');

    // ---- Conference editions (Ročníky) ----
    Route::get('/rocniky', [ConferenceController::class, 'index'])->name('conferences.index');
    Route::get('/rocniky/novy', [ConferenceController::class, 'create'])->name('conferences.create');
    Route::post('/rocniky', [ConferenceController::class, 'store'])->name('conferences.store');
    Route::get('/rocniky/{conference}/upravit', [ConferenceController::class, 'edit'])->name('conferences.edit');
    Route::put('/rocniky/{conference}', [ConferenceController::class, 'update'])->name('conferences.update');
    Route::post('/rocniky/{conference}/zverejnit', [ConferenceController::class, 'activate'])->name('conferences.activate');
    Route::post('/rocniky/{conference}/vybrat', [ConferenceController::class, 'select'])->name('conferences.select');

    // ---- Questions (Otázky) ----
    Route::get('/questions', function (Request $request) {
        $q = trim((string) $request->query('q', ''));

        $questionsQuery = \App\Models\Question::query();

        // Search in name and question
        if ($q !== '') {
            $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q) . '%';
            $questionsQuery->where(function ($query) use ($like) {
                $query->where('name', 'like', $like)
                    ->orWhere('question', 'like', $like);
            });
        }

        $questions = $questionsQuery
            ->orderBy('id', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('admin.questions', compact('questions'));
    })->name('questions');

    Route::post('/questions/{question}/answered', function (\App\Models\Question $question) {
        $question->update(['answered' => true]);

        return redirect()->back()->with('success', 'Otázka bola označená ako zodpovedaná.');
    })->name('questions.mark_answered');
});

require __DIR__.'/auth.php';

