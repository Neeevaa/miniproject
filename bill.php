<?php
session_start();

// Redirect if no final order details
if (!isset($_SESSION['final_order'])) {
    header('Location: menu.php');
    exit();
}

$order = $_SESSION['final_order'];
$order_number = strtoupper(uniqid('ARQ'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill - Aromiq</title>
    <!-- Template Stylesheet -->
    <link rel="stylesheet" href="css/style.css">
    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600&family=Nunito:wght@600;700;800&family=Pacifico&display=swap" rel="stylesheet">

    <!-- Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <style>
        .bill-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-radius: 10px;
        }

        .bill-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #fea116;
        }

        .restaurant-name {
            color: #fea116;
            font-size: 2em;
            font-family: 'Pacifico', cursive;
            margin-bottom: 10px;
        }

        .bill-info {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .info-group {
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }

        .info-label {
            font-weight: bold;
            color: #0f172b;
        }

        .bill-items {
            margin-bottom: 30px;
        }

        .item-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .item-header {
            font-weight: bold;
            color: #0f172b;
            border-bottom: 2px solid #fea116;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }

        .bill-total {
            text-align: right;
            padding-top: 20px;
            border-top: 2px solid #eee;
        }

        .total-row {
            display: flex;
            justify-content: flex-end;
            gap: 20px;
            margin-bottom: 10px;
        }

        .grand-total {
            font-size: 1.2em;
            font-weight: bold;
            color: #fea116;
        }

        .bill-footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #eee;
        }

        .action-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-top: 30px;
        }

        .btn-print, .btn-email, .btn-status {
            background: #fea116;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn-print:hover, .btn-email:hover, .btn-status:hover {
            background: #e89114;
        }

        @media print {
            .action-buttons {
                display: none;
            }
            
            .bill-container {
                box-shadow: none;
                margin: 0;
                padding: 20px;
            }
        }

        .gst-details {
            margin: 20px 0;
            font-size: 0.9em;
            color: #666;
        }

        .qr-code {
            text-align: center;
            margin: 20px 0;
        }

        .qr-code img {
            width: 180px;
            height: 180px;
        }
    </style>
</head>
<body>
    <div class="bill-container">
        <div class="bill-header">
            <div class="restaurant-name">Aromiq</div>
            <p>123 Restaurant Street, Foodie City</p>
            <p>Tel: +91 9895316120</p>
            <p>GSTIN: 22AAAAA0000A1Z5</p>
        </div>

        <div class="bill-info">
            <div class="info-group">
                <div><span class="info-label">Bill No:</span> <?php echo $order_number; ?></div>
                <div><span class="info-label">Date:</span> <?php echo date('d-m-Y', strtotime($order['timestamp'])); ?></div>
                <div><span class="info-label">Time:</span> <?php echo date('H:i', strtotime($order['timestamp'])); ?></div>
            </div>
            <div class="info-group">
                <div><span class="info-label">Customer:</span> <?php echo htmlspecialchars($order['details']['name']); ?></div>
                <div><span class="info-label">Mobile:</span> <?php echo htmlspecialchars($order['details']['mobile']); ?></div>
                <div><span class="info-label">Table No:</span> <?php echo htmlspecialchars($order['details']['table']); ?></div>
            </div>
        </div>

        <div class="bill-items">
            <div class="item-row item-header">
                <div>Item</div>
                <div>Price</div>
                <div>Qty</div>
                <div>Amount</div>
            </div>
            <?php foreach ($order['items'] as $item): ?>
            <div class="item-row">
                <div><?php echo htmlspecialchars($item['food_name']); ?></div>
                <div>₹<?php echo number_format($item['price'], 2); ?></div>
                <div><?php echo $item['quantity']; ?></div>
                <div>₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="bill-total">
            <?php
            $subtotal = $order['total'];
            $cgst = $subtotal * 0.025; // 2.5% CGST
            $sgst = $subtotal * 0.025; // 2.5% SGST
            $grand_total = $subtotal + $cgst + $sgst;
            ?>
            <div class="total-row">
                <span class="info-label">Subtotal:</span>
                <span>₹<?php echo number_format($subtotal, 2); ?></span>
            </div>
            <div class="total-row">
                <span class="info-label">CGST (2.5%):</span>
                <span>₹<?php echo number_format($cgst, 2); ?></span>
            </div>
            <div class="total-row">
                <span class="info-label">SGST (2.5%):</span>
                <span>₹<?php echo number_format($sgst, 2); ?></span>
            </div>
            <div class="total-row grand-total">
                <span class="info-label">Grand Total:</span>
                <span>₹<?php echo number_format($grand_total, 2); ?></span>
            </div>
        </div>

        <div class="gst-details">
            <p>CGST: 2.5% | SGST: 2.5%</p>
            <p>This is a computer-generated bill and does not require signature.</p>
        </div>

        <!-- <div class="qr-code">
            Add your restaurant's UPI QR code here 
            <img src="img/qr-code.jpg" alt="Payment QR Code">
        </div>-->

        <div class="bill-footer">
            <p>Thank you for dining with us!</p>
            <p>Please visit again</p>
        </div>

        <div class="action-buttons">
            <button onclick="window.print()" class="btn-print">Print Bill</button>
            <button onclick="emailBill()" class="btn-email">Email Bill</button>
            <button onclick="orderStatus()" class="btn-status">View Order Status</button>
        </div>
    </div>

    <script>
        function emailBill() {
            // Add email functionality here
            alert("I'll doo Email functionality soonn!!.");
        }
        funciton orderStatus(){
            alert("i'll doo thatt too soonn!!")
        }
    </script>
</body>
</html>