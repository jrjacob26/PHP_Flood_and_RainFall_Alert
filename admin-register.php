<?php
include 'db_connect.php'; 

$message = "";
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $_POST['fullname'];
    $email    = $_POST['email'];
    $username = $_POST['username'];
    $role     = "Barangay Official"; // 🔒 Fixed role
    $number   = $_POST['number'];
    $password = $_POST['password'];
    $confirm  = $_POST['confirm'];

    if ($password !== $confirm) {
        $message = "❌ Passwords do not match!";
    } else {
        // Email check
        $checkEmail = $conn->prepare("SELECT id FROM users WHERE email=?");
        $checkEmail->bind_param("s", $email);
        $checkEmail->execute();
        $resultEmail = $checkEmail->get_result();
        if ($resultEmail->num_rows > 0) {
            $message = "❌ Email already exists!";
        }
        $checkEmail->close();

        // Username check
        if (empty($message)) {
            $checkUser = $conn->prepare("SELECT id FROM users WHERE username=?");
            $checkUser->bind_param("s", $username);
            $checkUser->execute();
            $resultUser = $checkUser->get_result();
            if ($resultUser->num_rows > 0) {
                $message = "❌ Username already exists!";
            }
            $checkUser->close();
        }

        // Number check
        if (empty($message)) {
            $checkNumber = $conn->prepare("SELECT id FROM users WHERE number=?");
            $checkNumber->bind_param("s", $number);
            $checkNumber->execute();
            $resultNumber = $checkNumber->get_result();
            if ($resultNumber->num_rows > 0) {
                $message = "❌ Mobile Number already exists!";
            }
            $checkNumber->close();
        }

        // Insert admin
        if (empty($message)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (fullname, username, email, role, number, password) 
                                    VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $fullname, $username, $email, $role, $number, $hashedPassword);
            if ($stmt->execute()) {
                $success = true;
            } else {
                $message = "❌ Error: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BahaShield - Admin Registration</title>
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
      width: 380px;
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
      font-size: 15px;
    }
    input:focus {
      border-color: #004e92;
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
    .error-message {
      color: red;
      font-size: 14px;
      margin-bottom: 10px;
    }
    .login-link {
      margin-top: 15px;
      display: block;
      font-size: 14px;
      color: #333;
    }
    .login-link a {
      color: #004e92;
      text-decoration: none;
      font-weight: bold;
    }
    .login-link a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1 class="system-title">🌊 BahaShield</h1>
    <h2>Admin Registration</h2>

    <?php if (!empty($message)): ?>
      <p class="error-message"><?php echo $message; ?></p>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="form-group"><label>Full Name</label><input type="text" name="fullname" required></div>
      <div class="form-group"><label>Email Address</label><input type="email" name="email" required></div>
      <div class="form-group"><label>Username</label><input type="text" name="username" required></div>
      <div class="form-group"><label>Mobile Number</label><input type="text" name="number" required></div>
      <div class="form-group"><label>Password</label><input type="password" name="password" required></div>
      <div class="form-group"><label>Confirm Password</label><input type="password" name="confirm" required></div>
      <button type="submit" class="btn">Register</button>
    </form>
    <p class="login-link">Already have an account? <a href="admin-login.php">Login</a></p>
  </div>

  <?php if ($success): ?>
  <script>
    alert("✅ Admin registered successfully!");
    window.location.href = "admin-login.php";
  </script>
  <?php endif; ?>
</body>
</html>
