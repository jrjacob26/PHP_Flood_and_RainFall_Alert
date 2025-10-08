<?php
session_start();

// ✅ Only allow Barangay Officials
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Barangay Official') {
    header("Location: login.php");
    exit();
}

include 'db_connect.php';

// ✅ Add User Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $fullname = trim($_POST['fullname']);
    $email    = trim($_POST['email']);
    $number   = trim($_POST['number']);
    $role     = trim($_POST['role']);
    $purok    = intval($_POST['purok']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);

    // 🔑 Generate email verification token
    $verification_token = bin2hex(random_bytes(32));

    // 🔑 Generate unsubscribe token
    $unsubscribe_token = bin2hex(random_bytes(16));

    // ✅ Check for duplicates first
    $check = $conn->prepare("SELECT id FROM users WHERE email = ? OR number = ?");
    $check->bind_param("ss", $email, $number);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo "<script>alert('Email or Mobile Number already exists!'); 
              window.location.href='admin-dashboard.php';</script>";
        exit();
    }
    $check->close();

    // ✅ Insert new user
    $stmt = $conn->prepare("INSERT INTO users 
        (fullname, email, number, role, purok, password, created_at, email_verified, verification_token, unsubscribe_token, subscribed) 
        VALUES (?, ?, ?, ?, ?, ?, NOW(), 0, ?, ?, 1)");
    $stmt->bind_param("ssssisss", $fullname, $email, $number, $role, $purok, $password, $verification_token, $unsubscribe_token);

    if ($stmt->execute()) {
        echo "<script>alert('User added successfully!'); 
              window.location.href = 'admin-dashboard.php';</script>";
        exit();
    } else {
        echo "<script>alert('Failed to add user: " . addslashes($stmt->error) . "'); 
              window.location.href = 'admin-dashboard.php';</script>";
        exit();
    }
}

// ✅ Handle manual unsubscribe / resubscribe
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && in_array($_POST['action'], ['unsubscribe', 'resubscribe'])) {
    $id = intval($_POST['id']);
    $status = ($_POST['action'] === 'unsubscribe') ? 0 : 1;

    $stmt = $conn->prepare("UPDATE users SET subscribed=? WHERE id=?");
    $stmt->bind_param("ii", $status, $id);
    if ($stmt->execute()) {
        $msg = ($status === 0) ? "User unsubscribed successfully!" : "User resubscribed successfully!";
        echo "<script>alert('✅ $msg'); window.location.href='admin-dashboard.php';</script>";
        exit();
    } else {
        echo "<script>alert('Failed to update subscription!'); window.location.href='admin-dashboard.php';</script>";
        exit();
    }
}

//  Fetch all users
$sql = "SELECT * FROM users ORDER BY created_at DESC";
$result = $conn->query($sql);

//  Analytics counts
$totalUsers       = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];
$totalOfficials   = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='Barangay Official'")->fetch_assoc()['total'];
$totalResidents   = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='Resident'")->fetch_assoc()['total'];
$newThisMonth     = $conn->query("SELECT COUNT(*) as total FROM users WHERE MONTH(created_at)=MONTH(CURRENT_DATE()) AND YEAR(created_at)=YEAR(CURRENT_DATE())")->fetch_assoc()['total'];

//  SENSOR DATA HANDLING
date_default_timezone_set('Asia/Manila');
$currentDateTime = date("Y-m-d H:i:s");
$sensorMessage = "";
if (isset($_GET['water']) && isset($_GET['rain'])) {
    $waterLevel = $_GET['water'];
    $rainIntensity = $_GET['rain'];

    $sensorMessage = "
        <div style='background:#fff;padding:15px;margin:15px 0;border-radius:10px;box-shadow:0 2px 6px rgba(0,0,0,0.1);'>
            <h3>🌧 Sensor Data Received</h3>
            <p><b>Date & Time:</b> $currentDateTime</p>
            <p><b>Water Level:</b> $waterLevel cm</p>
            <p><b>Rain Intensity:</b> $rainIntensity</p>
        </div>
    ";
    
    $stmt = $conn->prepare("INSERT INTO sensor_data (datetime, water, rain) VALUES (?, ?, ?)");
    $stmt->bind_param("sis", $currentDateTime, $waterLevel, $rainIntensity);
    $stmt->execute();
    
} else {
    $sensorMessage = "<p style='color:gray;'>No sensor data received yet.</p>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BahaShield - Admin Dashboard</title>
  <style>
      
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: "Segoe UI", Tahoma, sans-serif;
}

