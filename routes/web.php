<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\Registration;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewRegistrationNotification;
use App\Exports\RegistrationsExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Conference;

Route::get('/', function () {
    $allowed = ['jpg','jpeg','png','gif','webp','svg'];
    $images = [];
    $imagesFs = public_path('images');
    if (is_dir($imagesFs)) {
        $files = scandir($imagesFs) ?: [];
        foreach ($files as $f) {
            if ($f === '.' || $f === '..') continue;
            $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
            if (in_array($ext, $allowed, true)) {
                $images[] = asset('images/' . rawurlencode($f));
            }
        }
        sort($images, SORT_NATURAL | SORT_FLAG_CASE);
    }
    return view('home', compact('images'));
 })->name('home');

Route::get('/prihlasenie', function () {
    return view('registration');
})->name('registration');

Route::get('/vybor', function () {
    return view('committee');
})->name('committee');

Route::get('/program', function () {
    $registrations = \App\Models\Registration::query()
        ->whereIn('participation_type', ['presentation', 'break'])
        ->orderByRaw('time_start is null')
        ->orderBy('time_start')
        ->orderBy('id')
        ->get();

    return view('program', compact('registrations'));
})->name('program');

Route::post('/prihlasenie', function (Request $request) {
    $data = $request->validate([
        'title_before' => ['nullable', 'string', 'max:50'],
        'name' => ['required', 'string', 'max:255'],
        'title_after' => ['nullable', 'string', 'max:50'],
        'email' => ['required', 'email', 'max:255'],
        'phone' => ['nullable', 'string', 'max:100'],
        'institution' => ['required', 'string', 'max:255'],
        'position' => ['nullable', 'string', 'max:255'],
        'title' => ['nullable', 'string', 'max:255'],
        'abstract' => ['nullable', 'string'],
        'keywords' => ['nullable', 'string', 'max:255'],
        'block' => ['nullable', 'in:intro,teaching,practice,students'],
        'notes' => ['nullable', 'string', 'max:2000'],
    ], [
        'name.required' => 'Meno a priezvisko je povinné pole',
        'email.required' => 'E-mail je povinné pole',
        'email.email' => 'Zadajte platnú e-mailovú adresu',
        'institution.required' => 'Inštitúcia / Organizácia je povinné pole',
        'block.in' => 'Vyberte platný blok',
    ]);

    $data['participation_type'] = 'passive';
    $data['online_participation'] = true;

    $registration = Registration::create($data);

    Mail::to('konferenciaAI@fri.uniza.sk')->send(new NewRegistrationNotification($registration));

    return redirect()->to(route('registration') . '#registration-form')
        ->with('success', 'Ďakujeme za prihlásenie! Vašu prihlášku sme zaregistrovali.');
})->name('registration.submit');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

