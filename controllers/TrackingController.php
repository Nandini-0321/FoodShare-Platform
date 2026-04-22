<?php
require_once __DIR__ . '/../models/Tracking.php';
require_once __DIR__ . '/../config/database.php';

class TrackingController {
    private $trackingModel;
    
    public function __construct() {
        $this->trackingModel = new Tracking();
        
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
    }
    
    // Handle AJAX requests
    public function handleRequest() {
        // Suppress errors to prevent HTML output in JSON responses
        ini_set('display_errors', 0);
        error_reporting(0);
        header('Content-Type: application/json');
        
        $action = $_GET['action'] ?? $_POST['action'] ?? '';
        
        switch ($action) {
            case 'start_tracking':
                $this->startTracking();
                break;
            case 'update_location':
                $this->updateLocation();
                break;
            case 'get_current_location':
                $this->getCurrentLocation();
                break;
            case 'get_tracking_history':
                $this->getTrackingHistory();
                break;
            case 'stop_tracking':
                $this->stopTracking();
                break;
            case 'pause_tracking':
                $this->pauseTracking();
                break;
            case 'resume_tracking':
                $this->resumeTracking();
                break;
            case 'get_active_tracking':
                $this->getActiveTracking();
                break;
            default:
                http_response_code(400);
                echo json_encode(['error' => 'Invalid action']);
        }
    }
    
