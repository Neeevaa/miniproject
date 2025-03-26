<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Always set content type to JSON
header('Content-Type: application/json');

try {
    session_start();
    if(!isset($_SESSION['uname'])){
        throw new Exception('Not authenticated');
    }

    include 'connect.php';

    // Get parameters from request
    if (!isset($_POST['booking_id']) || !isset($_POST['status'])) {
        throw new Exception('Booking ID and status are required');
    }
    
    $booking_id = mysqli_real_escape_string($conn, $_POST['booking_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    // Validate status value
    $allowed_statuses = ['Pending', 'Confirmed', 'Cancelled'];
    if (!in_array($status, $allowed_statuses)) {
        throw new Exception('Invalid status value');
    }
    
    // Update booking status
    $sql = "UPDATE tbl_bookings SET status = ? WHERE booking_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $status, $booking_id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode([
            'success' => true,
            'message' => "Booking status updated to $status"
        ]);
    } else {
        throw new Exception("Failed to update booking status: " . mysqli_error($conn));
    }
    
} catch (Exception $e) {
    error_log('Error in update_booking_status.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($conn) && $conn) {
        mysqli_close($conn);
    }
}
?> 