<?php
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');
date_default_timezone_set('Asia/Manila');
include 'db_connect.php';

// ----------------------------
// Fetch last 20 records for table
// ----------------------------
$result = $conn->query("SELECT * FROM sensor_data ORDER BY datetime DESC LIMIT 20");
$records = [];
while ($row = $result->fetch_assoc()) {
    $water = intval($row['water']);
    $rain = intval($row['rain']);

    // Flood classification
    if ($water <= 10) $floodStatus = 'High';
    elseif ($water <= 20) $floodStatus = 'Medium';
    else $floodStatus = 'Low';

    // Rain classification
    if ($rain >= 10) $rainStatus = 'Heavy';
    elseif ($rain >= 5) $rainStatus = 'Moderate';
    else $rainStatus = 'Light';

    $records[] = [
        'datetime' => date("M d, Y h:i A", strtotime($row['datetime'])),
        'water' => $water,
        'rain' => $rain,
        'flood_status' => $floodStatus,
        'rain_status' => $rainStatus
    ];
}

// ----------------------------
// Fetch all records for analytics chart
// ----------------------------
$allData = $conn->query("SELECT * FROM sensor_data ORDER BY datetime ASC");
$trendLabels = [];
$trendWater = [];
$trendRain = [];

while ($row = $allData->fetch_assoc()) {
    $trendLabels[] = date("M d, Y h:i A", strtotime($row['datetime']));
    $trendWater[] = intval($row['water']);
    $trendRain[] = intval($row['rain']);
}

// ----------------------------
// Latest record
// ----------------------------
$latestRow = $conn->query("SELECT * FROM sensor_data ORDER BY datetime DESC LIMIT 1")->fetch_assoc();
$totalRecords = $conn->query("SELECT COUNT(*) as total FROM sensor_data")->fetch_assoc()['total'];

if ($latestRow) {
    $latestWater = intval($latestRow['water']);
    $latestRain = intval($latestRow['rain']);
    $latestTime = date("M d, Y h:i A", strtotime($latestRow['datetime']));

    $latestFloodStatus = $latestWater <= 10 ? 'High' : ($latestWater <= 20 ? 'Medium' : 'Low');
    $latestRainStatus = $latestRain >= 10 ? 'Heavy' : ($latestRain >= 5 ? 'Moderate' : 'Light');
} else {
    $latestWater = 0;
    $latestRain = 0;
    $latestTime = 'N/A';
    $latestFloodStatus = 'N/A';
    $latestRainStatus = 'N/A';
}

// ----------------------------
// Return JSON
// ----------------------------
echo json_encode([
    'totalRecords' => $totalRecords,
    'latestWater' => $latestWater,
    'latestRain' => $latestRain,
    'latestTime' => $latestTime,
    'latestFloodStatus' => $latestFloodStatus,
    'latestRainStatus' => $latestRainStatus,
    'records' => $records,
    'trends' => [
        'labels' => $trendLabels,
        'water' => $trendWater,
        'rain' => $trendRain
    ]
]);
exit;