body {
  background: #f5f6fa;
  color: #333;
  min-height: 100vh;
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
  font-weight: 600;
}

.admin-name {
  font-weight: bold;
}

.logout-btn {
  background: transparent;
  border: 1px solid white;
  color: white;
  padding: 6px 14px;
  border-radius: 6px;
  font-size: 14px;
  cursor: pointer;
  transition: background 0.2s, color 0.2s;
}

.logout-btn:hover {
  background: white;
  color: #2563eb;
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
  padding: 20px;
  max-width: 1400px;
  margin: 0 auto;
}

h2 {
  margin-top: 20px;
  margin-bottom: 20px;
  text-align: center;
  
}

/*  Controls */
.controls {
  display: flex;
  justify-content: space-between;
  flex-wrap: wrap;
  margin-bottom: 15px;
}

.controls-left {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.search-bar input {
  padding: 8px 12px;
  border-radius: 5px;
  border: 1px solid #ccc;
  min-width: 200px;
}

select {
  padding: 8px 12px;
  border-radius: 5px;
  border: 1px solid #ccc;
}

/*  Buttons */
button, .btn {
  border: none;
  cursor: pointer;
  border-radius: 5px;
  padding: 7px 12px;
  font-size: 0.9rem;
  transition: background 0.2s;
}

.add-btn {
  background: #0096c7;
  color: white;
}

.add-btn:hover {
  background: #0077b6;
}

.btn.save {
  background: #2b9348;
  color: white;
}

.btn.save:hover {
  background: #1b7034;
}

.btn.edit {
  background: #0077b6;
  color: white;
}

.btn.edit:hover {
  background: #005f91;
}

.btn.delete {
  background: #d62828;
  color: white;
}

.btn.delete:hover {
  background: #b51e1e;
}

.btn.export {
  background: #555;
  color: white;
}

.btn.export:hover {
  background: #333;
}

/*  Table */
.table-container {
  background: #fff;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  overflow-x: auto;
  margin-top: 15px;
}

table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.95rem;
}

th, td {
  padding: 10px;
  text-align: left;
  border-bottom: 1px solid #eaeaea;
}

th {
  background-color: #f0f2f5;
  color: #333;
  font-weight: 600;
}

tr:hover {
  background-color: #f8f9fa;
}

/*  Badges */
.badge {
  display: inline-block;
  padding: 3px 8px;
  font-size: 0.75rem;
  border-radius: 5px;
  color: white;
}

