<?php
session_start();
include 'db_connect.php'; // DB connection

$message = "";
$successRedirect = "";
$loginMessage = "";

// Initialize attempt counters if not set
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt_time'] = 0;
}

$lock_time = 10; // seconds
$max_attempts = 5;

// Check lock
if ($_SESSION['login_attempts'] >= $max_attempts) {
    $time_since_last_attempt = time() - $_SESSION['last_attempt_time'];
    if ($time_since_last_attempt < $lock_time) {
        $remaining = $lock_time - $time_since_last_attempt;
        $message = "⛔ Too many attempts! Please wait $remaining seconds.";
    } else {
        // Reset after lock time
        $_SESSION['login_attempts'] = 0;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($message)) {
    $username = $_POST['username'];
    $role     = $_POST['role'];
    $password = $_POST['password'];

    // Check if user exists with role
    $stmt = $conn->prepare("SELECT * FROM users WHERE username=? AND role=? LIMIT 1");
    $stmt->bind_param("ss", $username, $role);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user['password'])) {
            // ✅ Success → reset attempts
            $_SESSION['login_attempts'] = 0;

            // Store session data
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['fullname']  = $user['fullname'];
            $_SESSION['username']  = $user['username'];
            $_SESSION['role']      = $user['role'];

            if ($user['role'] == "Barangay Official") {
                $successRedirect = "admin-dashboard.php";
            } else {
                $loginMessage = "📲 Your number is now registered to the 🌊 BahaShield Alert System.";

                if ($user['role'] == "Resident") {
                    $successRedirect = "login.php";
                } elseif ($user['role'] == "Barangay Official") {
                    $successRedirect = "login.php";
                } else {
                    $successRedirect = "login.php";
                }
            }
        } else {
            // ❌ Wrong password
            $_SESSION['login_attempts']++;
            $_SESSION['last_attempt_time'] = time();
            $remaining_attempts = $max_attempts - $_SESSION['login_attempts'];
            if ($remaining_attempts > 0) {
                $message = "❌ Incorrect password! You have $remaining_attempts attempt(s) left.";
            } else {
                $message = "⛔ Too many failed attempts. Please wait $lock_time seconds.";
            }
        }
    } else {
        // ❌ User not found
        $_SESSION['login_attempts']++;
        $_SESSION['last_attempt_time'] = time();
        $message = "❌ User not found or role mismatch!";
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BahaShield - Login</title>
  <link rel="stylesheet" href="login.css">
  <style>
    .password-container {
      position: relative;
    }
    .password-container input {
      width: 100%;
      padding-right: 35px;
    }
    .toggle-password {
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
      font-size: 18px;
    }
    .lock-msg {
      color: red;
      text-align: center;
      margin-bottom: 10px;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1 class="system-title">🌊 BahaShield</h1> <br>
    <h2>Login</h2>

    <!-- Show login error -->
    <?php if (!empty($message)): ?>
      <p class="lock-msg"><?php echo $message; ?></p>
    <?php endif; ?>

    <form method="POST" action="login.php">
      <div class="form-group">
        <label for="username">Username</label>
        <input type="text" name="username" placeholder="Enter your username" required>
      </div>

      <div class="form-group">
        <label for="role">Role</label>
        <select name="role" required>
          <option value="" disabled selected>Select your role</option>
          <option value="Barangay Official">Barangay Official</option>
          <option value="Resident">Resident</option>
        </select>
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <div class="password-container">
          <input type="password" name="password" id="password" placeholder="Enter your password" required>
          <span class="toggle-password" onclick="togglePassword()">👁</span>
        </div>
      </div>

      <button type="submit" class="btn" <?php if ($_SESSION['login_attempts'] >= $max_attempts && (time() - $_SESSION['last_attempt_time']) < $lock_time) echo "disabled"; ?>>Login</button>
    </form>
    <p class="login-link">Don’t have an account? <a href="register.php">Register</a></p>
  </div>

  <?php if (!empty($successRedirect) && empty($loginMessage)): ?>
  <script>
    alert("✅ Login successful! Redirecting...");
    window.location.href = "<?php echo $successRedirect; ?>";
  </script>
  <?php elseif (!empty($loginMessage)): ?>
  <script>
    alert("<?php echo $loginMessage; ?>");
    window.location.href = "<?php echo $successRedirect; ?>";
  </script>
  <?php endif; ?>

  <script>
    // Toggle password visibility
    function togglePassword() {
      const passInput = document.getElementById("password");
      const icon = document.querySelector(".toggle-password");
      if (passInput.type === "password") {
        passInput.type = "text";
        icon.textContent = "🙈";
      } else {
        passInput.type = "password";
        icon.textContent = "👁";
      }
    }
  </script>
</body>
</html>
