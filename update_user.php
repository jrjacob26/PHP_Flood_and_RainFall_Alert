<?php
session_start();

// Only Barangay Officials (Admin) can update
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Barangay Official') {
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

    // ✅ Check for duplicate username
    $checkUser = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
    $checkUser->bind_param("si", $username, $id);
    $checkUser->execute();
    $checkUser->store_result();
    if ($checkUser->num_rows > 0) {
        echo "<script>
                alert('⚠️ Username already exists! Please choose another.');
                window.location.href = 'admin-dashboard.php';
              </script>";
        $checkUser->close();
        exit();
    }
    $checkUser->close();

    // ✅ Check for duplicate email
    $checkEmail = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $checkEmail->bind_param("si", $email, $id);
    $checkEmail->execute();
    $checkEmail->store_result();
    if ($checkEmail->num_rows > 0) {
        echo "<script>
                alert('⚠️ This email is already registered to another user. Please use a different one.');
                window.location.href = 'admin-dashboard.php';
              </script>";
        $checkEmail->close();
        exit();
    }
    $checkEmail->close();

    // ✅ Check for duplicate mobile number
    $checkNumber = $conn->prepare("SELECT id FROM users WHERE number = ? AND id != ?");
    $checkNumber->bind_param("si", $number, $id);
    $checkNumber->execute();
    $checkNumber->store_result();
    if ($checkNumber->num_rows > 0) {
        echo "<script>
                alert('⚠️ This mobile number is already registered to another user. Please use a different number.');
                window.location.href = 'admin-dashboard.php';
              </script>";
        $checkNumber->close();
        exit();
    }
    $checkNumber->close();

    // ✅ If no duplicates, proceed with update
    $stmt = $conn->prepare("UPDATE users SET fullname=?, username=?, email=?, number=?, role=?, purok=? WHERE id=?");
    $stmt->bind_param("ssssssi", $fullname, $username, $email, $number, $role, $purok, $id);

    if ($stmt->execute()) {
        // If current logged-in user updated their own record, update session too
        if ($_SESSION['user_id'] == $id) {
            $_SESSION['fullname'] = $fullname;
            $_SESSION['username'] = $username;
            $_SESSION['email']    = $email;
            $_SESSION['number']   = $number;
            $_SESSION['role']     = $role;
            $_SESSION['purok']    = $purok;
        }

        echo "<script>
                alert('✅ User updated successfully!');
                window.location.href = 'admin-dashboard.php';
              </script>";
    } else {
        echo "<script>
                alert('❌ Something went wrong while updating. Please try again.');
                window.location.href = 'admin-dashboard.php';
              </script>";
    }

    $stmt->close();
    $conn->close();
    exit();
} else {
    echo "<script>
            alert('⚠️ Invalid request!');
            window.location.href = 'admin-dashboard.php';
          </script>";
    exit();
}
?>
