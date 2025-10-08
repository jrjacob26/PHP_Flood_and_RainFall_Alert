<?php
session_start();
include 'db_connect.php'; // Database connection

//  Only allow Barangay Officials
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Barangay Official') {
    header("Location: admin-login.php");
    exit();
}

//  TextBee API Configuration (v1)
$TEXTBEE_API_KEY   = 'acea6fd3-c3cb-47a0-8896-d506a93935ae';
$TEXTBEE_DEVICE_ID = '68e3b4d4951aa3aa0e321420';
$TEXTBEE_BASE_URL  = 'https://api.textbee.dev/api/v1';

//  Function to format numbers
function formatNumber($num) {
    $num = preg_replace('/\D/', '', $num); // remove non-digits

    if (strpos($num, '09') === 0) return '+63' . substr($num, 1);
    if (strpos($num, '63') === 0) return '+' . $num;
    if (strpos($num, '+63') === 0) return $num;
    return false;
}

//  Function to send SMS via TextBee (v1)
function sendTextBeeSMS($phone, $message, $apiKey, $deviceId, $baseUrl) {
    $url = "{$baseUrl}/gateway/devices/{$deviceId}/send-sms";

    $payload = ['recipients' => [$phone], 'message' => $message];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'x-api-key: ' . $apiKey,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    return $error ? false : true; // generic success/failure
}

//  Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipientType = $_POST['recipient_type'] ?? '';
    $message = trim($_POST['message'] ?? '');

    if (empty($recipientType) || empty($message)) {
        $_SESSION['error'] = ' Please fill out all required fields.';
    } else {
        if ($recipientType === 'specific') {
            $recipientRaw = trim($_POST['recipient']);
            $recipient = formatNumber($recipientRaw);

            if (!$recipient) {
                $_SESSION['error'] = ' Invalid phone number format.';
            } else {
                $res = sendTextBeeSMS($recipient, $message, $TEXTBEE_API_KEY, $TEXTBEE_DEVICE_ID, $TEXTBEE_BASE_URL);
                
                //  Save to messages table
                $stmt = $conn->prepare("INSERT INTO messages (user_id, phone, message, status, recipient_type) VALUES (?, ?, ?, ?, ?)");
                $user_id = null; // could match users.id if needed
                $status = $res ? 'Sent' : 'Failed';
                $stmt->bind_param("issss", $user_id, $recipient, $message, $status, $recipientType);
                $stmt->execute();

                $_SESSION['success'] = $res ? "SMS sent successfully to {$recipient}." : "Failed to send SMS. Please try again.";
            }

        } elseif ($recipientType === 'all') {
            $query = $conn->query("SELECT id, number FROM users WHERE subscribed = 1");

            if ($query && $query->num_rows > 0) {
                $sent = 0;

                while ($row = $query->fetch_assoc()) {
                    $recipientRaw = $row['number'];
                    $recipient = formatNumber($recipientRaw);
                    $user_id = $row['id'];

                    if ($recipient) {
                        $res = sendTextBeeSMS($recipient, $message, $TEXTBEE_API_KEY, $TEXTBEE_DEVICE_ID, $TEXTBEE_BASE_URL);

                        //  Save each message to database
                        $stmt = $conn->prepare("INSERT INTO messages (user_id, phone, message, status, recipient_type) VALUES (?, ?, ?, ?, ?)");
                        $status = $res ? 'Sent' : 'Failed';
                        $stmt->bind_param("issss", $user_id, $recipient, $message, $status, $recipientType);
                        $stmt->execute();

                        if ($res) $sent++;
                    }
                }

                if ($sent > 0) {
                    $_SESSION['success'] = "{$sent} messages sent successfully.";
                } else {
                    $_SESSION['error'] = "No messages could be sent. Please try again."; 
                }
            } else {
                $_SESSION['error'] = "No subscribed users found in the database.";
            }
        }
    }

    header("Location: send_message.php");
    exit();
}
?>

<!--  FRONTEND JS ALERT -->
<?php if (isset($_SESSION['success'])): ?>
<script>
    alert("<?php echo addslashes($_SESSION['success']); ?>");
</script>
<?php unset($_SESSION['success']); endif; ?>

<?php if (isset($_SESSION['error'])): ?>
<script>
    alert("<?php echo addslashes($_SESSION['error']); ?>");
</script>
<?php unset($_SESSION['error']); endif; ?>
