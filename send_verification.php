<?php
// send_verification.php
session_start();

// Only allow Barangay Officials
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Barangay Official') {
    header("Location: login.php");
    exit();
}

require __DIR__ . '/vendor/autoload.php'; // PHPMailer via Composer
require 'mail_config.php';
require 'db_connect.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: admin-dashboard.php");
    exit();
}

$id       = intval($_POST['id'] ?? 0);
$subject  = trim($_POST['subject'] ?? '');
$message  = trim($_POST['message'] ?? '');
$sendType = trim($_POST['send_type'] ?? 'verify'); // 'verify' or 'notify'

if ($id <= 0) {
    echo "<script>alert('Invalid user.');window.location='admin-dashboard.php';</script>";
    exit();
}

// Fetch user
$stmt = $conn->prepare("SELECT id, fullname, email, email_verified, verification_token FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo "<script>alert('User not found.');window.location='admin-dashboard.php';</script>";
    exit();
}

$user = $res->fetch_assoc();

// Ensure token exists for verification emails
if ($sendType === 'verify' && (empty($user['verification_token']) || is_null($user['verification_token']))) {
    $token = bin2hex(random_bytes(32));
    $upd = $conn->prepare("UPDATE users SET verification_token=? WHERE id=?");
    $upd->bind_param("si", $token, $id);
    $upd->execute();
    $user['verification_token'] = $token;
}

// Build verification link (for verify type)
$verifyLink = APP_BASE_URL . "/verify.php?id=" . $user['id'] . "&token=" . urlencode($user['verification_token'] ?? '');

// Default subjects/messages if blank
if ($sendType === 'verify') {
    if ($subject === '') $subject = "Verify your BahaShield account";
    if ($message === '') {
        $message = "
            Hi {$user['fullname']},<br><br>
            Welcome to <b>🌊 BahaShield</b> — Arduino-based Flood & Rainfall Alert System.<br>
            Please verify your email to complete your registration:<br><br>
            <a href='{$verifyLink}' style='background:#007bff;color:#fff;padding:10px 16px;border-radius:6px;text-decoration:none;'>Verify My Email</a><br><br>
            Or copy this link into your browser:<br>
            <span style='word-break:break-all;'>{$verifyLink}</span><br><br>
            Stay safe,<br>
            <b>🌊 BahaShield Team</b>
        ";
    }
} else {
    if ($subject === '') $subject = "You are registered to BahaShield";
    if ($message === '') {
        $message = "
            Hi {$user['fullname']},<br><br>
            You are now registered to <b>🌊 BahaShield</b> — our Flood & Rainfall Alert System with real-time SMS notifications.<br>
            If you have not verified your email yet, please verify here:<br><br>
            <a href='{$verifyLink}' style='background:#007bff;color:#fff;padding:10px 16px;border-radius:6px;text-decoration:none;'>Verify My Email</a><br><br>
            Thank you,<br>
            <b>🌊 BahaShield Team</b>
        ";
    }
}

try {
    $mail = new PHPMailer(true);

    // ✅ Added: Force UTF-8 so emojis (🌊) display correctly
    $mail->CharSet = "UTF-8";  

    // Server settings
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USERNAME;
    $mail->Password   = SMTP_PASSWORD;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = SMTP_PORT;

    // Recipients
    $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
    $mail->addAddress($user['email'], $user['fullname']);

    // Content
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body    = emailTemplate($subject, $message);
    $mail->AltBody = strip_tags(str_replace(["<br>", "<br/>", "<br />"], PHP_EOL, $message));

    $mail->send();

    //  Update email_verified = 1 after successful email sending
    $updStatus = $conn->prepare("UPDATE users SET email_verified = 1 WHERE id = ?");
    $updStatus->bind_param("i", $id);
    $updStatus->execute();

    echo "<script>alert('Email sent to {$user['email']} and status updated to verified');window.location='admin-dashboard.php';</script>";
} catch (Exception $e) {
    $err = addslashes($mail->ErrorInfo);
    echo "<script>alert('Email failed: {$err}');window.location='admin-dashboard.php';</script>";
}

function emailTemplate($title, $html)
{
    return "
    <div style='background:#f4f6f9;padding:20px;font-family:Segoe UI,Roboto,Arial,sans-serif'>
      <div style='max-width:560px;margin:0 auto;background:#fff;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.06);overflow:hidden'>
        <div style='background:linear-gradient(135deg,#007bff,#0056b3);padding:16px 20px;color:#fff'>
          <h2 style='margin:0;font-size:18px'>🌊 BahaShield</h2>
          <div style='font-size:12px;opacity:.9'>Arduino Flood & Rainfall Alert System</div>
        </div>
        <div style='padding:20px'>
          <h3 style='margin-top:0;color:#222;font-size:16px'>{$title}</h3>
          <div style='font-size:14px;color:#333;line-height:1.6'>{$html}</div>
        </div>
        <div style='padding:12px 20px;background:#fafbfc;border-top:1px solid #eee;color:#666;font-size:12px'>
          This is a system email from 🌊 BahaShield. Please do not reply.
        </div>
      </div>
    </div>";
}
