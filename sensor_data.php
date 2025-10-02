<?php
session_start();

// Only allow Barangay Officials
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Barangay Official') {
    header("Location: login.php");
    exit();
}

include 'db_connect.php';

// Handle sensor data (Arduino will send GET parameters like ?water=20&rain=50)
date_default_timezone_set('Asia/Manila');
$currentDateTime = date("Y-m-d H:i:s");
$sensorMessage = "";

if (isset($_GET['water']) && isset($_GET['rain'])) {
    $waterLevel = intval($_GET['water']);
    $rainIntensity = intval($_GET['rain']);

    // Determine status based on water level
    if ($waterLevel >= 30) {
        $status = 'Danger';
    } elseif ($waterLevel >= 20) {
        $status = 'Warning';
    } else {
        $status = 'Safe';
    }

    // Insert into sensor_data table including status
    $stmt = $conn->prepare("INSERT INTO sensor_data (datetime, water, rain, status) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("siis", $currentDateTime, $waterLevel, $rainIntensity, $status);
    $stmt->execute();

    $sensorMessage = "
        <div class='sensor-card'>
            <h3>🌧 Sensor Data Received</h3>
            <p><b>Date & Time:</b> $currentDateTime</p>
            <p><b>Water Level:</b> $waterLevel cm</p>
            <p><b>Rain Intensity:</b> $rainIntensity</p>
            <p><b>Status:</b> $status</p>
        </div>
    ";
} else {
    $sensorMessage = "<p style='color:gray;'>No new sensor data received yet.</p>";
}

// Fetch last 20 sensor readings
$result = $conn->query("SELECT * FROM sensor_data ORDER BY datetime DESC LIMIT 20");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>📡 Sensor Data - BahaShield</title>
  <link rel="stylesheet" href="sensor_data.css">
</head>
<body>

<main>
  <header>
    <h2>📡 Sensor Data</h2>
  </header>

  <!-- Back Button -->
  <div class="back-button">
    <a href="admin-dashboard.php">⬅️ Back to Dashboard</a>
  </div>

  <section class="content-card">
    <h3>Latest Sensor Reading</h3>
    <?= $sensorMessage ?>
  </section>

  <section class="content-card">
    <h3>Recent Sensor Records</h3>
    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Date & Time</th>
            <th>Water Level (cm)</th>
            <th>Rain Intensity</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $result->fetch_assoc()) { 
              // Inline style for status color
              $statusColor = '#28a745'; // green for Safe
              if ($row['status'] === 'Warning') $statusColor = '#ffc107'; // yellow
              if ($row['status'] === 'Danger') $statusColor = '#dc3545';  // red
          ?>
            <tr>
              <td><?= date("M d, Y h:i A", strtotime($row['datetime'])) ?></td>
              <td><?= htmlspecialchars($row['water']) ?></td>
              <td><?= htmlspecialchars($row['rain']) ?></td>
              <td>
                <span style="background-color: <?= $statusColor ?>; color:#fff; padding:5px 12px; border-radius:999px; font-weight:600;">
                  <?= htmlspecialchars($row['status']) ?>
                </span>
              </td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </section>
</main>

</body>
</html>
