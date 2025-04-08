<?php
// Database configuration
$servername = "localhost";
$username = "admin";
$password = "1234";
$dbname = "aromiq";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

// Set character set
$conn->set_charset("utf8mb4");

// Get coupon code
$coupon = $_POST['coupon'] ?? '';

// Validate data
if (empty($coupon)) {
    echo json_encode(['success' => false, 'message' => 'Coupon code is required']);
    exit;
}

// Prepare and execute SQL statement
$stmt = $conn->prepare("UPDATE tbl_spinthewheel SET is_collected = 1 WHERE coupon_code = ?");
$stmt->bind_param("s", $coupon);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>