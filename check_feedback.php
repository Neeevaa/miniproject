<?php
// Prevent any HTML error output
ini_set('display_errors', 0);
error_reporting(E_ERROR);

header('Content-Type: application/json');

session_start();
if(!isset($_SESSION['uname'])){
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$servername = "localhost";
$username = "chef";
$password = "1234";
$database = "aromiq";

// Connect to MySQL
$conn = mysqli_connect($servername, $username, $password, $database);

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . mysqli_connect_error()]);
    exit;
}

// Get order ID from request
$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : '';

if (empty($order_id)) {
    echo json_encode(['success' => false, 'message' => 'Order ID is required']);
    exit;
}

// Check feedback status from tbl_orders
$sql = "SELECT feedback_submitted FROM tbl_orders WHERE order_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $order_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    echo json_encode([
        'success' => true,
        'feedback_submitted' => $row['feedback_submitted']
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Order not found']);
}

mysqli_close($conn);
?> 