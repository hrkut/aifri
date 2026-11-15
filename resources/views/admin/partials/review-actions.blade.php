@php
    /** @var \App\Models\Registration $registration */
    $status = $registration->reviewStatus();
    // archived editions are read-only
    $canReview = $status !== null && ! \App\Models\Conference::current()->isReadOnly();
    $approveId = 'approve-dialog-' . $registration->id;
    $rejectId = 'reject-dialog-' . $registration->id;
@endphp
{{-- Approve / reject a submitted talk; the action matching the current state is left out.
     Both open a modal and send an e-mail to the author; rejecting asks for the reason. --}}
@if($canReview)
    @if($status !== \App\Models\Registration::REVIEW_APPROVED)
        <x-admin.icon-action
            tooltip="Schváliť"
            color="emerald"
            onclick="document.getElementById('{{ $approveId }}').showModal()">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
        </x-admin.icon-action>

        <x-admin.review-dialog :id="$approveId" heading="Schváliť príspevok" color="emerald"
                               :action="route('admin.registration.approve', $registration)"
                               submit-label="Schváliť a poslať e-mail">
            <p class="mt-1 text-sm text-slate-300">„{{ $registration->title }}“ – {{ $registration->fullName() }}</p>

            <ul class="mt-4 space-y-2 text-sm text-slate-200">
                <li class="flex gap-2">
                    <span class="text-emerald-400" aria-hidden="true">✓</span>
                    <span>Príspevok sa pridá na koniec programu, poradie a čas potom upravíte v časti Program.</span>
                </li>
                <li class="flex gap-2">
                    <span class="text-emerald-400" aria-hidden="true">✓</span>
                    <span>Autorovi sa pošle e-mail o prijatí na {{ $registration->email }}.</span>
                </li>
            </ul>
        </x-admin.review-dialog>
    @endif

    @if($status !== \App\Models\Registration::REVIEW_REJECTED)
        <x-admin.icon-action
            tooltip="Odmietnuť"
            color="red"
            onclick="document.getElementById('{{ $rejectId }}').showModal()">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </x-admin.icon-action>

        <x-admin.review-dialog :id="$rejectId" heading="Zamietnuť príspevok" color="red"
                               :action="route('admin.registration.reject', $registration)"
                               submit-label="Zamietnuť a poslať e-mail">
            <p class="mt-1 text-sm text-slate-300">„{{ $registration->title }}“ – {{ $registration->fullName() }}</p>

            <label for="{{ $rejectId }}-comment" class="mt-4 block text-sm font-medium text-slate-200">
                Zdôvodnenie pre autora
            </label>
            <textarea id="{{ $rejectId }}-comment" name="review_comment" rows="6" required minlength="10" maxlength="2000"
                      class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-800 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-400"
                      placeholder="Napr. téma príspevku sa nezhoduje so zameraním blokov konferencie…"></textarea>
            <p class="mt-2 text-xs text-slate-400">
                Text sa pošle autorovi e-mailom na {{ $registration->email }}. Príspevok sa odstráni z programu, registrácia zostane uložená.
            </p>
        </x-admin.review-dialog>
    @endif
@endif
