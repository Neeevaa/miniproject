<?php
session_start();
if(!isset($_SESSION['uname'])){
    header("Location: login.php");
    exit;
}

$servername = "localhost";
$username = "kitchenadmin";
$password = "1234";
$database = "aromiq";

// Connect to MySQL server
$conn = mysqli_connect($servername, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Process bulk update request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['orders'])) {
    $orders = $_POST['orders'];
    $success = true;
    $updated = 0;
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        foreach ($orders as $orderId => $status) {
            // Validate order ID and status
            $orderId = (int)$orderId;
            $status = mysqli_real_escape_string($conn, $status);
            
            if ($orderId > 0 && in_array($status, ['Pending', 'In Progress', 'Completed', 'Cancelled'])) {
                $query = "UPDATE tbl_orders SET order_status = '$status' WHERE order_id = $orderId";
                if (!mysqli_query($conn, $query)) {
                    throw new Exception("Error updating order #$orderId: " . mysqli_error($conn));
                }
                $updated++;
            }
        }
        
        // Commit transaction
        mysqli_commit($conn);
        
        echo json_encode([
            'success' => true,
            'message' => "$updated orders updated successfully.",
            'updated' => $updated
        ]);
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($conn);
        
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request.'
    ]);
}

mysqli_close($conn);
?>
