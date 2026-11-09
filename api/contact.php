<?php
/**
 * Consummo contact and demo form handler.
 *
 * Receives the Contact Us and Book a Demo forms, checks them, blocks spam,
 * emails the lead to the team, sends the visitor a confirmation and keeps a
 * private CSV copy of every lead.
 *
 * Settings live in api/config.php (not in Git). Copy config.sample.php to
 * config.php on the server and fill it in.
 */
declare(strict_types=1);

$configFile = __DIR__ . '/config.php';
if (!is_file($configFile)) {
    respond(false, 'The contact form is not configured yet. Please message us on WhatsApp.', 503);
}
$cfg = require $configFile;

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    respond(false, 'Method not allowed.', 405);
}

// ---------- Read and clean input ----------
function field(string $key, int $max = 200): string
{
    $v = $_POST[$key] ?? '';
    if (is_array($v)) {
        return '';
    }
    $v = trim(str_replace(["\r\n", "\r"], "\n", (string) $v));
    return mb_substr($v, 0, $max);
}
function oneLine(string $v): string
{
    return trim(preg_replace('/[\r\n\t]+/', ' ', $v) ?? '');
}

$type     = field('form_type', 20) === 'demo' ? 'demo' : 'contact';
$name     = oneLine(field('name', 100));
$email    = oneLine(field('email', 150));
$company  = oneLine(field('company', 120));
$role     = oneLine(field('role', 100));
$phone    = oneLine(field('phone', 30));
$business = oneLine(field('business', 60));
$size     = oneLine(field('size', 60));
$topic    = oneLine(field('topic', 60));
$message  = field('message', 2000);
$waOk     = field('whatsapp_ok', 5) === 'yes';
$consent  = field('consent', 5) === 'yes';
$page     = oneLine(field('page', 200));
$modules  = array_values(array_filter(array_map(
    fn($m) => oneLine(mb_substr((string) $m, 0, 40)),
    is_array($_POST['modules'] ?? null) ? $_POST['modules'] : []
)));

// ---------- Spam checks (silently accept so bots learn nothing) ----------
$honeypot = field('website', 200);
$elapsed  = (int) field('elapsed', 12);
if ($honeypot !== '' || ($elapsed > 0 && $elapsed < 2500)) {
    respond(true);
}

// ---------- Validation ----------
$errors = [];
if (mb_strlen($name) < 2) {
    $errors[] = 'Please enter your name.';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address.';
}
if ($type === 'demo' && $company === '') {
    $errors[] = 'Please enter your company name.';
}
if ($type === 'demo' && $business === '') {
    $errors[] = 'Please choose your business.';
}
if ($type === 'contact' && mb_strlen($message) < 5) {
    $errors[] = 'Please write a short message.';
}
if (!$consent) {
    $errors[] = 'Please agree to be contacted about your request.';
}
if ($errors) {
    respond(false, implode(' ', $errors), 422);
}

// ---------- Rate limit: max N submissions per IP per hour ----------
$dataDir = __DIR__ . '/data';
if (!is_dir($dataDir)) {
    @mkdir($dataDir, 0750, true);
}
$ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$limit = (int) ($cfg['rate_limit_per_hour'] ?? 5);
$rlFile = $dataDir . '/ratelimit.json';
$rl = is_file($rlFile) ? (json_decode((string) file_get_contents($rlFile), true) ?: []) : [];
$now = time();
$key = hash('sha256', $ip);
$rl[$key] = array_values(array_filter($rl[$key] ?? [], fn($t) => $t > $now - 3600));
if (count($rl[$key]) >= $limit) {
    respond(false, 'Too many messages from your connection. Please try again later, or message us on WhatsApp.', 429);
}
$rl[$key][] = $now;
foreach ($rl as $k => $times) {
    if (!array_filter($times, fn($t) => $t > $now - 3600)) {
        unset($rl[$k]);
    }
}
@file_put_contents($rlFile, json_encode($rl), LOCK_EX);

// ---------- Business label ----------
$BUSINESSES = [
    'food-packaged-foods' => 'Food & Packaged Foods', 'beverages' => 'Beverages', 'beauty-personal-care' => 'Beauty & Personal Care',
    'home-care-hygiene' => 'Home Care & Hygiene', 'baby-family-care' => 'Baby & Family Care', 'pet-care' => 'Pet Care',
    'grocery-supermarkets' => 'Grocery & Supermarkets', 'department-general-merchandise' => 'Department & General Merchandise',
    'fashion-apparel' => 'Fashion & Apparel', 'beauty-personal-care-retail' => 'Beauty & Personal Care Retail',
    'electronics-appliances' => 'Electronics & Appliances', 'pharmacy-healthcare-retail' => 'Pharmacy & Healthcare Retail',
    'home-lifestyle' => 'Home & Lifestyle', 'convenience-discount-retail' => 'Convenience & Discount Retail',
    'wholesale-cash-carry' => 'Wholesale & Cash-and-Carry', 'ecommerce-quick-commerce' => 'E-commerce & Quick Commerce', 'other' => 'Other',
];
$businessLabel = $BUSINESSES[$business] ?? $business;

