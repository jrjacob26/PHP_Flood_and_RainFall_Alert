<?php
session_start();
include 'db_connect.php';

// Only Barangay Officials
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Barangay Official') {
    header("Location: login.php");
    exit();
}

// Get form data
$recipient_type = $_POST['recipient_type'] ?? '';
$recipient      = $_POST['recipient'] ?? '';
$message        = trim($_POST['message'] ?? '');

// API credentials
$api_url = "YOUR_API_URL";        // Example: https://sms.teamssprogram.com/api/send
$api_key = "YOUR_API_KEY";

// Determine recipients
$numbers = [];
if($recipient_type === 'all'){
    // Fetch all subscribed users from database
    $result = mysqli_query($conn, "SELECT phone_number FROM users WHERE subscribed = 1");
    while($row = mysqli_fetch_assoc($result)){
        $numbers[] = $row['phone_number'];
    }
} elseif($recipient_type === 'specific'){
    $numbers[] = $recipient;
}

foreach($numbers as $number){
    $postData = [
        'api_key' => $api_key,
        'number'  => $number,
        'message' => $message
    ];

    // Use cURL to send the POST request
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    $response = curl_exec($ch);
    curl_close($ch);

    $responseData = json_decode($response, true);
    if(isset($responseData['status']) && $responseData['status'] === 'success'){
        $_SESSION['success'] = "Message sent successfully to $number!";
    } else {
        $_SESSION['error'] = "Failed to send message to $number.";
    }
}

header("Location: send_sms.php");
exit();
