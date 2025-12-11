<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/alma-client.php';

$title = isset($_GET['title']) ? trim((string)$_GET['title']) : '';
$mms   = isset($_GET['mms'])   ? trim((string)$_GET['mms'])   : '';

$locations = [];
$errorMsg  = '';

if ($mms !== '') {
    try {
        $almaKey  = defined('ALMA_API_KEY')  ? (string)ALMA_API_KEY  : (getenv('ALMA_API_KEY') ?: '');
        $almaBase = defined('ALMA_API_BASE') ? (string)ALMA_API_BASE : (getenv('ALMA_API_BASE') ?: '');
        if ($almaKey === '' || $almaBase === '') {
            throw new RuntimeException('Alma configuration (ALMA_API_KEY/ALMA_API_BASE) is missing.');
        }
        $holdings = alma_get_holdings_json($mms, $almaKey, $almaBase);
        $locations = alma_extract_location_options($holdings, $mms, $almaKey, $almaBase);
    } catch (Throwable $e) {
        $errorMsg = $e->getMessage();
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Text a Call Number</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" type="image/x-icon" href="favicon.ico">
  <style>
    :root {
      --gap: .9rem;
      --border: #d0d7de;
      --muted: #6b7280;
    }
    body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; margin: 1.5rem; }
    .card { max-width: 640px; border: 1px solid var(--border); border-radius: 10px; padding: 1rem 1.25rem; }
    h1 { font-size: 1.35rem; margin: 0 0 .5rem; }
    .title { font-weight: 600; margin-bottom: .25rem; }
    .subtle { color: var(--muted); font-size: .95rem; margin: 0 0 1rem; }
    form { display: grid; gap: var(--gap); margin-top: .5rem; }
    .group { display: grid; gap: .35rem; }
    label { font-weight: 600; }
    input[type="text"], input[type="tel"] {
      padding: .6rem .7rem; font-size: 1rem; border: 1px solid var(--border); border-radius: 8px; width: 100%;
    }
    fieldset { border: 1px solid var(--border); border-radius: 8px; }
    fieldset legend { font-weight: 700; padding: 0 .4rem; }
    .radios { display: grid; gap: .35rem; padding: .5rem .75rem .75rem; max-height: 260px; overflow: auto; }
    .radio { display: flex; align-items: center; gap: .5rem; }
    .consent { display: flex; align-items: start; gap: .6rem; font-size: .95rem; }
    .error { color: #b42318; background: #fee4e2; border: 1px solid #fda29b; padding: .6rem .75rem; border-radius: 8px; }
    .hint { color: var(--muted); font-size: .9rem; }
    button {
      background: #0d6efd; color: #fff; border: 0; padding: .7rem 1rem; border-radius: 10px; font-size: 1rem; cursor: pointer;
    }
    button:disabled { opacity: .6; cursor: not-allowed; }
    .header-logo {display: block;}
    @media (max-width: 600px) {.header-logo {display: none;}}
    button:focus {outline: 3px solid #0d6efd; outline-offset: 3px;}
  </style>
</head>
<body>
  <header><img class="header-logo" src="images/library-logo.png" alt="Musselman Library"></header>

  <div class="card" role="region" aria-labelledby="tacn-heading">
    <h1 id="tacn-heading">MUSCAT Plus Text a Call Number</h1>

    <?php if ($title !== ''): ?>
      <div class="title"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <!-- uncomment if debugging for MMSID -->
    <?php
      /*if ($mms !== ''): ?>
      <div class="subtle">MMS ID: <?= htmlspecialchars($mms, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; */?>

    <?php if ($errorMsg !== ''): ?>
      <div class="error">Error: <?= htmlspecialchars($errorMsg, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form action="send-sms.php" method="post" id="tacn-form" onsubmit="return validate()">
      <input type="hidden" name="mms" value="<?= htmlspecialchars($mms, ENT_QUOTES, 'UTF-8') ?>">
      <input type="hidden" name="title" value="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>">

      <fieldset class="group">
        <legend>Item location</legend>
        <div class="radios" id="locations">
          <?php if (!empty($locations)): ?>
            <?php foreach ($locations as $i => $opt): ?>
              <div class="radio">
                <input type="radio" name="holdings" id="<?= htmlspecialchars($opt['id'], ENT_QUOTES, 'UTF-8') ?>" value="<?= htmlspecialchars($opt['value'], ENT_QUOTES, 'UTF-8') ?>">
                <label for="<?= htmlspecialchars($opt['id'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($opt['label'], ENT_QUOTES, 'UTF-8') ?></label>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p class="hint">No locations found for this record.</p>
          <?php endif; ?>
        </div>
        <div class="hint">Choose where you’ll find this item in the library. If only one location is listed, it will be selected automatically.</div>
      </fieldset>

      <div class="group">
        <label for="to">Mobile number (U.S. only)</label>
        <input id="to" name="to" type="tel" inputmode="numeric" autocomplete="off" placeholder="7175551234" required>
        <div class="hint">Enter a 10-digit U.S. number. +1 or 1 prefix is allowed (e.g., +17175551234); other characters are ignored.</div>
      </div>

      <div class="consent">
        <input id="consent" name="consent" type="checkbox" value="yes" required aria-required="true">
        <label for="consent">By checking this box, you consent to receive a one-time text message from Musselman Library with call number information for the material you have selected. Standard messaging and data rates may apply. If you do not consent, or have changed your mind, <a href="JavaScript:window.close()">return to MUSCAT Plus without texting the call number</a>.</label>
      </div>

      <button type="submit">Send text</button>
    </form>
  </div>

  <script>
    (function autoCheckSingleLocation(){
      const radios = document.querySelectorAll('input[type="radio"][name="holdings"]');
      if (radios.length === 1) radios[0].checked = true;
    })();

    function validate() {
      const phone = document.getElementById('to').value.trim();
      const consent = document.getElementById('consent').checked;

      const digits = phone.replace(/\D/g, '');
      if (!/^1?\d{10}$/.test(digits)) {
        alert('Please enter a valid U.S. mobile number (10 digits, e.g., 7175551234 or +17175551234).');
        return false;
      }

      if (!consent) {
        alert('You must consent to receive the text message.');
        return false;
      }

      document.getElementById('to').value = digits;
      return true;
    }
  </script>

  <script>
    document.getElementById('consent').addEventListener('change', function () {
        if (this.checked) {
            // Move focus to the submit button
            const btn = document.querySelector('button[type="submit"]');
            if (btn) btn.focus();
        }
    });
  </script>

</body>
</html>
