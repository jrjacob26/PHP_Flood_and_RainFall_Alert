<?php
session_start();
include 'db_connect.php';

// Only allow Barangay Officials
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Barangay Official') {
    header("Location: login.php");
    exit();
}

// Fetch last 30 days average rainfall and water level from sensor_data
$data = $conn->query("
    SELECT DATE(datetime) as day, 
           AVG(rain) as avg_rain, 
           AVG(water) as avg_water
    FROM sensor_data
    GROUP BY day
    ORDER BY day ASC
    LIMIT 30
");

$labels = [];
$rain   = [];
$water  = [];

while ($row = $data->fetch_assoc()) {
    $labels[] = $row['day'];
    $rain[]   = round($row['avg_rain'], 2);
    $water[]  = round($row['avg_water'], 2);
}

// Fetch latest reading summary
$latest = $conn->query("SELECT * FROM sensor_data ORDER BY datetime DESC LIMIT 1")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Analytics - BahaShield</title>
  <link rel="stylesheet" href="analytics.css">
</head>
<body>

<main>
  <header>
    <h2>📈 Analytics</h2>
  </header>

  <!-- Back Button -->
  <div class="back-button">
    <a href="admin-dashboard.php">⬅️ Back to Dashboard</a>
  </div>

  <!-- Summary of latest reading -->
  <?php if ($latest) { 
      // Inline style for status color
      $statusColor = '#28a745'; // Safe - green
      if ($latest['status'] === 'Warning') $statusColor = '#ffc107'; // Warning - yellow
      if ($latest['status'] === 'Danger') $statusColor = '#dc3545';  // Danger - red
  ?>
  <section class="content-card">
      <h3>Latest Sensor Reading</h3>
      <p><b>Date & Time:</b> <?= $latest['datetime'] ?></p>
      <p><b>Water Level:</b> <?= $latest['water'] ?> cm</p>
      <p><b>Rain Intensity:</b> <?= $latest['rain'] ?> mm</p>
      <p>
        <b>Status:</b> 
        <span style="background-color: <?= $statusColor ?>; color:#fff; padding:5px 12px; border-radius:999px; font-weight:600;">
            <?= htmlspecialchars($latest['status']) ?>
        </span>
      </p>
  </section>
  <?php } ?>

  <!-- Chart Section -->
  <section class="content-card">
      <h3>Rainfall & Water Level Trends (Last 30 Days)</h3>
      <div class="chart-box">
        <canvas id="trendChart"></canvas>
      </div>
  </section>
</main>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('trendChart');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [{
            label: 'Rainfall (mm)',
            data: <?= json_encode($rain) ?>,
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59, 130, 246, 0.2)',
            tension: 0.3
        },{
            label: 'Water Level (cm)',
            data: <?= json_encode($water) ?>,
            borderColor: '#ef4444',
            backgroundColor: 'rgba(239, 68, 68, 0.2)',
            tension: 0.3
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'top' }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>
</body>
</html>
