<?php
include 'connect.php';

// Create tbl_shoppingcart table
$createTable = "CREATE TABLE IF NOT EXISTS tbl_shoppingcart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usersessionid VARCHAR(255) NOT NULL,
    food_name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    image_path VARCHAR(255) NOT NULL
)";

if (mysqli_query($conn, $createTable)) {
    echo "Table created successfully";
} else {
    echo "Error creating table: " . mysqli_error($conn);
}
mysqli_close($conn);
?> 