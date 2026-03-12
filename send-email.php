<?php
/**
 * send-email.php — Portfolio contact form mailer
 * Uses PHP built-in mail() — no SMTP, no app password, no libraries.
 * Works on any shared hosting (Hostinger, cPanel, etc.) out of the box.
 */

// ── CONFIG ─────────────────────────────────────────────────────────
define('YOUR_EMAIL', 'athithyanadhi00@gmail.com');
// ──────────────────────────────────────────────────────────────────

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.html');
    exit;
}

// ── Read & sanitise ────────────────────────────────────────────────
$name    = isset($_POST['name'])    ? trim(strip_tags($_POST['name']))    : '';
$email   = isset($_POST['email'])   ? trim(strip_tags($_POST['email']))   : '';
$subject = isset($_POST['subject']) ? trim(strip_tags($_POST['subject'])) : '';
$message = isset($_POST['message']) ? trim(strip_tags($_POST['message'])) : '';

// ── Validate ───────────────────────────────────────────────────────
$errors = [];
if (!$name)    $errors[] = 'Name is required.';
if (!$email)   $errors[] = 'Email is required.';
if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email.';
if (!$message) $errors[] = 'Message is required.';

if ($errors) {
    header('Location: index.html?status=error&msg=' . urlencode(implode(' ', $errors)));
    exit;
}

$mailSubject = $subject ? "Portfolio Contact: {$subject}" : 'Portfolio Contact Form Submission';
$safeName    = htmlspecialchars($name,    ENT_QUOTES, 'UTF-8');
$safeEmail   = htmlspecialchars($email,   ENT_QUOTES, 'UTF-8');
$safeSubject = htmlspecialchars($subject ?: '—', ENT_QUOTES, 'UTF-8');
$safeMessage = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

// ── Email to YOU ───────────────────────────────────────────────────
$ownerHeaders  = "MIME-Version: 1.0\r\n";
$ownerHeaders .= "Content-Type: text/html; charset=UTF-8\r\n";
$ownerHeaders .= "From: Portfolio Contact <" . YOUR_EMAIL . ">\r\n";
$ownerHeaders .= "Reply-To: {$safeName} <{$safeEmail}>\r\n";

$ownerBody = "
<div style='font-family:sans-serif;max-width:600px;margin:0 auto;padding:24px;border:1px solid #e5e7eb;border-radius:8px;'>
  <h2 style='margin:0 0 20px;color:#0d0d0d;border-bottom:2px solid #c8410a;padding-bottom:10px;'>
    &#128236; New Portfolio Message
  </h2>
  <table style='width:100%;border-collapse:collapse;'>
    <tr>
      <td style='padding:8px 0;color:#6b6560;font-size:13px;width:90px;vertical-align:top;'>FROM</td>
      <td style='padding:8px 0;color:#0d0d0d;font-weight:600;'>{$safeName}</td>
    </tr>
    <tr>
      <td style='padding:8px 0;color:#6b6560;font-size:13px;vertical-align:top;'>EMAIL</td>
      <td style='padding:8px 0;'><a href='mailto:{$safeEmail}' style='color:#c8410a;'>{$safeEmail}</a></td>
    </tr>
    <tr>
      <td style='padding:8px 0;color:#6b6560;font-size:13px;vertical-align:top;'>SUBJECT</td>
      <td style='padding:8px 0;color:#0d0d0d;'>{$safeSubject}</td>
    </tr>
    <tr>
      <td style='padding:8px 0;color:#6b6560;font-size:13px;vertical-align:top;'>MESSAGE</td>
      <td style='padding:8px 0;color:#0d0d0d;line-height:1.7;white-space:pre-wrap;'>{$safeMessage}</td>
    </tr>
  </table>
  <p style='margin:24px 0 0;font-size:12px;color:#9ca3af;'>
    Sent from your portfolio contact form &bull; Hit reply to respond directly to {$safeName}
  </p>
</div>";

// ── Auto-reply to the SENDER ───────────────────────────────────────
$senderHeaders  = "MIME-Version: 1.0\r\n";
$senderHeaders .= "Content-Type: text/html; charset=UTF-8\r\n";
$senderHeaders .= "From: Athithyan P <" . YOUR_EMAIL . ">\r\n";

$senderBody = "
<div style='font-family:sans-serif;max-width:600px;margin:0 auto;padding:24px;border:1px solid #e5e7eb;border-radius:8px;'>
  <h2 style='margin:0 0 20px;color:#0d0d0d;border-bottom:2px solid #c8410a;padding-bottom:10px;'>
    Hey {$safeName}! &#128075;
  </h2>
  <p style='color:#6b6560;line-height:1.75;'>
    Thanks for getting in touch! I've received your message and will reply within 24 hours.
  </p>
  <div style='background:#f7f3ed;border-left:3px solid #c8410a;padding:16px 20px;margin:20px 0;border-radius:0 6px 6px 0;'>
    <p style='margin:0;color:#0d0d0d;font-style:italic;white-space:pre-wrap;'>&ldquo;{$safeMessage}&rdquo;</p>
  </div>
  <p style='color:#6b6560;line-height:1.75;'>
    In the meantime, feel free to check out my work or connect with me.
  </p>
  <p style='margin:20px 0 0;color:#9ca3af;font-size:12px;'>
    &mdash; Athithyan P &nbsp;|&nbsp; AI &amp; Full-Stack Developer &nbsp;|&nbsp; Tiruppur, Tamil Nadu
  </p>
</div>";

// ── Send ───────────────────────────────────────────────────────────
$sent = mail(YOUR_EMAIL, $mailSubject, $ownerBody, $ownerHeaders);
mail($email, "Thanks for reaching out — I'll get back to you soon!", $senderBody, $senderHeaders);

if ($sent) {
    header('Location: index.html?status=success');
} else {
    header('Location: index.html?status=error&msg=' . urlencode('Mail could not be sent. Please email me directly.'));
}
exit;