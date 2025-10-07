<?php
// ==================================================
// ✅ ESP32 API Endpoint (Plain text, no session check)
// ==================================================
header("Access-Control-Allow-Origin: *");
$SECRET_KEY = "mySecret123";  // must match with ESP32
date_default_timezone_set('Asia/Manila');

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
    exit;
}

// ==================================================
// ✅ Dashboard + Analytics (For Barangay Officials)
// ==================================================
session_start();
include 'db_connect.php';

// Only allow Barangay Officials
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Barangay Official') {
    header("Location: login.php");
    exit();
}

// -----------------
// Fetch last 30 days average rainfall and water level
// -----------------
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

// -----------------
// Fetch latest reading summary
// -----------------
$latest = $conn->query("SELECT * FROM sensor_data ORDER BY datetime DESC LIMIT 1")->fetch_assoc();

// -----------------
// Stats summary
// -----------------
$totalRecords = $conn->query("SELECT COUNT(*) as total FROM sensor_data")->fetch_assoc()['total'];
$latestWater = $latest ? $latest['water'] : 0;
$latestRain = $latest ? $latest['rain'] : 0;
$latestTime = $latest ? date("M d, Y h:i A", strtotime($latest['datetime'])) : 'N/A';

// -----------------
// Sensor Data Message (manual POST/GET simulation)
// -----------------
$currentDateTime = date("Y-m-d H:i:s");
$sensorMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $waterLevel = isset($_POST['water_level']) ? intval($_POST['water_level']) : null;
    $rainIntensity = isset($_POST['rain_intensity']) ? intval($_POST['rain_intensity']) : null;
    $time = $_POST['time'] ?? date("H:i:s");
    $date = $_POST['date'] ?? date("Y-m-d");
    $currentDateTime = $date . " " . $time;

    if ($waterLevel !== null && $rainIntensity !== null) {
        if ($waterLevel >= 30) $status = 'Danger';
        elseif ($waterLevel >= 20) $status = 'Warning';
        else $status = 'Safe';

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
// Fetch last 20 records
// -----------------
$result = $conn->query("SELECT * FROM sensor_data ORDER BY datetime DESC LIMIT 20");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>📡 Sensor Data & Analytics - BahaShield</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      background: #f4f6f8;
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
      display: inline-block;
    }
    .system-title {
      font-size: 20px;
      margin: 0;
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
    #sidebar a, #sidebar button {
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
    #sidebar a:hover, #sidebar button:hover {
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
      max-width: 1100px;
      margin: 20px auto;
      padding: 0 15px;
    }
    h2 {
      margin-top: 20px;
      margin-bottom: 20px;
      text-align: center;
    }
    .stat-boxes {
      display: grid;
      grid-template-columns: repeat(auto-fit,minmax(200px,1fr));
      gap: 15px;
      margin-bottom: 20px;
    }
    .stat-box {
      background: #fff;
      border-radius: 10px;
      padding: 15px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      text-align: center;
    }
    .controls {
      display: flex;
      justify-content: space-between;
      margin-bottom: 15px;
    }
    .search-bar input {
      padding: 8px;
      width: 200px;
      border: 1px solid #ccc;
      border-radius: 6px;
    }
    .btn.export {
      background: #2563eb;
      color: white;
      padding: 8px 14px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
    }
    .table-container {
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      overflow-x: auto;
      overflow-y: auto;
      max-height: 300px; /* ✅ SCROLL BAR ADDED */
      margin-bottom: 20px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
    }
    th {
      background: #2563eb;
      color: white;
      padding: 10px;
    }
    td {
      padding: 10px;
      border-bottom: 1px solid #eee;
    }
    tr:hover {
      background: #f9fafb;
    }
    .content-card {
      background: #fff;
      border-radius: 10px;
      padding: 20px;
      margin-bottom: 20px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
  </style>
</head>
<body>

<header>
  <div class="header-left">
    <div class="menu-icon" onclick="toggleMenu()">☰</div>
    <h1 class="system-title">🌊 BahaShield</h1>
  </div>
  <span class="admin-name">Welcome, <?php echo htmlspecialchars($_SESSION['fullname']); ?></span>
</header>

<div id="sidebar">
  <span class="closebtn" onclick="toggleMenu()">×</span>
  <a href="admin-dashboard.php">👤 User Management</a>
  <a href="flood_history.php">📊 Flood & Rainfall History</a>
  <a href="sensor_data.php" class="active">📡 Sensor Data & Analytics</a>
  <a href="send_message.php">💬 Send SMS</a>
  <form method="POST" action="logout.php" onsubmit="return confirm('Logout?');">
    <button type="submit">🚪 Logout</button>
  </form>
</div>

<main>
  <h2>📡 Sensor Data Monitoring</h2>

  <div class="stat-boxes">
    <div class="stat-box">🧮 <p>Total Records</p><h3><?php echo $totalRecords; ?></h3></div>
    <div class="stat-box">🌊 <p>Latest Water Level</p><h3><?php echo htmlspecialchars($latestWater); ?> cm</h3></div>
    <div class="stat-box">🌧️ <p>Latest Rainfall</p><h3><?php echo htmlspecialchars($latestRain); ?></h3></div>
    <div class="stat-box">⏰ <p>Last Update</p><h3><?php echo $latestTime; ?></h3></div>
  </div>

  <div class="controls">
    <div class="search-bar">
      <input type="text" id="searchInput" placeholder="🔍 Search records...">
    </div>
    <button class="btn export" onclick="exportTableToCSV()">📄 Export CSV</button>
  </div>

  <div class="table-container">
    <table id="sensorTable">
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
            <td><span style="background:<?= $statusColor ?>;color:#fff;padding:5px 10px;border-radius:999px;"><?= $row['status'] ?></span></td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>

  <div class="content-card">
    <h3>📈 Rainfall & Water Level Trends (Last 30 Days)</h3>
    <canvas id="trendChart"></canvas>
  </div>
</main>

<script>
function toggleMenu() {
  const sidebar = document.getElementById('sidebar');
  sidebar.style.width = sidebar.style.width === '250px' ? '0' : '250px';
}

function exportTableToCSV() {
  const rows = document.querySelectorAll("table tr");
  let csv = [];
  rows.forEach(row => {
    let cols = row.querySelectorAll("td, th");
    let rowData = [];
    cols.forEach(col => rowData.push(col.innerText));
    csv.push(rowData.join(","));
  });
  const blob = new Blob([csv.join("\n")], { type: 'text/csv' });
  const a = document.createElement("a");
  a.href = URL.createObjectURL(blob);
  a.download = "sensor_data.csv";
  a.click();
}

document.getElementById('searchInput').addEventListener('keyup', function () {
  const filter = this.value.toLowerCase();
  document.querySelectorAll("#sensorTable tbody tr").forEach(row => {
    row.style.display = row.innerText.toLowerCase().includes(filter) ? "" : "none";
  });
});

const ctx = document.getElementById('trendChart');
new Chart(ctx, {
  type: 'line',
  data: {
    labels: <?= json_encode($labels) ?>,
    datasets: [{
      label: 'Rainfall (mm)',
      data: <?= json_encode($rain) ?>,
      borderColor: '#3b82f6',
      backgroundColor: 'rgba(59,130,246,0.2)',
      tension: 0.3
    },{
      label: 'Water Level (cm)',
      data: <?= json_encode($water) ?>,
      borderColor: '#ef4444',
      backgroundColor: 'rgba(239,68,68,0.2)',
      tension: 0.3
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { position: 'top' } },
    scales: { y: { beginAtZero: true } }
  }
});
</script>
</body>
</html>
