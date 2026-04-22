<?php
require_once __DIR__ . '/../config/database.php';

class Tracking {
    private $conn;
    
    public function __construct() {
        $this->conn = getDBConnection();
    }
    
    // Start a new tracking session
    public function startTracking($pickup_id, $donation_id, $volunteer_id) {
        try {
            // Check if tracking already exists and is active
            $stmt = $this->conn->prepare("
                SELECT id FROM delivery_tracking 
                WHERE pickup_id = ? AND status = 'active'
            ");
            $stmt->bind_param("i", $pickup_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                return $row['id']; // Return existing tracking ID
            }
            
            // Create new tracking session
            $stmt = $this->conn->prepare("
                INSERT INTO delivery_tracking (pickup_id, donation_id, volunteer_id, status) 
                VALUES (?, ?, ?, 'active')
            ");
            $stmt->bind_param("iii", $pickup_id, $donation_id, $volunteer_id);
            $stmt->execute();
            
            return $this->conn->insert_id;
        } catch (Exception $e) {
            error_log("Error starting tracking: " . $e->getMessage());
            return false;
        }
    }
    
    // Update location
    public function updateLocation($tracking_id, $latitude, $longitude, $accuracy = null, $speed = null, $heading = null) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO location_updates (tracking_id, latitude, longitude, accuracy, speed, heading) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("iddddd", $tracking_id, $latitude, $longitude, $accuracy, $speed, $heading);
            $stmt->execute();
            
            // Update tracking session timestamp
            $updateStmt = $this->conn->prepare("
                UPDATE delivery_tracking 
                SET updated_at = NOW() 
                WHERE id = ?
            ");
            $updateStmt->bind_param("i", $tracking_id);
            $updateStmt->execute();
            
            return $this->conn->insert_id;
        } catch (Exception $e) {
            error_log("Error updating location: " . $e->getMessage());
            return false;
        }
    }
    
    // Get current (latest) location
    public function getCurrentLocation($tracking_id) {
        $stmt = $this->conn->prepare("
            SELECT 
                latitude,
                longitude,
                accuracy,
                speed,
                heading,
                created_at
            FROM location_updates
            WHERE tracking_id = ?
            ORDER BY created_at DESC
            LIMIT 1
        ");
        $stmt->bind_param("i", $tracking_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return null;
    }
    
    // Get location history (route path)
    public function getLocationHistory($tracking_id, $limit = 100) {
        $stmt = $this->conn->prepare("
            SELECT 
                latitude,
                longitude,
                accuracy,
                speed,
                heading,
                created_at
            FROM location_updates
            WHERE tracking_id = ?
            ORDER BY created_at ASC
            LIMIT ?
        ");
        $stmt->bind_param("ii", $tracking_id, $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    // Stop tracking session
    public function stopTracking($tracking_id) {
        try {
            // Calculate total distance
            $distance = $this->calculateTotalDistance($tracking_id);
            
            $stmt = $this->conn->prepare("
                UPDATE delivery_tracking 
                SET status = 'completed', end_time = NOW(), total_distance = ? 
                WHERE id = ?
            ");
            $stmt->bind_param("di", $distance, $tracking_id);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error stopping tracking: " . $e->getMessage());
            return false;
        }
    }
    
    // Pause tracking
    public function pauseTracking($tracking_id) {
        $stmt = $this->conn->prepare("
            UPDATE delivery_tracking 
            SET status = 'paused' 
            WHERE id = ?
        ");
        $stmt->bind_param("i", $tracking_id);
        return $stmt->execute();
    }
    
    // Resume tracking
    public function resumeTracking($tracking_id) {
        $stmt = $this->conn->prepare("
            UPDATE delivery_tracking 
            SET status = 'active' 
            WHERE id = ?
        ");
        $stmt->bind_param("i", $tracking_id);
        return $stmt->execute();
    }
    
    // Get active tracking by donation ID
    public function getActiveTrackingByDonation($donation_id) {
        $stmt = $this->conn->prepare("
            SELECT 
                dt.*,
                u.full_name as volunteer_name,
                u.phone as volunteer_phone
            FROM delivery_tracking dt
            INNER JOIN users u ON dt.volunteer_id = u.id
            WHERE dt.donation_id = ? AND dt.status IN ('active', 'paused')
            ORDER BY dt.created_at DESC
            LIMIT 1
        ");
        $stmt->bind_param("i", $donation_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return null;
    }
    
    // Get active tracking by pickup ID
    public function getActiveTrackingByPickup($pickup_id) {
        $stmt = $this->conn->prepare("
            SELECT * FROM delivery_tracking 
            WHERE pickup_id = ? AND status IN ('active', 'paused')
            ORDER BY created_at DESC
            LIMIT 1
        ");
        $stmt->bind_param("i", $pickup_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return null;
    }
    
    // Get tracking by ID
    public function getTracking($tracking_id) {
        $stmt = $this->conn->prepare("
            SELECT 
                dt.*,
                d.food_name,
                d.pickup_address,
                donor.full_name as donor_name,
                donor.phone as donor_phone,
                ngo.full_name as ngo_name,
                ngo.address as ngo_address,
                volunteer.full_name as volunteer_name
            FROM delivery_tracking dt
            INNER JOIN donations d ON dt.donation_id = d.id
            LEFT JOIN users donor ON d.donor_id = donor.id
            LEFT JOIN users ngo ON d.assigned_ngo_id = ngo.id
            LEFT JOIN users volunteer ON dt.volunteer_id = volunteer.id
            WHERE dt.id = ?
        ");
        $stmt->bind_param("i", $tracking_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return null;
    }
    
    // Calculate total distance traveled (Haversine formula)
    private function calculateTotalDistance($tracking_id) {
        $locations = $this->getLocationHistory($tracking_id, 1000);
        
        if (count($locations) < 2) {
            return 0;
        }
        
        $totalDistance = 0;
        for ($i = 1; $i < count($locations); $i++) {
            $lat1 = deg2rad($locations[$i - 1]['latitude']);
            $lon1 = deg2rad($locations[$i - 1]['longitude']);
            $lat2 = deg2rad($locations[$i]['latitude']);
            $lon2 = deg2rad($locations[$i]['longitude']);
            
            $dlat = $lat2 - $lat1;
            $dlon = $lon2 - $lon1;
            
            $a = sin($dlat / 2) * sin($dlat / 2) +
                 cos($lat1) * cos($lat2) *
                 sin($dlon / 2) * sin($dlon / 2);
            $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
            $distance = 6371 * $c; // Earth radius in km
            
            $totalDistance += $distance;
        }
        
        return round($totalDistance, 2);
    }
    
    // Get tracking statistics for volunteer
    public function getVolunteerTrackingStats($volunteer_id) {
        $stmt = $this->conn->prepare("
            SELECT 
                COUNT(*) as total_deliveries,
                SUM(total_distance) as total_distance_km,
                AVG(total_distance) as avg_distance_km,
                AVG(TIMESTAMPDIFF(MINUTE, start_time, end_time)) as avg_duration_minutes
            FROM delivery_tracking
            WHERE volunteer_id = ? AND status = 'completed'
        ");
        $stmt->bind_param("i", $volunteer_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}
?>
