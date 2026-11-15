@props(['href'])
@php
    $c = \App\Mail\EmailTheme::COLORS;
    $font = \App\Mail\EmailTheme::FONT;
@endphp
<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin: 6px 0 24px;">
    <tr>
        <td bgcolor="{{ $c['accent'] }}" style="background-color: {{ $c['accent'] }}; border-radius: 10px;">
            <a href="{{ $href }}" style="display: inline-block; padding: 12px 22px; font-family: {!! $font !!}; font-size: 15px; font-weight: 700; color: {{ $c['onAccent'] }}; text-decoration: none;">{{ $slot }}</a>
        </td>
    </tr>
</table>
