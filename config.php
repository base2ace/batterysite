<?php
// config.php - Database connection settings

// Define database credentials
define('DB_SERVER', 'localhost'); // Your MySQL host (e.g., 'localhost' for XAMPP)
define('DB_USERNAME', 'root');    // Your MySQL username (e.g., 'root' for XAMPP)
define('DB_PASSWORD', '');        // Your MySQL password (often empty '' for XAMPP root)
define('DB_NAME', 'battery_db');  // The name of your database

// Attempt to establish a database connection
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    // Log the connection error instead of dying directly,
    // so the AJAX script can catch it and provide a message.
    error_log("Database connection failed: " . $conn->connect_error);
    // You might want to throw an exception here or set $conn to null
    // to ensure dependent functions handle the missing connection.
    // For now, we'll let functions handle $conn being potentially null/false.
}

// Set character set to utf8mb4 for proper handling of various characters
if ($conn && !$conn->set_charset("utf8mb4")) {
    error_log("Error loading character set utf8mb4: " . $conn->error);
}

?>
