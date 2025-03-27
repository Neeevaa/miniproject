<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Always set content type to JSON
header('Content-Type: application/json');

// Include PHPMailer library
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require './vendor/PHPMailer/src/Exception.php';
require './vendor/PHPMailer/src/PHPMailer.php';
require './vendor/PHPMailer/src/SMTP.php';

try {
    session_start();
    if(!isset($_SESSION['uname'])){
        throw new Exception('Not authenticated');
    }

    include 'connect.php';

    // Get booking ID from request
    if (!isset($_POST['booking_id'])) {
        throw new Exception('Booking ID is required');
    }
    
    $booking_id = mysqli_real_escape_string($conn, $_POST['booking_id']);
    
    // Get booking details
    $sql = "SELECT * FROM tbl_bookings WHERE booking_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $booking_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (!$result || mysqli_num_rows($result) === 0) {
        throw new Exception('Booking not found');
    }
    
    $booking = mysqli_fetch_assoc($result);
    
    // Format date and time for email
    $date_obj = new DateTime($booking['datetime']);
    $formatted_date = $date_obj->format('l, F j, Y');
    $formatted_time = $date_obj->format('h:i A');
    
    // Create PHPMailer instance
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'neeevaacodes@gmail.com';
        $mail->Password   = 'goyh taty yybp bhjw';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('no-reply@aromiq.com', 'Aromiq Restaurant');
        $mail->addAddress($booking['email']);
        $mail->addReplyTo('contact@aromiq.com', 'Aromiq Support');

        // Content
        $mail->isHTML(true);
        $mail->Subject = "Aromiq - Table Booking Confirmation #" . $booking['booking_id'];
        
        // HTML email template (same as previous script)
        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #fea116; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background-color: #f9f9f9; }
                .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
                .details { margin: 20px 0; }
                .details table { width: 100%; border-collapse: collapse; }
                .details td, .details th { padding: 10px; border-bottom: 1px solid #ddd; }
                .details th { text-align: left; width: 40%; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Booking Confirmation</h1>
                </div>
                <div class='content'>
                    <p>Dear " . htmlspecialchars($booking['name']) . ",</p>
                    
                    <p>Thank you for choosing Aromiq! Your table booking has been confirmed. Here are your booking details:</p>
                    
                    <div class='details'>
                        <table>
                            <tr>
                                <th>Booking ID:</th>
                                <td>#" . htmlspecialchars($booking['booking_id']) . "</td>
                            </tr>
                            <tr>
                                <th>Date:</th>
                                <td>" . htmlspecialchars($formatted_date) . "</td>
                            </tr>
                            <tr>
                                <th>Time:</th>
                                <td>" . htmlspecialchars($formatted_time) . "</td>
                            </tr>
                            <tr>
                                <th>Table Number:</th>
                                <td>" . htmlspecialchars($booking['table_number']) . "</td>
                            </tr>
                            <tr>
                                <th>Number of People:</th>
                                <td>" . htmlspecialchars($booking['people_count']) . "</td>
                            </tr>";
        
        if (!empty($booking['special_request'])) {
            $message .= "
                            <tr>
                                <th>Special Request:</th>
                                <td>" . htmlspecialchars($booking['special_request']) . "</td>
                            </tr>";
        }
        
        if (!empty($booking['special_option'])) {
            $message .= "
                            <tr>
                                <th>Special Event:</th>
                                <td>" . htmlspecialchars($booking['special_option']) . "</td>
                            </tr>";
        }
        
        $message .= "
                        </table>
                    </div>
                    
                    <p>Please note:</p>
                    <ul>
                        <li>Please arrive 10 minutes before your booking time</li>
                        <li>Your table will be held for 15 minutes after your booking time</li>
                        <li>For any changes or cancellations, please contact us at least 2 hours before your booking time</li>
                    </ul>
                    
                    <p>If you need to make any changes to your booking or have any questions, please don't hesitate to contact us:</p>
                    <p>Phone: +1234567890<br>Email: contact@aromiq.com</p>
                </div>
                <div class='footer'>
                    <p>Aromiq Restaurant<br>123 Food Street, Cuisine City<br>Thank you for choosing us!</p>
                </div>
            </div>
        </body>
        </html>
        ";

        $mail->Body = $message;

        // Send email
        $mail->send();
        
        // Update notification status in database if needed
        $update_sql = "UPDATE tbl_bookings SET notification_sent = 1 WHERE booking_id = ?";
        $update_stmt = mysqli_prepare($conn, $update_sql);
        mysqli_stmt_bind_param($update_stmt, "i", $booking_id);
        mysqli_stmt_execute($update_stmt);
        
        echo json_encode([
            'success' => true,
            'message' => 'Booking confirmation email sent successfully'
        ]);

    } catch (Exception $e) {
        throw new Exception('Email could not be sent. Mailer Error: ' . $mail->ErrorInfo);
    }
    
} catch (Exception $e) {
    error_log('Error in notify_booking.php: ' . $e->getMessage());
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