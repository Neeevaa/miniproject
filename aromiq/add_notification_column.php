<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'connect.php';

try {
    // Add notification_sent column if it doesn't exist
    $sql = "ALTER TABLE tbl_bookings 
            ADD COLUMN notification_sent TINYINT(1) DEFAULT 0 
            AFTER status";
            
    if (mysqli_query($conn, $sql)) {
        echo "Successfully added notification_sent column to tbl_bookings table";
    } else {
        throw new Exception("Error adding column: " . mysqli_error($conn));
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} finally {
    mysqli_close($conn);
}
?> 