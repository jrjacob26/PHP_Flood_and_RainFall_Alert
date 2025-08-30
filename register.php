<?php
include 'db_connect.php'; // include database connection

$message = "";
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $_POST['fullname'];
    $email    = $_POST['email'];
    $username = $_POST['username'];
    $role     = $_POST['role'];
    $address  = $_POST['address'];
    $purok    = $_POST['purok'];
    $number   = $_POST['number']; // 📱 new number field
    $password = $_POST['password'];
    $confirm  = $_POST['confirm'];

    // Check if passwords match
    if ($password !== $confirm) {
        $message = "❌ Passwords do not match!";
    } else {
        // --- Check email ---
        $checkEmail = $conn->prepare("SELECT id FROM users WHERE email=?");
        $checkEmail->bind_param("s", $email);
        $checkEmail->execute();
        $resultEmail = $checkEmail->get_result();

        if ($resultEmail->num_rows > 0) {
            $message = "❌ Email already exists!";
        }
        $checkEmail->close();

        // --- Check username ---
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

        // --- Check number ---
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

        // --- Insert new user if no errors ---
        if (empty($message)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO users (fullname, username, email, role, address, purok, number, password) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssss", $fullname, $username, $email, $role, $address, $purok, $number, $hashedPassword);

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
  <title>User Registration - BahaShield</title>
  <link rel="stylesheet" href="register.css">
  <style>
    .password-container {
      position: relative;
    }
    .password-container input {
      width: 100%;
      padding-right: 35px; /* space for the eye icon */
    }
    .toggle-password {
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
      font-size: 18px;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1 class="system-title">🌊 BahaShield</h1> <br>
    <h2>Create Account</h2>

    <!-- Show error message -->
    <?php if (!empty($message)): ?>
      <p style="color:red; text-align:center;"><?php echo $message; ?></p>
    <?php endif; ?>

    <form method="POST" action="register.php">
      <div class="form-group">
        <label for="fullname">Full Name</label>
        <input type="text" name="fullname" placeholder="Enter your full name" required>
      </div>

      <div class="form-group">
        <label for="email">Email Address</label>
        <input type="email" name="email" placeholder="Enter your email" required>
      </div>

      <div class="form-group">
        <label for="username">Username</label>
        <input type="text" name="username" placeholder="Choose a username" required>
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
        <label for="address">Address</label>
        <input type="text" name="address" placeholder="Enter your address" required>
      </div>

      <div class="form-group">
        <label for="purok">Purok</label>
        <select name="purok" required>
          <option value="" disabled selected>Select your Purok</option>
          <option value="1">Purok 1</option>
          <option value="2">Purok 2</option>
          <option value="3">Purok 3</option>
          <option value="4">Purok 4</option>
          <option value="5">Purok 5</option>
          <option value="6">Purok 6</option>
          <option value="7">Purok 7</option>
          <option value="8">Purok 8</option>
        </select>
      </div>

      <div class="form-group">
        <label for="number">Mobile Number</label>
        <input type="text" name="number" placeholder="Enter your mobile number" required>
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <div class="password-container">
          <input type="password" name="password" id="password" placeholder="Enter password" required>
          <span class="toggle-password" onclick="togglePassword('password', this)">👁</span>
        </div>
      </div>

      <div class="form-group">
        <label for="confirm">Confirm Password</label>
        <div class="password-container">
          <input type="password" name="confirm" id="confirm" placeholder="Re-enter password" required>
          <span class="toggle-password" onclick="togglePassword('confirm', this)">👁</span>
        </div>
      </div>

      <button type="submit" class="btn">Register</button>
    </form>
    <p class="login-link">Already have an account? <a href="login.php">Login</a></p>
  </div>

  <?php if ($success): ?>
  <script>
    alert("✅ Registration successful! Redirecting to login page...");
    window.location.href = "login.php"; // redirect to login
  </script>
  <?php endif; ?>

  <script>
    function togglePassword(fieldId, icon) {
      const input = document.getElementById(fieldId);
      if (input.type === "password") {
        input.type = "text";
        icon.textContent = "🙈"; // change icon when showing
      } else {
        input.type = "password";
        icon.textContent = "👁"; // revert icon when hiding
      }
    }
  </script>
</body>
</html>
