<?php
session_start();
include 'db_connect.php';

$token = $_GET['token'] ?? '';
if (!$token) {
    echo "Invalid unsubscribe link.";
    exit();
}

$token = $conn->real_escape_string($token);
$stmt = $conn->prepare("UPDATE subscribers SET subscribed = 0 WHERE unsubscribe_token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo "<h2>You're unsubscribed</h2><p>You will no longer receive SMS alerts from this service.</p>";
} else {
    echo "<h2>Link invalid or already unsubscribed</h2>";
}
