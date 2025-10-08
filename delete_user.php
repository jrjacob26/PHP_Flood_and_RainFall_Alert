<?php
session_start();

// Only allow Admins
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Barangay Official') {
    header("Location: admin-login.php");
    exit();
}

include 'db_connect.php';

// Check if ID is provided
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);

    // Prepare delete query
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "User deleted successfully!";
    } else {
        $_SESSION['message'] = "Error deleting user!";
    }

    $stmt->close();
} else {
    $_SESSION['message'] = "Invalid request!";
}

$conn->close();

// Redirect back to dashboard
header("Location: admin-dashboard.php");
exit();
