<?php
include 'db_connect.php';

//  Security key
$secretKey = "bahashield123";
if (!isset($_GET['key']) || $_GET['key'] !== $secretKey) {
    http_response_code(403);
    echo "Forbidden";
    exit;
}

header("Content-Type: text/plain");

//  Fetch numbers for both Residents and Barangay Officials
$sql = "SELECT DISTINCT phone_number FROM alert_recipients WHERE role IN ('Resident', 'Barangay Official')";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo trim($row['phone_number']) . "\n";
    }
} else {
    echo "NoNumbers\n"; // Arduino will check this
}

$conn->close();
?>
