<?php
/**
 * NIIB Institute — Native PHP SMTP Mailer
 *
 * Lightweight, dependency-free SMTP client and HTML email template engine.
 * Supports plain / STARTTLS / SSL, AUTH LOGIN, multi-recipient TO/CC/BCC,
 * proper MIME for HTML + plain-text alternative, and admin-controlled config.
 */
if (!defined('NIIB_INIT')) { http_response_code(403); exit('Forbidden'); }

/* =====================================================================
   Public API
   ===================================================================== */

/**
 * Send an email via SMTP (or PHP mail() fallback if SMTP disabled).
 *
 * @param string|array $to       Single address, or array of addresses, or ['email'=>..,'name'=>..]
 * @param string       $subject  Email subject
 * @param string       $htmlBody Full HTML body
 * @param array        $opts     { reply_to, cc, bcc, text_body, attachments[] }
 * @return array       ['ok' => bool, 'error' => ?string, 'log' => array]
 */
function niib_mail_send($to, string $subject, string $htmlBody, array $opts = []): array {
    $cfg = niib_mail_config();
    $log = [];

    // Normalize recipients
    $toList = niib_mail_normalize_recipients($to);
    if (empty($toList)) return ['ok'=>false, 'error'=>'No valid recipient address', 'log'=>$log];

    // Build text alternative (strip tags if not provided)
    $textBody = $opts['text_body'] ?? niib_mail_html_to_text($htmlBody);

    if (empty($cfg['smtp_enabled']) || $cfg['smtp_host'] === '') {
        // Fallback to PHP mail() if SMTP not configured
        return niib_mail_via_php($toList, $subject, $htmlBody, $textBody, $opts, $cfg);
    }

    return niib_mail_via_smtp($toList, $subject, $htmlBody, $textBody, $opts, $cfg);
}

/**
 * Render a branded HTML email from a title + body content.
 * The header includes the site logo, content block is rendered, footer has contact info.
 *
 * @param string $title       Big heading shown at top of the email body
 * @param string $intro       Lead paragraph below the title
 * @param array  $rows        [['label'=>'Name','value'=>'John'], ...] for the data table (optional)
 * @param string $bodyHtml    Additional free-form HTML appended below the table
 * @param string $ctaLabel    Optional CTA button label
 * @param string $ctaUrl      Optional CTA button URL
 */
