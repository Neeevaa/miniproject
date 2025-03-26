<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Always set content type to JSON
header('Content-Type: application/json');

// Log the raw POST data
error_log('POST data: ' . print_r($_POST, true));

try {
    // Database connection
    include 'connect.php';
    
    // Log received data for debugging
    error_log("Received booking data: " . print_r($_POST, true));
    
    // Check if form data exists
    if (empty($_POST)) {
        throw new Exception("No form data received. Please fill out the booking form.");
    }
    
    // Validate required fields
    $required_fields = ['name', 'email', 'contact', 'date', 'time', 'people'];
    $missing_fields = [];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            $missing_fields[] = $field;
        }
    }
    
    if (!empty($missing_fields)) {
        // Return detailed error about missing fields
        echo json_encode([
            'success' => false,
            'message' => 'Missing required fields: ' . implode(', ', $missing_fields)
        ]);
        exit;
    }
    
    // Sanitize inputs
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']); // Changed from 'phone'
    $date = mysqli_real_escape_string($conn, $_POST['date']);
    $time = mysqli_real_escape_string($conn, $_POST['time']);
    $people = mysqli_real_escape_string($conn, $_POST['people']);
    $special_request = isset($_POST['special_request']) ? mysqli_real_escape_string($conn, $_POST['special_request']) : '';
    $special_option = isset($_POST['special_option']) ? mysqli_real_escape_string($conn, $_POST['special_option']) : '';
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email format");
    }
    
    // Validate date format
    $date_obj = DateTime::createFromFormat('Y-m-d H:i', $date . ' ' . $time);
    if (!$date_obj || $date_obj->format('Y-m-d H:i') !== $date . ' ' . $time) {
        throw new Exception("Invalid date format. Please use YYYY-MM-DD HH:MM format");
    }
    
    // Check if the table is already booked for this date/time
    $check_sql = "SELECT * FROM tbl_bookings 
                 WHERE contact = ? 
                 AND datetime = ? 
                 AND (status = 'Pending' OR status = 'Confirmed')";
    
    $check_stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "ss", $contact, $date . ' ' . $time);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    
    if (mysqli_num_rows($check_result) > 0) {
        throw new Exception("This table is already booked for the selected date and time. Please choose another table or time.");
    }
    
    // Insert the booking
    $insert_sql = "INSERT INTO tbl_bookings (name, email, contact, datetime, people_count, special_request, special_option, status) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')";
    
    $insert_stmt = mysqli_prepare($conn, $insert_sql);
    mysqli_stmt_bind_param($insert_stmt, "sssssss", 
        $name, 
        $email, 
        $contact, 
        $date . ' ' . $time, 
        $people, 
        $special_request, 
        $special_option
    );
    
    if (mysqli_stmt_execute($insert_stmt)) {
        $booking_id = mysqli_insert_id($conn);
        echo json_encode([
            'success' => true,
            'message' => 'Booking confirmed! We look forward to serving you.',
            'booking_id' => $booking_id
        ]);
    } else {
        throw new Exception("Error saving booking: " . mysqli_error($conn));
    }
    
} catch (Exception $e) {
    error_log('Error in process-booking.php: ' . $e->getMessage());
    http_response_code(400);
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