<?php
// ─────────────────────────────────────────────────────────────
//  send-email.php  —  Portfolio contact form mailer
//  Uses PHP's mail() OR PHPMailer + Gmail SMTP (recommended)
//
//  OPTION A — Gmail SMTP via PHPMailer (same as your Node setup)
//  ─────────────────────────────────────────────────────────────
//  1. Upload this file alongside index.html on your PHP host
//  2. Install PHPMailer:
//       composer require phpmailer/phpmailer
//     OR upload the PHPMailer src/ folder manually (see below)
//  3. Fill in YOUR_GMAIL and YOUR_APP_PASSWORD below
//
//  OPTION B — Native mail() (works on most shared hosts, no setup)
//  ─────────────────────────────────────────────────────────────
//  Set $USE_SMTP = false; below — no composer needed.
// ─────────────────────────────────────────────────────────────

// ── CONFIG ────────────────────────────────────────────────────
$USE_SMTP       = true;                        // false → use native mail()
$GMAIL_USER     = 'athithyanadhi00@gmail.com'; // your Gmail address
$GMAIL_PASS     = 'ycvv qxiz hesm qehy'; // Gmail App Password (not your login password)
$TO_EMAIL       = 'athithyanadhi00@gmail.com'; // where contact messages land
$FROM_NAME      = 'Athithyan P';
// ─────────────────────────────────────────────────────────────

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
    exit;
}

// ── Parse JSON body (fetch sends JSON, not form data) ─────────
$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

$name    = isset($data['name'])    ? trim($data['name'])    : '';
$email   = isset($data['email'])   ? trim($data['email'])   : '';
$subject = isset($data['subject']) ? trim($data['subject']) : '';
$message = isset($data['message']) ? trim($data['message']) : '';

// ── Validation ────────────────────────────────────────────────
if (!$name || !$email || !$message) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Name, email and message are required.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid email address.']);
    exit;
}

$mailSubject = $subject
    ? 'Portfolio Contact: ' . $subject
    : 'Portfolio Contact Form Submission';

// ── HTML email bodies ─────────────────────────────────────────
$msgEscaped  = nl2br(htmlspecialchars($message));
$nameEsc     = htmlspecialchars($name);
$emailEsc    = htmlspecialchars($email);
$subjectEsc  = htmlspecialchars($subject ?: '—');

$bodyToOwner = <<<HTML
<div style="font-family:sans-serif;max-width:600px;margin:0 auto;padding:24px;border:1px solid #e5e7eb;border-radius:8px;">
  <h2 style="margin:0 0 20px;color:#0d0d0d;border-bottom:2px solid #c8410a;padding-bottom:10px;">
    📬 New Portfolio Message
  </h2>
  <table style="width:100%;border-collapse:collapse;">
    <tr>
      <td style="padding:8px 0;color:#6b6560;font-size:13px;width:90px;vertical-align:top;">FROM</td>
      <td style="padding:8px 0;color:#0d0d0d;font-weight:600;">{$nameEsc}</td>
    </tr>
    <tr>
      <td style="padding:8px 0;color:#6b6560;font-size:13px;vertical-align:top;">EMAIL</td>
      <td style="padding:8px 0;"><a href="mailto:{$emailEsc}" style="color:#c8410a;">{$emailEsc}</a></td>
    </tr>
    <tr>
      <td style="padding:8px 0;color:#6b6560;font-size:13px;vertical-align:top;">SUBJECT</td>
      <td style="padding:8px 0;color:#0d0d0d;">{$subjectEsc}</td>
    </tr>
    <tr>
      <td style="padding:8px 0;color:#6b6560;font-size:13px;vertical-align:top;">MESSAGE</td>
      <td style="padding:8px 0;color:#0d0d0d;line-height:1.7;">{$msgEscaped}</td>
    </tr>
  </table>
  <p style="margin:24px 0 0;font-size:12px;color:#9ca3af;">
    Sent from your portfolio contact form • Hit reply to respond directly to {$nameEsc}
  </p>
