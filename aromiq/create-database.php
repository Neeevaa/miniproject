<?php
// Database configuration
$servername = "localhost";
$username = "root";  // Replace with your MySQL username
$password = "";      // Replace with your MySQL password

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS aromiq";

if ($conn->query($sql) === TRUE) {
    echo "<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; border: 1px solid #ccc; border-radius: 5px; background-color: #f9f9f9;'>";
    echo "<h2 style='color: #4CAF50;'>Success!</h2>";
    echo "<p>Database 'aromiq' created successfully.</p>";
    echo "<p>You can now proceed to <a href='create-tables.php' style='color: #2196F3; text-decoration: none;'>create tables</a>.</p>";
    echo "</div>";
} else {
    echo "<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; border: 1px solid #ccc; border-radius: 5px; background-color: #fff0f0;'>";
    echo "<h2 style='color: #F44336;'>Error!</h2>";
    echo "<p>Error creating database: " . $conn->error . "</p>";
    echo "</div>";
}

// Close connection
$conn->close();
?>
