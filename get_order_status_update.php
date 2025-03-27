<?php
session_start();
include 'connect.php';

// Initialize response array
$response = [
    'status' => 'error',
    'message' => 'An error occurred',
    'order_status' => ''
];

// Check if order_id is provided
if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    $response['message'] = 'Order ID is required';
    echo json_encode($response);
    exit;
}

$order_id = mysqli_real_escape_string($conn, $_GET['order_id']);

// Query to get the current order status
$query = "SELECT status FROM tbl_orders WHERE order_id = ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    $response['message'] = 'Database error: ' . $conn->error;
    echo json_encode($response);
    exit;
}

$stmt->bind_param("s", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $response['message'] = 'Order not found';
    echo json_encode($response);
    exit;
}

// Get the order status
$row = $result->fetch_assoc();
$order_status = $row['status'];

// Return success response with the current order status
$response = [
    'status' => 'success',
    'message' => 'Order status retrieved successfully',
    'order_status' => $order_status
];

header('Content-Type: application/json');
echo json_encode($response);
exit;
?> 