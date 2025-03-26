<?php
// Prevent any HTML error output
ini_set('display_errors', 0);
error_reporting(E_ERROR);

header('Content-Type: application/json');

session_start();
if(!isset($_SESSION['uname'])){
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$servername = "localhost";
$username = "chef";
$password = "1234";
$database = "aromiq";

// Connect to MySQL
$conn = mysqli_connect($servername, $username, $password, $database);

if (!$conn) {
    echo json_encode(['error' => 'Database connection failed: ' . mysqli_connect_error()]);
    exit;
}

// Get active orders with table information
$sql = "SELECT * FROM tbl_orders WHERE status != 'Completed' ORDER BY timestamp DESC";
$result = mysqli_query($conn, $sql);

if (!$result) {
    echo json_encode(['error' => 'Query failed: ' . mysqli_error($conn)]);
    exit;
}

$tables = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Map the database fields to the expected format in the frontend
    $tables[] = [
        'order_id' => $row['order_id'],
        'table_number' => $row['table_number'],
        'order_status' => $row['status'],
        'payment_status' => $row['payment_status'],
        'order_date' => $row['timestamp'],
        'feedback_submitted' => $row['feedback_submitted']
    ];
}

echo json_encode($tables);
mysqli_close($conn);
?>