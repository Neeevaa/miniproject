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
    
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || 
        $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        throw new Exception('Invalid security token');
    }

    // Check required fields
    if (!isset($_POST['recipient']) || !isset($_POST['order_number'])) {
        throw new Exception('Required fields are missing');
    }

    include 'connect.php';

    $recipient = filter_var($_POST['recipient'], FILTER_SANITIZE_EMAIL);
    $order_number = htmlspecialchars($_POST['order_number']);
    $subject = isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : 
               "Your Aromiq Bill Receipt #" . $order_number;
    $message = isset($_POST['message']) ? htmlspecialchars($_POST['message']) : 
               "Thank you for dining with us!";

    // Get order details from database
    $sql = "SELECT * FROM tbl_orders WHERE order_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $order_number);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (!$result || mysqli_num_rows($result) === 0) {
        throw new Exception('Order not found');
    }

    $order = mysqli_fetch_assoc($result);

    // Get order items - first check the structure to get correct column names
    $check_columns_sql = "SHOW COLUMNS FROM tbl_order_items";
    $columns_result = mysqli_query($conn, $check_columns_sql);
    $has_item_name = false;
    $item_name_column = "";
    
    if ($columns_result) {
        while ($column = mysqli_fetch_assoc($columns_result)) {
            // Find the column that likely contains the item name
            if (strpos(strtolower($column['Field']), 'name') !== false || 
                strpos(strtolower($column['Field']), 'item') !== false || 
                strpos(strtolower($column['Field']), 'dish') !== false || 
                strpos(strtolower($column['Field']), 'product') !== false) {
                $item_name_column = $column['Field'];
                break;
            }
        }
    }
    
    if (empty($item_name_column)) {
        // Default fallback if we couldn't determine the column
        $item_name_column = "name";
    }
    
    // Get order items
    $items_sql = "SELECT * FROM tbl_order_items WHERE order_id = ?";
    $items_stmt = mysqli_prepare($conn, $items_sql);
    mysqli_stmt_bind_param($items_stmt, "s", $order_number);
    mysqli_stmt_execute($items_stmt);
    $items_result = mysqli_stmt_get_result($items_stmt);

    // Calculate totals
    $subtotal = $order['total'];
    $cgst = $subtotal * 0.025; // 2.5% CGST
    $sgst = $subtotal * 0.025; // 2.5% SGST
    $grand_total = $subtotal + $cgst + $sgst;

    // Create email HTML content
    $emailBody = "
    <div style='font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto;'>
        <div style='background-color: #fea116; color: white; padding: 20px; text-align: center;'>
            <h1 style='margin:0;'>Aromiq Restaurant</h1>
            <p style='margin:10px 0 0;'>Bill Receipt</p>
        </div>
        
        <div style='padding: 20px; background-color: #f9f9f9;'>
            <p>Dear " . htmlspecialchars($order['customer_name']) . ",</p>
            <p>" . $message . "</p>
            
            <div style='margin: 20px 0; padding: 15px; background-color: white; border-radius: 5px;'>
                <p><strong>Order Number:</strong> " . htmlspecialchars($order['order_id']) . "</p>
                <p><strong>Date:</strong> " . date('F j, Y', strtotime($order['timestamp'])) . "</p>
                <p><strong>Table Number:</strong> " . htmlspecialchars($order['table_number']) . "</p>
            </div>

            <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
                <thead>
                    <tr style='background-color: #f5f5f5;'>
                        <th style='padding: 10px; border-bottom: 2px solid #ddd; text-align: left;'>Item</th>
                        <th style='padding: 10px; border-bottom: 2px solid #ddd; text-align: center;'>Quantity</th>
                        <th style='padding: 10px; border-bottom: 2px solid #ddd; text-align: right;'>Price</th>
                        <th style='padding: 10px; border-bottom: 2px solid #ddd; text-align: right;'>Total</th>
                    </tr>
                </thead>
                <tbody>";

    while ($item = mysqli_fetch_assoc($items_result)) {
        // Safely access the item name using our detected column or fallback to a placeholder
        $item_name = isset($item[$item_name_column]) ? $item[$item_name_column] : 
                   (isset($item['dish_name']) ? $item['dish_name'] : 
                   (isset($item['food_item']) ? $item['food_item'] : 
                   (isset($item['name']) ? $item['name'] : 'Item')));
                   
        $itemTotal = $item['quantity'] * $item['price'];
        $emailBody .= "
                    <tr>
                        <td style='padding: 10px; border-bottom: 1px solid #ddd;'>" . htmlspecialchars($item_name) . "</td>
                        <td style='padding: 10px; border-bottom: 1px solid #ddd; text-align: center;'>" . htmlspecialchars($item['quantity']) . "</td>
                        <td style='padding: 10px; border-bottom: 1px solid #ddd; text-align: right;'>₹" . number_format($item['price'], 2) . "</td>
                        <td style='padding: 10px; border-bottom: 1px solid #ddd; text-align: right;'>₹" . number_format($itemTotal, 2) . "</td>
                    </tr>";
    }

    $emailBody .= "
                </tbody>
            </table>

            <div style='margin: 20px 0; text-align: right;'>
                <p style='margin: 5px 0;'><strong>Subtotal:</strong> ₹" . number_format($subtotal, 2) . "</p>
                <p style='margin: 5px 0;'><strong>CGST (2.5%):</strong> ₹" . number_format($cgst, 2) . "</p>
                <p style='margin: 5px 0;'><strong>SGST (2.5%):</strong> ₹" . number_format($sgst, 2) . "</p>
                <p style='margin: 15px 0; font-size: 1.2em; color: #fea116;'><strong>Grand Total:</strong> ₹" . number_format($grand_total, 2) . "</p>
            </div>
        </div>

        <div style='text-align: center; padding: 20px; background-color: #f5f5f5; margin-top: 20px;'>
            <p style='margin: 5px 0;'>Thank you for dining with us!</p>
            <p style='margin: 5px 0;'>Aromiq Restaurant</p>
            <p style='margin: 5px 0;'>123 Food Street, Cuisine City</p>
            <p style='margin: 5px 0;'>Phone: +1234567890</p>
            <p style='margin: 15px 0; font-size: 0.9em; color: #666;'>This is a computer-generated bill and does not require signature.</p>
        </div>
    </div>";

    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);

    // Flag to track if we need to use file-based fallback
    $useFileFallback = true;
    
    try {
        // Try SMTP first with a short timeout - exactly like notify_booking.php
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
        $mail->addAddress($recipient);
        $mail->addReplyTo('contact@aromiq.com', 'Aromiq Support');

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $emailBody;
        $mail->AltBody = strip_tags(str_replace(['<br>', '</p>', '</tr>'], "\n", $emailBody));

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
        $filename = $emailDir . '/bill_' . $order_number . '_' . date('Y-m-d_H-i-s') . '.html';
        file_put_contents($filename, $emailBody);
        
        // Log the fallback action
        error_log("Email saved to file: $filename");
    }
    
    // Return success regardless of the method used
    echo json_encode([
        'success' => true,
        'message' => $useFileFallback ? 
            'Email saved locally for development (SMTP connection failed)' : 
            'Bill has been sent to your email successfully!',
        'fallback_used' => $useFileFallback
    ]);

} catch (Exception $e) {
    error_log('Error in send-bill-email.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        mysqli_close($conn);
    }
}
?> 