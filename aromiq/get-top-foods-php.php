<?php
session_start();
if(!isset($_SESSION['uname'])){
    header("Location:login.php");
    exit;
}

// Database connection
$servername = "localhost";
$username = "admin";
$password = "1234";
$database = "aromiq";

$conn = mysqli_connect($servername, $username, $password, $database);

if (!$conn) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Get period filter
$period = isset($_GET['period']) ? $_GET['period'] : 'all';

// Set up the query based on the period
$date_condition = "";
switch ($period) {
    case 'day':
        $date_condition = "WHERE DATE(o.created_at) = CURDATE()";
        break;
    case 'week':
        $date_condition = "WHERE YEARWEEK(o.created_at, 1) = YEARWEEK(CURDATE(), 1)";
        break;
    case 'month':
        $date_condition = "WHERE YEAR(o.created_at) = YEAR(CURDATE()) AND MONTH(o.created_at) = MONTH(CURDATE())";
        break;
    default:
        $date_condition = "";
}

// Query for top foods
$query = "SELECT i.food_name, SUM(i.quantity) as total_quantity 
          FROM tbl_order_items i
          JOIN tbl_orders o ON i.order_id = o.order_id
          $date_condition
          GROUP BY i.food_name 
          ORDER BY total_quantity DESC 
          LIMIT 10";

$result = mysqli_query($conn, $query);

$labels = [];
$data = [];

if ($result && mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
        $labels[] = $row['food_name'];
        $data[] = (int)$row['total_quantity'];
    }
}

// Return data as JSON
header('Content-Type: application/json');
echo json_encode([
    'labels' => $labels,
    'data' => $data
]);

mysqli_close($conn);
?>