Route::get('/debug-auth', function () {
    if (!auth()->check()) {
        return 'Not logged in';
    }
    $user = auth()->user();
    return [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'is_admin' => $user->is_admin,
        'is_admin_type' => gettype($user->is_admin),
        'is_admin_bool_check' => $user->is_admin ? 'YES' : 'NO',
        'can_access_admin' => $user->is_admin ? 'Should see Admin link' : 'Should NOT see Admin link',
    ];
})->middleware(['auth']);

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
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
        $filterActive = $request->boolean('only_active');
        $filterInPerson = $request->boolean('only_in_person');

        $sort = (string) $request->query('sort', 'created_at');
        $direction = strtolower((string) $request->query('direction', 'desc'));

        if (!array_key_exists($sort, $sortable)) {
            $sort = 'created_at';
        }
        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        $registrationsQuery = Registration::query();

        if ($q !== '') {
            // Basic text search across name/email/institution
            $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q) . '%';
            $registrationsQuery->where(function ($query) use ($like) {
                $query->where('name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('institution', 'like', $like);
            });
        }

        // Checkbox filters
        if ($filterActive) {
            // Active = presentation
            $registrationsQuery->where('participation_type', 'presentation');
        }
        if ($filterInPerson) {
            // In-person = not online
            $registrationsQuery->where('online_participation', false);
        }

        $registrations = $registrationsQuery
            ->orderBy($sortable[$sort], $direction)
            ->paginate(10)
            ->withQueryString();

        return view('admin.dashboard', compact('registrations', 'sort', 'direction', 'q', 'filterActive', 'filterInPerson'));
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
        $filterActive = $request->boolean('only_active');
        $filterInPerson = $request->boolean('only_in_person');

        $sort = (string) $request->query('sort', 'created_at');
        $direction = strtolower((string) $request->query('direction', 'desc'));

        if (!array_key_exists($sort, $sortable)) {
            $sort = 'created_at';
        }
        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        $registrationsQuery = Registration::query();

        if ($q !== '') {
            $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q) . '%';
            $registrationsQuery->where(function ($query) use ($like) {
                $query->where('name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('institution', 'like', $like);
            });
        }

        if ($filterActive) {
            $registrationsQuery->where('participation_type', 'presentation');
        }
        if ($filterInPerson) {
            $registrationsQuery->where('online_participation', false);
        }

        $registrationsQuery->orderBy($sortable[$sort], $direction);

        $fileName = 'registrations_' . now()->format('Y-m-d_His') . '.xlsx';
        return Excel::download(new RegistrationsExport($registrationsQuery), $fileName);
    })->name('registrations.export');

    Route::get('/registration/{registration}', function (Registration $registration) {
        return view('admin.registration-show', compact('registration'));
    })->name('registration.show');

    Route::get('/program', function () {
        $registrations = \App\Models\Registration::query()
            ->whereIn('participation_type', ['presentation', 'break'])
            ->orderByRaw('time_start is null')
            ->orderBy('time_start')
            ->orderBy('id')
            ->get();

        return view('admin.program', compact('registrations'));
    })->name('program');

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
            'block' => ['nullable', 'in:intro,teaching,practice,students'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $data['online_participation'] = (bool) ((int) $data['online_participation']);

        $data['participation_type'] = 'presentation';

        // Allowed: matches existing program item start OR is strictly after last item end
        $items = \App\Models\Registration::query()
            ->whereIn('participation_type', ['presentation', 'break'])
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
                $isAfterLastEnd = \Illuminate\Support\Carbon::parse($start)->greaterThan($lastEnd);
            }
        } else {
            // If no items exist, anything is allowed
            $isAfterLastEnd = true;
        }

        if (!$isSlotStart && !$isAfterLastEnd) {
            return back()
                ->withErrors(['time_start' => 'Čas začiatku príspevku musí byť zhodný so začiatkom niektorého príspevku alebo prestávky v programe, alebo musí byť po poslednom príspevku. Povolené časy: ' . implode(', ', $allowedStarts)])
                ->withInput();
        }

        $data['participation_type'] = 'presentation';
        // online_participation comes from the form (already cast to bool above)

        // Assign duration if conference has default
        $conference = \App\Models\Conference::query()->first();
        if ($conference && empty($data['duration_minutes'])) {
            $data['duration_minutes'] = (int) ($conference->presentation_minutes ?? 0);
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
                ->whereIn('participation_type', ['presentation', 'break'])
                ->whereNotNull('program_order')
                ->where('program_order', '>=', $insertPos)
                ->increment('program_order');

            $data['program_order'] = $insertPos;
            $data['time_start'] = $start;

            \App\Models\Registration::create($data);

            // Recalculate times from the beginning (same algorithm as reorder)
            $sorted = \App\Models\Registration::query()
                ->whereIn('participation_type', ['presentation', 'break'])
                ->orderByRaw('program_order is null')
                ->orderBy('program_order')
                ->orderBy('id')
                ->get(['id', 'duration_minutes']);

            $conference = \App\Models\Conference::query()->first();
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
        $conference = \App\Models\Conference::query()->first();
        if (!$conference) {
            $conference = \App\Models\Conference::create([
                'start_time' => '09:00',
                'presentation_minutes' => 15,
                'break_minutes' => 20,
            ]);
        }

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
            ->whereIn('participation_type', ['presentation', 'break'])
            ->whereNotNull('time_start')
            ->where('time_start', $start)
            ->exists();

        if (!$exists) {
            // Provide allowed times to the user
            $allowed = \App\Models\Registration::query()
                ->whereIn('participation_type', ['presentation', 'break'])
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
            ->whereIn('participation_type', ['presentation', 'break'])
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
                ->whereIn('participation_type', ['presentation', 'break'])
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
                ->whereIn('participation_type', ['presentation', 'break'])
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
            ->whereIn('participation_type', ['presentation', 'break'])
            ->whereNotNull('time_start')
            ->where('time_start', $start)
            ->exists();

        if (!$exists) {
            $allowed = \App\Models\Registration::query()
                ->whereIn('participation_type', ['presentation', 'break'])
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

        // Reposition order based on time among other items
        $others = \App\Models\Registration::query()
            ->whereIn('participation_type', ['presentation', 'break'])
            ->where('id', '!=', $break->id)
            ->orderByRaw('program_order is null')
            ->orderBy('program_order')
            ->orderBy('id')
            ->get(['id', 'program_order', 'time_start']);

        $insertPos = 1;
        foreach ($others as $row) {
            $t = $row->time_start ? \Illuminate\Support\Carbon::parse($row->time_start)->format('H:i') : null;
            if ($t !== null && $t < $start) {
                $insertPos = max($insertPos, ((int) ($row->program_order ?? 0)) + 1);
            }
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($break, $data, $insertPos, $start, $breakMinutes) {
            // Close gap at old position
            if ($break->program_order !== null) {
                \App\Models\Registration::query()
                    ->whereIn('participation_type', ['presentation', 'break'])
                    ->whereNotNull('program_order')
                    ->where('program_order', '>', (int) $break->program_order)
                    ->decrement('program_order');
            }

            // Make room at new position
            \App\Models\Registration::query()
                ->whereIn('participation_type', ['presentation', 'break'])
                ->whereNotNull('program_order')
                ->where('program_order', '>=', $insertPos)
                ->increment('program_order');

            $break->fill($data);
            $break->program_order = $insertPos;
            $break->time_start = $start;
            $break->duration_minutes = $breakMinutes;
            $break->save();

            // Re-load program in new order
            $programAfter = \App\Models\Registration::query()
                ->whereIn('participation_type', ['presentation', 'break'])
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
            'block' => ['nullable', 'in:intro,teaching,practice,students'],
        ]);

        if (array_key_exists('online_participation', $data)) {
            $data['online_participation'] = (bool) ((int) $data['online_participation']);
        }

        // Extra rules only for program items
        if ($registration->participation_type === 'presentation') {
            $newStart = (string) ($data['time_start'] ?? '');
            if ($newStart === '') {
                return back()->withErrors(['time_start' => 'Čas začiatku je povinný.'])->withInput();
            }

            $items = \App\Models\Registration::query()
                ->whereIn('participation_type', ['presentation', 'break'])
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
                    $isAfterLastEnd = \Illuminate\Support\Carbon::parse($newStart)->greaterThan($lastEnd);
                }
            } else {
                $isAfterLastEnd = true;
            }

            if (!$isSlotStart && !$isAfterLastEnd) {
                return back()
                    ->withErrors(['time_start' => 'Čas začiatku príspevku musí byť zhodný so začiatkom niektorého príspevku alebo prestávky v programe, alebo musí byť po poslednom príspevku. Povolené časy: ' . implode(', ', $allowedStarts)])
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
                                ->whereIn('participation_type', ['presentation', 'break'])
                                ->whereNotNull('program_order')
                                ->where('program_order', '>', $oldOrder)
                                ->decrement('program_order');

                            // make room
                            \App\Models\Registration::query()
                                ->whereIn('participation_type', ['presentation', 'break'])
                                ->whereNotNull('program_order')
                                ->where('program_order', '>=', $newOrder)
                                ->increment('program_order');

                            $registration->program_order = $newOrder;
                        }
                    }
                } else {
                    // after last => move to end
                    $max = \App\Models\Registration::query()
                        ->whereIn('participation_type', ['presentation', 'break'])
                        ->max('program_order');
                    $registration->program_order = (int) ($max ?? 0) + 1;
                }

                $registration->time_start = $newStart;
                $registration->save();

                // Recalculate whole program from start
                $sorted = \App\Models\Registration::query()
                    ->whereIn('participation_type', ['presentation', 'break'])
                    ->orderByRaw('program_order is null')
                    ->orderBy('program_order')
                    ->orderBy('id')
                    ->get(['id', 'duration_minutes']);

                $conference = \App\Models\Conference::query()->first();
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
                    ->whereIn('participation_type', ['presentation', 'break'])
                    ->whereNotNull('program_order')
                    ->where('program_order', '>', (int) $deletedOrder)
                    ->decrement('program_order');
            }

            // Recalculate times for all items after the deleted one
            if ($startFrom) {
                $items = \App\Models\Registration::query()
                    ->whereIn('participation_type', ['presentation', 'break'])
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
            ->whereIn('participation_type', ['presentation', 'break'])
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
            ->whereIn('participation_type', ['presentation', 'break'])
            ->orderByRaw('program_order is null')
            ->orderBy('program_order')
            ->orderBy('id')
            ->get(['id', 'duration_minutes']);

        // 3) Štart od Conference.start_time
        $conference = \App\Models\Conference::query()->first();
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
        $conference = \App\Models\Conference::query()->first();
        if (!$conference) {
            $conference = \App\Models\Conference::create([
                'start_time' => '09:00',
                'presentation_minutes' => 15,
                'break_minutes' => 20,
            ]);
        }

        return view('admin.program-duration', compact('conference'));
    })->name('program.duration');

    Route::post('/program/duration', function (Request $request) {
        $payload = $request->validate([
            'start_time' => ['required', 'date_format:H:i'],
            'presentation_minutes' => ['required', 'integer', 'min:1', 'max:480'],
            'break_minutes' => ['nullable', 'integer', 'min:0', 'max:480'],
        ]);

        $conference = \App\Models\Conference::query()->first();
        if (!$conference) {
            $conference = new \App\Models\Conference();
        }

        $conference->start_time = (string) $payload['start_time'];
        $conference->presentation_minutes = (int) $payload['presentation_minutes'];
        $conference->break_minutes = (int) ($payload['break_minutes'] ?? 0);
        $conference->save();

        $items = \App\Models\Registration::query()
            ->whereIn('participation_type', ['presentation', 'break'])
            ->orderByRaw('program_order is null')
            ->orderBy('program_order')
            ->orderBy('id')
            ->get();

        $cursor = \Illuminate\Support\Carbon::parse((string) $conference->start_time);

        \Illuminate\Support\Facades\DB::transaction(function () use ($items, $cursor, $conference) {
            $current = $cursor->copy();

            foreach ($items as $item) {
                $item->time_start = $current->format('H:i');

                if ($item->participation_type === 'presentation') {
                    $item->duration_minutes = (int) $conference->presentation_minutes;
                    $item->save();
                    $current->addMinutes((int) $conference->presentation_minutes);
                } else {
                    $item->duration_minutes = (int) ($conference->break_minutes ?? 0);
                    $item->save();

                    if ($item->duration_minutes > 0) {
                        $current->addMinutes($item->duration_minutes);
                    }
                }
            }
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

        $dur = (int) $payload['duration_minutes'];

        $registration->duration_minutes = $dur;
        $registration->save();

        // Recalculate time_start for following items based on stored durations
        $items = \App\Models\Registration::query()
            ->whereIn('participation_type', ['presentation', 'break'])
            ->orderByRaw('program_order is null')
            ->orderBy('program_order')
            ->orderBy('id')
            ->get();

        $index = $items->search(fn ($item) => (int) $item->id === (int) $registration->id);
        if ($index === false) {
            abort(422, 'Položka nie je v programe.');
        }

        $start = $registration->time_start;
        if (!$start) {
            abort(422, 'Položka nemá nastavený čas začiatku.');
        }

        $cursor = \Illuminate\Support\Carbon::parse($start);
        $cursor->addMinutes(max(0, $dur));

        $updatedTimes = [];

        \Illuminate\Support\Facades\DB::transaction(function () use ($items, $index, $cursor, &$updatedTimes) {
            $current = $cursor->copy();

            for ($i = $index + 1; $i < $items->count(); $i++) {
                $item = $items[$i];
                $item->time_start = $current->format('H:i');
                $item->save();
                $updatedTimes[(int) $item->id] = $item->time_start;

                $minutes = (int) ($item->duration_minutes ?? 0);
                if ($minutes > 0) {
                    $current->addMinutes($minutes);
                }
            }
        });

        return response()->json(['ok' => true, 'times' => $updatedTimes]);
    })->name('program.duration.update');
});

require __DIR__.'/auth.php';

