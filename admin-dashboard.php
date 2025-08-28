<?php
session_start();

// Only allow Admins
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

include 'db_connect.php';

// Handle Add User (from modal)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $fullname = trim($_POST['fullname']);
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $number   = trim($_POST['number']);
    $role     = trim($_POST['role']);
    $purok    = intval($_POST['purok']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT); // hashed password from input

    $stmt = $conn->prepare("INSERT INTO users (fullname, username, email, number, role, purok, password) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssis", $fullname, $username, $email, $number, $role, $purok, $password);

    if ($stmt->execute()) {
        echo "<script>alert('✅ User added successfully!'); 
              window.location.href = 'admin-dashboard.php';</script>";
        exit();
    } else {
        echo "<script>alert('❌ Failed to add user: " . addslashes($stmt->error) . "'); 
              window.location.href = 'admin-dashboard.php';</script>";
        exit();
    }
}

// Fetch all users
$sql = "SELECT * FROM users";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BahaShield - Admin Dashboard</title>
  <link rel="stylesheet" href="admin-dashboard.css">
  <style>
    h2 {
      text-align: center;
      margin-bottom: 20px;
      font-size: 30px;
      font-weight: 600;
      color: #444;
    }
    .add-btn {
      background-color: #007bff;
      color: white;
      border: none;
      padding: 8px 15px;
      border-radius: 5px;
      cursor: pointer;
      margin-bottom: 15px;
    }
    .add-btn:hover {
      background-color: #0056b3;
    }
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0; top: 0;
      width: 100%; height: 100%;
      background: rgba(0,0,0,0.5);
      justify-content: center; align-items: center;
    }
    .modal-content {
      background: #fff;
      padding: 20px;
      border-radius: 10px;
      width: 400px;
    }
    .close-btn {
      float: right;
      cursor: pointer;
      font-size: 20px;
    }
    .modal-content input[type="text"],
    .modal-content input[type="email"],
    .modal-content input[type="password"],
    .modal-content select {
      width: 380px;
      padding: 6px;
      margin-top: 5px;
      border: 1px solid #ccc;
      border-radius: 4px;
    }

  </style>
