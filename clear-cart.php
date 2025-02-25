<?php
session_start();
include 'connect.php';

// Initialize response array
$response = array('success' => false);

try {
    // Get session ID
    $session_id = session_id();
    
    // Clear items from database
    $sql = "DELETE FROM tbl_shoppingcart WHERE usersessionid = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Error preparing statement: " . $conn->error);
    }
    
    $stmt->bind_param("s", $session_id);
    $result = $stmt->execute();
    
    if (!$result) {
        throw new Exception("Error executing query: " . $stmt->error);
    }
    
    // Store order details before clearing session
    $order_details = isset($_SESSION['order_details']) ? $_SESSION['order_details'] : null;
    $cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : null;
    $cart_total = isset($_SESSION['cart_total']) ? $_SESSION['cart_total'] : null;
    
    // Clear cart from session
    unset($_SESSION['cart']);
    
    // Keep order details for bill
    $_SESSION['final_order'] = array(
        'details' => $order_details,
        'items' => $cart_items,
        'total' => $cart_total,
        'timestamp' => date('Y-m-d H:i:s')
    );
    
    $response['success'] = true;
    
} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>