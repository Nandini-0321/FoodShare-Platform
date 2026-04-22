<?php
require_once __DIR__ . '/../models/Pickup.php';
require_once __DIR__ . '/../models/User.php';

class VolunteerController {
    private $pickupModel;
    private $userModel;
    
    public function __construct() {
        $this->pickupModel = new Pickup();
        $this->userModel = new User();
        
        // Ensure user is logged in
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'volunteer') {
            header('location: /FoodShare/views/auth/login.php');
            exit;
        }
    }
    
    public function getDashboardData() {
        $userId = $_SESSION['user_id'];
        
        $data = [
            'stats' => $this->pickupModel->getStats($userId),
            'my_tasks' => $this->pickupModel->getVolunteerTasks($userId),
            'available_pickups' => $this->pickupModel->getAvailablePickups(),
            'user' => $this->userModel->getUserById($userId)
        ];
        
        return $data;
    }
    
    public function getAvailablePickups() {
        return $this->pickupModel->getAvailablePickups();
    }
    
    public function acceptPickup($donation_id) {
        $volunteer_id = $_SESSION['user_id'];
        return $this->pickupModel->assignPickup($donation_id, $volunteer_id);
    }
    
    public function updateStatus($pickup_id, $status) {
        return $this->pickupModel->updateStatus($pickup_id, $status);
    }

    public function getAchievements() {
        $userId = $_SESSION['user_id'];
        // Get stats and calculate level/badges
        $stats = $this->pickupModel->getStats($userId);
        
        // Add some gamification logic here
        $points = $stats['total_pickups'] * 10 + $stats['completed_deliveries'] * 20;
        $level = floor($points / 100) + 1;
        
        return [
            'stats' => $stats,
            'points' => $points,
            'level' => $level,
            'next_level_points' => $level * 100
        ];
    }
    // Render profile page
    public function profile() {
        require_once __DIR__ . '/../views/volunteer/profile.php';
    }
}
