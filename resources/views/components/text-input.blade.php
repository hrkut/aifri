@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'bg-slate-800 border-slate-700 text-slate-100 placeholder-slate-500 focus:border-slate-500 focus:ring-slate-500 rounded-md shadow-sm']) }}>
