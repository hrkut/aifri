<?php

// Debug script na kontrolu session
session_start();

echo "=== Session Debug ===\n";
echo "Session ID: " . session_id() . "\n";
echo "Session Data: " . json_encode($_SESSION, JSON_PRETTY_PRINT) . "\n";
echo "Cookies: " . json_encode($_COOKIE, JSON_PRETTY_PRINT) . "\n";

?>
<!DOCTYPE html>
<html>
<head>
    <title>Session Debug</title>
    <style>
        body { font-family: monospace; padding: 2rem; background: #222; color: #0f0; }
        pre { background: #111; padding: 1rem; border-radius: 4px; }
    </style>
</head>
<body>
    <h1>🔍 Session Debug Info</h1>
    <pre><?php echo "Session ID: " . session_id() . "\n"; ?></pre>
    <pre><?php echo "Session Data:\n" . json_encode($_SESSION, JSON_PRETTY_PRINT) . "\n"; ?></pre>
    <pre><?php echo "Cookies:\n" . json_encode($_COOKIE, JSON_PRETTY_PRINT) . "\n"; ?></pre>

    <p>Ak vidíš recordings_authenticated=true v Session Data, session funguje.</p>
</body>
</html>