// ---------- Save a private copy of the lead ----------
$received = date('Y-m-d H:i:s T');
$row = [$received, $type, $name, $email, $company, $role, $phone, $waOk ? 'yes' : 'no', $businessLabel, $size,
    implode('; ', $modules), $topic, str_replace("\n", ' / ', $message), $page];
$csv = $dataDir . '/leads.csv';
$isNew = !is_file($csv);
if ($fh = @fopen($csv, 'ab')) {
    flock($fh, LOCK_EX);
    if ($isNew) {
        fputcsv($fh, ['received', 'type', 'name', 'email', 'company', 'role', 'phone', 'whatsapp_ok', 'business', 'revenue',
            'interested_in', 'topic', 'message', 'page']);
    }
    fputcsv($fh, $row);
    flock($fh, LOCK_UN);
    fclose($fh);
}

// ---------- Email to the team ----------
$e = fn(string $s) => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
$rows = [
    'Name' => $name, 'Email' => $email, 'Company' => $company, 'Job title' => $role, 'Phone' => $phone,
    'WhatsApp OK' => $phone !== '' ? ($waOk ? 'Yes' : 'No') : '', 'Business' => $businessLabel, 'Annual revenue' => $size,
    'Interested in' => implode(', ', $modules), 'Topic' => $topic, 'Message' => $message, 'Sent from' => $page, 'Received' => $received,
];
$rows = array_filter($rows, fn($v) => $v !== '');

$kind = $type === 'demo' ? 'Demo request' : 'Contact message';
$subject = $kind . ': ' . $name . ($company !== '' ? ', ' . $company : '') . ($businessLabel !== '' ? ' (' . $businessLabel . ')' : '');

$waLink = '';
if ($phone !== '') {
    $digits = preg_replace('/\D+/', '', $phone) ?? '';
    if (strlen($digits) === 10) {
        $digits = '91' . $digits;
    }
    $waLink = 'https://wa.me/' . $digits;
}

$html = '<div style="font-family:Arial,Helvetica,sans-serif;max-width:640px;margin:auto;color:#1e293b">'
    . '<div style="background:#002067;color:#fff;padding:18px 22px;border-radius:12px 12px 0 0;font-size:18px;font-weight:bold">New ' . $e(strtolower($kind)) . '</div>'
    . '<table style="width:100%;border-collapse:collapse;border:1px solid #e2e8f0;border-top:0">';
foreach ($rows as $label => $value) {
    $val = $label === 'Email' ? '<a href="mailto:' . $e($value) . '">' . $e($value) . '</a>' : nl2br($e($value));
    if ($label === 'Phone' && $waLink !== '') {
        $val .= ' &nbsp;<a href="' . $e($waLink) . '" style="color:#16a34a;font-weight:bold">WhatsApp</a>';
    }
    $html .= '<tr><td style="padding:10px 14px;border-top:1px solid #e2e8f0;background:#f8fafc;width:150px;font-weight:bold;vertical-align:top">' . $e($label)
        . '</td><td style="padding:10px 14px;border-top:1px solid #e2e8f0">' . $val . '</td></tr>';
}
$html .= '</table><p style="font-size:12px;color:#64748b">Reply to this email to answer ' . $e($name) . ' directly.</p></div>';
$text = "New " . strtolower($kind) . "\n\n";
foreach ($rows as $label => $value) {
    $text .= $label . ': ' . $value . "\n";
}

$sent = sendMail($cfg, (string) $cfg['to_email'], $subject, $html, $text, $email, $name);

// ---------- Confirmation to the visitor ----------
if ($sent && !empty($cfg['send_confirmation'])) {
    $first = explode(' ', $name)[0];
    $cSubject = $type === 'demo' ? 'Your Consummo demo request' : 'We received your message';
    $cBody = $type === 'demo'
        ? 'Thanks for booking a demo. We will reply within one business day to confirm a time that suits you.'
        : 'Thanks for getting in touch. We will reply within one business day.';
    $cHtml = '<div style="font-family:Arial,Helvetica,sans-serif;max-width:560px;margin:auto;color:#1e293b">'
        . '<p>Hi ' . $e($first) . ',</p><p>' . $e($cBody) . '</p>'
        . (!empty($cfg['whatsapp_link']) ? '<p>Want to talk sooner? <a href="' . $e((string) $cfg['whatsapp_link']) . '">Message us on WhatsApp</a>.</p>' : '')
        . '<p>The Consummo team<br><a href="https://www.consummo.com">consummo.com</a></p></div>';
    sendMail($cfg, $email, $cSubject, $cHtml, strip_tags(str_replace(['<br>', '</p>'], ["\n", "\n\n"], $cHtml)), (string) ($cfg['reply_to'] ?? ''), 'Consummo');
}

