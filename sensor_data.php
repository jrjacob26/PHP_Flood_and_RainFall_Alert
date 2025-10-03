<?php
// -----------------
// ESP32 API Endpoint (Plain text, no session check)
// -----------------
header("Access-Control-Allow-Origin: *");
$SECRET_KEY = "mySecret123";  // must match with ESP32
date_default_timezone_set('Asia/Manila');

// ✅ If ESP32 sends data with key, handle it here (before session start)
if (isset($_GET['key']) && $_GET['key'] === $SECRET_KEY &&
    isset($_GET['water_level']) && isset($_GET['rain_intensity'])) {

    include 'db_connect.php';

    $water = intval($_GET['water_level']);
    $rain = intval($_GET['rain_intensity']);
    $time = date("H:i:s");
    $date = date("Y-m-d");
    $datetime = $date . " " . $time;

    // Determine status
    if ($water >= 30) {
        $status = 'Danger';
    } elseif ($water >= 20) {
        $status = 'Warning';
    } else {
        $status = 'Safe';
    }

    $stmt = $conn->prepare("INSERT INTO sensor_data (datetime, water, rain, status) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("siis", $datetime, $water, $rain, $status);

    if ($stmt->execute()) {
        echo "✅ Data saved: Water=$water cm, Rain=$rain, Time=$time, Date=$date, Status=$status";
    } else {
        echo "❌ Database error: " . $conn->error;
    }
    exit; // ✅ Stop here so HTML is not sent to ESP32
}

// -----------------
// Dashboard for Barangay Officials (HTML)
// -----------------
session_start();

// Only allow Barangay Officials to view dashboard
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Barangay Official') {
    header("Location: login.php");
    exit();
}

include 'db_connect.php';

$currentDateTime = date("Y-m-d H:i:s");
$sensorMessage = "";

// -----------------
// Handle POST request
// -----------------
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $waterLevel = isset($_POST['water_level']) ? intval($_POST['water_level']) : null;
    $rainIntensity = isset($_POST['rain_intensity']) ? intval($_POST['rain_intensity']) : null;
    $time = $_POST['time'] ?? date("H:i:s");
    $date = $_POST['date'] ?? date("Y-m-d");
    $currentDateTime = $date . " " . $time;

    if ($waterLevel !== null && $rainIntensity !== null) {
        if ($waterLevel >= 30) {
            $status = 'Danger';
        } elseif ($waterLevel >= 20) {
            $status = 'Warning';
        } else {
            $status = 'Safe';
        }

        $stmt = $conn->prepare("INSERT INTO sensor_data (datetime, water, rain, status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("siis", $currentDateTime, $waterLevel, $rainIntensity, $status);
        $stmt->execute();

        $sensorMessage = "
            <div class='sensor-card'>
                <h3>🌧 Sensor Data Received (POST)</h3>
                <p><b>Date & Time:</b> $currentDateTime</p>
                <p><b>Water Level:</b> $waterLevel cm</p>
                <p><b>Rain Intensity:</b> $rainIntensity</p>
                <p><b>Status:</b> $status</p>
            </div>
        ";
    } else {
        $sensorMessage = "<p style='color:red;'>Invalid POST data received.</p>";
    }
}

// -----------------
// Handle GET (?water & ?rain)
// -----------------
elseif (isset($_GET['water']) && isset($_GET['rain'])) {
    $waterLevel = intval($_GET['water']);
    $rainIntensity = intval($_GET['rain']);

    if ($waterLevel >= 30) $status = 'Danger';
    elseif ($waterLevel >= 20) $status = 'Warning';
    else $status = 'Safe';

    $stmt = $conn->prepare("INSERT INTO sensor_data (datetime, water, rain, status) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("siis", $currentDateTime, $waterLevel, $rainIntensity, $status);
    $stmt->execute();

    $sensorMessage = "
        <div class='sensor-card'>
            <h3>🌧 Sensor Data Received (GET: water & rain)</h3>
            <p><b>Date & Time:</b> $currentDateTime</p>
            <p><b>Water Level:</b> $waterLevel cm</p>
            <p><b>Rain Intensity:</b> $rainIntensity</p>
            <p><b>Status:</b> $status</p>
        </div>
    ";
}

// -----------------
// Handle GET (?water_level & ?rain_intensity)
// -----------------
elseif (isset($_GET['water_level']) && isset($_GET['rain_intensity'])) {
    $waterLevel = intval($_GET['water_level']);
    $rainIntensity = intval($_GET['rain_intensity']);
    $time = date("H:i:s");
    $date = date("Y-m-d");
    $currentDateTime = $date . " " . $time;

    if ($waterLevel >= 30) $status = 'Danger';
    elseif ($waterLevel >= 20) $status = 'Warning';
    else $status = 'Safe';

    $stmt = $conn->prepare("INSERT INTO sensor_data (datetime, water, rain, status) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("siis", $currentDateTime, $waterLevel, $rainIntensity, $status);
    $stmt->execute();

    $sensorMessage = "
        <div class='sensor-card'>
            <h3>🌧 Sensor Data Received (GET: water_level & rain_intensity)</h3>
            <p><b>Date & Time:</b> $currentDateTime</p>
            <p><b>Water Level:</b> $waterLevel cm</p>
            <p><b>Rain Intensity:</b> $rainIntensity</p>
            <p><b>Status:</b> $status</p>
        </div>
    ";
}

// -----------------
// No new data
// -----------------
else {
    $sensorMessage = "<p style='color:gray;'>No new sensor data received yet.</p>";
}

// Fetch last 20 records
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
              $statusColor = '#28a745';
              if ($row['status'] === 'Warning') $statusColor = '#ffc107';
              if ($row['status'] === 'Danger') $statusColor = '#dc3545';
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
