<?php
session_start();
include 'connect.php';

// Debug function to log errors
function debug_log($message, $data = null) {
    error_log("ORDER DEBUG: " . $message . (is_null($data) ? "" : " - " . print_r($data, true)));
}

// Initialize response array
$response = [
    'success' => false,
    'message' => 'Unknown error occurred',
    'debug' => []
];

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    debug_log("POST request received");
    $response['debug']['request_method'] = 'POST';
    
    // Get form data
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $mobile = isset($_POST['mobile']) ? trim($_POST['mobile']) : '';
    $table = isset($_POST['table']) ? intval($_POST['table']) : 0;
    $payment = isset($_POST['payment']) ? trim($_POST['payment']) : '';
    $total_amount = isset($_POST['totalAmount']) ? floatval($_POST['totalAmount']) : 0;
    
    $response['debug']['form_data'] = [
        'name' => $name,
        'email' => $email,
        'mobile' => $mobile,
        'table' => $table,
        'payment' => $payment,
        'total_amount' => $total_amount
    ];
    
    // Try to get cart items from multiple sources
    $cartItems = [];
    
    // 1. First check POST data
    if (isset($_POST['cartItems'])) {
        debug_log("cartItems found in POST");
        $cartItemsJson = $_POST['cartItems'];
        $response['debug']['cart_source'] = 'POST';
        $response['debug']['cart_json'] = $cartItemsJson;
        
        // Decode JSON
        $cartItems = json_decode($cartItemsJson, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            debug_log("JSON decode error: " . json_last_error_msg());
            $response['debug']['json_error'] = json_last_error_msg();
        }
    }
    
    // 2. If that failed, try session
    if (empty($cartItems) && isset($_SESSION['cart'])) {
        debug_log("Using cart from SESSION");
        $cartItems = $_SESSION['cart'];
        $response['debug']['cart_source'] = 'SESSION';
    }
    
    // 3. If still empty, try getting from database
    if (empty($cartItems)) {
        debug_log("Trying to get cart from DATABASE");
        $response['debug']['cart_source'] = 'DATABASE';
        
        $session_id = session_id();
        $sql = "SELECT * FROM tbl_shoppingcart WHERE usersessionid = ?";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("s", $session_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $cartItems = $result->fetch_all(MYSQLI_ASSOC);
        } else {
            debug_log("Database error: " . $conn->error);
            $response['debug']['db_error'] = $conn->error;
        }
    }
    
    $response['debug']['cart_items'] = $cartItems;
    
    // Validate essential data
    if (empty($name) || empty($email) || empty($mobile) || empty($table) || empty($payment)) {
        $response['message'] = 'Please fill all required fields';
        debug_log("Form validation failed");
    }
    // Validate cart items
    else if (empty($cartItems)) {
        $response['message'] = 'Your cart is empty';
        debug_log("Cart is empty");
    }
    else {
        debug_log("All validation passed, processing order");
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Generate unique order ID
            $order_id = 'ORD' . time() . rand(100, 999);
            
            // Calculate subtotal and tax
            $subtotal = $total_amount; // Your original total amount
            $tax = $subtotal * 0.05; // Assuming 5% tax, adjust as needed
            $total = $subtotal + $tax;
            
            // Get current timestamp in MySQL format
            $current_timestamp = date('Y-m-d H:i:s');
            
            // Update SQL to include timestamp as a parameter instead of NOW()
            $sql = "INSERT INTO tbl_orders (
                order_id, 
                customer_name, 
                customer_email, 
                customer_phone, 
                table_number, 
                subtotal,
                tax,
                total,
                status,
                payment_status,
                payment_method,
                timestamp
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'pending', ?, ?)";
            
            $stmt = $conn->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Error preparing statement: " . $conn->error);
            }
            
            // Add timestamp to bind_param
            $stmt->bind_param("ssssidddss", // Changed from "ssssidddsss" - removed one "s"
    $order_id, 
    $name, 
    $email, 
    $mobile, 
    $table, 
    $subtotal,
    $tax,
    $total,
    $payment,
    $current_timestamp
);
            $stmt->execute();
            
            if ($stmt->affected_rows <= 0) {
                throw new Exception("Error inserting order");
            }
            
            // Insert order items
            foreach ($cartItems as $item) {
                $food_name = $item['food_name'];
                $price = floatval($item['price']);
                $quantity = intval($item['quantity']);
                
                $sql = "INSERT INTO tbl_order_items (order_id, food_name, price, quantity) 
                        VALUES (?, ?, ?, ?)";
                
                $stmt = $conn->prepare($sql);
                
                if (!$stmt) {
                    throw new Exception("Error preparing statement: " . $conn->error);
                }
                
                // Updated bind_param to match the new SQL structure
                $stmt->bind_param("ssdi", $order_id, $food_name, $price, $quantity);
                
                $stmt->execute();
                
                if ($stmt->affected_rows <= 0) {
                    throw new Exception("Error inserting order item: " . $food_name);
                }
            }
            
            // Clear cart
            $session_id = session_id();
            $sql = "DELETE FROM tbl_shoppingcart WHERE usersessionid = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $session_id);
            $stmt->execute();
            
            // Clear cart session
            unset($_SESSION['cart']);
            
            // Commit the transaction
            $conn->commit();
            
            // Success response
            $response = [
                'success' => true,
                'message' => 'Order placed successfully',
                'order_id' => $order_id,
                'redirect' => 'payment.php?order=' . $order_id
            ];
            
            debug_log("Order processed successfully. Order ID: " . $order_id);
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            
            debug_log("Exception occurred: " . $e->getMessage());
            
            // Return error response
            $response = [
                'success' => false,
                'message' => 'Error processing order: ' . $e->getMessage()
            ];
        }
    }
} else {
    debug_log("Non-POST request received: " . $_SERVER['REQUEST_METHOD']);
    $response['message'] = 'Invalid request method';
    $response['debug']['request_method'] = $_SERVER['REQUEST_METHOD'];
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit;
?>