function niib_mail_template(string $title, string $intro = '', array $rows = [], string $bodyHtml = '', string $ctaLabel = '', string $ctaUrl = ''): string {
    $brand     = htmlspecialchars(setting('site_name', 'NIIB Institute'), ENT_QUOTES);
    $tagline   = htmlspecialchars(setting('site_tagline', ''), ENT_QUOTES);
    $logoUrl   = niib_mail_absolute_logo();
    $phone     = htmlspecialchars(setting('site_phone', ''), ENT_QUOTES);
    $email     = htmlspecialchars(setting('site_email', ''), ENT_QUOTES);
    $addr      = htmlspecialchars(setting('site_address', ''), ENT_QUOTES);
    $year      = date('Y');
    $siteUrl   = SITE_URL;

    $rowsHtml = '';
    if ($rows) {
        $rowsHtml .= '<table role="presentation" cellpadding="0" cellspacing="0" style="width:100%;margin:24px 0 0;border-collapse:collapse;background:#f6f3f2;border-radius:8px;overflow:hidden;">';
        foreach ($rows as $r) {
            $label = htmlspecialchars($r['label'] ?? '', ENT_QUOTES);
            $value = $r['value'] ?? '';
            if (!is_string($value)) $value = (string)$value;
            $value = nl2br(htmlspecialchars($value, ENT_QUOTES));
            $rowsHtml .= '<tr>'
                       . '<td style="padding:12px 18px;border-bottom:1px solid #eae7e7;width:34%;color:#49454e;font-size:12px;text-transform:uppercase;letter-spacing:0.12em;font-weight:700;vertical-align:top;">' . $label . '</td>'
                       . '<td style="padding:12px 18px;border-bottom:1px solid #eae7e7;color:#1c1b1b;font-size:15px;vertical-align:top;">' . $value . '</td>'
                       . '</tr>';
        }
        $rowsHtml .= '</table>';
    }

    $ctaHtml = '';
    if ($ctaLabel !== '' && $ctaUrl !== '') {
        $ctaHtml = '<table role="presentation" cellpadding="0" cellspacing="0" style="margin:32px auto 0;"><tr><td style="background:#fe5e1e;border-radius:4px;">'
                 . '<a href="' . htmlspecialchars($ctaUrl, ENT_QUOTES) . '" style="display:inline-block;padding:14px 36px;color:#ffffff;text-decoration:none;font-family:Manrope,Arial,sans-serif;font-weight:700;font-size:13px;letter-spacing:0.18em;text-transform:uppercase;">'
                 . htmlspecialchars($ctaLabel, ENT_QUOTES) . '</a></td></tr></table>';
    }

    $logoBlock = $logoUrl
        ? '<img src="' . htmlspecialchars($logoUrl, ENT_QUOTES) . '" alt="' . $brand . '" width="180" style="height:auto;display:block;margin:0 auto;max-width:200px;">'
        : '<div style="font-family:Georgia,serif;font-size:32px;font-weight:700;color:#ffffff;letter-spacing:-0.02em;">' . $brand . '</div>';

    return <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{$title}</title>
</head>
<body style="margin:0;padding:0;background:#f0eded;font-family:Manrope,Arial,sans-serif;-webkit-font-smoothing:antialiased;color:#1c1b1b;">
<table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background:#f0eded;padding:24px 12px;">
  <tr>
    <td align="center">
      <table role="presentation" cellpadding="0" cellspacing="0" width="600" style="max-width:600px;background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 4px 12px rgba(23,3,56,.08);">

        <!-- Header with logo -->
        <tr>
          <td style="background:linear-gradient(135deg,#170338,#2d1b4e);padding:36px 24px;text-align:center;">
            {$logoBlock}
            <div style="margin-top:14px;font-family:Georgia,serif;font-style:italic;color:rgba(255,255,255,0.7);font-size:13px;">{$tagline}</div>
          </td>
        </tr>

        <!-- Orange accent line -->
        <tr><td style="height:4px;background:#fe5e1e;"></td></tr>

        <!-- Body -->
        <tr>
          <td style="padding:40px 32px 32px;">
            <h1 style="margin:0 0 16px;font-family:Georgia,serif;font-size:28px;line-height:1.2;color:#170338;font-weight:600;letter-spacing:-0.01em;">{$title}</h1>
            <p style="margin:0;color:#49454e;font-size:15px;line-height:1.65;">{$intro}</p>
            {$rowsHtml}
            <div style="margin-top:24px;color:#49454e;font-size:15px;line-height:1.65;">{$bodyHtml}</div>
            {$ctaHtml}
          </td>
        </tr>

        <!-- Divider -->
        <tr><td style="padding:0 32px;"><div style="height:1px;background:#eae7e7;"></div></td></tr>

        <!-- Footer -->
        <tr>
          <td style="padding:28px 32px;background:#f6f3f2;">
            <table role="presentation" cellpadding="0" cellspacing="0" width="100%">
              <tr>
                <td style="font-size:11px;color:#7a757f;letter-spacing:0.1em;text-transform:uppercase;font-weight:700;padding-bottom:10px;">Contact</td>
              </tr>
              <tr>
                <td style="font-size:13px;color:#49454e;line-height:1.7;">
                  {$addr}<br>
                  <a href="tel:{$phone}" style="color:#170338;text-decoration:none;">{$phone}</a>
                  &nbsp;&middot;&nbsp;
                  <a href="mailto:{$email}" style="color:#170338;text-decoration:none;">{$email}</a>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- Brand bar -->
        <tr>
          <td style="background:#170338;padding:18px 32px;text-align:center;font-size:11px;color:rgba(235,221,255,0.55);letter-spacing:0.15em;text-transform:uppercase;font-weight:700;">
            &copy; {$year} {$brand} &middot; <a href="{$siteUrl}" style="color:rgba(235,221,255,0.7);text-decoration:none;">{$siteUrl}</a>
          </td>
        </tr>
      </table>

      <div style="margin-top:16px;font-size:11px;color:#7a757f;text-align:center;letter-spacing:0.08em;">
        You're receiving this email because someone submitted a form on our website.
      </div>
    </td>
  </tr>
</table>
</body>
</html>
HTML;
}

