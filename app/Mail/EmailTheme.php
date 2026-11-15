<?php

namespace App\Mail;

/**
 * Colours and font of the HTML e-mails, following the public site (public/css/conference.css).
 * E-mail clients need solid inline colours, so the site's translucent layers are flattened here.
 */
final class EmailTheme
{
    public const COLORS = [
        'page' => '#062f40',
        'card' => '#0b4b67',
        'panel' => '#08384d',
        'border' => '#1d6684',
        'text' => '#f3f7fb',
        'muted' => '#cfe0e8',
        'accent' => '#ffb81c',
        'link' => '#9fe7ff',
        'onAccent' => '#0b2b3a',
    ];

    public const FONT = "system-ui, -apple-system, 'Segoe UI', Roboto, Arial, sans-serif";

    public const CONTACT_EMAIL = 'konferenciaAI@fri.uniza.sk';

    public const MAP_URL = 'https://maps.app.goo.gl/PaeifyvpnXGbSTbi6';
}
