<?php
/**
 * Google reCAPTCHA helpers — supports v3 (score-based) and v2 (challenge).
 * Settings live in `site_settings` under group `recaptcha`.
 */
if (!defined('NIIB_INIT')) { http_response_code(403); exit('Forbidden'); }

function niib_recaptcha_config(): array {
    return [
        'enabled'      => (int)setting('recaptcha_enabled', 0) === 1,
        'mode'         => in_array(setting('recaptcha_mode','v3'), ['v2','v3'], true) ? setting('recaptcha_mode','v3') : 'v3',
        'site_key'     => trim((string)setting('recaptcha_site_key','')),
        'secret_key'   => trim((string)setting('recaptcha_secret_key','')),
        'v3_threshold' => max(0.0, min(1.0, (float)setting('recaptcha_v3_threshold','0.5'))),
        'v3_action'    => preg_replace('/[^a-z0-9_]/i', '', (string)setting('recaptcha_v3_action','submit')) ?: 'submit',
    ];
}

function niib_recaptcha_active(): bool {
    $c = niib_recaptcha_config();
    return $c['enabled'] && $c['site_key'] !== '' && $c['secret_key'] !== '';
}

/**
 * Emit the <script> tag(s) for the reCAPTCHA library + a JS config object
 * the frontend form handler consumes. Call once from <head>.
 */
function niib_recaptcha_head(): string {
    if (!niib_recaptcha_active()) return '';
    $c = niib_recaptcha_config();
    $key = htmlspecialchars($c['site_key'], ENT_QUOTES);

    if ($c['mode'] === 'v3') {
        $action = htmlspecialchars($c['v3_action'], ENT_QUOTES);
        return '<script src="https://www.google.com/recaptcha/api.js?render=' . $key . '" async defer></script>'
             . '<script>window.NIIB_RECAPTCHA={mode:"v3",siteKey:"' . $key . '",action:"' . $action . '"};</script>';
    }
    return '<script src="https://www.google.com/recaptcha/api.js" async defer></script>'
         . '<script>window.NIIB_RECAPTCHA={mode:"v2",siteKey:"' . $key . '"};</script>';
}

/**
 * Emit the per-form widget. Call inside every <form> just before the submit button.
 *   - v3: nothing visible; JS adds the token at submit time.
 *   - v2: a visible challenge widget.
 */
function niib_recaptcha_widget(): string {
    if (!niib_recaptcha_active()) return '';
    $c = niib_recaptcha_config();
    if ($c['mode'] === 'v3') return '';
    $key = htmlspecialchars($c['site_key'], ENT_QUOTES);
    return '<div class="g-recaptcha niib-recaptcha-v2" data-sitekey="' . $key . '" style="margin:12px 0;"></div>';
}

/**
 * Server-side verify the user-submitted token.
 * Returns ['ok'=>bool, 'error'=>?string, 'score'=>?float].
 * If reCAPTCHA isn't configured, returns ok=true (pass-through).
 */
function niib_recaptcha_verify(string $token): array {
    if (!niib_recaptcha_active()) return ['ok'=>true];
    $c = niib_recaptcha_config();
    if ($token === '') return ['ok'=>false, 'error'=>'Please complete the captcha.'];

    $payload = http_build_query([
        'secret'   => $c['secret_key'],
        'response' => $token,
        'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
    ]);
    $ctx = stream_context_create(['http' => [
        'method'        => 'POST',
        'header'        => "Content-Type: application/x-www-form-urlencoded\r\n",
        'content'       => $payload,
        'timeout'       => 8,
        'ignore_errors' => true,
    ]]);
    $raw = @file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $ctx);
    if ($raw === false) return ['ok'=>false, 'error'=>'Captcha service unreachable. Try again.'];

    $res = json_decode($raw, true) ?: [];
    if (empty($res['success'])) {
        return ['ok'=>false, 'error'=>'Captcha verification failed.'];
    }
    if ($c['mode'] === 'v3') {
        $score = (float)($res['score'] ?? 0);
        if ($score < $c['v3_threshold']) {
            return ['ok'=>false, 'error'=>'Submission flagged as suspicious. Please try again.', 'score'=>$score];
        }
        return ['ok'=>true, 'score'=>$score];
    }
    return ['ok'=>true];
}