/* =====================================================================
   Internals
   ===================================================================== */

function niib_mail_config(): array {
    return [
        'smtp_enabled'    => (int)setting('smtp_enabled', '0'),
        'smtp_host'       => trim(setting('smtp_host', '')),
        'smtp_port'       => (int)setting('smtp_port', '587'),
        'smtp_encryption' => strtolower(trim(setting('smtp_encryption', 'tls'))),
        'smtp_username'   => trim(setting('smtp_username', '')),
        'smtp_password'   => setting('smtp_password', ''),
        'smtp_from_email' => trim(setting('smtp_from_email', '')) ?: trim(setting('site_email', '')),
        'smtp_from_name'  => trim(setting('smtp_from_name', 'NIIB Institute')),
        'smtp_to_admin'   => trim(setting('smtp_to_admin', '')) ?: trim(setting('site_email', '')),
        'smtp_send_confirmation' => (int)setting('smtp_send_confirmation', '1'),
        'smtp_timeout'    => max(5, (int)setting('smtp_timeout', '15')),
    ];
}

function niib_mail_to_admin(): string {
    return niib_mail_config()['smtp_to_admin'];
}

function niib_mail_normalize_recipients($input): array {
    if (is_string($input)) {
        $list = preg_split('/[,;]/', $input);
        $out = [];
        foreach ($list as $r) {
            $r = trim($r);
            if ($r !== '' && filter_var($r, FILTER_VALIDATE_EMAIL)) $out[] = ['email'=>$r, 'name'=>''];
        }
        return $out;
    }
    if (is_array($input)) {
        if (isset($input['email'])) return [['email'=>$input['email'], 'name'=>$input['name'] ?? '']];
        $out = [];
        foreach ($input as $r) {
            if (is_string($r) && filter_var($r, FILTER_VALIDATE_EMAIL)) $out[] = ['email'=>$r, 'name'=>''];
            elseif (is_array($r) && !empty($r['email']) && filter_var($r['email'], FILTER_VALIDATE_EMAIL)) $out[] = ['email'=>$r['email'], 'name'=>$r['name'] ?? ''];
        }
        return $out;
    }
    return [];
}

function niib_mail_addr_format(array $r): string {
    if (!empty($r['name'])) {
        $name = preg_replace('/["\r\n]/', '', $r['name']);
        return '"' . $name . '" <' . $r['email'] . '>';
    }
    return $r['email'];
}

