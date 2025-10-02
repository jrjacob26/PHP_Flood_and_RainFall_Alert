<?php
session_start();
include 'db_connect.php';

// Only allow Barangay Officials
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Barangay Official') {
    header("Location: login.php");
    exit();
}

// Status messages
$success = $_SESSION['success'] ?? '';
$error   = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Send SMS - BahaShield</title>
  <link rel="stylesheet" href="send_message.css">
</head>
<body>

<main>
  <header>
    <h2>💬 Send SMS Alerts</h2>
  </header>

  <!-- Back Button -->
  <div class="back-button">
    <a href="admin-dashboard.php">⬅️ Back to Dashboard</a>
  </div>

  <section class="content-card">
    <?php if ($success): ?>
      <div class="alert success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="alert error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="process_sms.php" class="modern-form">
      <label>Send To:</label>
      <select name="recipient_type" id="recipient_type" required>
        <option value="">Select Recipient</option>
        <option value="all">All Subscribed Users</option>
        <option value="specific">Specific Number</option>
      </select>

      <div id="specific-number-field" style="display:none;">
        <label>Phone Number (include country code, e.g. +63...):</label>
        <input type="text" name="recipient" placeholder="Enter phone number">
      </div>

      <label>Message:</label>
      <textarea name="message" id="message" placeholder="Type your alert message..." required></textarea>

      <button type="submit" class="primary-btn">🚀 Send SMS</button>
    </form>
  </section>
</main>

<script>
  const recipientSelect = document.getElementById('recipient_type');
  const specificField = document.getElementById('specific-number-field');

  recipientSelect.addEventListener('change', function() {
    if(this.value === 'specific'){
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
