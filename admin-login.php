<?php
// admin-login.php
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

$message = "";

// Login attempt limits (session-based)
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt_time'] = 0;
}
$lock_time = 10; 
$max_attempts = 5;

if ($_SESSION['login_attempts'] >= $max_attempts) {
    $time_since_last_attempt = time() - $_SESSION['last_attempt_time'];
    if ($time_since_last_attempt < $lock_time) {
        $remaining = $lock_time - $time_since_last_attempt;
        $message = "Too many attempts! Please wait $remaining seconds.";
    } else {
        $_SESSION['login_attempts'] = 0;
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && empty($message)) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = "Barangay Official";

    $stmt = $conn->prepare("SELECT id, fullname, email, password, role FROM users WHERE email=? AND role=? LIMIT 1");
    $stmt->bind_param("ss", $email, $role);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            // Generate secure 6-digit OTP
            $otp_plain = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $otp_hash = password_hash($otp_plain, PASSWORD_DEFAULT);
            $expiry = date("Y-m-d H:i:s", time() + 300); // 5 minutes

            //  Debug log for testing
            error_log("OTP for {$user['email']} = $otp_plain, expiry = $expiry");

            // Save hashed OTP and expiry to DB
            $update = $conn->prepare("UPDATE users SET otp=?, otp_expiry=? WHERE id=?");
            $update->bind_param("ssi", $otp_hash, $expiry, $user['id']);
            $ok = $update->execute();
            $update->close();

            if (!$ok) {
                $message = "Server error. Try again later.";
            } else {
                // Send OTP email
                $sent = sendOtpMail($user['email'], $user['fullname'], $otp_plain);
                if (!$sent) {
                    $message = "Couldn't send OTP email. Check SMTP settings.";
                } else {
                    // Store pending login info and redirect to OTP page
                    $_SESSION['pending_user_id'] = $user['id'];
                    $_SESSION['otp_last_sent'] = time();
                    $_SESSION['otp_attempts'] = 0;
                    header("Location: verify-otp.php");
                    exit();
                }
            }
        } else {
            $_SESSION['login_attempts']++;
            $_SESSION['last_attempt_time'] = time();
            $remaining_attempts = max(0, $max_attempts - $_SESSION['login_attempts']);
            $message = $remaining_attempts > 0 ? 
                "Incorrect password! You have $remaining_attempts attempt(s) left." :
                "Too many failed attempts. Please wait $lock_time seconds.";
        }
    } else {
        $_SESSION['login_attempts']++;
        $_SESSION['last_attempt_time'] = time();
        $message = "Admin not found!";
    }
    if (isset($stmt)) $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BahaShield - Admin Login</title>
  <link rel="stylesheet" href="admin-login.css">
</head>
<body>
  <div class="container">
    <h2>Admin Login</h2>

    <?php if (!empty($message)): ?>
      <p class="lock-msg"><?php echo $message; ?></p>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" name="email" placeholder="Enter your email" required>
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <div class="password-container">
          <input type="password" name="password" id="password" placeholder="Enter your password" required>
          <span class="toggle-password" onclick="togglePassword()">👁</span>
        </div>
      </div>

      <button type="submit" class="btn" 
        <?php if ($_SESSION['login_attempts'] >= $max_attempts && (time() - $_SESSION['last_attempt_time']) < $lock_time) echo "disabled"; ?>>
        Login
      </button>
    </form>

    <p class="register-link">Don't have an account? <a href="admin-register.php">Register</a></p>
  </div>

  <script>
    function togglePassword() {
      const passInput = document.getElementById("password");
      const icon = document.querySelector(".toggle-password");
      if (passInput.type === "password") {
        passInput.type = "text";
        icon.textContent = "";
      } else {
        passInput.type = "password";
        icon.textContent = "👁";
      }
    }
  </script>
</body>
</html>
