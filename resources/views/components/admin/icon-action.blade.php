@props([
    'tooltip',               // text of the tooltip, also the accessible name of the action
    'href' => null,          // link action
    'action' => null,        // form action (POST) for buttons that change data
    'method' => 'POST',
    'confirm' => null,       // optional confirmation question before submitting
    'color' => 'slate',      // slate | blue | emerald | red
])
@php
    $colors = [
        'slate' => 'bg-slate-700 hover:bg-slate-600 text-slate-200',
        'blue' => 'bg-blue-700 hover:bg-blue-600 text-white',
        'emerald' => 'bg-emerald-700 hover:bg-emerald-600 text-white',
        'red' => 'bg-red-700 hover:bg-red-600 text-white',
    ];
    $classes = 'inline-flex items-center justify-center w-8 h-8 rounded-lg transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400 ' . ($colors[$color] ?? $colors['slate']);
@endphp
{{-- Icon button with a tooltip shown on hover and on keyboard focus --}}
<span class="relative inline-flex group">
    @if($href)
        <a href="{{ $href }}" class="{{ $classes }}" aria-label="{{ $tooltip }}" {{ $attributes }}>{{ $slot }}</a>
    @elseif($action)
        <form method="POST" action="{{ $action }}" class="inline-flex"
              @if($confirm) onsubmit="return confirm(@js($confirm));" @endif>
            @csrf
            @unless(strtoupper($method) === 'POST')
                @method($method)
            @endunless
            <button type="submit" class="{{ $classes }}" aria-label="{{ $tooltip }}" {{ $attributes }}>{{ $slot }}</button>
        </form>
    @else
        <button type="button" class="{{ $classes }}" aria-label="{{ $tooltip }}" {{ $attributes }}>{{ $slot }}</button>
    @endif

    {{-- hidden (not just transparent) so it never widens the table; anchored to the button's
         right edge and growing leftwards, so it stays inside the last table column --}}
    <span role="tooltip"
          class="pointer-events-none absolute bottom-full right-0 z-30 mb-2 hidden whitespace-nowrap rounded-md border border-slate-700 bg-slate-950 px-2 py-1 text-xs font-medium text-slate-100 shadow-lg group-hover:block group-focus-within:block">
        {{ $tooltip }}
    </span>
</span>