</head>
<body>
  <header>
    <div class="header-left">
      <h1 class="system-title">🌊 BahaShield</h1>
      <span class="admin-name">Welcome, <?php echo htmlspecialchars($_SESSION['fullname']); ?></span>
    </div>
    <form method="POST" action="logout.php">
      <button type="submit" class="logout-btn">Logout</button>
    </form>
  </header>

  <main>
    <h2>User Management</h2>

    <!-- Add User Button -->
    <button class="add-btn" onclick="openAddModal()">Add User</button>

    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Full Name</th>
            <th>Username</th>
            <th>Email</th>
            <th>Mobile Number</th>
            <th>Role</th>
            <th>Purok</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
              <td><?php echo htmlspecialchars($row['fullname']); ?></td>
              <td><?php echo htmlspecialchars($row['username']); ?></td>
              <td><?php echo htmlspecialchars($row['email']); ?></td>
              <td><?php echo htmlspecialchars($row['number']); ?></td>
              <td><?php echo htmlspecialchars($row['role']); ?></td>
              <td><?php echo "Purok " . htmlspecialchars($row['purok']); ?></td>
              <td>
                <button type="button" class="btn edit" 
                  onclick="openEditModal(
                    '<?php echo $row['id']; ?>',
                    '<?php echo htmlspecialchars($row['fullname'], ENT_QUOTES); ?>',
                    '<?php echo htmlspecialchars($row['username'], ENT_QUOTES); ?>',
                    '<?php echo htmlspecialchars($row['email'], ENT_QUOTES); ?>',
                    '<?php echo htmlspecialchars($row['number'], ENT_QUOTES); ?>',
                    '<?php echo htmlspecialchars($row['role'], ENT_QUOTES); ?>',
                    '<?php echo htmlspecialchars($row['purok'], ENT_QUOTES); ?>'
                  )">Edit</button>
                <form method="POST" action="delete_user.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
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

  <!-- Add User Modal -->
  <div class="modal" id="addModal">
    <div class="modal-content">
      <span class="close-btn" onclick="closeAddModal()">&times;</span>
      <h3>Add User</h3>
      <form method="POST" action="admin-dashboard.php">
        <input type="hidden" name="action" value="add">
        <div class="form-group">
          <label>Full Name</label>
          <input type="text" name="fullname" required>
        </div>
        <div class="form-group">
          <label>Username</label>
          <input type="text" name="username" required>
        </div>
        <div class="form-group">
          <label>Email</label>
          <input type="email" name="email" required>
        </div>
        <div class="form-group">
          <label>Mobile Number</label>
          <input type="text" name="number">
        </div>
        <div class="form-group">
          <label>Password</label>
          <input type="password" name="password" required>
        </div>
        <div class="form-group">
          <label>Role</label>
          <select name="role" required>
            <option value="Resident">Resident</option>
            <option value="Admin">Admin</option>
            <option value="Responder">Responder</option>
            <option value="Barangay Official">Barangay Official</option>
          </select>
        </div>
        <div class="form-group">
          <label>Purok</label>
          <select name="purok" required>
            <?php for ($i=1; $i<=10; $i++) { ?>
              <option value="<?php echo $i; ?>">Purok <?php echo $i; ?></option>
            <?php } ?>
          </select>
        </div>
        <button type="submit" class="btn save">Add User</button>
      </form>
    </div>
  </div>

  <!-- Edit User Modal -->
  <div class="modal" id="editModal">
    <div class="modal-content">
      <span class="close-btn" onclick="closeEditModal()">&times;</span>
      <h3>Edit User</h3>
      <form method="POST" action="update_user.php">
        <input type="hidden" name="id" id="edit-id">

        <div class="form-group">
          <label>Full Name</label>
          <input type="text" name="fullname" id="edit-fullname" required>
        </div>
        <div class="form-group">
          <label>Username</label>
          <input type="text" name="username" id="edit-username" required>
        </div>
        <div class="form-group">
          <label>Email</label>
          <input type="email" name="email" id="edit-email" required>
        </div>
        <div class="form-group">
          <label>Mobile Number</label>
          <input type="text" name="number" id="edit-number">
        </div>
        <div class="form-group">
          <label>Role</label>
          <select name="role" id="edit-role" required>
            <option value="Resident">Resident</option>
            <option value="Admin">Admin</option>
            <option value="Responder">Responder</option>
            <option value="Barangay Official">Barangay Official</option>
          </select>
        </div>
        <div class="form-group">
          <label>Purok</label>
          <select name="purok" id="edit-purok" required>
            <?php for ($i=1; $i<=10; $i++) { ?>
              <option value="<?php echo $i; ?>">Purok <?php echo $i; ?></option>
            <?php } ?>
          </select>
        </div>

        <button type="submit" class="btn save">Save Changes</button>
      </form>
    </div>
  </div>

  <script>
    // Add User Modal Functions
    function openAddModal() {
      document.getElementById('addModal').style.display = 'flex';
    }
    function closeAddModal() {
      document.getElementById('addModal').style.display = 'none';
    }

    // Edit User Modal Functions
    function openEditModal(id, fullname, username, email, number, role, purok) {
      document.getElementById('edit-id').value = id;
      document.getElementById('edit-fullname').value = fullname;
      document.getElementById('edit-username').value = username;
      document.getElementById('edit-email').value = email;
      document.getElementById('edit-number').value = number;
      document.getElementById('edit-role').value = role;
      document.getElementById('edit-purok').value = purok;
      document.getElementById('editModal').style.display = 'flex';
    }
    function closeEditModal() {
      document.getElementById('editModal').style.display = 'none';
    }

    // Close modal if clicking outside
    window.onclick = function(event) {
      if (event.target == document.getElementById('addModal')) {
        closeAddModal();
      }
      if (event.target == document.getElementById('editModal')) {
        closeEditModal();
      }
    }
  </script>
</body>
</html>
