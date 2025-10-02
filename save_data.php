<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rainfall = intval($_POST['rainfall'] ?? 0);
    $flood    = intval($_POST['flood'] ?? 0);

    // Determine status automatically
    $status = 'Low';
    if ($flood > 30 || $rainfall > 100) {
        $status = 'High';
    } elseif ($flood > 10 || $rainfall > 50) {
        $status = 'Moderate';
    }

    $sql = "INSERT INTO flood_history (date, rainfall, flood, status) VALUES (NOW(), ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("iis", $rainfall, $flood, $status);
        if ($stmt->execute()) {
            echo "OK"; // ✅ Success
        } else {
            echo "ERROR: " . $stmt->error; // ❌ Execution failed
        }
        $stmt->close();
    } else {
        echo "ERROR: " . $conn->error; // ❌ SQL issue
    }
}
?>
