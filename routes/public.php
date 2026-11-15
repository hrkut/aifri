<?php

/*
 * Public site. Registered twice in routes/web.php: in Slovak without a prefix
 * (route names "home", "program", ...) and in English under /en (names "en.home", ...).
 * Inside these routes use lroute() for links and redirects, so a visitor stays in
 * the language of the page they came from.
 */

use App\Mail\NewRegistrationNotification;
use App\Mail\RegistrationConfirmation;
use App\Models\Conference;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

// Path segments in the language of the group being registered (/prihlasenie vs /en/registration)
$english = trim((string) Route::getLastGroupPrefix(), '/') === 'en';
$path = fn (string $sk, string $en) => $english ? $en : $sk;

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

Route::get($path('/prihlasenie', '/registration'), function () {
    return view('registration');
})->name('registration');

Route::get($path('/vybor', '/committee'), function () {
    return view('committee');
})->name('committee');

Route::get('/program', function () {
    // Only scheduled items: freshly submitted talks have no time until the organisers place them
    $registrations = \App\Models\Registration::query()
        ->inProgram()
        ->whereNotNull('time_start')
        ->orderBy('time_start')
        ->orderBy('id')
        ->get();

    return view('program', compact('registrations'));
})->name('program');

Route::post($path('/prihlasenie', '/registration'), function (Request $request) {
    // After the registration deadline (or the conference day) nobody can register any more
    if (! Conference::current()->isRegistrationOpen()) {
        return redirect()->to(lroute('registration') . '#registration-form')
            ->withErrors(['registration' => __('Registrácia na tento ročník konferencie je už uzatvorená.')]);
    }

    $data = $request->validate([
        'title_before' => ['nullable', 'string', 'max:50'],
        'name' => ['required', 'string', 'max:255'],
        'title_after' => ['nullable', 'string', 'max:50'],
        'email' => ['required', 'email', 'max:255'],
        'phone' => ['nullable', 'string', 'max:100'],
        'institution' => ['required', 'string', 'max:255'],
        'position' => ['nullable', 'string', 'max:255'],
        'participation_type' => [
            'required', 'in:passive,presentation',
            function (string $attribute, mixed $value, \Closure $fail) {
                $conference = Conference::current();
                if ($value === 'presentation' && ! $conference->isContributionOpen()) {
                    $until = Conference::formatDate($conference->contributionDeadline());
                    $fail(__('Prihlasovanie príspevkov bolo uzatvorené:until. Môžete sa prihlásiť ako pasívny účastník.', ['until' => $until ? " ({$until})" : '']));
                }
            },
        ],
        'online_participation' => ['sometimes', 'accepted'],
        'title' => ['required_if:participation_type,presentation', 'nullable', 'string', 'max:255'],
        'abstract' => [
            'required_if:participation_type,presentation', 'nullable', 'string',
            function (string $attribute, mixed $value, \Closure $fail) {
                $words = count(preg_split('/\s+/u', trim((string) $value), -1, PREG_SPLIT_NO_EMPTY));
                if ($words > Registration::ABSTRACT_MAX_WORDS) {
                    $fail(__('Abstrakt prekračuje limit :max slov (aktuálne: :count slov)', ['max' => Registration::ABSTRACT_MAX_WORDS, 'count' => $words]));
                }
            },
        ],
        'keywords' => ['nullable', 'string', 'max:255'],
        'block' => ['nullable', 'in:' . implode(',', array_keys(Registration::BLOCKS))],
        'notes' => ['nullable', 'string', 'max:2000'],
    ], [
        'name.required' => __('Meno a priezvisko je povinné pole'),
        'email.required' => __('E-mail je povinné pole'),
        'email.email' => 'Zadajte platnú e-mailovú adresu',
        'institution.required' => __('Inštitúcia / Organizácia je povinné pole'),
        'participation_type.required' => __('Vyberte typ účasti'),
        'participation_type.in' => __('Neplatný typ účasti'),
        'title.required_if' => __('Názov príspevku je povinný pre aktívnu účasť'),
        'abstract.required_if' => __('Abstrakt je povinný pre aktívnu účasť'),
        'block.in' => __('Vyberte platný blok'),
    ]);

    $data['online_participation'] = $request->boolean('online_participation');
    // e-mails to the participant are sent in the language of the form
    $data['locale'] = app()->getLocale();

    // Contribution details only belong to active participants (hidden fields may still be posted)
    if ($data['participation_type'] !== 'presentation') {
        $data['title'] = $data['abstract'] = $data['keywords'] = $data['block'] = null;
    } else {
        // Default length of a talk for this edition; it waits for review before entering the programme
        $data['duration_minutes'] = (int) (Conference::current()->presentation_minutes ?: 15);
        $data['review_status'] = Registration::REVIEW_PENDING;
    }

    $registration = Registration::create($data);

    // A mail failure must not lose the (already saved) registration nor show an error page,
    // otherwise people submit the form again and create duplicates.
    try {
        Mail::to('konferenciaAI@fri.uniza.sk')->locale('sk')->send(new NewRegistrationNotification($registration));
    } catch (\Throwable $e) {
        report($e);
    }

    // Confirmation to the participant; a mail failure must not lose the (already saved) registration.
    try {
        Mail::to($registration->email, $registration->name)
            ->locale($registration->locale)
            ->send(new RegistrationConfirmation($registration, Conference::current()));
    } catch (\Throwable $e) {
        report($e);
    }

    return redirect()->to(lroute('registration') . '#registration-form')
        ->with('success', __('Ďakujeme za registráciu! Potvrdenie sme vám poslali na e-mail.'));
})->name('registration.submit');

