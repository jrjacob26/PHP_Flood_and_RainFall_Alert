<?php
// ==================================================
//  ESP32 API Endpoint (Plain text, no session check)
// ==================================================
header("Access-Control-Allow-Origin: *");
date_default_timezone_set('Asia/Manila');
include 'db_connect.php';

// ==========================
// ESP32 Data Insertion
// ==========================
$SECRET_KEY = "mySecret123";
if (isset($_GET['key']) && $_GET['key'] === $SECRET_KEY &&
    isset($_GET['water_level']) && isset($_GET['rain_intensity'])) {

    $water = intval($_GET['water_level']);
    $rain = intval($_GET['rain_intensity']);
    $datetime = date("Y-m-d H:i:s");

    // Flood classification
    if ($water <= 10) $floodStatus = 'High';
    elseif ($water <= 20) $floodStatus = 'Medium';
    else $floodStatus = 'Low';

    // Rain classification
    if ($rain >= 10) $rainStatus = 'Heavy';
    elseif ($rain >= 5) $rainStatus = 'Moderate';
    else $rainStatus = 'Light';

    $stmt = $conn->prepare("INSERT INTO sensor_data (datetime, water, rain, flood_status, rain_status) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("siiss", $datetime, $water, $rain, $floodStatus, $rainStatus);
    if ($stmt->execute()) {
        echo " Data saved: Water=$water cm, Rain=$rain mm, Flood=$floodStatus, Rain=$rainStatus";
    } else {
        echo " Database error: " . $conn->error;
    }
    exit;
}

// ==========================
// Live AJAX Fetch
// ==========================
if (isset($_GET['action']) && $_GET['action'] === 'fetch') {
    // Fetch last 20 records for table
    $result = $conn->query("SELECT * FROM sensor_data ORDER BY datetime DESC LIMIT 20");
    $records = [];
    while ($row = $result->fetch_assoc()) {
        $records[] = [
            'datetime' => date("M d, Y h:i A", strtotime($row['datetime'])),
            'water' => $row['water'],
            'rain' => $row['rain'],
            'flood_status' => $row['flood_status'],
            'rain_status' => $row['rain_status']
        ];
    }

    // Fetch all records for chart
    $chartData = $conn->query("SELECT * FROM sensor_data ORDER BY datetime ASC");
    $trendLabels = [];
    $trendWater = [];
    $trendRain = [];
    while ($row = $chartData->fetch_assoc()) {
        $trendLabels[] = date("M d, h:i A", strtotime($row['datetime']));
        $trendWater[] = $row['water'];
        $trendRain[] = $row['rain'];
    }

    // Latest record and total count
    $latest = $conn->query("SELECT * FROM sensor_data ORDER BY datetime DESC LIMIT 1")->fetch_assoc();
    $totalRecords = $conn->query("SELECT COUNT(*) as total FROM sensor_data")->fetch_assoc()['total'];
    $latestWater = $latest ? $latest['water'] : 0;
    $latestRain = $latest ? $latest['rain'] : 0;
    $latestTime = $latest ? date("M d, Y h:i A", strtotime($latest['datetime'])) : 'N/A';

    echo json_encode([
        'totalRecords' => $totalRecords,
        'latestWater' => $latestWater,
        'latestRain' => $latestRain,
        'latestTime' => $latestTime,
        'records' => $records,
        'trends' => [
            'labels' => $trendLabels,   // all records
            'water' => $trendWater,
            'rain' => $trendRain
        ]
    ]);
    exit;
}

// ==================================================
//  Dashboard + Analytics (For Barangay Officials)
// ==================================================
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Barangay Official') {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sensor Data & Analytics - BahaShield</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body {font-family: Arial, sans-serif;margin:0;background:#f4f6f8;}
    header {background:#2563eb;color:white;padding:15px 20px;display:flex;align-items:center;justify-content:space-between;}
    .header-left {display:flex;align-items:center;gap:10px;}
    .menu-icon {font-size:22px;cursor:pointer;}
    .system-title {font-size:20px;margin:0;}
    .admin-name {font-weight:bold;}
    #sidebar {height:100%;width:0;position:fixed;z-index:2;top:0;left:0;background-color:#1e3a8a;overflow-x:hidden;transition:0.3s;padding-top:60px;}
    #sidebar a,#sidebar button {padding:10px 20px;text-decoration:none;font-size:16px;color:white;display:block;transition:0.2s;background:none;border:none;text-align:left;width:100%;}
    #sidebar a:hover,#sidebar button:hover {background-color:#2563eb;}
    #sidebar .closebtn {position:absolute;top:10px;right:20px;font-size:30px;color:white;cursor:pointer;}
    main {max-width:1100px;margin:20px auto;padding:0 15px;}
    h2 {margin:20px 0;text-align:center;}
    .stat-boxes {display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:15px;margin-bottom:20px;}
    .stat-box {background:#fff;border-radius:10px;padding:15px;box-shadow:0 2px 5px rgba(0,0,0,0.1);text-align:center;}
    .controls {display:flex;justify-content:space-between;margin-bottom:15px;}
    .search-bar input {padding:8px;width:200px;border:1px solid #ccc;border-radius:6px;}
    .btn.export {background:#2563eb;color:white;padding:8px 14px;border:none;border-radius:6px;cursor:pointer;}
    .table-container {background:#fff;border-radius:10px;box-shadow:0 2px 5px rgba(0,0,0,0.1);overflow-x:auto;overflow-y:auto;max-height:300px;margin-bottom:20px;}
    table {width:100%;border-collapse:collapse;text-align:center;}
    th {background:#2563eb;color:white;padding:10px;}
    td {padding:10px;border-bottom:1px solid #eee;}
    tr:hover {background:#f9fafb;}
    .content-card {background:#fff;border-radius:10px;padding:20px;margin-bottom:20px;box-shadow:0 2px 5px rgba(0,0,0,0.1);}
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
  <a href="admin-dashboard.php"> User Management</a>
  <a href="flood_history.php"> Flood & Rainfall History</a>
  <a href="sensor_data.php" class="active"> Sensor Data & Analytics</a>
  <a href="send_message.php"> Send SMS</a>
  <form method="POST" action="logout.php" onsubmit="return confirm('Logout?');">
    <button type="submit"> Logout</button>
  </form>
</div>

<main>
  <h2>📡 Sensor Data Monitoring</h2>

  <div class="stat-boxes">
    <div class="stat-box"><p>Total Records</p><h3 id="totalRecords">0</h3></div>
    <div class="stat-box"><p>Latest Water Level</p><h3 id="latestWater">0 cm</h3></div>
    <div class="stat-box"><p>Latest Rainfall</p><h3 id="latestRain">0 mm</h3></div>
    <div class="stat-box"><p>Last Update</p><h3 id="latestTime">N/A</h3></div>
  </div>

  <div class="controls">
    <div class="search-bar">
      <input type="text" id="searchInput" placeholder="Search records...">
    </div>
    <button class="btn export" onclick="exportTableToCSV()">Export CSV</button>
  </div>

  <div class="table-container">
    <table id="sensorTable">
      <thead>
        <tr>
          <th>Date & Time</th>
          <th>Water Level (cm)</th>
          <th>Rainfall (mm)</th>
          <th>Flood Status</th>
          <th>Rain Status</th>
        </tr>
      </thead>
      <tbody id="tableBody"></tbody>
    </table>
  </div>

  <div class="content-card">
    <h3>Rainfall & Water Level Trends (All Records)</h3>
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
const trendChart = new Chart(ctx, {
  type: 'line',
  data: { labels: [], datasets: [] },
  options: { responsive: true, plugins: { legend: { position: 'top' } }, scales: { y: { beginAtZero: true } } }
});

function fetchData() {
  fetch('fetch_sensor_data.php?action=fetch')
    .then(res => res.json())
    .then(data => {
      // Stats
      document.getElementById('totalRecords').innerText = data.totalRecords;
      document.getElementById('latestWater').innerText = data.latestWater + " cm";
      document.getElementById('latestRain').innerText = data.latestRain + " mm";
      document.getElementById('latestTime').innerText = data.latestTime;

      // Table (last 20)
      const tbody = document.getElementById('tableBody');
      tbody.innerHTML = '';
      data.records.forEach(row => {
        let floodColor = row.water >= 20 ? '#52c41a' : (row.water >= 10 ? '#ffc53d' : '#ff4d4f');
        let rainColor = row.rain >= 10 ? '#ff4d4f' : (row.rain >= 5 ? '#ffc53d' : '#52c41a');
        tbody.innerHTML += `
          <tr>
            <td>${row.datetime}</td>
            <td>${row.water}</td>
            <td>${row.rain}</td>
            <td><span style="background:${floodColor};color:#fff;padding:5px 10px;border-radius:999px;">${row.flood_status}</span></td>
            <td><span style="background:${rainColor};color:#fff;padding:5px 10px;border-radius:999px;">${row.rain_status}</span></td>
          </tr>`;
      });

      // Chart (all records)
      trendChart.data.labels = data.trends.labels;
      trendChart.data.datasets = [
        {
          label: 'Rainfall (mm)',
          data: data.trends.rain,
          borderColor: '#3b82f6',
          backgroundColor: 'rgba(59,130,246,0.2)',
          tension: 0.3
        },
        {
          label: 'Water Level (cm)',
          data: data.trends.water,
          borderColor: '#ef4444',
          backgroundColor: 'rgba(239,68,68,0.2)',
          tension: 0.3
        }
      ];
      trendChart.update();
    })
    .catch(err => console.error(err));
}

fetchData();
setInterval(fetchData, 5000);
</script>

</body>
</html>
