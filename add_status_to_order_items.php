<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database connection
include 'connect.php';

try {
    // Check if table exists
    $result = mysqli_query($conn, "SHOW TABLES LIKE 'tbl_order_items'");
    
    if (mysqli_num_rows($result) > 0) {
        // Check if the column already exists
        $columns = mysqli_query($conn, "SHOW COLUMNS FROM tbl_order_items LIKE 'status'");
        
        if (mysqli_num_rows($columns) == 0) {
            // Add the status column
            $sql = "ALTER TABLE tbl_order_items ADD COLUMN status VARCHAR(20) DEFAULT 'Pending'";
            
            if (mysqli_query($conn, $sql)) {
                echo "Status column added to tbl_order_items table successfully";
            } else {
                echo "Error adding status column: " . mysqli_error($conn);
            }
        } else {
            echo "Status column already exists in tbl_order_items table";
        }
    } else {
        echo "tbl_order_items table does not exist";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} finally {
    mysqli_close($conn);
}
?> 