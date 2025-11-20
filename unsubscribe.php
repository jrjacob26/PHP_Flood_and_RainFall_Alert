<?php
include 'db_connect.php';

// Get email from URL
$email = isset($_GET['email']) ? $conn->real_escape_string($_GET['email']) : '';

if (!$email) {
    die("Invalid link.");
}

// Check if user exists
$result = $conn->query("SELECT * FROM users WHERE email='$email'");
if ($result->num_rows == 0) {
    die("User not found.");
}

$user = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // ✅ Unsubscribe
    if ($action === 'unsubscribe') {
        $conn->query("UPDATE users SET subscribed=0 WHERE email='$email'");
        echo '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Unsubscribed Successfully</title>
            <style>
                @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap");
                body {
                    font-family: "Poppins", sans-serif;
                    background: linear-gradient(135deg, #007bff, #00c6ff);
                    height: 100vh;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    margin: 0;
                    color: #333;
                }
                .result-container {
                    background: #fff;
                    width: 420px;
                    padding: 40px 30px;
                    border-radius: 15px;
                    text-align: center;
                    box-shadow: 0 8px 25px rgba(0,0,0,0.2);
                    animation: fadeIn 0.6s ease-in-out;
                }
                .icon {
                    font-size: 70px;
                    margin-bottom: 15px;
                    color: #28a745;
                    animation: pop 0.5s ease;
                }
                h2 {
                    margin-bottom: 10px;
                    font-weight: 600;
                }
                p {
                    color: #555;
                    margin-bottom: 25px;
                    font-size: 1rem;
                }
                .back-btn {
                    display: inline-block;
                    text-decoration: none;
                    background: #007bff;
                    color: #fff;
                    padding: 12px 25px;
                    border-radius: 8px;
                    font-weight: 500;
                    transition: 0.3s;
                }
                .back-btn:hover {
                    background: #0056b3;
                    transform: scale(1.05);
                }
                @keyframes fadeIn {
                    from {opacity:0; transform:translateY(20px);}
                    to {opacity:1; transform:translateY(0);}
                }
                @keyframes pop {
                    from {transform:scale(0.6); opacity:0;}
                    to {transform:scale(1); opacity:1;}
                }
            </style>
        </head>
        <body>
            <div class="result-container">
                <div class="icon">&#10003;</div>
                <h2>Unsubscribed Successfully</h2>
                <p>You will no longer receive SMS/email alerts from our system.</p>
                <a href="unsubscribe.php?email=' . htmlspecialchars($email) . '" class="back-btn">Back</a>
            </div>
        </body>
        </html>';
        exit;
    }

    // ✅ Resubscribe
    elseif ($action === 'resubscribe') {
        $conn->query("UPDATE users SET subscribed=1 WHERE email='$email'");
        echo '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Resubscribed Successfully</title>
            <style>
                @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap");
                body {
                    font-family: "Poppins", sans-serif;
                    background: linear-gradient(135deg, #007bff, #00c6ff);
                    height: 100vh;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    margin: 0;
                    color: #333;
                }
                .result-container {
                    background: #fff;
                    width: 420px;
                    padding: 40px 30px;
                    border-radius: 15px;
                    text-align: center;
                    box-shadow: 0 8px 25px rgba(0,0,0,0.2);
                    animation: fadeIn 0.6s ease-in-out;
                }
                .icon {
                    font-size: 70px;
                    margin-bottom: 15px;
                    color: #007bff;
                    animation: pop 0.5s ease;
                }
                h2 {
                    margin-bottom: 10px;
                    font-weight: 600;
                }
                p {
                    color: #555;
                    margin-bottom: 25px;
                    font-size: 1rem;
                }
                .back-btn {
                    display: inline-block;
                    text-decoration: none;
                    background: #007bff;
                    color: #fff;
                    padding: 12px 25px;
                    border-radius: 8px;
                    font-weight: 500;
                    transition: 0.3s;
                }
                .back-btn:hover {
                    background: #0056b3;
                    transform: scale(1.05);
                }
                @keyframes fadeIn {
                    from {opacity:0; transform:translateY(20px);}
                    to {opacity:1; transform:translateY(0);}
                }
                @keyframes pop {
                    from {transform:scale(0.6); opacity:0;}
                    to {transform:scale(1); opacity:1;}
                }
            </style>
        </head>
        <body>
            <div class="result-container">
                <div class="icon">&#128276;</div>
                <h2>Resubscribed Successfully</h2>
                <p>You will now receive SMS/email alerts again.</p>
                <a href="unsubscribe.php?email=' . htmlspecialchars($email) . '" class="back-btn">Back</a>
            </div>
        </body>
        </html>';
        exit;
    }

    // ✅ No Changes
    else {
        echo '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>No Changes Made</title>
            <style>
                @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap");
                body {
                    font-family: "Poppins", sans-serif;
                    background: linear-gradient(135deg, #007bff, #00c6ff);
                    height: 100vh;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    margin: 0;
                    color: #333;
                }
                .result-container {
                    background: #fff;
                    width: 420px;
                    padding: 40px 30px;
                    border-radius: 15px;
                    text-align: center;
                    box-shadow: 0 8px 25px rgba(0,0,0,0.2);
                    animation: fadeIn 0.6s ease-in-out;
                }
                .icon {
                    font-size: 70px;
                    margin-bottom: 15px;
                    color: #ffc107;
                    animation: pop 0.5s ease;
                }
                h2 {
                    margin-bottom: 10px;
                    font-weight: 600;
                }
                p {
                    color: #555;
                    margin-bottom: 25px;
                    font-size: 1rem;
                }
                .back-btn {
                    display: inline-block;
                    text-decoration: none;
                    background: #007bff;
                    color: #fff;
                    padding: 12px 25px;
                    border-radius: 8px;
                    font-weight: 500;
                    transition: 0.3s;
                }
                .back-btn:hover {
                    background: #0056b3;
                    transform: scale(1.05);
                }
                @keyframes fadeIn {
                    from {opacity:0; transform:translateY(20px);}
                    to {opacity:1; transform:translateY(0);}
                }
                @keyframes pop {
                    from {transform:scale(0.6); opacity:0;}
                    to {transform:scale(1); opacity:1;}
                }
            </style>
        </head>
        <body>
            <div class="result-container">
                <div class="icon">&#9888;</div>
                <h2>No Changes Made</h2>
                <p>Your subscription settings remain unchanged.</p>
                <a href="unsubscribe.php?email=' . htmlspecialchars($email) . '" class="back-btn">Back</a>
            </div>
        </body>
        </html>';
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Alerts Subscription</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');

body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #007bff, #00c6ff);
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 0;
    color: #333;
}
.container {
    background: #ffffff;
    padding: 40px 35px;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
    text-align: center;
    width: 400px;
    animation: fadeIn 0.6s ease-in-out;
}
.container h2 {
    font-weight: 600;
    color: #222;
    margin-bottom: 15px;
}
.container p {
    color: #555;
    font-size: 1rem;
    margin-bottom: 30px;
}
button {
    padding: 12px 25px;
    margin: 10px 5px;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}
.unsubscribe { background-color: #dc3545; color: white; }
.unsubscribe:hover { background-color: #b02a37; transform: scale(1.05); }
.resubscribe { background-color: #007bff; color: white; }
.resubscribe:hover { background-color: #0056b3; transform: scale(1.05); }
.stay { background-color: #28a745; color: white; }
.stay:hover { background-color: #1e7e34; transform: scale(1.05); }
@keyframes fadeIn {
    from {opacity: 0; transform: translateY(30px);}
    to {opacity: 1; transform: translateY(0);}
}
@media (max-width: 480px) {
    .container { width: 90%; padding: 30px 20px; }
    button { width: 100%; margin: 10px 0; }
}
</style>
</head>
<body>
<div class="container">
    <h2>Hello <?php echo htmlspecialchars($user['fullname']); ?>!</h2>
    <p>Manage your subscription to SMS/email alerts from our system:</p>
    <form method="POST">
        <button type="submit" name="action" value="unsubscribe" class="unsubscribe">Unsubscribe</button>
        <button type="submit" name="action" value="resubscribe" class="resubscribe">Resubscribe</button>
        <button type="submit" name="action" value="stay" class="stay">Keep Current Status</button>
    </form>
</div>
</body>
</html>
