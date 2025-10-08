<?php
include 'db_connect.php'; 

$message = "";
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $_POST['fullname'];
    $email    = $_POST['email'];
    $role     = "Barangay Official"; // 🔒 Fixed role
    $number   = $_POST['number'];
    $password = $_POST['password'];
    $confirm  = $_POST['confirm'];

    if ($password !== $confirm) {
        $message = "Passwords do not match!";
    } else {
        // Email check
        $checkEmail = $conn->prepare("SELECT id FROM users WHERE email=?");
        $checkEmail->bind_param("s", $email);
        $checkEmail->execute();
        $resultEmail = $checkEmail->get_result();
        if ($resultEmail->num_rows > 0) {
            $message = "Email already exists!";
        }
        $checkEmail->close();

        // Number check
        if (empty($message)) {
            $checkNumber = $conn->prepare("SELECT id FROM users WHERE number=?");
            $checkNumber->bind_param("s", $number);
            $checkNumber->execute();
            $resultNumber = $checkNumber->get_result();
            if ($resultNumber->num_rows > 0) {
                $message = "Mobile Number already exists!";
            }
            $checkNumber->close();
        }

        // Insert admin
        if (empty($message)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (fullname, email, role, number, password) 
                                    VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $fullname, $email, $role, $number, $hashedPassword);
            if ($stmt->execute()) {
                $success = true;
            } else {
                $message = "Error: " . $stmt->error;
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
  <link rel="stylesheet" href="admin-register.css">
</head>
<body>
  <div class="container">
    <h2>Admin Registration</h2>

    <?php if (!empty($message)): ?>
      <p class="error-message"><?php echo $message; ?></p>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="form-group"><label>Full Name</label><input type="text" name="fullname" required></div>
      <div class="form-group"><label>Email Address</label><input type="email" name="email" required></div>
      <div class="form-group"><label>Mobile Number</label><input type="text" name="number" required></div>
      <div class="form-group"><label>Password</label><input type="password" name="password" required></div>
      <div class="form-group"><label>Confirm Password</label><input type="password" name="confirm" required></div>
      <button type="submit" class="btn">Register</button>
    </form>
    <p class="login-link">Already have an account? <a href="admin-login.php">Login</a></p>
  </div>

  <?php if ($success): ?>
  <script>
    alert("Admin registered successfully!");
    window.location.href = "admin-login.php";
  </script>
  <?php endif; ?>
</body>
</html>
