<?php
// send_otp_mail.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';

function sendOtpMail($toEmail, $toName, $otp) {
    // --- CONFIG: set these to your SMTP provider credentials ---
    $smtp_host = 'smtp.gmail.com';
    $smtp_user = 'bahashield@gmail.com';            // your SMTP username
    $smtp_pass = 'gbzu rtgi wgbz jfps';    // app password or API key
    $smtp_port = 587;
    $smtp_secure = 'tls'; // 'tls' or 'ssl'
    $from_email = 'bahashield@gmail.com';       // must be allowed by SMTP provider
    $from_name = 'BahaShield';

    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = $smtp_host;
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtp_user;
        $mail->Password   = $smtp_pass;
        $mail->SMTPSecure = $smtp_secure === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $smtp_port;

        $mail->setFrom($from_email, $from_name);
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);
        $mail->Subject = 'Your BahaShield Login OTP';
        $mail->Body    = "<p>Hello {$toName},</p>
                          <p>Your one-time login code (OTP) is: <strong>{$otp}</strong></p>
                          <p>This code will expire in 5 minutes.</p>";
        $mail->AltBody = "Hello {$toName},\n\nYour OTP is: {$otp}\nThis code will expire in 5 minutes.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("sendOtpMail error: " . $mail->ErrorInfo);
        return false;
    }
}