.badge.green { background: #2b9348; }
.badge.red   { background: #d62828; }
.badge.gray  { background: #999; }

/*  Modal Styles */
.modal {
  display: none;
  position: fixed;
  z-index: 2000;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  overflow: auto;
  background-color: rgba(0,0,0,0.5);
  justify-content: center;
  align-items: center;
}

.modal-content {
  background-color: #fff;
  padding: 20px;
  border-radius: 12px;
  width: 90%;
  max-width: 500px;
  position: relative;
  animation: fadeIn 0.3s ease;
}

.close-btn {
  position: absolute;
  top: 10px;
  right: 15px;
  font-size: 1.5rem;
  color: #666;
  cursor: pointer;
}

.close-btn:hover {
  color: #000;
}

.form-group {
  margin-bottom: 12px;
}

.form-group label {
  display: block;
  font-weight: 600;
  margin-bottom: 5px;
}

.form-group input, 
.form-group select, 
.form-group textarea {
  width: 100%;
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 5px;
}

.modal h3 {
  margin-bottom: 10px;
  color: #023e8a;
  font-size: 1.2rem;
  text-align: center;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-20px); }
  to   { opacity: 1; transform: translateY(0); }
}

/*  Analytics Cards */
.analytics > div {
  transition: transform 0.2s;
}

.analytics > div:hover {
  transform: translateY(-3px);
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

<!--  Sidebar -->
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

<script>
function confirmLogout() {
    return confirm("Are you sure you want to logout?");
}
</script>
</div>

<main>
  <h2>User Management</h2>

  <!--  Analytics Section -->
  <div class="analytics" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:15px;margin-bottom:20px;">
    <div style="background:#fff;padding:15px;border-radius:10px;text-align:center;box-shadow:0 2px 6px rgba(0,0,0,0.1);">
      <h3><?php echo $totalUsers; ?></h3>
      <p>Total Users</p>
    </div>
    <div style="background:#fff;padding:15px;border-radius:10px;text-align:center;box-shadow:0 2px 6px rgba(0,0,0,0.1);">
      <h3><?php echo $totalOfficials; ?></h3>
      <p>Barangay Officials</p>
    </div>
    <div style="background:#fff;padding:15px;border-radius:10px;text-align:center;box-shadow:0 2px 6px rgba(0,0,0,0.1);">
      <h3><?php echo $totalResidents; ?></h3>
      <p>Residents</p>
    </div>
    <div style="background:#fff;padding:15px;border-radius:10px;text-align:center;box-shadow:0 2px 6px rgba(0,0,0,0.1);">
      <h3><?php echo $newThisMonth; ?></h3>
      <p>New This Month</p>
    </div>
  </div>

  <!--  Controls -->
  <div class="controls">
    <div class="controls-left">
      <div class="search-bar">
        <input type="text" id="searchInput" placeholder="Search users...">
      </div>
      <button class="add-btn" onclick="openAddModal()">+ Add User</button>
      <button class="btn export" onclick="exportTableToCSV()">Export CSV</button>
      <button class="btn export" onclick="printTable()">Print / PDF</button>
    </div>
    <div class="role-filter">
      <select id="roleFilter">
        <option value="">Filter by Role</option>
        <option value="Barangay Official">Barangay Official</option>
        <option value="Resident">Resident</option>
      </select>
    </div>
  </div>

  <!--  User Table -->
  <div class="table-container">
    <table id="userTable">
      <thead>
        <tr>
          <th>Full Name</th>
          <th>Email</th>
          <th>Mobile Number</th>
          <th>Role</th>
          <th>Purok</th>
          <th>Email Status</th>
          <th>Subscription</th>
          <th>Date Registered</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()) { ?>
          <tr>
            <td><?php echo htmlspecialchars($row['fullname']); ?></td>
            <td><?php echo htmlspecialchars($row['email']); ?></td>
            <td><?php echo htmlspecialchars($row['number']); ?></td>
            <td><?php echo htmlspecialchars($row['role']); ?></td>
            <td><?php echo "Purok " . htmlspecialchars($row['purok']); ?></td>
            <td>
              <?php if ((int)($row['email_verified'] ?? 0) === 1) { ?>
                <span class="badge green">Verified</span>
              <?php } else { ?>
                <span class="badge gray">Not Verified</span>
              <?php } ?>
            </td>
            <td>
              <?php if ((int)$row['subscribed'] === 1) { ?>
                <span class="badge green">Subscribed</span>
              <?php } else { ?>
                <span class="badge red">Unsubscribed</span>
              <?php } ?>
            </td>
            <td><?php echo date("M d, Y h:i A", strtotime($row['created_at'])); ?></td>
            <td>
              <button type="button" class="btn edit" 
                onclick="openEditModal(
                  '<?php echo $row['id']; ?>',
                  '<?php echo htmlspecialchars($row['fullname'], ENT_QUOTES); ?>',
                  '<?php echo htmlspecialchars($row['email'], ENT_QUOTES); ?>',
                  '<?php echo htmlspecialchars($row['number'], ENT_QUOTES); ?>',
                  '<?php echo htmlspecialchars($row['role'], ENT_QUOTES); ?>',
                  '<?php echo htmlspecialchars($row['purok'], ENT_QUOTES); ?>'
                )">Edit</button>

              <button type="button" class="btn save"
                onclick="openEmailModal(
                  '<?php echo $row['id']; ?>',
                  '<?php echo htmlspecialchars($row['fullname'], ENT_QUOTES); ?>',
                  '<?php echo htmlspecialchars($row['email'], ENT_QUOTES); ?>',
                  <?php echo ((int)($row['email_verified'] ?? 0) === 1) ? "'notify'" : "'verify'"; ?>
                )">Email</button>

              <!--  Subscription toggle -->
              <form method="POST" action="admin-dashboard.php" style="display:inline;" 
                    onsubmit="return confirm('Are you sure you want to change subscription status?');">
                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                <?php if ((int)$row['subscribed'] === 1) { ?>
                  <input type="hidden" name="action" value="unsubscribe">
                  <button type="submit" class="btn delete">Unsubscribe</button>
                <?php } else { ?>
                  <input type="hidden" name="action" value="resubscribe">
                  <button type="submit" class="btn save">Resubscribe</button>
                <?php } ?>
              </form>

              <form method="POST" action="delete_user.php" style="display:inline;" 
                    onsubmit="return confirm('Are you sure you want to delete this user?');">
                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                <button type="submit" class="btn delete">Delete</button>
              </form>
            </td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</main>

