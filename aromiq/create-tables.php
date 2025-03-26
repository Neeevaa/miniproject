<?php
// Database configuration
$servername = "localhost";
$username = "root";  // Replace with your MySQL username
$password = "";      // Replace with your MySQL password
$dbname = "aromiq";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set character set
$conn->set_charset("utf8mb4");

// Array to track table creation success/failure
$tables = [
    'tbl_admin' => false,
    'tbl_bookings' => false,
    'tbl_feedback' => false,
    'tbl_fooditem' => false,
    'tbl_fooditemdetailed' => false,
    'tbl_orders' => false,
    'tbl_order_items' => false,
    'tbl_payment' => false,
    'tbl_shoppingcart' => false
];

// Create tables
$sql = [];

// Table structure for tbl_admin
$sql[] = "CREATE TABLE IF NOT EXISTS `tbl_admin` (
  `adminid` int(3) NOT NULL AUTO_INCREMENT,
  `adminuname` varchar(25) NOT NULL,
  `adminpswd` varchar(30) NOT NULL,
  PRIMARY KEY (`adminid`),
  UNIQUE KEY `adminuname` (`adminuname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

// Table structure for tbl_bookings
$sql[] = "CREATE TABLE IF NOT EXISTS `tbl_bookings` (
  `booking_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `table_number` int(11) NOT NULL,
  `datetime` datetime NOT NULL,
  `people_count` int(11) NOT NULL,
  `special_request` text DEFAULT NULL,
  `special_option` varchar(100) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`booking_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

// Table structure for tbl_feedback
$sql[] = "CREATE TABLE IF NOT EXISTS `tbl_feedback` (
  `feedbackId` int(4) NOT NULL AUTO_INCREMENT,
  `orderid` int(6) NOT NULL,
  `rating` int(5) NOT NULL,
  `comments` text DEFAULT NULL,
  PRIMARY KEY (`feedbackId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

// Table structure for tbl_fooditem
$sql[] = "CREATE TABLE IF NOT EXISTS `tbl_fooditem` (
  `itemid` int(11) NOT NULL AUTO_INCREMENT,
  `itemname` varchar(80) NOT NULL,
  `category` varchar(50) NOT NULL,
  `price` float NOT NULL,
  `itemdescription` text NOT NULL,
  `itemdetailed` text NOT NULL,
  `itemimage` varchar(50) NOT NULL,
  PRIMARY KEY (`itemid`),
  UNIQUE KEY `itemname` (`itemname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

// Table structure for tbl_fooditemdetailed
$sql[] = "CREATE TABLE IF NOT EXISTS `tbl_fooditemdetailed` (
  `ditemid` int(11) NOT NULL AUTO_INCREMENT,
  `itemid` int(11) DEFAULT NULL,
  `itemdetailed` text DEFAULT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ditemid`),
  UNIQUE KEY `unique_itemid` (`itemid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

// Table structure for tbl_orders
$sql[] = "CREATE TABLE IF NOT EXISTS `tbl_orders` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_name` varchar(255) NOT NULL,
  `mobile_number` varchar(15) NOT NULL,
  `table_number` int(11) NOT NULL,
  `payment_mode` varchar(50) NOT NULL,
  `order_status` enum('Pending','In Progress','Completed','Cancelled') DEFAULT 'Pending',
  `total_amount` decimal(10,2) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

// Table structure for tbl_order_items
$sql[] = "CREATE TABLE IF NOT EXISTS `tbl_order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) DEFAULT NULL,
  `food_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

// Table structure for tbl_payment
$sql[] = "CREATE TABLE IF NOT EXISTS `tbl_payment` (
  `paymentid` int(4) NOT NULL AUTO_INCREMENT,
  `orderid` int(6) NOT NULL,
  `sessionid` int(11) NOT NULL,
  `amount` int(8) NOT NULL,
  `method` enum('Card','UPI','Cash') NOT NULL,
  PRIMARY KEY (`paymentid`),
  UNIQUE KEY `orderid` (`orderid`),
  UNIQUE KEY `sessionid` (`sessionid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

// Table structure for tbl_shoppingcart
$sql[] = "CREATE TABLE IF NOT EXISTS `tbl_shoppingcart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usersessionid` varchar(255) NOT NULL,
  `food_name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

// Add constraints
$sql[] = "ALTER TABLE `tbl_fooditemdetailed`
  ADD CONSTRAINT `tbl_fooditemdetailed_ibfk_1` FOREIGN KEY (`itemid`) REFERENCES `tbl_fooditem` (`itemid`) ON DELETE CASCADE";

$sql[] = "ALTER TABLE `tbl_order_items`
  ADD CONSTRAINT `tbl_order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `tbl_orders` (`order_id`)";

// Initialize variables to count successes and failures
$successCount = 0;
$totalQueries = count($sql);
$errors = [];

// Execute each SQL query
foreach ($sql as $index => $query) {
    if ($conn->query($query) === TRUE) {
        $successCount++;
    } else {
        $errors[] = "Error in query #" . ($index + 1) . ": " . $conn->error;
    }
}

// Sample data insertion for tbl_admin
$adminDataSql = "INSERT INTO `tbl_admin` (`adminid`, `adminuname`, `adminpswd`) 
                 VALUES (101, 'admin', '1234'),
                        (102, 'kitchenadmin', '1234'),
                        (103, 'chef', '1234')";

if ($conn->query($adminDataSql) === TRUE) {
    echo "Admin data inserted successfully.<br>";
} else {
    // If error is not due to duplicate entry, show it
    if (strpos($conn->error, 'Duplicate entry') === false) {
        $errors[] = "Error inserting admin data: " . $conn->error;
    } else {
        echo "Admin data already exists.<br>";
    }
}

// Display results
echo "<!DOCTYPE html>
<html>
<head>
    <title>Table Creation Results</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 5px;
        }
        h1 {
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .success {
            color: #4CAF50;
            font-weight: bold;
        }
        .error {
            color: #F44336;
            font-weight: bold;
        }
        .progress {
            height: 20px;
            background-color: #e0e0e0;
            border-radius: 10px;
            margin: 20px 0;
            overflow: hidden;
        }
        .progress-bar {
            height: 100%;
            background-color: #4CAF50;
            border-radius: 10px;
        }
        ul {
            list-style-type: none;
            padding: 0;
        }
        li {
            margin-bottom: 5px;
            padding: 5px;
            background-color: #f9f9f9;
            border-radius: 3px;
        }
        .back-button {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 15px;
            background-color: #2196F3;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        .back-button:hover {
            background-color: #0b7dda;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Database Table Creation</h1>";

if ($successCount == $totalQueries) {
    echo "<p class='success'>All tables created successfully!</p>";
} else {
    echo "<p>Created $successCount out of $totalQueries tables.</p>";
}

echo "<div class='progress'>
          <div class='progress-bar' style='width: " . ($successCount / $totalQueries * 100) . "%;'></div>
      </div>";

if (!empty($errors)) {
    echo "<h2 class='error'>Errors:</h2>
          <ul>";
    foreach ($errors as $error) {
        echo "<li>$error</li>";
    }
    echo "</ul>";
}

echo "<p>You can now use your database 'aromiq' with all necessary tables.</p>
      <a href='create_database.php' class='back-button'>Back to Database Creation</a>
    </div>
</body>
</html>";

// Close connection
$conn->close();
?>
