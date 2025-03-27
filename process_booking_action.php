<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Always set content type to JSON
header('Content-Type: application/json');

// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Make sure PHPMailer is installed via Composer

try {
    session_start();
    if(!isset($_SESSION['uname'])){
        throw new Exception('Not authenticated');
    }

    include 'connect.php';

    // Check if required parameters are provided
    if (!isset($_POST['action']) || !isset($_POST['booking_id'])) {
        throw new Exception('Missing required parameters');
    }

    $action = $_POST['action'];
    $booking_id = $_POST['booking_id'];

    // Get booking details
    $sql = "SELECT * FROM tbl_bookings WHERE booking_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $booking_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (!$result || mysqli_num_rows($result) == 0) {
        throw new Exception('Booking not found');
    }

    $booking = mysqli_fetch_assoc($result);
    $customer_name = $booking['name'];
    $customer_email = $booking['email'];
    $table_number = $booking['table_number'];
    $booking_datetime = $booking['datetime'];

    // Format date for email
    $formatted_date = date('l, F j, Y', strtotime($booking_datetime));
    $formatted_time = date('g:i A', strtotime($booking_datetime));

    // Initialize PHPMailer
    $mail = new PHPMailer(true);
    
    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // Replace with your SMTP server
    $mail->SMTPAuth = true;
    $mail->Username = 'your-email@gmail.com'; // Replace with your email
    $mail->Password = 'your-password'; // Replace with your email password or app password
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    // Sender and recipient
    $mail->setFrom('aromiq@restaurant.com', 'Aromiq Restaurant');
    $mail->addAddress($customer_email, $customer_name);

    // Process based on action
    switch ($action) {
        case 'confirm':
            // Update booking status
            $sql = "UPDATE tbl_bookings SET status = 'Confirmed' WHERE booking_id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $booking_id);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception('Failed to update booking status: ' . mysqli_error($conn));
            }

            // Send confirmation email
            $mail->Subject = 'Your Reservation at Aromiq is Confirmed';
            $mail->Body = "Dear $customer_name,\n\n"
                . "We're pleased to confirm your reservation at Aromiq Restaurant.\n\n"
                . "Reservation Details:\n"
                . "Date: $formatted_date\n"
                . "Time: $formatted_time\n"
                . "Table: $table_number\n\n"
                . "We look forward to serving you!\n\n"
                . "Best regards,\n"
                . "The Aromiq Team";

            if ($mail->send()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Booking confirmed and confirmation email sent'
                ]);
            } else {
                echo json_encode([
                    'success' => true,
                    'message' => 'Booking confirmed but failed to send email: ' . $mail->ErrorInfo
                ]);
            }
            break;

        case 'cancel':
            // Update booking status
            $sql = "UPDATE tbl_bookings SET status = 'Cancelled' WHERE booking_id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $booking_id);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception('Failed to update booking status: ' . mysqli_error($conn));
            }

            // Send cancellation email
            $mail->Subject = 'Your Reservation at Aromiq has been Cancelled';
            $mail->Body = "Dear $customer_name,\n\n"
                . "We regret to inform you that your reservation at Aromiq Restaurant has been cancelled.\n\n"
                . "Cancelled Reservation Details:\n"
                . "Date: $formatted_date\n"
                . "Time: $formatted_time\n"
                . "Table: $table_number\n\n"
                . "If you have any questions, please contact us.\n\n"
                . "Best regards,\n"
                . "The Aromiq Team";

            if ($mail->send()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Booking cancelled and notification email sent'
                ]);
            } else {
                echo json_encode([
                    'success' => true,
                    'message' => 'Booking cancelled but failed to send email: ' . $mail->ErrorInfo
                ]);
            }
            break;

        case 'notify':
            // Send notification email
            $mail->Subject = 'Reminder: Your Upcoming Reservation at Aromiq';
            $mail->Body = "Dear $customer_name,\n\n"
                . "This is a friendly reminder about your upcoming reservation at Aromiq Restaurant.\n\n"
                . "Reservation Details:\n"
                . "Date: $formatted_date\n"
                . "Time: $formatted_time\n"
                . "Table: $table_number\n\n"
                . "We look forward to serving you!\n\n"
                . "Best regards,\n"
                . "The Aromiq Team";

            if ($mail->send()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Notification email sent successfully'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to send notification email: ' . $mail->ErrorInfo
                ]);
            }
            break;

        default:
            throw new Exception('Invalid action');
    }

} catch (Exception $e) {
    error_log('Error in process_booking_action.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} finally {
    if (isset($conn) && $conn) {
        mysqli_close($conn);
    }
}
?> 