</div>
HTML;

$bodyToSender = <<<HTML
<div style="font-family:sans-serif;max-width:600px;margin:0 auto;padding:24px;border:1px solid #e5e7eb;border-radius:8px;">
  <h2 style="margin:0 0 20px;color:#0d0d0d;border-bottom:2px solid #c8410a;padding-bottom:10px;">
    Hey {$nameEsc}! 👋
  </h2>
  <p style="color:#6b6560;line-height:1.75;">
    Thanks for getting in touch! I've received your message and will reply within 24 hours.
  </p>
  <div style="background:#f7f3ed;border-left:3px solid #c8410a;padding:16px 20px;margin:20px 0;border-radius:0 6px 6px 0;">
    <p style="margin:0;color:#0d0d0d;font-style:italic;">{$msgEscaped}</p>
  </div>
  <p style="color:#6b6560;line-height:1.75;">
    In the meantime, feel free to check out my work or connect with me.
  </p>
  <p style="margin:20px 0 0;color:#9ca3af;font-size:12px;">
    — Athithyan P &nbsp;|&nbsp; AI &amp; Full-Stack Developer &nbsp;|&nbsp; Tiruppur, Tamil Nadu
  </p>
</div>
HTML;

// ── Send ──────────────────────────────────────────────────────
try {
    if ($USE_SMTP) {
        // ── PHPMailer + Gmail SMTP ────────────────────────────
        // Requires PHPMailer. Install via composer or upload manually.
        // Composer:  composer require phpmailer/phpmailer
        // Manual:    https://github.com/PHPMailer/PHPMailer/releases
        //            upload src/ folder next to this file, then use:
        //            require 'PHPMailer/src/PHPMailer.php'; etc.

        require 'vendor/autoload.php'; // use this line if installed via composer

        // ── If installed MANUALLY (no composer), replace the line above with: ──
        // require 'PHPMailer/src/Exception.php';
        // require 'PHPMailer/src/PHPMailer.php';
        // require 'PHPMailer/src/SMTP.php';

        use PHPMailer\PHPMailer\PHPMailer;
        use PHPMailer\PHPMailer\Exception;

        // Email 1 — to you (the owner)
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $GMAIL_USER;
        $mail->Password   = $GMAIL_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom($GMAIL_USER, 'Portfolio Contact');
        $mail->addAddress($TO_EMAIL);
        $mail->addReplyTo($email, $name);   // reply goes directly to sender
        $mail->isHTML(true);
        $mail->Subject = $mailSubject;
        $mail->Body    = $bodyToOwner;
        $mail->send();

        // Email 2 — auto-reply to the sender
        $mail2 = new PHPMailer(true);
        $mail2->isSMTP();
        $mail2->Host       = 'smtp.gmail.com';
        $mail2->SMTPAuth   = true;
        $mail2->Username   = $GMAIL_USER;
        $mail2->Password   = $GMAIL_PASS;
        $mail2->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail2->Port       = 587;

        $mail2->setFrom($GMAIL_USER, $FROM_NAME);
        $mail2->addAddress($email, $name);
        $mail2->isHTML(true);
        $mail2->Subject = "Thanks for reaching out — I'll get back to you soon!";
        $mail2->Body    = $bodyToSender;
        $mail2->send();

    } else {
        // ── Native mail() fallback ────────────────────────────
        // Works on most shared hosts (cPanel, etc.) with no setup.
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: Portfolio Contact <{$GMAIL_USER}>\r\n";
        $headers .= "Reply-To: {$email}\r\n";

        mail($TO_EMAIL, $mailSubject, $bodyToOwner, $headers);

        $headers2  = "MIME-Version: 1.0\r\n";
        $headers2 .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers2 .= "From: {$FROM_NAME} <{$GMAIL_USER}>\r\n";

        mail($email, "Thanks for reaching out — I'll get back to you soon!", $bodyToSender, $headers2);
    }

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    error_log('send-email.php error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to send email. Please try again.']);
}