<?php
// relay.php
// Relay incoming HTTP request from SIM800L to your secure HTTPS backend

//  Change this to your actual ngrok HTTPS URL + sensor_data.php path
$target = "http://stercoricolous-piper-mossiest.ngrok-free.dev/Flood_and_RainFall_System/sensor_data.php";

// Collect all GET/POST parameters sent by SIM800L
$data = $_REQUEST;

// Forward them as query string or POST body
$postfields = http_build_query($data);

// Setup cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $target);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

//  Disable SSL verification (ok for ngrok demo, not safe for production)
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

// Execute the request
$response = curl_exec($ch);
$errno = curl_errno($ch);
$errmsg = curl_error($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Return JSON back to the SIM800L
header('Content-Type: application/json');
echo json_encode([
  'status' => $errno ? 'error' : 'ok',
  'http_code' => $httpcode,
  'response' => $response,
  'error' => $errmsg,
  'forwarded_to' => $target,
  'sent_data' => $data
]);
