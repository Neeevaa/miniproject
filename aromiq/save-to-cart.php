<?php
session_start();
include 'connect.php';

// Debug request information
error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
error_log("POST Data: " . print_r($_POST, true));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate required fields
    $required_fields = ['food_name', 'price', 'quantity', 'image_path'];
    $missing_fields = [];
    
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            $missing_fields[] = $field;
        }
    }
    
    if (!empty($missing_fields)) {
        echo json_encode([
            'success' => false,
            'message' => 'Missing required fields: ' . implode(', ', $missing_fields)
        ]);
        exit;
    }

    // Rest of your existing code...
    $food_name = $_POST['food_name'];
    $price = floatval($_POST['price']);
    $quantity = intval($_POST['quantity']);
    $image_path = $_POST['image_path'];
    $session_id = session_id();

    // Debug values
    error_log("Processing cart item:");
    error_log("Session ID: " . $session_id);
    error_log("Food Name: " . $food_name);
    error_log("Price: " . $price);
    error_log("Quantity: " . $quantity);
    error_log("Image Path: " . $image_path);

    try {
        // Check if item exists
        $check_sql = "SELECT * FROM tbl_shoppingcart WHERE usersessionid = ? AND food_name = ?";
        $check_stmt = $conn->prepare($check_sql);
        if (!$check_stmt) {
            throw new Exception("Error preparing check statement: " . $conn->error);
        }
        
        $check_stmt->bind_param("ss", $session_id, $food_name);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {
            // Update existing item
            $update_sql = "UPDATE tbl_shoppingcart SET quantity = quantity + ? WHERE usersessionid = ? AND food_name = ?";
            $stmt = $conn->prepare($update_sql);
            if (!$stmt) {
                throw new Exception("Error preparing update statement: " . $conn->error);
            }
            $stmt->bind_param("iss", $quantity, $session_id, $food_name);
        } else {
            // Insert new item
            $insert_sql = "INSERT INTO tbl_shoppingcart (usersessionid, food_name, price, quantity, image_path) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_sql);
            if (!$stmt) {
                throw new Exception("Error preparing insert statement: " . $conn->error);
            }
            $stmt->bind_param("ssdis", $session_id, $food_name, $price, $quantity, $image_path);
        }

        if ($stmt->execute()) {
            // Verify the data was saved
            $verify_sql = "SELECT * FROM tbl_shoppingcart WHERE usersessionid = ?";
            $verify_stmt = $conn->prepare($verify_sql);
            if (!$verify_stmt) {
                throw new Exception("Error preparing verify statement: " . $conn->error);
            }
            
            $verify_stmt->bind_param("s", $session_id);
            $verify_stmt->execute();
            $verify_result = $verify_stmt->get_result();
            $cart_items = $verify_result->fetch_all(MYSQLI_ASSOC);
            
            $_SESSION['cart'] = $cart_items;
            
            echo json_encode([
                'success' => true, 
                'message' => 'Item added to cart',
                'cartItems' => $cart_items
            ]);
        } else {
            throw new Exception("Error executing statement: " . $stmt->error);
        }
    } catch (Exception $e) {
        error_log("Error in save-to-cart.php: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
} else {
    error_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method. Expected POST, got ' . $_SERVER['REQUEST_METHOD']
    ]);
}
?>