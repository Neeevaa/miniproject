<?php
// check_new_orders.php
session_start();
if(!isset($_SESSION['uname'])) {
    die(json_encode(['hasNewOrders' => false, 'error' => 'Unauthorized']));
}

$servername = "localhost";
$username = "kitchenadmin";
$password = "1234";
$database = "aromiq";

$conn = mysqli_connect($servername, $username, $password, $database);

if (!$conn) {
    die(json_encode(['hasNewOrders' => false, 'error' => 'Connection failed']));
}

// Get the timestamp of the latest order in the session
if (!isset($_SESSION['last_order_check'])) {
    $_SESSION['last_order_check'] = date('Y-m-d H:i:s');
}

$query = "SELECT COUNT(*) as new_orders FROM tbl_orders 
          WHERE timestamp > ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $_SESSION['last_order_check']);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$_SESSION['last_order_check'] = date('Y-m-d H:i:s');

echo json_encode(['hasNewOrders' => $row['new_orders'] > 0]);

$stmt->close();
$conn->close();
?>