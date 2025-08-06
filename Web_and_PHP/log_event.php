<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer.php';
require 'Exception.php';
require 'SMTP.php';

// Database connection settings
$host = "localhost";      // Change if MySQL server is remote
$db   = "inventorydb";    // Database name
$user = "root";           // MySQL username
$pass = "test@123";       // MySQL password

// Create connection
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Retrieve POST data
$eventType = isset($_POST['eventType']) ? $_POST['eventType'] : "";
$item      = isset($_POST['item']) ? $_POST['item'] : "";
$value1    = isset($_POST['value1']) ? $_POST['value1'] : "";
$value2    = isset($_POST['value2']) ? $_POST['value2'] : "";
$value3    = isset($_POST['value3']) ? $_POST['value3'] : "";

// Email recipient
$recipient = "gpinfinity000@gmail.com";

try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // Change if using another provider
    $mail->SMTPAuth = true;
    $mail->Username = 'nagateja9110@gmail.com'; // Your Gmail
    $mail->Password = 'eest pvcn uieh ugli';   // Use an App Password instead of actual password
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;
    $mail->setFrom('nagateja9110@gmail.com', 'Smart Inventory System');
    $mail->addAddress($recipient);

    if ($eventType == "order") {
        $quantity = floatval($value1);
        $sql = "INSERT INTO orders(item, quantity) VALUES('$item', $quantity)";
        $subject = "Order Placement - " . $item;
        $message = "Order placement for " . $quantity . " grams of " . $item;

        if ($conn->query($sql) === TRUE) {
            echo "Order logged successfully.";
        } else {
            echo "Error logging order: " . $conn->error;
        }

        // Send email alert
        $mail->Subject = $subject;
        $mail->Body    = $message;
        $mail->send();

    } elseif ($eventType == "consumption") {
        $morning   = floatval($value1);
        $evening   = floatval($value2);
        $consumed  = floatval($value3);
        $sql = "INSERT INTO consumption(item, morning_weight, evening_weight, consumption) 
                VALUES('$item', $morning, $evening, $consumed)";

        if ($conn->query($sql) === TRUE) {
            echo "Consumption logged successfully.";
        } else {
            echo "Error logging consumption: " . $conn->error;
        }

    } elseif ($eventType == "temperature") {
        $temp = floatval($value1);
        $alertMsg = "Temperature alert in $item: " . $temp . "°C";
        $sql = "INSERT INTO temp_alerts(temp_alert) VALUES('$alertMsg')";

        if ($conn->query($sql) === TRUE) {
            echo "Temperature alert logged successfully.";
        } else {
            echo "Error logging temperature alert: " . $conn->error;
        }

        // Send email alert
        $mail->Subject = "Temperature Alert - " . $item;
        $mail->Body    = $alertMsg;
        $mail->send();

    } elseif ($eventType == "security") {
        $alertMsg = $value1;
        $sql = "INSERT INTO security_alerts(security_alert) VALUES('$alertMsg')";

        if ($conn->query($sql) === TRUE) {
            echo "Security alert logged successfully.";
        } else {
            echo "Error logging security alert: " . $conn->error;
        }

        // Send email alert
        $mail->Subject = "Security Alert - " . $item;
        $mail->Body    = $alertMsg;
        $mail->send();

    } else {
        echo "Invalid event type.";
    }

} catch (Exception $e) {
    echo "Error sending email: " . $mail->ErrorInfo;
}

$conn->close();
?>