if (!$sent) {
    error_log('[consummo-contact] email delivery failed for lead from ' . $email);
}
// The lead is saved even if email fails, so the visitor still gets a success message.
respond(true);

// =====================================================================

function respond(bool $ok, string $error = '', int $code = 200): void
{
    $wantsJson = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
    http_response_code($ok ? 200 : $code);
    if ($wantsJson) {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode($ok ? ['ok' => true] : ['ok' => false, 'error' => $error]);
    } else {
        $back = parse_url($_SERVER['HTTP_REFERER'] ?? '/contact/', PHP_URL_PATH) ?: '/contact/';
        if (!str_starts_with($back, '/')) {
            $back = '/contact/';
        }
        header('Location: ' . $back . ($ok ? '?sent=1#form' : '#form'), true, 303);
    }
    exit;
}

function encodeHeader(string $s): string
{
    return preg_match('/[^\x20-\x7E]/', $s) ? '=?UTF-8?B?' . base64_encode($s) . '?=' : $s;
}

/** Send via SMTP when configured, otherwise PHP mail(). */
function sendMail(array $cfg, string $to, string $subject, string $html, string $text, string $replyTo = '', string $replyName = ''): bool
{
    $from = (string) $cfg['from_email'];
    $fromName = (string) ($cfg['from_name'] ?? 'Consummo');
    $boundary = 'b' . bin2hex(random_bytes(12));
    $headers = [
        'From: ' . encodeHeader($fromName) . ' <' . $from . '>',
        'MIME-Version: 1.0',
        'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
    ];
    if ($replyTo !== '' && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
        $headers[] = 'Reply-To: ' . ($replyName !== '' ? encodeHeader($replyName) . ' ' : '') . '<' . $replyTo . '>';
    }
    $body = "--$boundary\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n"
        . chunk_split(base64_encode($text))
        . "--$boundary\r\nContent-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n"
        . chunk_split(base64_encode($html)) . "--$boundary--\r\n";

    if (!empty($cfg['smtp']['host']) && !empty($cfg['smtp']['password'])) {
        return smtpSend($cfg['smtp'], $from, $to, encodeHeader($subject), $headers, $body);
    }
    return @mail($to, encodeHeader($subject), $body, implode("\r\n", $headers), '-f' . $from);
}

function smtpSend(array $s, string $from, string $to, string $subject, array $headers, string $body): bool
{
    $port = (int) ($s['port'] ?? 465);
    $host = ($port === 465 ? 'ssl://' : 'tcp://') . $s['host'];
    $fp = @stream_socket_client($host . ':' . $port, $errno, $errstr, 15);
    if (!$fp) {
        error_log("[consummo-contact] SMTP connect failed: $errstr");
        return false;
    }
    stream_set_timeout($fp, 15);
    $read = function () use ($fp): string {
        $out = '';
        while (($line = fgets($fp, 515)) !== false) {
            $out .= $line;
            if (strlen($line) < 4 || $line[3] === ' ') {
                break;
            }
        }
        return $out;
    };
    $cmd = function (string $c, array $okCodes) use ($fp, $read): bool {
        fwrite($fp, $c . "\r\n");
        $r = $read();
        $okay = in_array((int) substr($r, 0, 3), $okCodes, true);
        if (!$okay) {
            error_log('[consummo-contact] SMTP "' . explode(' ', $c)[0] . '" failed: ' . trim($r));
        }
        return $okay;
    };
    $read();
    $ehloHost = $_SERVER['SERVER_NAME'] ?? 'localhost';
    if (!$cmd('EHLO ' . $ehloHost, [250])) {
        return false;
    }
    if ($port === 587) {
        if (!$cmd('STARTTLS', [220]) || !stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT) || !$cmd('EHLO ' . $ehloHost, [250])) {
            return false;
        }
    }
    $ok = $cmd('AUTH LOGIN', [334]) && $cmd(base64_encode((string) $s['username']), [334]) && $cmd(base64_encode((string) $s['password']), [235])
        && $cmd('MAIL FROM:<' . $from . '>', [250]) && $cmd('RCPT TO:<' . $to . '>', [250, 251]) && $cmd('DATA', [354]);
    if ($ok) {
        $msg = 'To: <' . $to . ">\r\nSubject: " . $subject . "\r\nDate: " . date('r') . "\r\nMessage-ID: <" . bin2hex(random_bytes(10)) . '@' . explode('@', $from)[1] . ">\r\n"
            . implode("\r\n", $headers) . "\r\n\r\n" . preg_replace('/^\./m', '..', $body);
        $ok = $cmd($msg . "\r\n.", [250]);
    }
    $cmd('QUIT', [221]);
    fclose($fp);
    return $ok;
}