function niib_mail_html_to_text(string $html): string {
    $t = preg_replace('~<br\s*/?>~i', "\n", $html);
    $t = preg_replace('~</p>~i', "\n\n", $t);
    $t = preg_replace('~</li>~i', "\n", $t);
    $t = strip_tags($t);
    $t = html_entity_decode($t, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return trim(preg_replace("/\n{3,}/", "\n\n", $t));
}

function niib_mail_absolute_logo(): string {
    $logo = setting('site_logo', '');
    if (!$logo) return '';
    if (preg_match('~^https?://~i', $logo)) return $logo;
    return rtrim(SITE_URL, '/') . '/' . ltrim($logo, '/');
}

/* ----- Fallback: PHP mail() ----- */
function niib_mail_via_php(array $toList, string $subject, string $htmlBody, string $textBody, array $opts, array $cfg): array {
    $headers = [];
    $boundary = '=_niib_' . bin2hex(random_bytes(8));
    $fromName = $cfg['smtp_from_name'];
    $fromEmail = $cfg['smtp_from_email'] ?: ('noreply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
    $headers[] = 'From: "' . $fromName . '" <' . $fromEmail . '>';
    if (!empty($opts['reply_to'])) $headers[] = 'Reply-To: ' . $opts['reply_to'];
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-Type: multipart/alternative; boundary="' . $boundary . '"';
    $headers[] = 'X-Mailer: NIIB-Mailer';

    $body  = "--$boundary\r\n";
    $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $body .= $textBody . "\r\n\r\n";
    $body .= "--$boundary\r\n";
    $body .= "Content-Type: text/html; charset=UTF-8\r\n";
    $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $body .= $htmlBody . "\r\n\r\n";
    $body .= "--$boundary--";

    $toString = implode(', ', array_map('niib_mail_addr_format', $toList));
    $ok = @mail($toString, $subject, $body, implode("\r\n", $headers));
    return ['ok'=>(bool)$ok, 'error'=>$ok ? null : 'PHP mail() returned false (SMTP disabled in admin or server has no mail)', 'log'=>['method'=>'php_mail']];
}

/* ----- Real SMTP ----- */
function niib_mail_via_smtp(array $toList, string $subject, string $htmlBody, string $textBody, array $opts, array $cfg): array {
    $log = [];
    $err = null;
    $socket = null;
    try {
        $host    = $cfg['smtp_host'];
        $port    = $cfg['smtp_port'];
        $enc     = $cfg['smtp_encryption'];
        $timeout = $cfg['smtp_timeout'];

        $prefix = ($enc === 'ssl') ? 'ssl://' : '';
        $err_no = 0; $err_str = '';
        $socket = @stream_socket_client($prefix . $host . ':' . $port, $err_no, $err_str, $timeout, STREAM_CLIENT_CONNECT);
        if (!$socket) throw new Exception("Connect failed: $err_str ($err_no)");
        stream_set_timeout($socket, $timeout);

        $read = function() use ($socket, &$log) {
            $out = '';
            while (!feof($socket)) {
                $line = fgets($socket, 512);
                if ($line === false) break;
                $out .= $line;
                if (preg_match('/^\d{3} /', $line)) break;
            }
            $log[] = '< ' . trim($out);
            return $out;
        };
        $send = function($cmd, $hide=false) use ($socket, &$log) {
            $log[] = '> ' . ($hide ? '[hidden]' : trim($cmd));
            fwrite($socket, $cmd . "\r\n");
        };
        $expect = function($resp, $code) {
            if (!preg_match('/^' . $code . '/', $resp)) {
                throw new Exception('SMTP error: ' . trim($resp));
            }
        };

        // Greeting
        $expect($read(), '220');

        // EHLO
        $hostname = $_SERVER['HTTP_HOST'] ?? gethostname() ?: 'localhost';
        $send("EHLO " . $hostname);
        $ehlo = $read();
        $expect($ehlo, '250');

        // STARTTLS
        if ($enc === 'tls') {
            $send("STARTTLS");
            $expect($read(), '220');
            $crypto = STREAM_CRYPTO_METHOD_TLS_CLIENT;
            if (defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')) $crypto |= STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
            if (defined('STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT')) $crypto |= STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT;
            if (!@stream_socket_enable_crypto($socket, true, $crypto)) {
                throw new Exception('STARTTLS upgrade failed');
            }
            // Re-EHLO after STARTTLS
            $send("EHLO " . $hostname);
            $expect($read(), '250');
        }

        // AUTH LOGIN
        if ($cfg['smtp_username'] !== '') {
            $send("AUTH LOGIN");
            $expect($read(), '334');
            $send(base64_encode($cfg['smtp_username']), true);
            $expect($read(), '334');
            $send(base64_encode($cfg['smtp_password']), true);
            $expect($read(), '235');
        }

        // MAIL FROM
        $from = $cfg['smtp_from_email'];
        $send("MAIL FROM:<" . $from . ">");
        $expect($read(), '250');

        // RCPT TO
        $allRcpt = $toList;
        foreach (niib_mail_normalize_recipients($opts['cc'] ?? '') as $r) $allRcpt[] = $r;
        foreach (niib_mail_normalize_recipients($opts['bcc'] ?? '') as $r) $allRcpt[] = $r;
        foreach ($allRcpt as $r) {
            $send("RCPT TO:<" . $r['email'] . ">");
            $expect($read(), '250');
        }

        // DATA
        $send("DATA");
        $expect($read(), '354');

        // Build message
        $boundary = '=_niib_' . bin2hex(random_bytes(8));
        $headers = [];
        $headers[] = 'Date: ' . date('r');
        $headers[] = 'From: "' . preg_replace('/["\r\n]/','', $cfg['smtp_from_name']) . '" <' . $from . '>';
        $headers[] = 'To: ' . implode(', ', array_map('niib_mail_addr_format', $toList));
        if (!empty($opts['cc'])) {
            $ccList = niib_mail_normalize_recipients($opts['cc']);
            if ($ccList) $headers[] = 'Cc: ' . implode(', ', array_map('niib_mail_addr_format', $ccList));
        }
        if (!empty($opts['reply_to']) && filter_var($opts['reply_to'], FILTER_VALIDATE_EMAIL)) {
            $headers[] = 'Reply-To: <' . $opts['reply_to'] . '>';
        }
        $headers[] = 'Subject: =?UTF-8?B?' . base64_encode($subject) . '?=';
        $headers[] = 'Message-ID: <' . bin2hex(random_bytes(8)) . '@' . $hostname . '>';
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: multipart/alternative; boundary="' . $boundary . '"';
        $headers[] = 'X-Mailer: NIIB-Mailer/1.0';

        $body  = "--$boundary\r\n";
        $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
        $body .= $textBody . "\r\n";
        $body .= "--$boundary\r\n";
        $body .= "Content-Type: text/html; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
        $body .= $htmlBody . "\r\n";
        $body .= "--$boundary--\r\n";

        // Dot-stuff per RFC 5321
        $payload = implode("\r\n", $headers) . "\r\n\r\n" . $body;
        $payload = preg_replace('/^\./m', '..', $payload);

        fwrite($socket, $payload . "\r\n.\r\n");
        $expect($read(), '250');

        $send("QUIT");
        @fclose($socket);

        return ['ok'=>true, 'error'=>null, 'log'=>$log];
    } catch (Throwable $e) {
        if ($socket) @fclose($socket);
        return ['ok'=>false, 'error'=>$e->getMessage(), 'log'=>$log];
    }
}

/* =====================================================================
   Convenience wrappers used by the form APIs
   ===================================================================== */

/** Notify admin AND optionally confirm to the submitter. */
function niib_mail_notify_admin(string $subject, string $intro, array $rows, string $replyToEmail = '', string $confirmToEmail = ''): array {
    $cfg = niib_mail_config();
    $results = ['admin' => null, 'user' => null];

    // Admin copy
    $adminTo = niib_mail_to_admin();
    if ($adminTo) {
        $html = niib_mail_template(
            $subject,
            $intro,
            $rows,
            '<p style="color:#7a757f;font-size:13px;margin-top:24px;">Submitted at ' . date('d M Y, H:i') . ' &middot; from ' . htmlspecialchars($_SERVER['REMOTE_ADDR'] ?? 'unknown', ENT_QUOTES) . '</p>'
        );
        $results['admin'] = niib_mail_send($adminTo, '[NIIB] ' . $subject, $html, ['reply_to' => $replyToEmail]);
    }

    // User confirmation
    if ($confirmToEmail && $cfg['smtp_send_confirmation'] && filter_var($confirmToEmail, FILTER_VALIDATE_EMAIL)) {
        $confirmHtml = niib_mail_template(
            'Thank you for getting in touch',
            'We have received your enquiry and our admissions team will respond within 4 working hours.',
            $rows,
            '<p>In the meantime, feel free to explore our <a href="' . SITE_URL . '/courses" style="color:#170338;font-weight:600;">course catalogue</a> or call us directly &mdash; the team is happy to walk you through any questions.</p>',
            'Browse Courses',
            SITE_URL . '/courses'
        );
        $results['user'] = niib_mail_send(['email'=>$confirmToEmail], 'We have received your enquiry — ' . setting('site_name','NIIB Institute'), $confirmHtml);
    }

    return $results;
}