    // Start tracking session
    private function startTracking() {
        $pickup_id = $_POST['pickup_id'] ?? 0;
        $donation_id = $_POST['donation_id'] ?? 0;
        $volunteer_id = $_SESSION['user_id'];
        
        if (!$pickup_id || !$donation_id) {
            echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
            return;
        }
        
        // Verify volunteer owns this pickup
        if (!$this->verifyVolunteerPickup($pickup_id, $volunteer_id)) {
            http_response_code(403);
            echo json_encode(['error' => 'Access denied']);
            return;
        }
        
        $tracking_id = $this->trackingModel->startTracking($pickup_id, $donation_id, $volunteer_id);
        
        if ($tracking_id) {
            echo json_encode([
                'success' => true,
                'tracking_id' => $tracking_id,
                'message' => 'Tracking started successfully'
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to start tracking']);
        }
    }
    
    // Update location
    private function updateLocation() {
        $tracking_id = $_POST['tracking_id'] ?? 0;
        $latitude = $_POST['latitude'] ?? 0;
        $longitude = $_POST['longitude'] ?? 0;
        $accuracy = $_POST['accuracy'] ?? null;
        $speed = $_POST['speed'] ?? null;
        $heading = $_POST['heading'] ?? null;
        
        if (!$tracking_id || !$latitude || !$longitude) {
            echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
            return;
        }
        
        // Verify tracking belongs to current user
        $tracking = $this->trackingModel->getTracking($tracking_id);
        if (!$tracking || $tracking['volunteer_id'] != $_SESSION['user_id']) {
            http_response_code(403);
            echo json_encode(['error' => 'Access denied']);
            return;
        }
        
        $location_id = $this->trackingModel->updateLocation(
            $tracking_id, 
            $latitude, 
            $longitude, 
            $accuracy, 
            $speed, 
            $heading
        );
        
        if ($location_id) {
            echo json_encode([
                'success' => true,
                'location_id' => $location_id,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update location']);
        }
    }
    
    // Get current location
    private function getCurrentLocation() {
        $tracking_id = $_GET['tracking_id'] ?? 0;
        
        if (!$tracking_id) {
            echo json_encode(['success' => false, 'error' => 'Tracking ID required']);
            return;
        }
        
        // Verify access (donor, ngo, or volunteer)
        if (!$this->verifyTrackingAccess($tracking_id, $_SESSION['user_id'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Access denied']);
            return;
        }
        
        $location = $this->trackingModel->getCurrentLocation($tracking_id);
        
        if ($location) {
            echo json_encode([
                'success' => true,
                'location' => $location
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'No location data available']);
        }
    }
    
    // Get tracking history (route)
    private function getTrackingHistory() {
        $tracking_id = $_GET['tracking_id'] ?? 0;
        
        if (!$tracking_id) {
            echo json_encode(['success' => false, 'error' => 'Tracking ID required']);
            return;
        }
        
        // Verify access
        if (!$this->verifyTrackingAccess($tracking_id, $_SESSION['user_id'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Access denied']);
            return;
        }
        
        $history = $this->trackingModel->getLocationHistory($tracking_id);
        
        echo json_encode([
            'success' => true,
            'history' => $history,
            'count' => count($history)
        ]);
    }
    
    // Stop tracking
    private function stopTracking() {
        $tracking_id = $_POST['tracking_id'] ?? 0;
        
        if (!$tracking_id) {
            echo json_encode(['success' => false, 'error' => 'Tracking ID required']);
            return;
        }
        
        // Verify tracking belongs to current user
        $tracking = $this->trackingModel->getTracking($tracking_id);
        if (!$tracking || $tracking['volunteer_id'] != $_SESSION['user_id']) {
            http_response_code(403);
            echo json_encode(['error' => 'Access denied']);
            return;
        }
        
        $result = $this->trackingModel->stopTracking($tracking_id);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Tracking stopped successfully'
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to stop tracking']);
        }
    }
    
    // Pause tracking
    private function pauseTracking() {
        $tracking_id = $_POST['tracking_id'] ?? 0;
        
        if (!$tracking_id) {
            echo json_encode(['success' => false, 'error' => 'Tracking ID required']);
            return;
        }
        
        $tracking = $this->trackingModel->getTracking($tracking_id);
        if (!$tracking || $tracking['volunteer_id'] != $_SESSION['user_id']) {
            http_response_code(403);
            echo json_encode(['error' => 'Access denied']);
            return;
        }
        
        $result = $this->trackingModel->pauseTracking($tracking_id);
        
        echo json_encode(['success' => $result]);
    }
    
    // Resume tracking
    private function resumeTracking() {
        $tracking_id = $_POST['tracking_id'] ?? 0;
        
        if (!$tracking_id) {
            echo json_encode(['success' => false, 'error' => 'Tracking ID required']);
            return;
        }
        
        $tracking = $this->trackingModel->getTracking($tracking_id);
        if (!$tracking || $tracking['volunteer_id'] != $_SESSION['user_id']) {
            http_response_code(403);
            echo json_encode(['error' => 'Access denied']);
            return;
        }
        
        $result = $this->trackingModel->resumeTracking($tracking_id);
        
        echo json_encode(['success' => $result]);
    }
    
    // Get active tracking by donation
    private function getActiveTracking() {
        $donation_id = $_GET['donation_id'] ?? 0;
        
        if (!$donation_id) {
            echo json_encode(['success' => false, 'error' => 'Donation ID required']);
            return;
        }
        
        // Verify access to this donation
        if (!$this->verifyDonationAccess($donation_id, $_SESSION['user_id'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Access denied']);
            return;
        }
        
        $tracking = $this->trackingModel->getActiveTrackingByDonation($donation_id);
        
        if ($tracking) {
            echo json_encode([
                'success' => true,
                'tracking' => $tracking
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'No active tracking found'
            ]);
        }
    }
    
    // Verify volunteer owns the pickup
    private function verifyVolunteerPickup($pickup_id, $volunteer_id) {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT id FROM pickups WHERE id = ? AND volunteer_id = ?");
        $stmt->bind_param("ii", $pickup_id, $volunteer_id);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }
    
    // Verify user has access to tracking (donor, ngo, or volunteer)
    private function verifyTrackingAccess($tracking_id, $user_id) {
        $conn = getDBConnection();
        $stmt = $conn->prepare("
            SELECT dt.id 
            FROM delivery_tracking dt
            INNER JOIN donations d ON dt.donation_id = d.id
            WHERE dt.id = ? AND (
                d.donor_id = ? OR 
                d.assigned_ngo_id = ? OR 
                dt.volunteer_id = ?
            )
        ");
        $stmt->bind_param("iiii", $tracking_id, $user_id, $user_id, $user_id);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }
    
    // Verify user has access to donation
    private function verifyDonationAccess($donation_id, $user_id) {
        $conn = getDBConnection();
        $stmt = $conn->prepare("
            SELECT id FROM donations 
            WHERE id = ? AND (
                donor_id = ? OR 
                assigned_ngo_id = ? OR 
                assigned_volunteer_id = ?
            )
        ");
        $stmt->bind_param("iiii", $donation_id, $user_id, $user_id, $user_id);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }
}

// Handle request if called directly
if (basename($_SERVER['PHP_SELF']) == 'TrackingController.php') {
    $controller = new TrackingController();
    $controller->handleRequest();
}
?>
