<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Always set content type to JSON
header('Content-Type: application/json');

// Include PHPMailer classes manually
require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/Exception.php';
require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

try {
    session_start();
    if(!isset($_SESSION['uname'])){
        throw new Exception('Not authenticated');
    }

    include 'connect.php';
    
    // Check if notification_sent column exists and add it if needed
    $checkColumnQuery = "SELECT COLUMN_NAME 
                        FROM INFORMATION_SCHEMA.COLUMNS 
                        WHERE TABLE_SCHEMA = DATABASE() 
                        AND TABLE_NAME = 'tbl_bookings' 
                        AND COLUMN_NAME = 'notification_sent'";
    
    $checkColumnResult = mysqli_query($conn, $checkColumnQuery);
    
    // If column doesn't exist, add it
    if (mysqli_num_rows($checkColumnResult) == 0) {
        $addColumnQuery = "ALTER TABLE tbl_bookings 
                           ADD COLUMN notification_sent TINYINT(1) DEFAULT 0 
                           AFTER status";
        if (!mysqli_query($conn, $addColumnQuery)) {
            error_log("Failed to add notification_sent column: " . mysqli_error($conn));
            // Continue execution - we'll handle this later
        } else {
            error_log("Successfully added notification_sent column to tbl_bookings table");
        }
    }

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
    
    // Create HTML email content
    $emailBody = "
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
        $emailBody .= "
                        <tr>
                            <th>Special Request:</th>
                            <td>" . htmlspecialchars($booking['special_request']) . "</td>
                        </tr>";
    }
    
    if (!empty($booking['special_option'])) {
        $emailBody .= "
                        <tr>
                            <th>Special Event:</th>
                            <td>" . htmlspecialchars($booking['special_option']) . "</td>
                        </tr>";
    }
    
    $emailBody .= "
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
    </html>";

    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);

    // Flag to track if we need to use file-based fallback
    $useFileFallback = true;
    
    try {
        // Try SMTP first with a short timeout
        $mail->SMTPDebug = SMTP::DEBUG_OFF;                       // Disable debug output
        $mail->isSMTP();                                          // Send using SMTP
        $mail->Host       = 'smtp.gmail.com';                     // Set the SMTP server
        $mail->SMTPAuth   = true;                                 // Enable SMTP authentication
        $mail->Username   = 'neeevaacodes@gmail.com';             // SMTP username
        $mail->Password   = 'goyh taty yybp bhjw';                // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;       // Enable TLS encryption
        $mail->Port       = 587;                                  // TCP port to connect to
        $mail->Timeout    = 5;                                    // Set a shorter timeout (5 seconds)
        
        // Recipients
        $mail->setFrom('neeevaacodes@gmail.com', 'Aromiq Restaurant');
        $mail->addAddress($booking['email'], $booking['name']);   // Add a recipient
        $mail->addReplyTo('contact@aromiq.com', 'Aromiq Support');
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = "Aromiq - Table Booking Confirmation #" . $booking['booking_id'];
        $mail->Body    = $emailBody;
        $mail->AltBody = strip_tags(str_replace(['<br>', '</tr>'], "\n", $emailBody));
        
        // Try to send email
        $mail->send();
        $useFileFallback = false; // SMTP worked, don't need fallback
        
    } catch (Exception $e) {
        // Log the SMTP error but don't stop execution
        error_log("SMTP connection failed: " . $e->getMessage());
        // Continue to fallback method
    }
    
    // If SMTP failed, use file-based fallback for development environment
    if ($useFileFallback) {
        // Create emails directory if it doesn't exist
        $emailDir = __DIR__ . '/emails';
        if (!file_exists($emailDir)) {
            mkdir($emailDir, 0777, true);
        }
        
        // Save email content to file for debugging/development
        $filename = $emailDir . '/booking_' . $booking_id . '_' . date('Y-m-d_H-i-s') . '.html';
        file_put_contents($filename, $emailBody);
        
        // Log the fallback action
        error_log("Email saved to file: $filename");
    }
    
    // Try to update notification status in database - with better error handling
    $notificationUpdated = false;
    try {
        // First check if the column exists now (it might have been added above)
        $checkColumnResult = mysqli_query($conn, $checkColumnQuery);
        
        if (mysqli_num_rows($checkColumnResult) > 0) {
            $update_sql = "UPDATE tbl_bookings SET notification_sent = 1 WHERE booking_id = ?";
            $update_stmt = mysqli_prepare($conn, $update_sql);
            mysqli_stmt_bind_param($update_stmt, "i", $booking_id);
            mysqli_stmt_execute($update_stmt);
            $notificationUpdated = true;
        } else {
            error_log("notification_sent column still doesn't exist");
        }
    } catch (Exception $e) {
        error_log("Could not update notification status: " . $e->getMessage());
    }
    
    // Return success regardless of the method used
    echo json_encode([
        'success' => true,
        'message' => $useFileFallback ? 
            'Email saved locally for development (SMTP connection failed)' : 
            'Booking confirmation email sent successfully',
        'notification_updated' => $notificationUpdated
    ]);
    
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