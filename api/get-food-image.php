<?php
// c:\xampp\htdocs\Foodshare3\api\get-food-image.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../controllers/DonorController.php';

// Detect if it is JSON or regular POST
$input = file_get_contents('php://input');
$data = json_decode($input, true);

$foodName = $data['food_name'] ?? $_GET['name'] ?? '';
$category = $data['category'] ?? $_GET['category'] ?? 'other';
$refresh = isset($data['refresh']) || isset($_GET['refresh']);

if (empty($foodName)) {
    echo json_encode(['success' => false, 'message' => 'Food name is required']);
    exit;
}

$controller = new DonorController();
// Provide a fresh search if requested
$images = $controller->fetchMultipleFoodImages($foodName, $category, 5, $refresh);

if (!empty($images)) {
    echo json_encode([
        'success' => true, 
        'images' => $images, 
        'image_url' => $images[0],
        'cached' => !$refresh
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to fetch images']);
}
