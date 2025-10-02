<?php
session_start();
include 'db_connect.php';

// Only allow Barangay Officials
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Barangay Official') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Flood & Rainfall History - BahaShield</title>
  <link rel="stylesheet" href="flood_history.css">
</head>
<body>

<main>
  <header>
    <h2>📊 Flood & Rainfall History</h2>
  </header>

  <!-- Back Button -->
  <div class="back-button">
    <a href="admin-dashboard.php">⬅️ Back to Dashboard</a>
  </div>

  <section class="content-card">
    <h3>Recent Records</h3>
    <table class="modern-table">
      <thead>
        <tr>
          <th>Date & Time</th>
          <th>Rainfall (mm)</th>
          <th>Water Level (cm)</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php
        // Fetch last 10 sensor readings
        $result = $conn->query("SELECT * FROM sensor_data ORDER BY datetime DESC LIMIT 10");
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Determine status dynamically
                $status = "Safe";
                $status_class = "low"; // Green by default
                if ($row['water'] >= 20 && $row['water'] < 30) {
                    $status = "Warning";
                    $status_class = "moderate"; // Yellow
                } elseif ($row['water'] >= 30) {
                    $status = "Danger";
                    $status_class = "high"; // Red
                }

                echo "<tr>
                        <td>" . date("M d, Y h:i A", strtotime($row['datetime'])) . "</td>
                        <td>{$row['rain']}</td>
                        <td>{$row['water']}</td>
                        <td><span class='status {$status_class}'>{$status}</span></td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='4'>No data available</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </section>
</main>
</body>
</html>
