<?php
require_once 'Database.php';

class Pickup {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    // Get available pickups (donations that are 'requested' or 'confirmed' but not yet assigned to volunteer)
    // Or maybe 'confirmed' status means NGO accepted it, now needs pickup?
    // Let's assume 'confirmed' means ready for pickup assignment
    public function getAvailablePickups() {
        $this->db->query('SELECT d.*, u.full_name as donor_name, dd.organization_name, 
                          u.phone as donor_phone,
                          ngo.full_name as ngo_name, ngo_d.ngo_name as ngo_org_name,
                          ngo.phone as ngo_phone, ngo.address as ngo_address, ngo.city as ngo_city
                          FROM donations d 
                          JOIN users u ON d.donor_id = u.id 
                          LEFT JOIN donor_details dd ON u.id = dd.user_id
                          JOIN users ngo ON d.assigned_ngo_id = ngo.id
                          LEFT JOIN ngo_details ngo_d ON ngo.id = ngo_d.user_id
                          WHERE d.status = "confirmed" 
                          AND d.assigned_volunteer_id IS NULL
                          ORDER BY d.updated_at DESC');
        return $this->db->resultSet();
    }
    
    // Assign pickup to volunteer
    public function assignPickup($donation_id, $volunteer_id) {
        $this->db->query('UPDATE donations SET assigned_volunteer_id = :vol_id, status = "picked_up" WHERE id = :id');
        // Note: status flow might be: confirmed -> assigned -> picked_up -> delivered
        // For simplicity, let's say when volunteer accepts, it becomes 'assigned'
        // But wait, schema has a 'pickups' table too.
        // Let's use the pickups table as designed in schema.
        
        try {
            $this->db->query('START TRANSACTION');
            $this->db->execute();
            
            // 1. Create entry in pickups table
            $this->db->query('INSERT INTO pickups (donation_id, volunteer_id, pickup_status) VALUES (:donation_id, :volunteer_id, "assigned")');
            $this->db->bind(':donation_id', $donation_id);
            $this->db->bind(':volunteer_id', $volunteer_id);
            $this->db->execute();
            
            // 2. Update donations table
            $this->db->query('UPDATE donations SET assigned_volunteer_id = :vol_id WHERE id = :id');
            $this->db->bind(':vol_id', $volunteer_id);
            $this->db->bind(':id', $donation_id);
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
    
    // Get tasks for volunteer
    public function getVolunteerTasks($volunteer_id) {
        $this->db->query('SELECT p.*, d.food_name, d.quantity, d.unit, d.pickup_address, d.pickup_city,
                          u.full_name as donor_name, u.phone as donor_phone,
                          ngo.full_name as ngo_name, ngo.phone as ngo_phone, ngo.address as ngo_address
                          FROM pickups p
                          JOIN donations d ON p.donation_id = d.id
                          JOIN users u ON d.donor_id = u.id
                          JOIN users ngo ON d.assigned_ngo_id = ngo.id
                          WHERE p.volunteer_id = :vol_id
                          ORDER BY p.created_at DESC');
        $this->db->bind(':vol_id', $volunteer_id);
        return $this->db->resultSet();
    }
    
    // Update pickup status
    public function updateStatus($pickup_id, $status) {
        try {
            $this->db->query('START TRANSACTION');
            $this->db->execute();
            
            // Update pickups table
            $this->db->query('UPDATE pickups SET pickup_status = :status WHERE id = :id');
            $this->db->bind(':status', $status);
            $this->db->bind(':id', $pickup_id);
            $this->db->execute();
            
            // If delivered, update donation status too
            if($status == 'delivered') {
                // Get donation id
                $this->db->query('SELECT donation_id FROM pickups WHERE id = :id');
                $this->db->bind(':id', $pickup_id);
                $row = $this->db->single();
                
                if($row) {
                    $this->db->query('UPDATE donations SET status = "delivered" WHERE id = :d_id');
                    $this->db->bind(':d_id', $row->donation_id);
                    $this->db->execute();
                }
            } elseif($status == 'picked_up') {
                 // Get donation id
                 $this->db->query('SELECT donation_id FROM pickups WHERE id = :id');
                 $this->db->bind(':id', $pickup_id);
                 $row = $this->db->single();
                 
                 if($row) {
                     $this->db->query('UPDATE donations SET status = "picked_up" WHERE id = :d_id');
                     $this->db->bind(':d_id', $row->donation_id);
                     $this->db->execute();
                 }
            }
            
            $this->db->query('COMMIT');
            $this->db->execute();
            return true;
        } catch(Exception $e) {
            $this->db->query('ROLLBACK');
            $this->db->execute();
            return false;
        }
    }
    
    // Get Volunteer Stats
    public function getStats($volunteer_id) {
        $stats = [
            'pending_tasks' => 0,
            'completed_deliveries' => 0,
            'total_pickups' => 0,
            'total_distance' => 0 // Placeholder
        ];
        
        // Pending
        $this->db->query('SELECT COUNT(*) as count FROM pickups WHERE volunteer_id = :vol_id AND pickup_status NOT IN ("delivered", "failed", "cancelled")');
        $this->db->bind(':vol_id', $volunteer_id);
        $row = $this->db->single();
        $stats['pending_tasks'] = $row->count;
        
        // Completed
        $this->db->query('SELECT COUNT(*) as count FROM pickups WHERE volunteer_id = :vol_id AND pickup_status = "delivered"');
        $this->db->bind(':vol_id', $volunteer_id);
        $row = $this->db->single();
        $stats['completed_deliveries'] = $row->count;
        
        // Total pickups (all statuses)
        $this->db->query('SELECT COUNT(*) as count FROM pickups WHERE volunteer_id = :vol_id');
        $this->db->bind(':vol_id', $volunteer_id);
        $row = $this->db->single();
        $stats['total_pickups'] = $row->count;
        
        return $stats;
    }
}
