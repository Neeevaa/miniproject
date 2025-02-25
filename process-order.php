<?php
session_start();
include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = $_POST['name'];
    $mobile_number = $_POST['mobile'];
    $table_number = $_POST['table'];
    $payment_mode = $_POST['payment'];
    $session_id = session_id();

    // Start transaction
    $conn->begin_transaction();

    try {
        // Get cart items
        $cart_sql = "SELECT * FROM tbl_shoppingcart WHERE usersessionid = ?";
        $cart_stmt = $conn->prepare($cart_sql);
        $cart_stmt->bind_param("s", $session_id);
        $cart_stmt->execute();
        $cart_result = $cart_stmt->get_result();
        $cart_items = $cart_result->fetch_all(MYSQLI_ASSOC);

        // Calculate total amount
        $total_amount = 0;
        foreach ($cart_items as $item) {
            $total_amount += $item['price'] * $item['quantity'];
        }

        // Insert order
        $order_sql = "INSERT INTO tbl_orders (customer_name, mobile_number, table_number, payment_mode, total_amount) 
                      VALUES (?, ?, ?, ?, ?)";
        $order_stmt = $conn->prepare($order_sql);
        $order_stmt->bind_param("ssssd", $customer_name, $mobile_number, $table_number, $payment_mode, $total_amount);
        $order_stmt->execute();
        $order_id = $conn->insert_id;

        // Insert order items
        $items_sql = "INSERT INTO tbl_order_items (order_id, food_name, quantity, price) VALUES (?, ?, ?, ?)";
        $items_stmt = $conn->prepare($items_sql);
        
        foreach ($cart_items as $item) {
            $items_stmt->bind_param("isid", $order_id, $item['food_name'], $item['quantity'], $item['price']);
            $items_stmt->execute();
        }

        // Clear cart
        $clear_sql = "DELETE FROM tbl_shoppingcart WHERE usersessionid = ?";
        $clear_stmt = $conn->prepare($clear_sql);
        $clear_stmt->bind_param("s", $session_id);
        $clear_stmt->execute();

        // Commit transaction
        $conn->commit();
        
        // Store order details in session
        $_SESSION['order_details'] = [
            'name' => $customer_name,
            'mobile' => $mobile_number,
            'table' => $table_number,
            'payment' => strtolower($payment_mode) // Convert to lowercase when storing
        ];

        // Keep the cart items in session until confirmation
        $_SESSION['cart'] = $cart_items;
        $_SESSION['cart_total'] = $total_amount;

        // Success response
        $response = [
            'success' => true,
            'message' => 'Order processed successfully',
            'redirect_url' => 'order-confirmation.php',
            'order_details' => [
                'order_id' => $order_id,
                'customer_name' => $customer_name,
                'mobile_number' => $mobile_number,
                'table_number' => $table_number,
                'payment_mode' => $payment_mode,
                'total_amount' => $total_amount
            ]
        ];

    } catch (Exception $e) {
        // Rollback the transaction
        $conn->rollback();
        
        // Error response
        $response = [
            'success' => false,
            'message' => 'Error processing order: ' . $e->getMessage(),
            'redirect_url' => null
        ];
    }

    // Send JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>