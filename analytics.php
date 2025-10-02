<?php
session_start();
include 'db_connect.php';

// Only allow Barangay Officials
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Barangay Official') {
    header("Location: login.php");
    exit();
}

// Fetch last 30 days average rainfall and flood level
$data = $conn->query("SELECT DATE(date) as day, 
                             AVG(rainfall) as avg_rain, 
                             AVG(flood) as avg_flood 
                      FROM flood_history 
                      GROUP BY day 
                      ORDER BY day ASC 
                      LIMIT 30");

$labels = [];
$rain   = [];
$flood  = [];

while ($row = $data->fetch_assoc()) {
    $labels[] = $row['day'];
    $rain[]   = round($row['avg_rain'], 2);
    $flood[]  = round($row['avg_flood'], 2);
}
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

    <!-- ✅ Back Button -->
    <div class="back-button">
      <a href="admin-dashboard.php">⬅️ Back to Dashboard</a>
    </div>

    <section class="content-card">
      <h3>Rainfall & Flood Trends (Last 30 Days)</h3>
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
          label: 'Flood Level (cm)',
          data: <?= json_encode($flood) ?>,
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
          y: {
            beginAtZero: true
          }
        }
      }
    });
  </script>
</body>
</html>
