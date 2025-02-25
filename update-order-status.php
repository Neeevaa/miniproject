<?php
// Create a new file named update_order_status.php
session_start();

if (!isset($_SESSION['uname'])) {
    http_response_code(403);
    exit('Unauthorized');
}

$servername = "localhost";
$username = "kitchenadmin";
$password = "1234";
$database = "aromiq";

$conn = mysqli_connect($servername, $username, $password, $database);

if (!$conn) {
    http_response_code(500);
    exit("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = mysqli_real_escape_string($conn, $_POST['order_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $allowed_statuses = ['Pending', 'In Progress', 'Completed', 'Cancelled'];
    if (!in_array($status, $allowed_statuses)) {
        die(json_encode(['success' => false, 'error' => 'Invalid status']));
    }

    $query = "UPDATE tbl_orders SET order_status = ? WHERE order_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $status, $order_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Update failed']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}

?>