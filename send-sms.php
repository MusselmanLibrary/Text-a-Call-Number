
text/x-generic send-sms.php ( PHP script, UTF-8 Unicode text, with very long lines )
<?php
declare(strict_types=1);

session_start();

require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/alma-client.php';

use Twilio\Rest\Client as TwilioClient;

// --- Helpers ---------------------------------------------------------------
function env_or_const(string $constName, string $envName): string {
    if (defined($constName) && constant($constName) !== '') {
        return (string)constant($constName);
    }
    $v = getenv($envName);
    return $v === false ? '' : $v;
}

function require_value(string $value, string $label): string {
    if (trim($value) === '') {
        throw new InvalidArgumentException($label . ' is required.');
    }
    return $value;
}

function normalize_us_phone(string $raw): string {
    $digits = preg_replace('/\D/', '', $raw);
    if ($digits === null) $digits = '';
    if (preg_match('/^1?(\d{10})$/', $digits, $m)) {
        return '+1' . $m[1];
    }
    throw new InvalidArgumentException('Please enter a valid U.S. mobile number.');
}

// --- Inputs ----------------------------------------------------------------
$toNumber = $_POST['to']        ?? '';
$mmsId    = $_POST['mms']       ?? '';
$title    = $_POST['title']     ?? '';
$holdings = $_POST['holdings']  ?? '';
$consent  = $_POST['consent']   ?? '';

try {
    // Config
    $sid      = require_value(env_or_const('TWILIO_ACCOUNT_SID', 'TWILIO_ACCOUNT_SID'), 'Twilio Account SID');
    $token    = require_value(env_or_const('TWILIO_AUTH_TOKEN', 'TWILIO_AUTH_TOKEN'), 'Twilio Auth Token');
    $from     = require_value(env_or_const('TWILIO_FROM_NUMBER', 'TWILIO_FROM_NUMBER'), 'Twilio From Number');
    $almaKey  = env_or_const('ALMA_API_KEY', 'ALMA_API_KEY');     // fallback only
    $almaBase = env_or_const('ALMA_API_BASE', 'ALMA_API_BASE');   // fallback only

    // Required inputs
    $toNumber = normalize_us_phone(require_value($toNumber, 'Destination phone'));
    $mmsId    = require_value($mmsId, 'MMS ID');
    if ($consent !== 'yes') {
        throw new InvalidArgumentException('Consent is required for messaging.');
    }

    // Derive call number: prefer holdings radio (contains "(Call no: ...)"), then fallback to bib.
    $callno = null;
    if (is_string($holdings) && $holdings !== '') {
        if (preg_match('/\(\s*Call\s*no:\s*([^\)]+)\)/i', $holdings, $m)) {
            $callno = trim($m[1]);
        }
    }
    if ($callno === null && $almaKey !== '' && $almaBase !== '') {
        $bibXml = alma_get_bib_xml($mmsId, $almaKey, $almaBase);
        $callno = alma_extract_call_number_from_bib_xml($bibXml);
    }

    // Compose message (no MMS ID)
    $parts = [];
    if (is_string($title) && trim($title) !== '') {
        $parts[] = trim($title);
    }
    if ($callno) {
        $parts[] = 'Call number: ' . $callno;
    }
    if (is_string($holdings) && trim($holdings) !== '') {
        // Strip "(Call no: ...)" to avoid duplication in the location line
        $cleanLocation = preg_replace('/\(\s*Call\s*no:\s*[^\)]+\)/i', '', (string)$holdings);
        $cleanLocation = trim(preg_replace('/\s+—\s+$/', '', (string)$cleanLocation));
        $parts[] = 'Location: ' . $cleanLocation;
    }
    if (empty($parts)) {
        $parts[] = 'Library item information';
    }
    $body = implode("\n", $parts);

    // Send SMS
    $twilio = new TwilioClient($sid, $token);
    $msg = $twilio->messages->create($toNumber, [
        'from' => $from,
        'body' => $body
    ]);

    // Success page with auto-close + fallback
    $toSafe = htmlspecialchars($toNumber, ENT_QUOTES, 'UTF-8');
    echo '<!doctype html><html><head><meta charset="utf-8"><title>SMS sent</title>';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<style>body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;margin:1.5rem;} .card{max-width:640px;border:1px solid #d0d7de;border-radius:10px;padding:1rem 1.25rem} button{background:#0d6efd;color:#fff;border:0;padding:.6rem 1rem;border-radius:8px;cursor:pointer}</style>';
    echo '<script>(function(){function showFallback(){var el=document.getElementById("fallback");if(el){el.hidden=false;}} function tryClose(){try{window.open("","_self");}catch(e){} try{window.close();}catch(e){} setTimeout(showFallback,300);} window.addEventListener("load",tryClose);})();</script>';
    echo '</head><body><div class="card"><div id="fallback" hidden>';
    echo '<p>Message sent to ' . $toSafe . '.</p>';
    echo '<p><button onclick="window.close()">Close</button></p>';
    echo '</div></div></body></html>';

} catch (Throwable $e) {
    http_response_code(400);
    echo '<!doctype html><meta charset="utf-8"><title>Error</title>';
    echo '<h1>Error</h1>';
    echo '<pre style="white-space:pre-wrap">' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</pre>';
    echo '<p><a href="javascript:history.back()">Go back</a></p>';
}
