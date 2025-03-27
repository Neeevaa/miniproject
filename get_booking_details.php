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

    // Get table number from request
    $table_number = isset($_GET['table_number']) ? $_GET['table_number'] : null;
    $booking_id = isset($_GET['booking_id']) ? $_GET['booking_id'] : null;

    if (!$table_number && !$booking_id) {
        // If neither parameter is provided, get all bookings
        $sql = "SELECT * FROM tbl_bookings WHERE status != 'Cancelled' ORDER BY datetime";
        $stmt = mysqli_prepare($conn, $sql);
    } elseif ($booking_id) {
        // If booking_id is provided, get specific booking
        $sql = "SELECT * FROM tbl_bookings WHERE booking_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $booking_id);
    } else {
        // If table_number is provided, get booking for that table
        $sql = "SELECT * FROM tbl_bookings WHERE table_number = ? AND status != 'Cancelled' ORDER BY datetime LIMIT 1";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $table_number);
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (!$result) {
        throw new Exception('Query failed: ' . mysqli_error($conn));
    }

    $bookings = [];
    while ($row = mysqli_fetch_assoc($result)) {
        // Format datetime for display
        $date_obj = new DateTime($row['datetime']);
        
        $booking = [
            'booking_id' => $row['booking_id'],
            'customer_name' => $row['name'],
            'email' => $row['email'],
            'table_number' => $row['table_number'],
            'datetime' => $row['datetime'],
            'booking_date' => $date_obj->format('Y-m-d'),
            'booking_time' => $date_obj->format('h:i A'),
            'guest_count' => $row['people_count'],
            'special_requests' => $row['special_request'],
            'special_option' => $row['special_option'],
            'status' => $row['status']
        ];
        
        $bookings[] = $booking;
    }

    // Return appropriate response based on request type
    if ($booking_id || $table_number) {
        echo json_encode([
            'success' => true,
            'booking' => !empty($bookings) ? $bookings[0] : null
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'bookings' => $bookings
        ]);
    }

} catch (Exception $e) {
    error_log('Error in get_booking_details.php: ' . $e->getMessage());
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