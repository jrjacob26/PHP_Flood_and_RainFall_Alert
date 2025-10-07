<?php
session_start();
include 'db_connect.php';

// ✅ Only allow Barangay Officials
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
  <style>
/* 🌊 BahaShield - Flood & Rainfall History Page CSS */

/* ✅ Global Reset */
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

/* ✅ Header (same as sensor_data.php) */
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

.system-title {
  font-size: 20px;
  margin: 0;
}

.admin-name {
  font-weight: bold;
}

/* ✅ Sidebar */
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

/* ✅ Main Content */
main {
  max-width: 1100px;
  margin: 20px auto;
  padding: 0 15px;
}

main h2 {
  text-align: center;
  margin-bottom: 20px;
  
}

/* ✅ Card Section */
.content-card {
  background: #fff;
  border-radius: 10px;
  padding: 20px;
  box-shadow: 0 2px 5px rgba(0,0,0,0.1);
  margin-bottom: 20px;
}

.content-card h3 {
  margin-bottom: 15px;
  
  text-align: center;
}

/* ✅ Table Styling */
.modern-table {
  width: 100%;
  border-collapse: collapse;
  overflow: hidden;
  border-radius: 10px;
}

.modern-table thead {
  background: #2563eb;
  color: white;
}

.modern-table th,
.modern-table td {
  padding: 10px;
  text-align: center;
  border-bottom: 1px solid #eaeaea;
}

.modern-table tr:hover {
  background-color: #f9fafb;
}

.modern-table td {
  font-size: 0.95rem;
}

/* ✅ Status Badges */
.status {
  display: inline-block;
  padding: 5px 10px;
  border-radius: 15px;
  font-size: 0.8rem;
  font-weight: bold;
  color: white;
}

.status.low {
  background-color: #2b9348; /* Green */
}

.status.moderate {
  background-color: #f4a261; /* Yellow-Orange */
}

.status.high {
  background-color: #d62828; /* Red */
}

/* ✅ Responsive */
@media (max-width: 600px) {
  .modern-table th,
  .modern-table td {
    padding: 8px;
    font-size: 0.85rem;
  }
}
  </style>
</head>
<body>

<!-- ✅ Top Header -->
<header>
  <div class="header-left">
    <div class="menu-icon" onclick="toggleMenu()">☰</div>
    <h1 class="system-title">🌊 BahaShield</h1>
  </div>
  <span class="admin-name">Welcome, <?php echo htmlspecialchars($_SESSION['fullname']); ?></span>
</header>

<!-- ✅ Sidebar -->
<div id="sidebar">
  <span class="closebtn" onclick="toggleMenu()">×</span>
  <a href="admin-dashboard.php">👤 User Management</a>
  <a href="flood_history.php" class="active">📊 Flood & Rainfall History</a>
  <a href="sensor_data.php">📡 Sensor Data & Analytics</a>
  <a href="send_message.php">💬 Send SMS</a>
  <form method="POST" action="logout.php" onsubmit="return confirm('Logout?');">
    <button type="submit">🚪 Logout</button>
  </form>
</div>

<!-- ✅ Main Content -->
<main>
  <h2>📊 Flood & Rainfall History</h2>

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
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $status = "Safe";
                $status_class = "low";
                if ($row['water'] >= 20 && $row['water'] < 30) {
                    $status = "Warning";
                    $status_class = "moderate";
                } elseif ($row['water'] >= 30) {
                    $status = "Danger";
                    $status_class = "high";
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

<!-- ✅ Sidebar Toggle Script -->
<script>
function toggleMenu() {
  const sidebar = document.getElementById("sidebar");
  sidebar.style.width = (sidebar.style.width === "250px") ? "0" : "250px";
}
</script>

</body>
</html>