<!--  Add User Modal -->
<div class="modal" id="addModal">
  <div class="modal-content">
    <span class="close-btn" onclick="closeAddModal()">&times;</span>
    <h3>Add User</h3>
    <form method="POST" action="admin-dashboard.php">
      <input type="hidden" name="action" value="add">
      <div class="form-group"><label>Full Name</label><input type="text" name="fullname" required></div>
      <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
      <div class="form-group"><label>Mobile Number</label><input type="text" name="number" required></div>
      <div class="form-group"><label>Password</label><input type="password" name="password" required></div>
      <div class="form-group">
        <label>Role</label>
        <select name="role" required>
          <option value="Barangay Official">Barangay Official</option>
          <option value="Resident">Resident</option>
        </select>
      </div>
      <div class="form-group">
        <label>Purok</label>
        <select name="purok" required>
          <?php for ($i=1; $i<=8; $i++) { ?>
            <option value="<?php echo $i; ?>">Purok <?php echo $i; ?></option>
          <?php } ?>
        </select>
      </div>
      <button type="submit" class="btn save">Add User</button>
    </form>
  </div>
</div>

<!--  Edit User Modal -->
<div class="modal" id="editModal">
  <div class="modal-content">
    <span class="close-btn" onclick="closeEditModal()">&times;</span>
    <h3>Edit User</h3>
    <form method="POST" action="update_user.php">
      <input type="hidden" name="id" id="edit-id">
      <div class="form-group"><label>Full Name</label><input type="text" name="fullname" id="edit-fullname" required></div>
      <div class="form-group"><label>Email</label><input type="email" name="email" id="edit-email" required></div>
      <div class="form-group"><label>Mobile Number</label><input type="text" name="number" id="edit-number" required></div>
      <div class="form-group">
        <label>Role</label>
        <select name="role" id="edit-role" required>
          <option value="Barangay Official">Barangay Official</option>
          <option value="Resident">Resident</option>
        </select>
      </div>
      <div class="form-group">
        <label>Purok</label>
        <select name="purok" id="edit-purok" required>
          <?php for ($i=1; $i<=8; $i++) { ?>
            <option value="<?php echo $i; ?>">Purok <?php echo $i; ?></option>
          <?php } ?>
        </select>
      </div>
      <button type="submit" class="btn save">Save Changes</button>
    </form>
  </div>
</div>

<!--  Email Modal -->
<div class="modal" id="emailModal">
  <div class="modal-content">
    <span class="close-btn" onclick="closeEmailModal()">&times;</span>
    <h3>Send Email</h3>
    <form method="POST" action="send_verification.php">
      <input type="hidden" name="id" id="email-user-id">
      <input type="hidden" name="send_type" id="email-send-type" value="verify">

      <div class="form-group">
        <label>Recipient</label>
        <input type="text" id="email-recipient" disabled>
      </div>

      <div class="form-group">
        <label>Subject</label>
        <input type="text" name="subject" id="email-subject" placeholder="Verify your BahaShield account">
      </div>

      <div class="form-group">
        <label>Message </label>
        <textarea name="message" id="email-message" rows="12"></textarea>
      </div>

      <button type="submit" class="btn save">Send Email</button>
    </form>
  </div>
</div>

<script>
//  Search
document.getElementById("searchInput").addEventListener("keyup", function() {
  let value = this.value.toLowerCase();
  let rows = document.querySelectorAll("#userTable tbody tr");
  rows.forEach(row => {
    row.style.display = row.innerText.toLowerCase().includes(value) ? "" : "none";
  });
});

//  Role Filter
document.getElementById("roleFilter").addEventListener("change", function() {
  let role = this.value.toLowerCase();
  let rows = document.querySelectorAll("#userTable tbody tr");
  rows.forEach(row => {
    let roleCell = row.cells[3].innerText.toLowerCase();
    row.style.display = (role === "" || roleCell === role) ? "" : "none";
  });
});

