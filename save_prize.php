<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "aromiq";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

// Set character set
$conn->set_charset("utf8mb4");

// Get form data
$name = $_POST['name'] ?? '';
$gender = $_POST['gender'] ?? '';
$contact = $_POST['contact'] ?? '';
$email = $_POST['email'] ?? '';
$prize = $_POST['prize'] ?? '';
$coupon = $_POST['coupon'] ?? '';

// Validate data
if (empty($name) || empty($gender) || empty($contact) || empty($email) || empty($prize) || empty($coupon)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

// Prepare and execute SQL statement
$stmt = $conn->prepare("INSERT INTO tbl_spinthewheel (name, gender, contact, email, prize, coupon_code) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssss", $name, $gender, $contact, $email, $prize, $coupon);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>