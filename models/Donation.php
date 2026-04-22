<?php
require_once 'Database.php';
require_once __DIR__ . '/../helpers/LocationHelper.php';

class Donation {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    // Create new donation
    public function addDonation($data) {
        $this->db->query('INSERT INTO donations (donor_id, food_type, food_name, quantity, unit, people_served, expiry_time, description, pickup_address, pickup_city, pickup_latitude, pickup_longitude, status, images) VALUES (:donor_id, :food_type, :food_name, :quantity, :unit, :people_served, :expiry_time, :description, :pickup_address, :pickup_city, :pickup_latitude, :pickup_longitude, :status, :images)');
        
        $this->db->bind(':donor_id', $data['donor_id']);
        $this->db->bind(':food_type', $data['food_type']);
        $this->db->bind(':food_name', $data['food_name']);
        $this->db->bind(':quantity', $data['quantity']);
        $this->db->bind(':unit', $data['unit']);
        $this->db->bind(':people_served', $data['people_served'] ?: null); // Handle empty string
        $this->db->bind(':expiry_time', $data['expiry_time']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':pickup_address', $data['pickup_address']);
        $this->db->bind(':pickup_city', $data['pickup_city']);
        $this->db->bind(':pickup_latitude', $data['pickup_latitude'] ?? null);
        $this->db->bind(':pickup_longitude', $data['pickup_longitude'] ?? null);
        $this->db->bind(':status', 'available');
        $this->db->bind(':images', $data['food_image']); // Storing single image path for now, schema supports JSON but simple string is fine for v1
        
        if($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }
    
    // Get single donation by id
    public function getDonationById($id) {
        $this->db->query('SELECT * FROM donations WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }
    
    // Update existing donation
    public function updateDonation($data) {
        $this->db->query('UPDATE donations SET food_type = :food_type, food_name = :food_name, quantity = :quantity, unit = :unit, people_served = :people_served, expiry_time = :expiry_time, description = :description, pickup_address = :pickup_address, pickup_city = :pickup_city, pickup_latitude = :pickup_latitude, pickup_longitude = :pickup_longitude, images = :images WHERE id = :id AND donor_id = :donor_id');
        
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':donor_id', $data['donor_id']);
        $this->db->bind(':food_type', $data['food_type']);
        $this->db->bind(':food_name', $data['food_name']);
        $this->db->bind(':quantity', $data['quantity']);
        $this->db->bind(':unit', $data['unit']);
        $this->db->bind(':people_served', $data['people_served'] ?: null);
        $this->db->bind(':expiry_time', $data['expiry_time']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':pickup_address', $data['pickup_address']);
        $this->db->bind(':pickup_city', $data['pickup_city']);
        $this->db->bind(':pickup_latitude', $data['pickup_latitude'] ?? null);
        $this->db->bind(':pickup_longitude', $data['pickup_longitude'] ?? null);
        $this->db->bind(':images', $data['food_image']);
        
        return $this->db->execute();
    }
    
    // Get donations by donor ID
    public function getDonationsByDonor($donor_id) {
        $this->db->query('SELECT * FROM donations WHERE donor_id = :donor_id ORDER BY created_at DESC');
        $this->db->bind(':donor_id', $donor_id);
        return $this->db->resultSet();
    }
    
    // Get recent donations by donor ID (limit 5)
    public function getRecentDonationsByDonor($donor_id) {
        $this->db->query('SELECT * FROM donations WHERE donor_id = :donor_id ORDER BY created_at DESC LIMIT 5');
        $this->db->bind(':donor_id', $donor_id);
        return $this->db->resultSet();
    }
    
    // Get donation stats for donor
    public function getDonorStats($donor_id) {
        $stats = [
            'total_donations' => 0,
            'meals_provided' => 0,
            'ngos_helped' => 0,
            'impact_score' => 0
        ];
        
        // Total donations
        $this->db->query('SELECT COUNT(*) as count FROM donations WHERE donor_id = :donor_id');
        $this->db->bind(':donor_id', $donor_id);
        $row = $this->db->single();
        $stats['total_donations'] = $row->count;
        
        // Meals provided (sum of people_served)
        $this->db->query('SELECT SUM(people_served) as total FROM donations WHERE donor_id = :donor_id');
        $this->db->bind(':donor_id', $donor_id);
        $row = $this->db->single();
        $stats['meals_provided'] = $row->total ?? 0;
        
        // NGOs helped (distinct assigned_ngo_id)
        $this->db->query('SELECT COUNT(DISTINCT assigned_ngo_id) as count FROM donations WHERE donor_id = :donor_id AND assigned_ngo_id IS NOT NULL');
        $this->db->bind(':donor_id', $donor_id);
        $row = $this->db->single();
        $stats['ngos_helped'] = $row->count;
        
        // Calculate impact score (arbitrary logic for now)
        $stats['impact_score'] = min(100, ($stats['meals_provided'] / 10) + ($stats['total_donations'] * 2));
        
        return $stats;
    }
    
    // Get active requests (donations with status 'requested')
    public function getActiveRequests($donor_id) {
        // In a real scenario, this might join with a requests table
        // For now, we'll just check donations that have been requested but not yet delivered
        $this->db->query('SELECT d.*, u.full_name as ngo_name, nd.ngo_type 
                          FROM donations d 
                          LEFT JOIN users u ON d.assigned_ngo_id = u.id 
                          LEFT JOIN ngo_details nd ON u.id = nd.user_id
                          WHERE d.donor_id = :donor_id 
                          AND d.status IN ("requested", "confirmed", "picked_up")');
        $this->db->bind(':donor_id', $donor_id);
        return $this->db->resultSet();
    }

    // Get all available donations (for NGO to request)
    public function getAvailableDonations($userLat = null, $userLon = null) {
        $this->db->query('SELECT d.*, u.full_name as donor_name, dd.organization_name, dd.organization_type 
                          FROM donations d 
                          JOIN users u ON d.donor_id = u.id 
                          LEFT JOIN donor_details dd ON u.id = dd.user_id
                          WHERE d.status = "available" AND d.expiry_time > NOW() 
                          ORDER BY d.created_at DESC');
        
        $donations = $this->db->resultSet();
        
        // Calculate distance for each donation if user coordinates provided
        if ($userLat && $userLon) {
            foreach($donations as &$donation) {
                if ($donation->pickup_latitude && $donation->pickup_longitude) {
                    $donation->distance = LocationHelper::haversineDistance(
                        $userLat, $userLon,
                        $donation->pickup_latitude, $donation->pickup_longitude
                    );
                    $donation->distance = round($donation->distance, 1); // Round to 1 decimal
                } else {
                    $donation->distance = null; // Location not available for this donation
                }
            }
            
            // Sort by distance (nearest first, but keep items without distance at the end)
            usort($donations, function($a, $b) {
                if ($a->distance === null) return 1;
                if ($b->distance === null) return -1;
                return $a->distance <=> $b->distance;
            });
        } else {
            // If no user coordinates, set distance to null for all
            foreach($donations as &$donation) {
                $donation->distance = null;
            }
        }
        
        return $donations;
    }
    
    // Request a donation
    public function requestDonation($donation_id, $ngo_id) {
        // Start transaction
        $this->db->query('START TRANSACTION');
        $this->db->execute();
        
        try {
            // Update donation status
            $this->db->query('UPDATE donations SET status = "requested", assigned_ngo_id = :ngo_id WHERE id = :id AND status = "available"');
            $this->db->bind(':ngo_id', $ngo_id);
            $this->db->bind(':id', $donation_id);
            
            if(!$this->db->execute() || $this->db->rowCount() == 0) {
                throw new Exception("Failed to update donation");
            }
            
            // Add to requests table (optional if we just use donation status, but good for tracking)
            $this->db->query('INSERT INTO requests (ngo_id, donation_id, status) VALUES (:ngo_id, :donation_id, "pending")');
            $this->db->bind(':ngo_id', $ngo_id);
            $this->db->bind(':donation_id', $donation_id);
            $this->db->execute();
            
            // Commit
            $this->db->query('COMMIT');
            $this->db->execute();
            return true;
            
        } catch(Exception $e) {
            $this->db->query('ROLLBACK');
            $this->db->execute();
            return false;
        }
    }
    
    // Get NGO Stats
    public function getNgoStats($ngo_id) {
        $stats = [
            'active_requests' => 0,
            'received_donations' => 0,
            'people_fed' => 0
        ];
        
        // Active requests
        $this->db->query('SELECT COUNT(*) as count FROM donations WHERE assigned_ngo_id = :ngo_id AND status IN ("requested", "confirmed", "picked_up")');
        $this->db->bind(':ngo_id', $ngo_id);
        $row = $this->db->single();
        $stats['active_requests'] = $row->count;
        
        // Received donations
        $this->db->query('SELECT COUNT(*) as count FROM donations WHERE assigned_ngo_id = :ngo_id AND status = "delivered"');
        $this->db->bind(':ngo_id', $ngo_id);
        $row = $this->db->single();
        $stats['received_donations'] = $row->count;
        
        // People fed (sum from donations)
        $this->db->query('SELECT SUM(people_served) as total FROM donations WHERE assigned_ngo_id = :ngo_id AND status = "delivered"');
        $this->db->bind(':ngo_id', $ngo_id);
        $row = $this->db->single();
        $stats['people_fed'] = $row->total ?? 0;
        
        return $stats;
    }
    
    // Get NGO Requests
    public function getNgoRequests($ngo_id) {
        $this->db->query('SELECT r.*, d.food_name, d.quantity, d.unit, d.status as donation_status, d.donor_id, 
                          u.full_name as donor_name, dd.organization_name 
                          FROM requests r 
                          JOIN donations d ON r.donation_id = d.id 
                          JOIN users u ON d.donor_id = u.id 
                          LEFT JOIN donor_details dd ON u.id = dd.user_id
                          WHERE r.ngo_id = :ngo_id 
                          ORDER BY r.requested_at DESC');
        $this->db->bind(':ngo_id', $ngo_id);
        return $this->db->resultSet();
    }
    
    // Delete a donation
    public function deleteDonation($donation_id, $donor_id) {
        // Allow deleting any donation belonging to the donor
        $this->db->query('DELETE FROM donations WHERE id = :id AND donor_id = :donor_id');
        $this->db->bind(':id', $donation_id);
        $this->db->bind(':donor_id', $donor_id);
        
        if($this->db->execute()) {
            // If it was a confirmed/requested donation, we might want to notify the NGO/Volunteer
            // But for now, simple deletion is requested to fix stats
            return $this->db->rowCount() > 0; 
        }
        return false;
    }
    
    // Update donation status (for accepting/rejecting requests)
    public function updateDonationStatus($donation_id, $status, $donor_id = null) {
        if ($donor_id) {
            // If donor_id is provided, ensure only the owner can update
            $this->db->query('UPDATE donations SET status = :status WHERE id = :id AND donor_id = :donor_id');
            $this->db->bind(':donor_id', $donor_id);
        } else {
            $this->db->query('UPDATE donations SET status = :status WHERE id = :id');
        }
        
        $this->db->bind(':status', $status);
        $this->db->bind(':id', $donation_id);
        
        return $this->db->execute();
    }
    
    // Reject an NGO request (reset to available)
    public function rejectRequest($donation_id, $donor_id) {
        $this->db->query('UPDATE donations SET status = "available", assigned_ngo_id = NULL WHERE id = :id AND donor_id = :donor_id AND status = "requested"');
        $this->db->bind(':id', $donation_id);
        $this->db->bind(':donor_id', $donor_id);
        
        return $this->db->execute();
    }
    // Check if NGO already requested this donation
    public function checkExistingRequest($donation_id, $ngo_id) {
        $this->db->query('SELECT COUNT(*) as count FROM requests WHERE donation_id = :donation_id AND ngo_id = :ngo_id');
        $this->db->bind(':donation_id', $donation_id);
        $this->db->bind(':ngo_id', $ngo_id);
        $row = $this->db->single();
        
        if($row->count > 0) {
            return true;
        }
        
        // Also check if they are the assigned NGO in donations table (double check)
        $this->db->query('SELECT COUNT(*) as count FROM donations WHERE id = :id AND assigned_ngo_id = :ngo_id');
        $this->db->bind(':id', $donation_id);
        $this->db->bind(':ngo_id', $ngo_id);
        $row2 = $this->db->single();
        
        return $row2->count > 0;
    }

    // Cancel a donation (by donor)
    public function cancelDonation($donation_id, $donor_id, $reason = '') {
        // Only allow cancellation if status is 'confirmed' or 'requested'
        // If 'available', they should use delete instead
        $this->db->query('UPDATE donations SET status = "cancelled", cancellation_reason = :reason WHERE id = :id AND donor_id = :donor_id AND status IN ("confirmed", "requested")');
        $this->db->bind(':reason', $reason);
        $this->db->bind(':id', $donation_id);
        $this->db->bind(':donor_id', $donor_id);
        
        if($this->db->execute()) {
            // Also update any associated requests to cancelled
            $this->db->query('UPDATE requests SET status = "cancelled" WHERE donation_id = :donation_id');
            $this->db->bind(':donation_id', $donation_id);
            $this->db->execute();
            return true;
        }
        return false;
    }

    // Cancel a request (by NGO)
    public function cancelRequest($donation_id, $ngo_id) {
        // Start transaction
        $this->db->query('START TRANSACTION');
        $this->db->execute();
        
        try {
            // 1. Update request status
            $this->db->query('UPDATE requests SET status = "cancelled" WHERE donation_id = :donation_id AND ngo_id = :ngo_id');
            $this->db->bind(':donation_id', $donation_id);
            $this->db->bind(':ngo_id', $ngo_id);
            $this->db->execute();
            
            // 2. Reset donation status to available if it was assigned to this NGO
            $this->db->query('UPDATE donations SET status = "available", assigned_ngo_id = NULL WHERE id = :id AND assigned_ngo_id = :ngo_id');
            $this->db->bind(':id', $donation_id);
            $this->db->bind(':ngo_id', $ngo_id);
            $this->db->execute();
            
            $this->db->query('COMMIT');
            $this->db->execute();
            return true;
        } catch(Exception $e) {
            $this->db->query('ROLLBACK');
            $this->db->execute();
            return false;
        }
    }
}
