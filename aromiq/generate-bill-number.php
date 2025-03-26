<?php
include 'connect.php';
/**
 * Bill Number Generator
 * 
 * Generates unique bill numbers using a pattern similar to order numbers
 */

/**
 * Generate a unique bill number
 * 
 * @param string $order_id The order ID to associate with this bill
 * @return string Unique bill number in format bl-YYYYMMDDHHMMSS
 */
function generateBillNumber($order_id) {
    // Get current timestamp in YYYYMMDDHHMMSS format
    $timestamp = date('YmdHis');
    
    // Create the bill number with format bl-YYYYMMDDHHMMSS
    $bill_number = "bl-{$timestamp}";
    
    return $bill_number;
}

/**
 * Save a bill number to the database
 * 
 * @param string $order_id The order ID
 * @param string $bill_number The generated bill number
 * @return bool Success status
 */
function saveBillNumber($order_id, $bill_number) {
    global $conn;
    
    // Check if a bill number already exists for this order
    $check_query = "SELECT bill_number FROM tbl_bills WHERE order_id = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("s", $order_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Bill number already exists, return it
        $row = $result->fetch_assoc();
        return $row['bill_number'];
    }
    
    // Insert the new bill number
    $query = "INSERT INTO tbl_bills (order_id, bill_number, generated_at) 
              VALUES (?, ?, NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $order_id, $bill_number);
    $success = $stmt->execute();
    
    return $success ? $bill_number : false;
}

/**
 * Get a bill number for an order
 * 
 * @param string $order_id The order ID
 * @return string|bool The bill number or false if not found
 */
function getBillNumber($order_id) {
    global $conn;
    
    $query = "SELECT bill_number FROM tbl_bills WHERE order_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['bill_number'];
    }
    
    // If not found, generate a new one and save it
    $bill_number = generateBillNumber($order_id);
    $saved = saveBillNumber($order_id, $bill_number);
    
    return $saved ? $bill_number : false;
}

/**
 * Get an order ID from a bill number
 * 
 * @param string $bill_number The bill number
 * @return string|bool The order ID or false if not found
 */
function getOrderIdFromBillNumber($bill_number) {
    global $conn;
    
    $query = "SELECT order_id FROM tbl_bills WHERE bill_number = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $bill_number);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['order_id'];
    }
    
    return false;
}

// Create the bills table if it doesn't exist
function createBillsTableIfNotExists() {
    global $conn;
    
    // First check if orders table exists and has expected structure
    $check_orders = "SHOW TABLES LIKE 'tbl_orders'";
    $result = $conn->query($check_orders);
    if ($result->num_rows == 0) {
        error_log("tbl_orders table does not exist!");
    }
    
    // Check column names in orders table
    $check_columns = "SHOW COLUMNS FROM tbl_orders";
    $result = $conn->query($check_columns);
    $columns = [];
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
        error_log("Found column: " . $row['Field']);
    }
    
    if (!in_array('order_id', $columns)) {
        error_log("order_id column doesn't exist in tbl_orders!");
    }
    
    $query = "CREATE TABLE IF NOT EXISTS tbl_bills (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id VARCHAR(50) NOT NULL,
        bill_number VARCHAR(50) NOT NULL,
        generated_at DATETIME NOT NULL,
        UNIQUE KEY (order_id),
        UNIQUE KEY (bill_number)
    )";
    
    return $conn->query($query);
}

// Call this function to ensure the table exists
createBillsTableIfNotExists();
?> 