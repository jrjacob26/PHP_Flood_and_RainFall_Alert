<?php
session_start();
include 'db_connect.php';

//  Only allow Barangay Officials
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Barangay Official') {
    header("Location: admin-login.php");
    exit();
}

//  Status messages
$success = $_SESSION['success'] ?? '';
$error   = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

// Default SMS template (editable in the form)
$default_sms = "⚠️ RAIN AND FLOOD ALERT
Barangay Cabangan, Legazpi City

🌧 Rain Intensity: 45 mm
💧 Water Level: 32 cm — Status: DANGER

Please stay alert and follow barangay safety instructions. 
Secure your belongings and be ready to evacuate if necessary.

📅 October 9, 2025 | 🕒 3:45 PM
– BahaShield Monitoring Team

To stop receiving alerts, click here and replace <YOUR_EMAIL> with your registered email:
http://localhost/Flood_and_RainFall_System/unsubscribe.php?email=<YOUR_EMAIL>";
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Send SMS - BahaShield</title>
  <style>

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: Arial, sans-serif;
    }

    body {
      background: #f4f6f8;
      min-height: 100vh;
    }

    header {
      background: #2563eb;
      color: white;
      padding: 15px 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .header-left {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .menu-icon {
      font-size: 22px;
      cursor: pointer;
    }

    .header-left {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .admin-name {
      font-weight: bold;
    }

    #sidebar {
      height: 100%;
      width: 0;
      position: fixed;
      z-index: 2;
      top: 0;
      left: 0;
      background-color: #1e3a8a;
      overflow-x: hidden;
      transition: 0.3s;
      padding-top: 60px;
    }

    #sidebar a,
    #sidebar button {
      padding: 10px 20px;
      text-decoration: none;
      font-size: 16px;
      color: white;
      display: block;
      transition: 0.2s;
      background: none;
      border: none;
      text-align: left;
      width: 100%;
    }

    #sidebar a:hover,
    #sidebar button:hover {
      background-color: #2563eb;
    }

    #sidebar .closebtn {
      position: absolute;
      top: 10px;
      right: 20px;
      font-size: 30px;
      color: white;
      cursor: pointer;
    }

    main {
      max-width: 600px;
      margin: 30px auto;
      padding: 0 15px;
    }

    main h2 {
      text-align: center;
      margin-bottom: 20px;
      
    }

    .alert {
      padding: 10px;
      border-radius: 5px;
      margin-bottom: 15px;
      text-align: center;
      font-weight: bold;
    }

    .alert.success {
      background: #2b9348;
      color: white;
    }

    .alert.error {
      background: #d62828;
      color: white;
    }

    /* ✅ Form Card */
    .content-card {
      background: #fff;
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .modern-form label {
      display: block;
      margin-bottom: 5px;
      font-weight: bold;
      color: #023e8a;
    }

    .modern-form select,
    .modern-form input,
    .modern-form textarea {
      width: 100%;
      padding: 8px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }

    .modern-form textarea {
      resize: vertical;
      min-height: 120px;
    }

    .primary-btn {
      background: #2563eb;
      color: white;
      border: none;
      padding: 10px 15px;
      border-radius: 5px;
      cursor: pointer;
      font-size: 16px;
      width: 100%;
    }

    .primary-btn:hover {
      background: #1e3a8a;
    }

    @media (max-width: 600px) {
      main {
        margin: 15px;
      }
    }
  </style>
</head>
<body>

<!--  Header -->
<header>
  <div class="header-left">
    <div class="menu-icon" onclick="toggleMenu()">☰</div>
    <h2 class="system-title">🌊 BahaShield</h2>
  </div>
  <span class="admin-name">Welcome, <?php echo htmlspecialchars($_SESSION['fullname']); ?></span>
</header>

<!--  Sidebar -->
<div id="sidebar">
  <span class="closebtn" onclick="toggleMenu()">×</span>
  <a href="admin-dashboard.php"> User Management</a>
  <a href="flood_history.php"> Flood & Rainfall History</a>
  <a href="sensor_data.php"> Sensor Data & Analytics</a>
  <a href="send_message.php" class="active"> Send SMS</a>
  <form method="POST" action="logout.php" onsubmit="return confirm('Logout?');">
    <button type="submit"> Logout</button>
  </form>
</div>

<!--  Main Content -->
<main>
  <h2>Send SMS Alert</h2>

  <section class="content-card">
    <?php if ($success): ?>
      <div class="alert success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="alert error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="send_sms.php" class="modern-form">
      <label>Send To:</label>
      <select name="recipient_type" id="recipient_type" required>
        <option value="">Select Recipient</option>
        <option value="all">All Subscribed Users</option>
        <option value="specific">Specific Number</option>
      </select>

      <div id="specific-number-field" style="display:none;">
        <label>Phone Number (start with 09...):</label>
        <input type="text" name="recipient" placeholder="e.g. 09123456789">
      </div>

      <label>Message:</label>
      <textarea name="message" id="message" placeholder="Type your alert message..." required><?= htmlspecialchars($default_sms) ?></textarea>

      <button type="submit" class="primary-btn">Send SMS</button>
    </form>
  </section>
</main>

<script>
  //  Toggle Sidebar Menu
  function toggleMenu() {
    const sidebar = document.getElementById("sidebar");
    sidebar.style.width = (sidebar.style.width === "250px") ? "0" : "250px";
  }

  //  Recipient select toggle
  const recipientSelect = document.getElementById('recipient_type');
  const specificField = document.getElementById('specific-number-field');

  recipientSelect.addEventListener('change', function() {
    if (this.value === 'specific') {
      specificField.style.display = 'block';
      specificField.querySelector('input').setAttribute('required', 'required');
    } else {
      specificField.style.display = 'none';
      const inp = specificField.querySelector('input');
      if (inp) inp.removeAttribute('required');
    }
  });
</script>

</body>
</html>
