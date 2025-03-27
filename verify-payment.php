<?php
session_start();
include 'connect.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$payment_id = isset($input['payment_id']) ? $input['payment_id'] : null;
$order_id = isset($input['order_id']) ? $input['order_id'] : null;

// Initialize response
$response = [
    'success' => false,
    'message' => 'Invalid request'
];

if (!$payment_id || !$order_id) {
    // Bad request
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// In a production environment, you would verify the payment with Razorpay API
// For this example, we'll just update the order status in the database

try {
    // Start transaction
    $conn->begin_transaction();
    
    // Update order payment status
    $sql = "UPDATE tbl_orders SET payment_status = 'paid' WHERE order_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $order_id);
    $stmt->execute();
    
    // Check if order was updated successfully
    if ($stmt->affected_rows > 0) {
        // Commit transaction
        $conn->commit();
        
        $response = [
            'success' => true,
            'message' => 'Payment verified successfully',
            'order_id' => $order_id
        ];
    } else {
        // Order not found or already paid
        $conn->rollback();
        $response = [
            'success' => false,
            'message' => 'Order not found or already paid'
        ];
    }
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    $response = [
        'success' => false,
        'message' => 'Error verifying payment: ' . $e->getMessage()
    ];
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit;
?> 