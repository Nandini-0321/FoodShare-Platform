<?php
require_once 'config/database.php';

$conn = getDBConnection();

echo "Starting database reset...\n";

// Disable foreign key checks
$conn->query("SET FOREIGN_KEY_CHECKS = 0");

$tables = [
    'users',
    'donor_details',
    'ngo_details',
    'volunteer_details',
    'donations',
    'requests',
    'pickups',
    'notifications',
    'reviews',
    'activity_log'
];

foreach ($tables as $table) {
    echo "Truncating table: $table... ";
    if ($conn->query("TRUNCATE TABLE $table")) {
        echo "Done.\n";
    } else {
        echo "Error: " . $conn->error . "\n";
    }
}

// Enable foreign key checks
$conn->query("SET FOREIGN_KEY_CHECKS = 1");

echo "Database reset completed successfully.\n";
?>
