<?php
$servername = "localhost";
$database = "aromiq"; // Ensure this matches the database name you created

// Define users
$users = [
    "admin" => "1234",
    "kitchenadmin" => "1234", 
    "chef" => "1234"
];

// Set the user you want to connect with
$current_user = "admin"; // Change this to "kitchenadmin" or "chef" as needed

// Check if the user exists in our array
if (array_key_exists($current_user, $users)) {
    $username = $current_user;
    $password = $users[$current_user];
    
    // Connect to MySQL server and select the database
    $conn = mysqli_connect($servername, $username, $password, $database);
    
    // Check connection
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    } else {
       // echo "Connected as user '$username' to the database server and selected database successfully!<br>";
    }
} else {
    die("Invalid user specified");
}
?>