<?php
// This file will be included by send-bill-email.php
// $order variable should be available from the parent scope

// Calculate totals
$subtotal = $order['total'];
$cgst = $subtotal * 0.025; // 2.5% CGST
$sgst = $subtotal * 0.025; // 2.5% SGST
$grand_total = $subtotal + $cgst + $sgst;

// Get order items
$items_sql = "SELECT * FROM tbl_order_items WHERE order_id = ?";
$items_stmt = mysqli_prepare($conn, $items_sql);
mysqli_stmt_bind_param($items_stmt, "s", $order_number);
mysqli_stmt_execute($items_stmt);
$items_result = mysqli_stmt_get_result($items_stmt);

$billHtml = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Bill - Aromiq</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 800px; margin: 30px auto; }
        .header { background: #fea116; color: white; padding: 20px; text-align: center; }
        .bill-info { margin: 20px 0; }
        .items-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .items-table th, .items-table td { padding: 10px; border-bottom: 1px solid #ddd; }
        .total-section { margin: 20px 0; text-align: right; }
        .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>Aromiq Restaurant</h1>
            <p>Bill Receipt</p>
        </div>
        
        <div class='bill-info'>
            <p><strong>Order Number:</strong> {$order['order_id']}</p>
            <p><strong>Date:</strong> " . date('F j, Y', strtotime($order['timestamp'])) . "</p>
            <p><strong>Customer:</strong> {$order['customer_name']}</p>
            <p><strong>Table Number:</strong> {$order['table_number']}</p>
        </div>

        <table class='items-table'>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>";

while ($item = mysqli_fetch_assoc($items_result)) {
    $billHtml .= "
                <tr>
                    <td>{$item['item_name']}</td>
                    <td>{$item['quantity']}</td>
                    <td>₹" . number_format($item['price'], 2) . "</td>
                    <td>₹" . number_format($item['quantity'] * $item['price'], 2) . "</td>
                </tr>";
}

$billHtml .= "
            </tbody>
        </table>

        <div class='total-section'>
            <p><strong>Subtotal:</strong> ₹" . number_format($subtotal, 2) . "</p>
            <p><strong>CGST (2.5%):</strong> ₹" . number_format($cgst, 2) . "</p>
            <p><strong>SGST (2.5%):</strong> ₹" . number_format($sgst, 2) . "</p>
            <p><strong>Grand Total:</strong> ₹" . number_format($grand_total, 2) . "</p>
        </div>

        <div class='footer'>
            <p>Thank you for dining with us!</p>
            <p>123 Food Street, Cuisine City | Phone: +1234567890</p>
            <p>This is a computer-generated bill and does not require signature.</p>
        </div>
    </div>
</body>
</html>";

return $billHtml;
?> 