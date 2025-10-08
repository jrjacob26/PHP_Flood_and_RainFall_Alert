<?php
// verify.php
session_start();
include 'db_connect.php';

$id    = isset($_GET['id']) ? intval($_GET['id']) : 0;
$token = isset($_GET['token']) ? $_GET['token'] : '';

if ($id <= 0 || empty($token)) {
    die('Invalid verification link.');
}

$stmt = $conn->prepare("SELECT id, email_verified FROM users WHERE id = ? AND verification_token = ?");
$stmt->bind_param("is", $id, $token);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    die('Invalid or expired verification link.');
}

$user = $res->fetch_assoc();
if ((int)$user['email_verified'] === 1) {
    echo "<script>alert('Email is already verified.');window.location='login.php';</script>";
    exit;
}

$update = $conn->prepare("UPDATE users SET email_verified = 1, email_verified_at = NOW(), verification_token = NULL WHERE id = ?");
$update->bind_param("i", $id);

if ($update->execute()) {
    echo "<script>alert('Email verified successfully! You can now log in.');window.location='login.php';</script>";
} else {
    echo "<script>alert('Verification failed.');window.location='login.php';</script>";
}
