    <?php
    session_start();

    // Only allow Barangay Officials
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Barangay Official') {
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
        $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT); // hashed password

        $stmt = $conn->prepare("INSERT INTO users (fullname, username, email, number, role, purok, password, created_at) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
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
    $sql = "SELECT * FROM users ORDER BY created_at DESC";
    $result = $conn->query($sql);

    // Analytics counts
    $totalUsersQuery = $conn->query("SELECT COUNT(*) as total FROM users");
    $totalUsers = $totalUsersQuery->fetch_assoc()['total'];

    $officialsQuery = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='Barangay Official'");
    $totalOfficials = $officialsQuery->fetch_assoc()['total'];

    $residentsQuery = $conn->query("SELECT COUNT(*) as total FROM users WHERE role='Resident'");
    $totalResidents = $residentsQuery->fetch_assoc()['total'];

    $newThisMonthQuery = $conn->query("SELECT COUNT(*) as total FROM users WHERE MONTH(created_at)=MONTH(CURRENT_DATE()) AND YEAR(created_at)=YEAR(CURRENT_DATE())");
    $newThisMonth = $newThisMonthQuery->fetch_assoc()['total'];
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>BahaShield - Admin Dashboard</title>
                  <style>
        /* General Reset */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }

    body {
      background: #f4f6f9;
      color: #333;
      line-height: 1.6;
      padding: 20px;
    }

    /* Page Title */
    h2 {
      text-align: center;
      margin-bottom: 20px;
      font-size: 26px;
      font-weight: 700;
      color: #222;
    }

    /* Header */
    header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: linear-gradient(135deg, #007bff, #0056b3);
      color: white;
      padding: 15px 20px;
      border-radius: 10px;
      margin-bottom: 25px;
      box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }

    header h1.system-title {
      margin: 0;
      font-size: 22px;
      font-weight: 600;
    }

    header span {
      margin-left: 10px;
      font-size: 14px;
      opacity: 0.9;
    }

    /* Logout button */
    .logout-btn {
      background: #dc3545;
      color: white;
      border: none;
      padding: 8px 15px;
      border-radius: 6px;
      cursor: pointer;
      font-size: 14px;
      transition: background 0.3s ease;
    }
    .logout-btn:hover {
      background: #c82333;
    }

    /* Controls (Search + Filter + Add) */
    .controls {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
      flex-wrap: wrap;
      gap: 10px;
    }

    .controls-left {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    /* Add button */
    .add-btn {
      background: #007bff;
      color: white;
      border: none;
      padding: 10px 18px;
      border-radius: 8px;
      cursor: pointer;
      font-size: 14px;
      font-weight: 600;
      transition: all 0.3s ease;
    }
    .add-btn:hover {
      background: #0056b3;
      transform: translateY(-2px);
    }

    /* Search bar */
    .search-bar input {
      padding: 10px 12px;
      border: 1px solid #ccc;
      border-radius: 8px;
      width: 220px;
      font-size: 14px;
      transition: border 0.3s ease, box-shadow 0.3s ease;
    }
    .search-bar input:focus {
      border: 1px solid #007bff;
      outline: none;
      box-shadow: 0 0 6px rgba(0,123,255,0.3);
    }

    /* Role filter */
    .role-filter select {
      padding: 10px 12px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 14px;
      transition: border 0.3s ease, box-shadow 0.3s ease;
    }
    .role-filter select:focus {
      border: 1px solid #007bff;
      box-shadow: 0 0 6px rgba(0,123,255,0.3);
    }

    /* Table */
    .table-container {
      overflow-x: auto;
      margin-top: 15px;
      border-radius: 10px;
      box-shadow: 0 3px 12px rgba(0,0,0,0.08);
    }

    #userTable {
      width: 100%;
      border-collapse: collapse;
      background: #fff;
      border-radius: 10px;
      overflow: hidden;
    }

    #userTable th,
    #userTable td {
      padding: 12px 14px;
      text-align: left;
      border-bottom: 1px solid #eee;
      font-size: 14px;
    }

    #userTable th {
      background: #f8f9fa;
      font-weight: bold;
      color: #555;
      text-transform: uppercase;
      font-size: 13px;
    }

    #userTable tr:hover {
      background: #f1f7ff;
      transition: background 0.3s ease;
    }

    /* Buttons */
    .btn {
      padding: 6px 12px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      margin: 2px;
      font-size: 13px;
      transition: all 0.3s ease;
    }

    .btn.edit {
      background: #28a745;
      color: white;
    }
    .btn.edit:hover { background: #218838; transform: translateY(-1px); }

    .btn.delete {
      background: #dc3545;
      color: white;
    }
    .btn.delete:hover { background: #c82333; transform: translateY(-1px); }

    .btn.save {
      background: #007bff;
      color: white;
    }
    .btn.save:hover { background: #0056b3; transform: translateY(-1px); }

          /* Modal Overlay */
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0, 0, 0, 0.6);
      justify-content: center;
      align-items: center;
      padding: 20px;
      overflow-y: auto; /* allow scroll if content is taller */
    }

    /* Modal Box */
    .modal-content {
      background: #fff;
      border-radius: 12px;
      padding: 25px 20px;
      width: 420px;
      max-width: 100%;
      position: relative;
      box-shadow: 0 8px 20px rgba(0,0,0,0.2);
      animation: fadeInUp 0.3s ease;
    }

    /* Modal Title */
    .modal-content h3 {
      text-align: center;
      margin-bottom: 18px;
      font-size: 20px;
      font-weight: 600;
      color: #333;
    }

    /* Form Styling */
    .modal-content form {
      display: flex;
      flex-direction: column;
      gap: 15px;
    }

    .modal-content label {
      font-weight: 500;
      margin-bottom: 5px;
      font-size: 14px;
      color: #444;
    }

    .modal-content input,
    .modal-content select {
      width: 100%;
      padding: 10px 12px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 14px;
      transition: 0.3s;
    }

    .modal-content input:focus,
    .modal-content select:focus {
      border-color: #007bff;
      outline: none;
      box-shadow: 0 0 6px rgba(0,123,255,0.3);
    }

    /* Save Button */
    .modal-content .btn.save {
      width: 100%;
      padding: 10px;
      font-size: 15px;
      border-radius: 8px;
      font-weight: 600;
    }

    /* Close Button */
    .close-btn {
      position: absolute;
      top: 12px;
      right: 15px;
      font-size: 22px;
      cursor: pointer;
      color: #666;
      transition: 0.3s;
    }
    .close-btn:hover {
      color: #000;
    }

    /* Animation */
    @keyframes fadeInUp {
      from { transform: translateY(30px); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
    }

    /* Responsive */
    @media (max-width: 500px) {
      .modal-content {
        width: 100%;
        max-width: 95%;
        padding: 20px;
      }

      .modal-content h3 {
        font-size: 18px;
      }

      .modal-content input,
      .modal-content select,
      .modal-content .btn.save {
        font-size: 14px;
        padding: 9px;
      }
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

        <!-- Analytics Section -->
        <div class="analytics" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:15px;margin-bottom:20px;">
          <div style="background:#fff;padding:15px;border-radius:10px;text-align:center;box-shadow:0 2px 6px rgba(0,0,0,0.1);">
            👥 <h3><?php echo $totalUsers; ?></h3>
            <p>Total Users</p>
          </div>
          <div style="background:#fff;padding:15px;border-radius:10px;text-align:center;box-shadow:0 2px 6px rgba(0,0,0,0.1);">
            👮 <h3><?php echo $totalOfficials; ?></h3>
            <p>Barangay Officials</p>
          </div>
          <div style="background:#fff;padding:15px;border-radius:10px;text-align:center;box-shadow:0 2px 6px rgba(0,0,0,0.1);">
            🏠 <h3><?php echo $totalResidents; ?></h3>
            <p>Residents</p>
          </div>
          <div style="background:#fff;padding:15px;border-radius:10px;text-align:center;box-shadow:0 2px 6px rgba(0,0,0,0.1);">
            📅 <h3><?php echo $newThisMonth; ?></h3>
            <p>New This Month</p>
          </div>
        </div>

        <!-- Controls -->
        <div class="controls">
          <div class="controls-left">
            <div class="search-bar">
              <input type="text" id="searchInput" placeholder="🔍 Search users...">
            </div>
            <button class="add-btn" onclick="openAddModal()">+ Add User</button>
            <button class="btn export" onclick="exportTableToCSV()">📄 Export CSV</button>
            <button class="btn export" onclick="printTable()">🖨️ Print / PDF</button>
          </div>
          <div class="role-filter">
            <select id="roleFilter">
              <option value="">Filter by Role</option>
              <option value="Barangay Official">Barangay Official</option>
              <option value="Resident">Resident</option>
            </select>
          </div>
        </div>

        <div class="table-container">
          <table id="userTable">
            <thead>
              <tr>
                <th>Full Name</th>
                <th>Username</th>
                <th>Email</th>
                <th>Mobile Number</th>
                <th>Role</th>
                <th>Purok</th>
                <th>Date Registered</th>
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
                  <td><?php echo date("M d, Y h:i A", strtotime($row['created_at'])); ?></td>
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
            <div class="form-group"><label>Full Name</label><input type="text" name="fullname" required></div>
            <div class="form-group"><label>Username</label><input type="text" name="username" required></div>
            <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
            <div class="form-group"><label>Mobile Number</label><input type="text" name="number"></div>
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
            <div class="form-group"><label>Full Name</label><input type="text" name="fullname" id="edit-fullname" required></div>
            <div class="form-group"><label>Username</label><input type="text" name="username" id="edit-username" required></div>
            <div class="form-group"><label>Email</label><input type="email" name="email" id="edit-email" required></div>
            <div class="form-group"><label>Mobile Number</label><input type="text" name="number" id="edit-number"></div>
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
        // 🔍 Search
        document.getElementById("searchInput").addEventListener("keyup", function() {
          let value = this.value.toLowerCase();
          let rows = document.querySelectorAll("#userTable tbody tr");
          rows.forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(value) ? "" : "none";
          });
        });

        // 🔎 Filter
        document.getElementById("roleFilter").addEventListener("change", function() {
          let role = this.value.toLowerCase();
          let rows = document.querySelectorAll("#userTable tbody tr");
          rows.forEach(row => {
            let roleCell = row.cells[4].innerText.toLowerCase();
            row.style.display = (role === "" || roleCell === role) ? "" : "none";
          });
        });

        // 📝 Export to CSV
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
            for (let j = 0; j < cols.length - 1; j++) { // exclude "Actions" column
              row.push('"' + cols[j].innerText.replace(/"/g, '""') + '"');
            }
            csv.push(row.join(","));
          }
          downloadCSV(csv.join("\n"), "users.csv");
        }

        // 🖨️ Print / Save as PDF
        function printTable() {
          // Clone table and remove Actions column
          let table = document.getElementById("userTable").cloneNode(true);
          table.querySelectorAll("tr").forEach(tr => tr.deleteCell(tr.cells.length - 1));

          // Open new print window
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
                  👥 Total Users: <?php echo $totalUsers; ?> &nbsp; | 
                  👮 Officials: <?php echo $totalOfficials; ?> &nbsp; | 
                  🏠 Residents: <?php echo $totalResidents; ?> &nbsp; | 
                  📅 New This Month: <?php echo $newThisMonth; ?>
                </p>
                ${table.outerHTML}
              </body>
            </html>
          `);
          newWin.document.close();
          newWin.print();
        }


        // 🟢 Modal Functions
        function openAddModal() { document.getElementById('addModal').style.display = 'flex'; }
        function closeAddModal() { document.getElementById('addModal').style.display = 'none'; }

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
        function closeEditModal() { document.getElementById('editModal').style.display = 'none'; }

        window.onclick = function(event) {
          if (event.target == document.getElementById('addModal')) closeAddModal();
          if (event.target == document.getElementById('editModal')) closeEditModal();
        }

        // ✅ Prevent back button after logout
        window.addEventListener("pageshow", function (event) {
          if (event.persisted || performance.getEntriesByType("navigation")[0].type === "back_forward") {
            window.location.reload();
          }
        });
      </script>
    </body>
    </html>