// ---- Question Routes ----
Route::get($path('/otazka', '/question'), function () {
    return view('question');
})->name('question');

Route::post($path('/otazka', '/question'), function (Request $request) {
    $data = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['nullable', 'email', 'max:255'],
        'question' => ['required', 'string', 'min:3', 'max:5000'],
    ], [
        'name.required' => __('Meno je povinné pole'),
        'email.email' => 'Zadajte platnú e-mailovú adresu',
        'question.required' => __('Otázka je povinné pole'),
        'question.min' => __('Otázka musí obsahovať aspoň 3 znaky'),
        'question.max' => __('Otázka môže obsahovať maximálne 5000 znakov'),
    ]);

    \App\Models\Question::create($data);

    return redirect()->to(lroute('question'))
        ->with('success', __('Vaša otázka bola odoslaná. Ďakujeme!'))
        ->withInput($request->only('name'));
})->name('question.store');

// ---- Public Recordings (Záznamy z minulých ročníkov) ----
Route::get($path('/zaznamy', '/recordings'), function (Request $request) {
    // Ak je user už prihlásený, presmeruj na zoznam
    if ($request->session()->get('recordings_authenticated') === true) {
        return redirect()->to(lroute('recordings.list'));
    }
    return view('recordings.login');
})->name('recordings.login');

Route::post($path('/zaznamy', '/recordings'), function (Request $request) {
    $password = (string) $request->input('password', '');

    $expected = (string) config('services.recordings.password');

    if ($expected !== '' && hash_equals($expected, $password)) {
        // new session id (against session fixation) without dropping the rest of the
        // session, so an admin who is signed in stays signed in
        $request->session()->regenerate();
        $request->session()->put('recordings_authenticated', true);

        return redirect()->to(lroute('recordings.list'))
            ->with('success', __('Úspešne ste sa prihlásili.'));
    }

    return back()
        ->withErrors(['password' => __('Nesprávne heslo.')])
        ->withInput();
})->name('recordings.authenticate');

Route::middleware(['auth.recordings'])->prefix($path('/zaznamy', '/recordings'))->group(function () {
    Route::get('/list', function () {
        // All editions that already took place (incl. today), newest first, with their talks
        $presentations = fn ($q) => $q->where('participation_type', 'presentation')
            ->where('review_status', Registration::REVIEW_APPROVED)
            ->whereNotNull('time_start');

        $editions = Conference::query()
            ->whereDate('date', '<=', local_now()->toDateString())
            ->whereHas('registrations', $presentations)
            ->with(['registrations' => fn ($q) => $presentations($q)->orderBy('time_start')->orderBy('id')])
            ->orderByDesc('year')
            ->get();

        return view('recordings.list', compact('editions'));
    })->name('recordings.list');

    /*
     * Serve a recording / slides file. Only files that belong to a talk (stored in the
     * registration's column) are served, and the resolved path must stay inside the
     * directory - so "../" or any other file on the server can never be reached.
     */
    $serveTalkFile = function (string $directory, string $column, string $filename) {
        $known = Registration::withoutGlobalScope('conference')
            ->where('participation_type', 'presentation')
            ->where($column, $filename)
            ->exists();

        $base = realpath(storage_path('app/private/' . $directory));
        $path = $known && $base ? realpath($base . DIRECTORY_SEPARATOR . $filename) : false;

        if ($path === false || ! str_starts_with($path, $base . DIRECTORY_SEPARATOR) || ! is_file($path)) {
            abort(404, $directory === 'zaznamy' ? __('Záznam nebol nájdený.') : __('Prezentácia nebola nájdená.'));
        }

        return response()->file($path);
    };

    Route::get('/record/{filename}', fn (string $filename) => $serveTalkFile('zaznamy', 'record', $filename))
        ->name('recordings.download')->where('filename', '.*');

    Route::get('/presentation/{filename}', fn (string $filename) => $serveTalkFile('prezentacie', 'presentation', $filename))
        ->name('recordings.presentation')->where('filename', '.*');
});
