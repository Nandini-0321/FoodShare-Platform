<?php
// c:\xampp\htdocs\Foodshare3\api\get-food-suggestions.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once '../controllers/DonorController.php';

$query = $_GET['q'] ?? '';

if (empty($query) || strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

$controller = new DonorController();
$suggestions = $controller->getFoodSuggestions($query);

echo json_encode($suggestions);
