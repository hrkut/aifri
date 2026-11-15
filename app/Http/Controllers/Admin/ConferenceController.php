<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Middleware\UseAdminConference;
use App\Models\Conference;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ConferenceController extends Controller
{
    public function index(): View
    {
        $conferences = Conference::query()
            ->withCount([
                'registrations as registrations_count' => fn ($q) => $q->where('participation_type', '!=', 'break'),
                'questions',
            ])
            ->orderByDesc('year')
            ->get();

        return view('admin.conferences.index', [
            'conferences' => $conferences,
            'working' => Conference::current(),
        ]);
    }

    public function create(): View
    {
        $template = Conference::active();

        return view('admin.conferences.form', [
            'conference' => new Conference([
                'year' => ((int) Conference::query()->max('year')) + 1,
                'start_time' => '09:00',
                'presentation_minutes' => $template->presentation_minutes ?? 15,
                'break_minutes' => $template->break_minutes ?? 20,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request, null);

        $conference = Conference::create($data + ['is_active' => false]);

        return redirect()->route('admin.conferences.index')
            ->with('success', "Ročník {$conference->year} bol vytvorený. Na verejnom webe sa zobrazí až po aktivovaní.");
    }

    public function edit(Conference $conference): View
    {
        return view('admin.conferences.form', compact('conference'));
    }

    public function update(Request $request, Conference $conference): RedirectResponse
    {
        $conference->update($this->validated($request, $conference));

        return redirect()->route('admin.conferences.index')
            ->with('success', "Ročník {$conference->year} bol uložený.");
    }

    /** Make the edition the one shown on the public site. */
    public function activate(Conference $conference): RedirectResponse
    {
        $conference->activate();

        return redirect()->route('admin.conferences.index')
            ->with('success', "Ročník {$conference->year} je teraz zverejnený na webe.");
    }

    /** Switch which edition the admin pages (registrations, program, questions) work with. */
    public function select(Request $request, Conference $conference): RedirectResponse
    {
        if ($conference->is_active) {
            $request->session()->forget(UseAdminConference::SESSION_KEY);
        } else {
            $request->session()->put(UseAdminConference::SESSION_KEY, $conference->id);
        }

        return redirect()->route('admin.dashboard')
            ->with('success', $conference->isReadOnly()
                ? "Prezeráte archívny ročník {$conference->year}."
                : "Pracujete s ročníkom {$conference->year}.");
    }

    private function validated(Request $request, ?Conference $conference): array
    {
        $rules = [
            'year' => ['required', 'integer', 'min:2000', 'max:2100', Rule::unique('conferences', 'year')->ignore($conference?->id)],
            'date' => ['required', 'date'],
            'registration_deadline' => ['nullable', 'date', 'before_or_equal:date'],
            'contribution_deadline' => array_merge(
                ['nullable', 'date', 'before_or_equal:date'],
                $request->filled('registration_deadline') ? ['before_or_equal:registration_deadline'] : [],
            ),
            'notification_date' => ['nullable', 'date', 'before_or_equal:date'],
            'teams_url' => ['nullable', 'url', 'max:2000'],
        ];

        // Program timing is only set here for a new edition; afterwards it is changed
        // via "Nastaviť dĺžku príspevku", which also recalculates the program.
        if ($conference === null) {
            $rules += [
                'start_time' => ['required', 'date_format:H:i'],
                'presentation_minutes' => ['required', 'integer', 'min:1', 'max:480'],
                'break_minutes' => ['nullable', 'integer', 'min:0', 'max:480'],
            ];
        }

        $data = $request->validate($rules, [
            'year.unique' => 'Tento ročník už existuje.',
            'registration_deadline.before_or_equal' => 'Deadline registrácie musí byť najneskôr v deň konferencie.',
            'contribution_deadline.before_or_equal' => 'Deadline príspevkov musí byť najneskôr v deň konferencie a nie neskôr ako deadline registrácie.',
            'notification_date.before_or_equal' => 'Notifikácia autorov musí byť najneskôr v deň konferencie.',
        ]);

        if (array_key_exists('break_minutes', $data)) {
            $data['break_minutes'] = (int) ($data['break_minutes'] ?? 0);
        }

        return $data;
    }
}
