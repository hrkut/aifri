@props(['title', 'highlight' => false])
@php
    $c = \App\Mail\EmailTheme::COLORS;
    $font = \App\Mail\EmailTheme::FONT;
@endphp
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="{{ $c['panel'] }}" style="background-color: {{ $c['panel'] }}; border-radius: 10px; margin: 0 0 18px;{{ $highlight ? ' border-left: 4px solid ' . $c['accent'] . ';' : '' }}">
    <tr>
        <td style="padding: 18px 20px; font-family: {!! $font !!}; font-size: 15px; line-height: 1.6; color: {{ $c['text'] }};">
            <div style="color: {{ $c['accent'] }}; font-weight: 700; font-size: 13px; letter-spacing: .08em; text-transform: uppercase; padding-bottom: 6px;">{{ $title }}</div>
            {{ $slot }}
        </td>
    </tr>
</table>
