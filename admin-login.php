<?php
session_start();
include 'db_connect.php';

$message = "";
$successRedirect = "";
$loginMessage = "";

// Initialize attempts
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
        $message = "⛔ Too many attempts! Please wait $remaining seconds.";
    } else {
        $_SESSION['login_attempts'] = 0;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($message)) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role     = "Barangay Official"; // 🔒 Only Admins allowed

    $stmt = $conn->prepare("SELECT * FROM users WHERE username=? AND role=? LIMIT 1");
    $stmt->bind_param("ss", $username, $role);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['login_attempts'] = 0;
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['fullname']  = $user['fullname'];
            $_SESSION['username']  = $user['username'];
            $_SESSION['role']      = $user['role'];

            $loginMessage = "📲 Barangay Official registered to the 🌊 BahaShield Admin Panel.";
            $successRedirect = "admin-dashboard.php";
        } else {
            $_SESSION['login_attempts']++;
            $_SESSION['last_attempt_time'] = time();
            $remaining_attempts = $max_attempts - $_SESSION['login_attempts'];
            $message = $remaining_attempts > 0 ? 
                "❌ Incorrect password! You have $remaining_attempts attempt(s) left." :
                "⛔ Too many failed attempts. Please wait $lock_time seconds.";
        }
    } else {
        $_SESSION['login_attempts']++;
        $_SESSION['last_attempt_time'] = time();
        $message = "❌ Admin not found!";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BahaShield - Admin Login</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: linear-gradient(135deg, #004e92, #000428);
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      margin: 0;
    }
    .container {
      background: #fff;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0px 6px 20px rgba(0,0,0,0.3);
      width: 350px;
      text-align: center;
    }
    .system-title {
      font-size: 26px;
      font-weight: bold;
      color: #004e92;
      margin-bottom: 10px;
    }
    h2 {
      margin-bottom: 20px;
      color: #333;
    }
    .form-group {
      margin-bottom: 15px;
      text-align: left;
    }
    label {
      display: block;
      margin-bottom: 5px;
      font-size: 14px;
      color: #333;
    }
    input {
      width: 94%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 8px;
      outline: none;
      transition: 0.3s;
    }
    input:focus {
      border-color: #004e92;
    }
    .password-container {
      position: relative;
    }
    .password-container input {
      padding-right: 10px;
    }
    .toggle-password {
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
      font-size: 18px;
    }
    .btn {
      width: 100%;
      padding: 12px;
      background: #004e92;
      color: #fff;
      font-size: 16px;
      font-weight: bold;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: 0.3s;
    }
    .btn:hover {
      background: #003366;
    }
    .lock-msg {
      color: red;
      margin-bottom: 10px;
      font-size: 14px;
    }
    .register-link {
      margin-top: 15px;
      display: block;
      font-size: 14px;
      color: #004e92;
      text-decoration: none;
    }
    .register-link:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1 class="system-title">🌊 BahaShield</h1>
    <h2>Admin Login</h2>

    <?php if (!empty($message)): ?>
      <p class="lock-msg"><?php echo $message; ?></p>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="form-group">
        <label for="username">Admin Username</label>
        <input type="text" name="username" placeholder="Enter your username" required>
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

    <a href="admin-register.php" class="register-link">➕ Register New Admin</a>
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
