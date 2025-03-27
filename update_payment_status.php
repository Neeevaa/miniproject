<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in as admin
if (!isset($_SESSION['uname'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Not authorized'
    ]);
    exit;
}

// Get request body
$request_body = file_get_contents('php://input');
$data = json_decode($request_body, true);

// Validate input
if (!isset($data['order_id']) || !isset($data['status'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required parameters'
    ]);
    exit;
}

$order_id = $data['order_id'];
$status = $data['status'];

// Validate status
$valid_statuses = ['Pending', 'Completed', 'Cancelled'];
if (!in_array($status, $valid_statuses)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid status'
    ]);
    exit;
}

// Update database
include 'connect.php';

$sql = "UPDATE tbl_orders SET payment_status = ? WHERE order_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $status, $order_id);
$result = $stmt->execute();

if ($result) {
    echo json_encode([
        'success' => true,
        'message' => 'Payment status updated successfully'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $conn->error
    ]);
}

$stmt->close();
$conn->close();
?> 