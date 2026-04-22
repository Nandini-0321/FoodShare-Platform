<?php
require_once __DIR__ . '/../models/Donation.php';
require_once __DIR__ . '/../models/User.php';

class NgoController {
    private $donationModel;
    private $userModel;
    
    public function __construct() {
        $this->donationModel = new Donation();
        $this->userModel = new User();
        
        // Ensure user is logged in
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'ngo') {
            header('location: /FoodShare/views/auth/login.php');
            exit;
        }
    }
    
    public function getDashboardData() {
        $userId = $_SESSION['user_id'];
        
        $data = [
            'stats' => $this->donationModel->getNgoStats($userId),
            'my_requests' => $this->donationModel->getNgoRequests($userId),
            'available_food' => $this->donationModel->getAvailableDonations(), // Show some available food on dashboard
            'user' => $this->userModel->getUserById($userId)
        ];
        
        return $data;
    }
    
    public function getAvailableFood() {
        $userId = $_SESSION['user_id'];
        
        // Get NGO's coordinates from users table
        $user = $this->userModel->getUserById($userId);
        
        $userLat = $user->latitude ?? null;
        $userLon = $user->longitude ?? null;
        
        return $this->donationModel->getAvailableDonations($userLat, $userLon);
    }
    
    public function requestFood($donation_id) {
        $userId = $_SESSION['user_id'];
        
        // Check if already requested
        if($this->donationModel->checkExistingRequest($donation_id, $userId)) {
            return false; // Already requested
        }
        
        return $this->donationModel->requestDonation($donation_id, $userId);
    }

    // Cancel a request
    public function cancelRequest($donation_id) {
        $userId = $_SESSION['user_id'];
        return $this->donationModel->cancelRequest($donation_id, $userId);
    }

    // Check if request exists
    public function checkExistingRequest($donation_id) {
        $userId = $_SESSION['user_id'];
        return $this->donationModel->checkExistingRequest($donation_id, $userId);
    }
    // Get all requests for this NGO
    public function getRequests() {
        $userId = $_SESSION['user_id'];
        return $this->donationModel->getNgoRequests($userId);
    }

    // Get volunteers who have delivered to this NGO
    public function getVolunteers() {
        $userId = $_SESSION['user_id'];
        // This would ideally come from a more complex query joining pickups, donations, and users
        // For now, we'll return an empty array or implement a basic query in Donation model if needed
        // Let's assume we want to show volunteers associated with completed donations
        return []; 
    }
    
    // Get stats for reports
    public function getStats() {
        $userId = $_SESSION['user_id'];
        return $this->donationModel->getNgoStats($userId);
    }

    // Render profile page
    public function profile() {
        // The view handles the logic directly for now, similar to donor profile
        // In a stricter MVC, we would move the POST handling here
        require_once __DIR__ . '/../views/ngo/profile.php';
    }
}
