<?php
session_start();
if(!isset($_SESSION['uname'])){
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

include 'connect.php';

// Initialize response
$response = [
    'success' => false,
    'message' => 'An error occurred',
    'order' => null,
    'items' => []
];

// Check if order_id is provided
if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    $response['message'] = 'Order ID is required';
    echo json_encode($response);
    exit;
}

$order_id = mysqli_real_escape_string($conn, $_GET['order_id']);

// Fetch order details
$order_query = "SELECT * FROM tbl_orders WHERE order_id = '$order_id'";
$order_result = mysqli_query($conn, $order_query);

if (!$order_result) {
    $response['message'] = 'Database error: ' . mysqli_error($conn);
    echo json_encode($response);
    exit;
}

if (mysqli_num_rows($order_result) === 0) {
    $response['message'] = 'Order not found';
    echo json_encode($response);
    exit;
}

// Get order data
$order = mysqli_fetch_assoc($order_result);

// Fetch order items
$items_query = "SELECT * FROM tbl_order_items WHERE order_id = '$order_id'";
$items_result = mysqli_query($conn, $items_query);

if (!$items_result) {
    $response['message'] = 'Error fetching order items: ' . mysqli_error($conn);
    $response['order'] = $order;
    echo json_encode($response);
    exit;
}

// Get all items
$items = [];
while ($item = mysqli_fetch_assoc($items_result)) {
    $items[] = $item;
}

// Return success response
$response['success'] = true;
$response['message'] = 'Order details retrieved successfully';
$response['order'] = $order;
$response['items'] = $items;

header('Content-Type: application/json');
echo json_encode($response);
exit;
?> 