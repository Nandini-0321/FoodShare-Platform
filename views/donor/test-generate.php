<?php
// Test endpoint
session_start();

// Simulate being logged in for testing
$_SESSION['user_id'] = 1;
$_SESSION['user_type'] = 'donor';

require_once '../../controllers/DonorController.php';

// Simulate POST request
$_SERVER['REQUEST_METHOD'] = 'POST';
file_put_contents('php://input', json_encode([
    'food_name' => 'Vegetable Biryani',
    'category' => 'cooked'
]));

$controller = new DonorController();
$controller->handleImageGenerationRequest();
?>
