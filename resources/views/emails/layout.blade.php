@php
    /** @var \App\Models\Conference $conference */
    $c = \App\Mail\EmailTheme::COLORS;
    $font = \App\Mail\EmailTheme::FONT;
    $contactEmail = \App\Mail\EmailTheme::CONTACT_EMAIL;
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="dark light">
    <meta name="supported-color-schemes" content="dark light">
    <title>@yield('title')</title>
</head>
<body style="margin: 0; padding: 0; background-color: {{ $c['page'] }}; color: {{ $c['text'] }}; font-family: {!! $font !!};">
    {{-- Preheader (shown in inbox preview) --}}
    <div style="display: none; max-height: 0; overflow: hidden; opacity: 0;">@yield('preheader')</div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="{{ $c['page'] }}" style="background-color: {{ $c['page'] }};">
        <tr>
            <td align="center" style="padding: 32px 12px;">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="width: 100%; max-width: 600px;">

                    {{-- Logo --}}
                    <tr>
                        <td align="center" style="padding: 0 0 20px;">
                            <img src="{{ asset('images/FRI_logo.png') }}" alt="{{ __('Fakulta riadenia a informatiky UNIZA') }}" width="260" style="display: block; width: 260px; max-width: 80%; height: auto; border: 0;">
                        </td>
                    </tr>

                    {{-- Card --}}
                    <tr>
                        <td bgcolor="{{ $c['card'] }}" style="background-color: {{ $c['card'] }}; border: 1px solid {{ $c['border'] }}; border-radius: 16px; padding: 32px 28px;">

                            {{-- Conference heading + edition stamp --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="font-family: {!! $font !!}; color: {{ $c['text'] }}; font-size: 22px; line-height: 1.25; font-weight: 800; letter-spacing: .02em;">
                                        {!! __('KONFERENCIA O UMELEJ INTELIGENCII VO&nbsp;VZDELÁVANÍ') !!}
                                        <div style="font-size: 15px; font-weight: 400; font-style: italic; color: {{ $c['muted'] }}; padding-top: 4px; letter-spacing: 0;">{{ __('(Conference on AI in education)') }}</div>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding-top: 14px;">
                                        <span style="display: inline-block; padding: 4px 12px; border: 3px double {{ $c['accent'] }}; border-radius: 8px; color: {{ $c['accent'] }}; font-family: {!! $font !!}; font-size: 14px; font-weight: 800; letter-spacing: .12em; text-transform: uppercase;">
                                            {{ $conference->editionLabel() }} · {{ $conference->year }}
                                        </span>
                                    </td>
                                </tr>
                            </table>

                            <h1 style="margin: 28px 0 12px; font-family: {!! $font !!}; font-size: 24px; line-height: 1.3; color: {{ $c['accent'] }};">
                                @yield('heading')
                            </h1>

                            @yield('content')
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td align="center" style="padding: 20px 12px 0; font-family: {!! $font !!}; font-size: 13px; line-height: 1.6; color: {{ $c['muted'] }};">
                            {!! __('Fakulta riadenia a informatiky, Žilinská univerzita v&nbsp;Žiline') !!}<br>
                            <a href="mailto:{{ $contactEmail }}" style="color: {{ $c['link'] }}; text-decoration: none;">{{ $contactEmail }}</a>
                            &nbsp;·&nbsp;
                            <a href="{{ lroute('home') }}" style="color: {{ $c['link'] }}; text-decoration: none;">{{ preg_replace('#^https?://#', '', rtrim(url('/'), '/')) }}</a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
