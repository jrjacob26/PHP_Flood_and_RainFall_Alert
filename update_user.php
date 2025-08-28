<?php
session_start();

// Only Admins can update
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id       = intval($_POST['id']);
    $fullname = trim($_POST['fullname']);
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $number   = trim($_POST['number']);
    $role     = trim($_POST['role']);
    $purok    = intval($_POST['purok']);

    $stmt = $conn->prepare("UPDATE users SET fullname=?, username=?, email=?, number=?, role=?, purok=? WHERE id=?");
    $stmt->bind_param("ssssssi", $fullname, $username, $email, $number, $role, $purok, $id);

    if ($stmt->execute()) {
        // If current logged-in user updated their own record, update session
        if ($_SESSION['user_id'] == $id) {
            $_SESSION['fullname'] = $fullname;
            $_SESSION['username'] = $username;
            $_SESSION['email']    = $email;
            $_SESSION['number']   = $number;
            $_SESSION['role']     = $role;
            $_SESSION['purok']    = $purok;
        }

        // ✅ Success alert with JavaScript
        echo "<script>
                alert('✅ User updated successfully!');
                window.location.href = 'admin-dashboard.php';
              </script>";
    } else {
        // ❌ Error alert
        echo "<script>
                alert('❌ Failed to update user.');
                window.location.href = 'admin-dashboard.php';
              </script>";
    }

    $stmt->close();
    $conn->close();
    exit();
} else {
    // ⚠️ Invalid request alert
    echo "<script>
            alert('⚠️ Invalid request!');
            window.location.href = 'admin-dashboard.php';
          </script>";
    exit();
}
?>
