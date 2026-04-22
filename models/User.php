<?php
require_once 'Database.php';

class User {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    // Register User
    public function register($data) {
        $this->db->query('INSERT INTO users (full_name, email, password, user_type, phone, address, city, state, pincode) VALUES (:name, :email, :password, :type, :phone, :address, :city, :state, :pincode)');
        
        // Bind values
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':password', $data['password']);
        $this->db->bind(':type', $data['type']);
        $this->db->bind(':phone', $data['phone']);
        $this->db->bind(':address', $data['address']);
        $this->db->bind(':city', $data['city']);
        $this->db->bind(':state', $data['state']);
        $this->db->bind(':pincode', $data['pincode']);
        
        // Execute
        if($this->db->execute()) {
            return $this->db->lastInsertId();
        } else {
            return false;
        }
    }
    
    // Add Donor Details
    public function addDonorDetails($userId, $data) {
        $this->db->query('INSERT INTO donor_details (user_id, organization_name, organization_type) VALUES (:user_id, :org_name, :org_type)');
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':org_name', $data['org_name']);
        $this->db->bind(':org_type', $data['org_type']);
        return $this->db->execute();
    }
    
    // Add NGO Details
    public function addNgoDetails($userId, $data) {
        $this->db->query('INSERT INTO ngo_details (user_id, ngo_name, registration_number, ngo_type, website) VALUES (:user_id, :ngo_name, :reg_no, :ngo_type, :website)');
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':ngo_name', $data['ngo_name']);
        $this->db->bind(':reg_no', $data['reg_no']);
        $this->db->bind(':ngo_type', $data['ngo_type']);
        $this->db->bind(':website', $data['website']);
        return $this->db->execute();
    }
    
    // Add Volunteer Details
    public function addVolunteerDetails($userId, $data) {
        $this->db->query('INSERT INTO volunteer_details (user_id, vehicle_type, vehicle_number, availability) VALUES (:user_id, :vehicle_type, :vehicle_no, :availability)');
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':vehicle_type', $data['vehicle_type']);
        $this->db->bind(':vehicle_no', $data['vehicle_no']);
        $this->db->bind(':availability', $data['availability']);
        return $this->db->execute();
    }
    
    // Login User
    public function login($email, $password) {
        $this->db->query('SELECT * FROM users WHERE email = :email');
        $this->db->bind(':email', $email);
        
        $row = $this->db->single();
        
        // Check if user exists
        if($this->db->rowCount() > 0) {
            $hashed_password = $row->password;
            if(password_verify($password, $hashed_password)) {
                return $row;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    
    // Find user by email
    public function findUserByEmail($email) {
        $this->db->query('SELECT * FROM users WHERE email = :email');
        $this->db->bind(':email', $email);
        
        $row = $this->db->single();
        
        // Check row
        if($this->db->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }
    
    // Get User by ID with Details
    public function getUserById($id) {
        // First get the user to know the type
        $this->db->query('SELECT * FROM users WHERE id = :id');
        $this->db->bind(':id', $id);
        $user = $this->db->single();
        
        if(!$user) return false;
        
        // Now join with appropriate details table
        if($user->user_type == 'donor') {
            $this->db->query('SELECT u.*, d.organization_name, d.organization_type, d.license_number, d.total_donations, d.total_meals_provided, d.rating 
                              FROM users u 
                              LEFT JOIN donor_details d ON u.id = d.user_id 
                              WHERE u.id = :id');
        } elseif($user->user_type == 'ngo') {
            $this->db->query('SELECT u.*, n.ngo_name, n.registration_number, n.ngo_type, n.website, n.capacity, n.total_requests, n.total_received, n.rating, n.verified_by_admin 
                              FROM users u 
                              LEFT JOIN ngo_details n ON u.id = n.user_id 
                              WHERE u.id = :id');
        } elseif($user->user_type == 'volunteer') {
            $this->db->query('SELECT u.*, v.vehicle_type, v.vehicle_number, v.driving_license, v.availability, v.total_pickups, v.total_deliveries, v.rating, v.points, v.level 
                              FROM users u 
                              LEFT JOIN volunteer_details v ON u.id = v.user_id 
                              WHERE u.id = :id');
        } else {
            return $user;
        }
        
        $this->db->bind(':id', $id);
        return $this->db->single();
    }
    
    // Update User Profile
    public function updateUser($id, $data) {
        // Update basic user info
        $this->db->query('UPDATE users SET full_name = :name, email = :email, phone = :phone, address = :address, city = :city, state = :state, pincode = :pincode WHERE id = :id');
        $this->db->bind(':name', $data['full_name']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':phone', $data['phone']);
        $this->db->bind(':address', $data['address']);
        $this->db->bind(':city', $data['city']);
        $this->db->bind(':state', $data['state']);
        $this->db->bind(':pincode', $data['pincode']);
        $this->db->bind(':id', $id);
        
        if(!$this->db->execute()) return false;
        
        // Update role specific details
        $user = $this->getUserById($id);
        
        if($user->user_type == 'donor') {
            // Check if details exist
            $this->db->query('SELECT id FROM donor_details WHERE user_id = :id');
            $this->db->bind(':id', $id);
            if($this->db->single()) {
                $this->db->query('UPDATE donor_details SET organization_name = :org_name WHERE user_id = :id');
            } else {
                $this->db->query('INSERT INTO donor_details (user_id, organization_name) VALUES (:id, :org_name)');
            }
            $this->db->bind(':org_name', $data['organization_name'] ?? '');
            $this->db->bind(':id', $id);
        } elseif($user->user_type == 'ngo') {
            // Check if details exist
            $this->db->query('SELECT id FROM ngo_details WHERE user_id = :id');
            $this->db->bind(':id', $id);
            if($this->db->single()) {
                $this->db->query('UPDATE ngo_details SET ngo_name = :ngo_name, registration_number = :reg_no, ngo_type = :ngo_type, capacity = :capacity, website = :website WHERE user_id = :id');
            } else {
                $this->db->query('INSERT INTO ngo_details (user_id, ngo_name, registration_number, ngo_type, capacity, website) VALUES (:id, :ngo_name, :reg_no, :ngo_type, :capacity, :website)');
            }
            $this->db->bind(':ngo_name', $data['ngo_name'] ?? '');
            $this->db->bind(':reg_no', $data['registration_number'] ?? '');
            $this->db->bind(':ngo_type', $data['ngo_type'] ?? 'other');
            $this->db->bind(':capacity', $data['capacity'] ?? 0);
            $this->db->bind(':website', $data['website'] ?? '');
            $this->db->bind(':id', $id);
        } elseif($user->user_type == 'volunteer') {
            // Check if details exist
            $this->db->query('SELECT id FROM volunteer_details WHERE user_id = :id');
            $this->db->bind(':id', $id);
            if($this->db->single()) {
                $this->db->query('UPDATE volunteer_details SET vehicle_type = :vehicle_type, vehicle_number = :vehicle_no, driving_license = :license, availability = :availability WHERE user_id = :id');
            } else {
                $this->db->query('INSERT INTO volunteer_details (user_id, vehicle_type, vehicle_number, driving_license, availability) VALUES (:id, :vehicle_type, :vehicle_no, :license, :availability)');
            }
            $this->db->bind(':vehicle_type', $data['vehicle_type'] ?? 'none');
            $this->db->bind(':vehicle_no', $data['vehicle_number'] ?? '');
            $this->db->bind(':license', $data['driving_license'] ?? '');
            $this->db->bind(':availability', $data['availability'] ?? 'flexible');
            $this->db->bind(':id', $id);
        }
        
        return $this->db->execute();
    }
    
    // Verify Password
    public function verifyPassword($id, $password) {
        $this->db->query('SELECT password FROM users WHERE id = :id');
        $this->db->bind(':id', $id);
        $row = $this->db->single();
        
        if($row) {
            return password_verify($password, $row->password);
        }
        return false;
    }
    
    // Update Password
    public function updatePassword($id, $newPassword) {
        $hashed_password = password_hash($newPassword, PASSWORD_DEFAULT);
        $this->db->query('UPDATE users SET password = :password WHERE id = :id');
        $this->db->bind(':password', $hashed_password);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}
