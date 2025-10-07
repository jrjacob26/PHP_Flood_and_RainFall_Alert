<?php
// verify-otp.php
session_set_cookie_params([
  'lifetime' => 0,
  'path' => '/',
  'domain' => $_SERVER['HTTP_HOST'],
  'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
  'httponly' => true,
  'samesite' => 'Strict'
]);
session_start();

require_once 'db_connect.php';
require_once 'send_otp_mail.php';

if (!isset($_SESSION['pending_user_id'])) {
    header("Location: admin-login.php");
    exit();
}

$message = "";
$user_id = (int) $_SESSION['pending_user_id'];
$resend_cooldown = 30; // seconds
$max_otp_attempts = 5;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 🔄 Resend OTP flow
    if (isset($_POST['resend'])) {
        if (isset($_SESSION['otp_last_sent']) && (time() - $_SESSION['otp_last_sent']) < $resend_cooldown) {
            $remaining = $resend_cooldown - (time() - $_SESSION['otp_last_sent']);
            $message = "Please wait {$remaining} seconds before resending.";
        } else {
            $s = $conn->prepare("SELECT id, fullname, email FROM users WHERE id=? LIMIT 1");
            $s->bind_param("i", $user_id);
            $s->execute();
            $r = $s->get_result();
            if ($r && $r->num_rows === 1) {
                $u = $r->fetch_assoc();

                // Generate new OTP
                $otp_plain = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                $otp_hash  = password_hash($otp_plain, PASSWORD_DEFAULT); // ✅ store hash
                $expiry    = date("Y-m-d H:i:s", time() + 300);

                $up = $conn->prepare("UPDATE users SET otp=?, otp_expiry=? WHERE id=?");
                $up->bind_param("ssi", $otp_hash, $expiry, $user_id);
                $ok = $up->execute();

                if ($ok && sendOtpMail($u['email'], $u['fullname'], $otp_plain)) {
                    $_SESSION['otp_last_sent'] = time();
                    $_SESSION['otp_attempts'] = 0;
                    $message = "✅ OTP resent. Check your email.";
                } else {
                    $message = "⚠️ Unable to resend OTP. Check SMTP settings or try later.";
                }
                $up->close();
            } else {
                $message = "User not found.";
            }
            $s->close();
        }
    } else {
        // 🔐 Verify OTP flow
        $entered = trim($_POST['otp'] ?? '');
        if (!preg_match('/^\d{6}$/', $entered)) {
            $message = "Enter the 6-digit code sent to your email.";
        } else {
            $_SESSION['otp_attempts'] = $_SESSION['otp_attempts'] ?? 0;
            if ($_SESSION['otp_attempts'] >= $max_otp_attempts) {
                $message = "Too many wrong attempts. Please request a new OTP.";
            } else {
                $stmt = $conn->prepare("SELECT id, fullname, email, role, otp, otp_expiry 
                                        FROM users 
                                        WHERE id=? AND otp_expiry > NOW() LIMIT 1");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $res = $stmt->get_result();

                if ($res && $res->num_rows === 1) {
                    $row = $res->fetch_assoc();

                    // ✅ Verify using password_verify
                    if (password_verify($entered, $row['otp'])) {
                        $clear = $conn->prepare("UPDATE users SET otp=NULL, otp_expiry=NULL WHERE id=?");
                        $clear->bind_param("i", $user_id);
                        $clear->execute();
                        $clear->close();

                        session_regenerate_id(true);
                        $_SESSION['user_id']   = $row['id'];
                        $_SESSION['fullname'] = $row['fullname'];
                        $_SESSION['email']    = $row['email'];
                        $_SESSION['role']     = $row['role'];

                        unset($_SESSION['pending_user_id'], $_SESSION['otp_last_sent'], $_SESSION['otp_attempts']);

                        // ✅ If running on localhost, show success alert
                        if ($_SERVER['HTTP_HOST'] === 'localhost') {
                            echo "<script>alert('✅ Successfully logged in'); window.location.href='sensor_data.php';</script>";
                            exit();
                        } else {
                            header("Location: admin-dashboard.php");
                            exit();
                        }
                    } else {
                        $_SESSION['otp_attempts']++;
                        $left = $max_otp_attempts - $_SESSION['otp_attempts'];
                        $message = "Incorrect OTP. You have {$left} attempt(s) left.";
                    }
                } else {
                    $message = "OTP expired or not found. Request a new OTP.";
                }
                $stmt->close();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Verify OTP - BahaShield</title>
  <link rel="stylesheet" href="verify-otp.css">
</head>
<body>
  <div class="container">
    <h2>Verify OTP</h2>
    <?php if (!empty($message)): ?>
      <p class="lock-msg"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="form-group">
        <label>Enter the 6-digit code</label>
        <input type="text" name="otp" maxlength="6" placeholder="123456" required>
      </div>
      <button type="submit" class="btn">Verify</button> <br>
      <button type="submit" name="resend" class="btn" style="margin-left:0px;">Resend OTP</button>
    </form>
  </div>
</body>
</html>
