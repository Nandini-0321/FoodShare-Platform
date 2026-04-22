<?php
// Database Configuration
// Replace with your database credentials

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'foodshare');

// Create connection
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    return $conn;
}

// Site Configuration
define('SITE_NAME', 'FoodShare');
define('SITE_URL', 'http://localhost/Foodshare3');
define('ADMIN_EMAIL', 'admin@foodshare.com');

// Session Configuration
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
