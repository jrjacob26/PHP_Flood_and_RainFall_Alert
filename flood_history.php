<?php
session_start();
include 'db_connect.php';

//  Only allow Barangay Officials
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Barangay Official') {
    header("Location: admin-login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Flood & Rainfall History - BahaShield</title>
<style>
/* Global Reset */
* { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
body { background: #f4f6f8; min-height: 100vh; }

/* Header */
header { background: #2563eb; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
.header-left { display: flex; align-items: center; gap: 10px; }
.menu-icon { font-size: 22px; cursor: pointer; }
.system-title { font-size: 20px; }
.admin-name { font-weight: bold; }

/* Sidebar */
#sidebar { height: 100%; width: 0; position: fixed; z-index: 2; top: 0; left: 0; background-color: #1e3a8a; overflow-x: hidden; transition: 0.3s; padding-top: 60px; }
#sidebar a, #sidebar button { padding: 10px 20px; text-decoration: none; font-size: 16px; color: white; display: block; border: none; background:none; text-align:left; width:100%; transition:0.2s; }
#sidebar a:hover, #sidebar button:hover { background-color:#2563eb; }
#sidebar .closebtn { position:absolute; top:10px; right:20px; font-size:30px; color:white; cursor:pointer; }

/* Main Content */
main { max-width:1100px; margin:20px auto; padding:0 15px; }
main h2 { text-align:center; margin-bottom:20px; }

/* Card Section */
.content-card { background:#fff; border-radius:10px; padding:20px; box-shadow:0 2px 5px rgba(0,0,0,0.1); margin-bottom:20px; }
.content-card h3 { text-align:center; margin-bottom:15px; }

/* Table Styling */
.modern-table { width:100%; border-collapse: collapse; border-radius:10px; overflow:hidden; }
.modern-table thead { background:#2563eb; color:white; }
.modern-table th, .modern-table td { padding:10px; text-align:center; border-bottom:1px solid #eaeaea; }
.modern-table tr:hover { background-color:#f9fafb; }

/* Status Badges */
.status { display:inline-block; padding:5px 10px; border-radius:999px; font-size:0.8rem; font-weight:bold; color:white; }
.status.low { background-color:#52c41a; }       /* Low/Light */
.status.moderate { background-color:#ffc53d; } /* Medium/Moderate */
.status.high { background-color:#ff4d4f; }     /* High/Heavy */
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
  <a href="flood_history.php" class="active"> Flood & Rainfall History</a>
  <a href="sensor_data.php"> Sensor Data & Analytics</a>
  <a href="send_message.php"> Send SMS</a>
  <form method="POST" action="logout.php" onsubmit="return confirm('Logout?');">
    <button type="submit"> Logout</button>
  </form>
</div>

<main>
  <h2>Flood & Rainfall History</h2>

  <section class="content-card">
    <h3>Recent Records</h3>
    <div class="table-container">
      <table class="modern-table" id="historyTable">
        <thead>
          <tr>
            <th>Date & Time</th>
            <th>Water Level (cm)</th>
            <th>Flood Status</th>
            <th>Rainfall (mm)</th>
            <th>Rain Status</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </section>
</main>

<script>
// Sidebar toggle
function toggleMenu() {
  const sidebar = document.getElementById("sidebar");
  sidebar.style.width = (sidebar.style.width === "250px") ? "0" : "250px";
}

// Fetch sensor_data.php JSON and populate table
function fetchHistory() {
  fetch('fetch_sensor_data.php?action=fetch')
    .then(res => res.json())
    .then(data => {
      const tbody = document.querySelector("#historyTable tbody");
      tbody.innerHTML = '';
      // Display last 10 records
      data.records.slice(0,10).forEach(row => {
        let floodColor = row.flood_status === "High" ? "#ff4d4f" : (row.flood_status === "Medium" ? "#ffc53d" : "#52c41a");
        let rainColor = row.rain_status === "Heavy" ? "#ff4d4f" : (row.rain_status === "Moderate" ? "#ffc53d" : "#52c41a");
        tbody.innerHTML += `
          <tr>
            <td>${row.datetime}</td>
            <td>${row.water}</td>
            <td><span class="status" style="background:${floodColor}">${row.flood_status}</span></td>
            <td>${row.rain}</td>
            <td><span class="status" style="background:${rainColor}">${row.rain_status}</span></td>
          </tr>
        `;
      });
    })
    .catch(err => console.error(err));
}

// Initial fetch & auto-update every 5s
fetchHistory();
setInterval(fetchHistory, 5000);
</script>

</body>
</html>
