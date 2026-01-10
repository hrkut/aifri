<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\Registration;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewRegistrationNotification;

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

Route::post('/prihlasenie', function (Request $request) {
    $data = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'max:255'],
        'phone' => ['nullable', 'string', 'max:100'],
        'institution' => ['required', 'string', 'max:255'],
        'position' => ['nullable', 'string', 'max:255'],
        'participation_type' => ['required', 'in:passive,presentation'],
        'online_participation' => ['sometimes', 'accepted'],
        'title' => ['required_if:participation_type,presentation', 'nullable', 'string', 'max:255'],
        'abstract' => ['required_if:participation_type,presentation', 'nullable', 'string'],
        'keywords' => ['nullable', 'string', 'max:255'],
        'block' => ['nullable', 'in:intro,teaching,practice,students'],
        'notes' => ['nullable', 'string', 'max:2000'],
    ], [
        'name.required' => 'Meno a priezvisko je povinné pole',
        'email.required' => 'E-mail je povinné pole',
        'email.email' => 'Zadajte platnú e-mailovú adresu',
        'institution.required' => 'Inštitúcia / Organizácia je povinné pole',
        'participation_type.required' => 'Vyberte typ účasti',
        'participation_type.in' => 'Neplatný typ účasti',
        'title.required_if' => 'Názov príspevku je povinný pre aktívnu účasť',
        'abstract.required_if' => 'Abstrakt je povinný pre aktívnu účasť',
        'block.in' => 'Vyberte platný blok',
    ]);

    $data['online_participation'] = $request->boolean('online_participation');

    // Word-count validation for abstract (max 300 words)
    if (($data['participation_type'] ?? '') === 'presentation') {
        $abstract = $data['abstract'] ?? '';
        $wordCount = str_word_count($abstract);
        if ($wordCount > 300) {
            return back()
                ->withErrors(['abstract' => 'Abstrakt prekračuje limit 300 slov (aktuálne: ' . $wordCount . ' slov)'])
                ->withInput();
        }
    }

    $registration = Registration::create($data);

    Mail::to('konferenciaAI@fri.uniza.sk')->send(new NewRegistrationNotification($registration));

    return redirect()->route('registration')
        ->with('success', 'Ďakujeme za prihlásenie! Potvrdenie vám pošleme na e-mail.');
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
    Route::get('/', function () {
        $registrations = Registration::latest()->paginate(20);
        return view('admin.dashboard', compact('registrations'));
    })->name('dashboard');

    Route::get('/registration/{registration}', function (Registration $registration) {
        return view('admin.registration-show', compact('registration'));
    })->name('registration.show');
});

require __DIR__.'/auth.php';

