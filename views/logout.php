<?php
// Logout Script
require_once __DIR__ . '/../controllers/AuthController.php';
$init = new AuthController;
$init->logout();
