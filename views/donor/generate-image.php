<?php
// Endpoint for AI Image Generation
session_start();

// Check if user is logged in as donor
if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'donor') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once '../../controllers/DonorController.php';

$controller = new DonorController();
$controller->handleImageGenerationRequest();
?>