//  Export CSV
function downloadCSV(csv, filename) {
  let csvFile = new Blob([csv], { type: "text/csv" });
  let downloadLink = document.createElement("a");
  downloadLink.download = filename;
  downloadLink.href = window.URL.createObjectURL(csvFile);
  downloadLink.style.display = "none";
  document.body.appendChild(downloadLink);
  downloadLink.click();
}

function exportTableToCSV() {
  let csv = [];
  let rows = document.querySelectorAll("#userTable tr");
  for (let i = 0; i < rows.length; i++) {
    let row = [], cols = rows[i].querySelectorAll("td, th");
    for (let j = 0; j < cols.length - 1; j++) { 
      row.push('"' + cols[j].innerText.replace(/"/g, '""') + '"');
    }
    csv.push(row.join(","));
  }
  downloadCSV(csv.join("\n"), "users.csv");
}

// 🖨 Print / PDF
function printTable() {
  let table = document.getElementById("userTable").cloneNode(true);
  table.querySelectorAll("tr").forEach(tr => tr.deleteCell(tr.cells.length - 1));

  let newWin = window.open("");
  newWin.document.write(`
    <html>
      <head>
        <title>User Management Report</title>
        <style>
          body { font-family: Arial, sans-serif; padding: 20px; }
          h2 { text-align: center; margin-bottom: 20px; }
          p.summary { text-align: center; margin-bottom: 15px; font-weight: bold; }
          table { width: 100%; border-collapse: collapse; }
          th, td { border: 1px solid #000; padding: 8px; text-align: left; font-size: 14px; }
          th { background: #f0f0f0; }
        </style>
      </head>
      <body>
        <h2>User Management Report</h2>
        <p class="summary">
           Total Users: <?php echo $totalUsers; ?> &nbsp; | 
           Officials: <?php echo $totalOfficials; ?> &nbsp; | 
           Residents: <?php echo $totalResidents; ?> &nbsp; | 
           New This Month: <?php echo $newThisMonth; ?>
        </p>
        ${table.outerHTML}
      </body>
    </html>
  `);
  newWin.document.close();
  newWin.print();
}

//  Modals
function openAddModal() { document.getElementById('addModal').style.display = 'flex'; }
function closeAddModal() { document.getElementById('addModal').style.display = 'none'; }

function openEditModal(id, fullname, email, number, role, purok) {
  document.getElementById('edit-id').value = id;
  document.getElementById('edit-fullname').value = fullname;
  document.getElementById('edit-email').value = email;
  document.getElementById('edit-number').value = number;
  document.getElementById('edit-role').value = role;
  document.getElementById('edit-purok').value = purok;
  document.getElementById('editModal').style.display = 'flex';
}
function closeEditModal() { document.getElementById('editModal').style.display = 'none'; }

function openEmailModal(id, fullname, email, type) {
  document.getElementById('email-user-id').value = id;
  document.getElementById('email-recipient').value = fullname + " <" + email + ">";
  document.getElementById('email-send-type').value = type;

  if (type === 'verify') {
    document.getElementById('email-subject').value = "Verify your BahaShield account";
    document.getElementById('email-message').value = "";
  } else {
    document.getElementById('email-subject').value = "You are registered to BahaShield";
    document.getElementById('email-message').value = "";
  }

  document.getElementById('emailModal').style.display = 'flex';
}
function closeEmailModal() { document.getElementById('emailModal').style.display = 'none'; }

window.onclick = function(event) {
  if (event.target == document.getElementById('addModal')) closeAddModal();
  if (event.target == document.getElementById('editModal')) closeEditModal();
  if (event.target == document.getElementById('emailModal')) closeEmailModal();
}

//  Sidebar Toggle
function toggleMenu() {
  let sidebar = document.getElementById("sidebar");
  sidebar.style.width = (sidebar.style.width === "250px") ? "0" : "250px";
}
window.addEventListener("click", function(event) {
  let sidebar = document.getElementById("sidebar");
  if (!event.target.closest('#sidebar') && !event.target.closest('.menu-icon')) {
    sidebar.style.width = "0";
  }
});

//  Prevent back button after logout
window.addEventListener("pageshow", function (event) {
  if (event.persisted || performance.getEntriesByType("navigation")[0].type === "back_forward") {
    window.location.reload();
  }
});
</script>
</body>
</html>
