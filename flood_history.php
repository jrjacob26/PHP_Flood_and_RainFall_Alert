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

    <!-- ✅ Back Button -->
    <div class="back-button">
      <a href="admin-dashboard.php">⬅️ Back to Dashboard</a>
    </div>

    <section class="content-card">
      <h3>Recent Records</h3>
      <table class="modern-table">
        <thead>
          <tr>
            <th>Date</th>
            <th>Rainfall (mm)</th>
            <th>Flood Level (cm)</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $result = $conn->query("SELECT * FROM flood_history ORDER BY date DESC LIMIT 10");
          if ($result->num_rows > 0) {
              while ($row = $result->fetch_assoc()) {
                  echo "<tr>
                          <td>{$row['date']}</td>
                          <td>{$row['rainfall']}</td>
                          <td>{$row['flood']}</td>
                          <td><span class='status " . strtolower($row['status']) . "'>" . ucfirst($row['status']) . "</span></td>
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
