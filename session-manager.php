<?php
/**
 * Save order details to the database
 * @param bool $force Force update if order already exists
 * @return bool Success or failure
 */
function saveOrderToDatabase($force = false) {
    global $conn, $order, $order_number;
    
    if (empty($order_number)) {
        error_log("Cannot save order: No order number provided");
        return false;
    }
    
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Check if order already exists
        $check_query = "SELECT order_id FROM tbl_orders WHERE order_id = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("s", $order_number);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $order_exists = ($check_result->num_rows > 0);
        $check_stmt->close();
        
        if ($order_exists && !$force) {
            error_log("Order already exists and force update not enabled");
            $conn->rollback();
            return false;
        }
        
        // Prepare order data
        $customer_name = isset($order['customer_name']) ? $order['customer_name'] : '';
        $customer_email = isset($order['customer_email']) ? $order['customer_email'] : '';
        $customer_phone = isset($order['customer_phone']) ? $order['customer_phone'] : '';
        $table_number = isset($order['table_number']) ? $order['table_number'] : '';
        $subtotal = isset($order['subtotal']) ? $order['subtotal'] : 0;
        $tax = isset($order['tax']) ? $order['tax'] : 0;
        $total = isset($order['total']) ? $order['total'] : 0;
        $status = isset($order['status']) ? $order['status'] : 'pending';
        $payment_status = isset($order['payment_status']) ? $order['payment_status'] : 'pending';
        $payment_method = isset($order['payment_method']) ? $order['payment_method'] : 'Cash';
        $timestamp = isset($order['timestamp']) ? $order['timestamp'] : date('Y-m-d H:i:s');
        
        if ($order_exists) {
            // Update existing order
            $query = "UPDATE tbl_orders SET 
                customer_name = ?,
                customer_email = ?,
                customer_phone = ?,
                table_number = ?,
                subtotal = ?,
                tax = ?,
                total = ?,
                status = ?,
                payment_status = ?,
                payment_method = ?,
                timestamp = ?
                WHERE order_id = ?";
                
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssssdddssss", 
                $customer_name,
                $customer_email,
                $customer_phone,
                $table_number,
                $subtotal,
                $tax,
                $total,
                $status,
                $payment_status,
                $payment_method,
                $timestamp,
                $order_number
            );
            
            // Execute update
            $result = $stmt->execute();
            if (!$result) {
                error_log("Error updating order: " . $stmt->error);
                $conn->rollback();
                return false;
            }
            
            // Delete existing order items to replace them
            $delete_items_query = "DELETE FROM tbl_order_items WHERE order_id = ?";
            $delete_items_stmt = $conn->prepare($delete_items_query);
            $delete_items_stmt->bind_param("s", $order_number);
            $delete_items_stmt->execute();
            $delete_items_stmt->close();
            
        } else {
            // Insert new order
            $query = "INSERT INTO tbl_orders (
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
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssssdddssss", 
                $order_number,
                $customer_name,
                $customer_email,
                $customer_phone,
                $table_number,
                $subtotal,
                $tax,
                $total,
                $status,
                $payment_status,
                $payment_method,
                $timestamp
            );
            
            // Execute insert
            $result = $stmt->execute();
            if (!$result) {
                error_log("Error inserting order: " . $stmt->error);
                $conn->rollback();
                return false;
            }
        }
        
        // Close statement
        $stmt->close();
        
        // Insert order items
        if (!empty($order['items'])) {
            foreach ($order['items'] as $item) {
                $item_name = isset($item['food_name']) ? $item['food_name'] : 
                            (isset($item['name']) ? $item['name'] : 'Unknown Item');
                $item_price = isset($item['price']) ? $item['price'] : 0;
                $item_quantity = isset($item['quantity']) ? $item['quantity'] : 1;
                
                $items_query = "INSERT INTO tbl_order_items (
                    order_id,
                    food_name,
                    price,
                    quantity
                ) VALUES (?, ?, ?, ?)";
                
                $items_stmt = $conn->prepare($items_query);
                $items_stmt->bind_param("ssdi", 
                    $order_number,
                    $item_name,
                    $item_price,
                    $item_quantity
                );
                
                // Execute insert
                $items_result = $items_stmt->execute();
                if (!$items_result) {
                    error_log("Error inserting order item: " . $items_stmt->error);
                    $conn->rollback();
                    return false;
                }
                
                $items_stmt->close();
            }
        }
        
        // Commit transaction
        $conn->commit();
        error_log("Successfully saved order #" . $order_number . " to database");
        return true;
        
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        error_log("Database error: " . $e->getMessage());
        return false;
    }
}

/**
 * Load order details from the database
 * @param string $order_number Order ID to load
 * @return bool Success or failure
 */
function loadOrderFromDatabase($order_number) {
    global $conn, $order;
    
    if (empty($order_number)) {
        error_log("Cannot load order: No order number provided");
        return false;
    }
    
    try {
        // Load order details
        $query = "SELECT * FROM tbl_orders WHERE order_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $order_number);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            error_log("Order not found in database: " . $order_number);
            return false;
        }
        
        // Get order data
        $order_data = $result->fetch_assoc();
        $stmt->close();
        
        // Update order array
        $order['order_id'] = $order_data['order_id'];
        $order['customer_name'] = $order_data['customer_name'];
        $order['customer_email'] = $order_data['customer_email'];
        $order['customer_phone'] = $order_data['customer_phone'];
        $order['table_number'] = $order_data['table_number'];
        $order['subtotal'] = $order_data['subtotal'];
        $order['tax'] = $order_data['tax'];
        $order['total'] = $order_data['total'];
        $order['status'] = $order_data['status'];
        $order['payment_status'] = $order_data['payment_status'];
        $order['payment_method'] = $order_data['payment_method'];
        $order['timestamp'] = $order_data['timestamp'];
        
        // Load order items
        $items_query = "SELECT * FROM tbl_order_items WHERE order_id = ?";
        $items_stmt = $conn->prepare($items_query);
        $items_stmt->bind_param("s", $order_number);
        $items_stmt->execute();
        $items_result = $items_stmt->get_result();
        
        // Clear existing items and load from database
        $order['items'] = [];
        while ($item = $items_result->fetch_assoc()) {
            $order['items'][] = $item;
        }
        
        $items_stmt->close();
        error_log("Successfully loaded order #" . $order_number . " from database with " . count($order['items']) . " items");
        return true;
        
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
        return false;
    }
} 