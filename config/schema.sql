-- FoodShare Database Schema
-- Run this file to set up the complete database structure

-- Create database (if not exists)
CREATE DATABASE IF NOT EXISTS foodshare;
USE foodshare;

-- ============================================
-- Users Table (All user types)
-- ============================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_type ENUM('donor', 'ngo', 'volunteer', 'admin') NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    pincode VARCHAR(10),
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    profile_image VARCHAR(255),
    is_verified BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_type (user_type),
    INDEX idx_email (email),
    INDEX idx_city (city)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Donor Details Table
-- ============================================
CREATE TABLE IF NOT EXISTS donor_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    organization_name VARCHAR(255),
    organization_type ENUM('individual', 'restaurant', 'hotel', 'catering', 'grocery', 'other') DEFAULT 'individual',
    license_number VARCHAR(100),
    total_donations INT DEFAULT 0,
    total_meals_provided INT DEFAULT 0,
    rating DECIMAL(3, 2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- NGO Details Table
-- ============================================
CREATE TABLE IF NOT EXISTS ngo_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    ngo_name VARCHAR(255) NOT NULL,
    registration_number VARCHAR(100) UNIQUE NOT NULL,
    ngo_type ENUM('orphanage', 'old_age_home', 'street_feeding', 'disaster_relief', 'other') NOT NULL,
    website VARCHAR(255),
    capacity INT DEFAULT 0,
    total_requests INT DEFAULT 0,
    total_received INT DEFAULT 0,
    rating DECIMAL(3, 2) DEFAULT 0.00,
    verified_by_admin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Volunteer Details Table
-- ============================================
CREATE TABLE IF NOT EXISTS volunteer_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    vehicle_type ENUM('bike', 'car', 'van', 'none') DEFAULT 'none',
    vehicle_number VARCHAR(50),
    driving_license VARCHAR(100),
    availability ENUM('full_time', 'part_time', 'weekends', 'flexible') DEFAULT 'flexible',
    total_pickups INT DEFAULT 0,
    total_deliveries INT DEFAULT 0,
    rating DECIMAL(3, 2) DEFAULT 0.00,
    points INT DEFAULT 0,
    level INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Donations Table
-- ============================================
CREATE TABLE IF NOT EXISTS donations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    donor_id INT NOT NULL,
    food_type ENUM('cooked', 'raw', 'packaged', 'dairy', 'fruits_vegetables', 'other') NOT NULL,
    food_name VARCHAR(255) NOT NULL,
    quantity VARCHAR(100) NOT NULL,
    unit ENUM('kg', 'liters', 'units', 'plates', 'boxes') NOT NULL,
    expiry_time DATETIME NOT NULL,
    description TEXT,
    images TEXT, -- JSON array of image paths
    pickup_address TEXT NOT NULL,
    pickup_city VARCHAR(100),
    pickup_latitude DECIMAL(10, 8),
    pickup_longitude DECIMAL(11, 8),
    status ENUM('available', 'requested', 'confirmed', 'picked_up', 'delivered', 'cancelled', 'expired') DEFAULT 'available',
    assigned_ngo_id INT,
    assigned_volunteer_id INT,
    people_served INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (donor_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_ngo_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_volunteer_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_donor_id (donor_id),
    INDEX idx_status (status),
    INDEX idx_food_type (food_type),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Requests Table (NGOs requesting donations)
-- ============================================
CREATE TABLE IF NOT EXISTS requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ngo_id INT NOT NULL,
    donation_id INT NOT NULL,
    message TEXT,
    status ENUM('pending', 'accepted', 'rejected', 'completed', 'cancelled') DEFAULT 'pending',
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    responded_at TIMESTAMP NULL,
    FOREIGN KEY (ngo_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (donation_id) REFERENCES donations(id) ON DELETE CASCADE,
    INDEX idx_ngo_id (ngo_id),
    INDEX idx_donation_id (donation_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Pickups Table (Volunteer assignments)
-- ============================================
CREATE TABLE IF NOT EXISTS pickups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    donation_id INT NOT NULL,
    volunteer_id INT NOT NULL,
    pickup_time DATETIME,
    delivery_time DATETIME,
    pickup_status ENUM('assigned', 'on_the_way_pickup', 'picked_up', 'on_the_way_delivery', 'delivered', 'failed') DEFAULT 'assigned',
    pickup_latitude DECIMAL(10, 8),
    pickup_longitude DECIMAL(11, 8),
    delivery_latitude DECIMAL(10, 8),
    delivery_longitude DECIMAL(11, 8),
    notes TEXT,
    proof_image VARCHAR(255), -- Delivery proof
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (donation_id) REFERENCES donations(id) ON DELETE CASCADE,
    FOREIGN KEY (volunteer_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_donation_id (donation_id),
    INDEX idx_volunteer_id (volunteer_id),
    INDEX idx_status (pickup_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Notifications Table
-- ============================================
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('donation', 'request', 'pickup', 'delivery', 'system') NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    link VARCHAR(255),
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Ratings & Reviews Table
-- ============================================
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    donation_id INT NOT NULL,
    reviewer_id INT NOT NULL,
    reviewee_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    review_text TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (donation_id) REFERENCES donations(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewee_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_donation_id (donation_id),
    INDEX idx_reviewee_id (reviewee_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Activity Log Table
-- ============================================
CREATE TABLE IF NOT EXISTS activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(255) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Insert Sample Data for Testing
-- ============================================

-- Sample Donor User
INSERT INTO users (user_type, email, password, full_name, phone, address, city, state, pincode) VALUES
('donor', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Smith', '9876543210', '123 Main Street', 'Mumbai', 'Maharashtra', '400001');

SET @donor_id = LAST_INSERT_ID();

INSERT INTO donor_details (user_id, organization_name, organization_type, total_donations, total_meals_provided, rating) VALUES
(@donor_id, 'Smith Restaurant', 'restaurant', 24, 320, 4.8);

-- Sample NGO User
INSERT INTO users (user_type, email, password, full_name, phone, address, city, state, pincode) VALUES
('ngo', 'hope@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Hope Foundation', '9876543211', '456 NGO Road', 'Mumbai', 'Maharashtra', '400002');

SET @ngo_id = LAST_INSERT_ID();

INSERT INTO ngo_details (user_id, ngo_name, registration_number, ngo_type, capacity, verified_by_admin, rating) VALUES
(@ngo_id, 'Hope Foundation', 'NGO2024001', 'street_feeding', 100, TRUE, 4.7);

-- Sample Volunteer User
INSERT INTO users (user_type, email, password, full_name, phone, address, city, state, pincode) VALUES
('volunteer', 'david@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'David Wilson', '9876543212', '789 Volunteer Street', 'Mumbai', 'Maharashtra', '400003');

SET @volunteer_id = LAST_INSERT_ID();

INSERT INTO volunteer_details (user_id, vehicle_type, vehicle_number, availability, total_pickups, total_deliveries, rating, points, level) VALUES
(@volunteer_id, 'bike', 'MH01AB1234', 'flexible', 45, 42, 4.9, 1250, 3);

-- Sample Donations
INSERT INTO donations (donor_id, food_type, food_name, quantity, unit, expiry_time, description, pickup_address, pickup_city, status, assigned_ngo_id, people_served) VALUES
(@donor_id, 'cooked', 'Fresh Vegetables', '50', 'kg', DATE_ADD(NOW(), INTERVAL 2 DAY), 'Fresh seasonal vegetables', '123 Main Street, Mumbai', 'Mumbai', 'delivered', @ngo_id, 75),
(@donor_id, 'packaged', 'Packaged Meals', '100', 'units', DATE_ADD(NOW(), INTERVAL 1 DAY), 'Ready to eat packaged meals', '123 Main Street, Mumbai', 'Mumbai', 'delivered', @ngo_id, 100),
(@donor_id, 'dairy', 'Dairy Products', '30', 'liters', DATE_ADD(NOW(), INTERVAL 12 HOUR), 'Fresh milk and dairy', '123 Main Street, Mumbai', 'Mumbai', 'requested', @ngo_id, 0),
(@donor_id, 'packaged', 'Canned Food', '200', 'units', DATE_ADD(NOW(), INTERVAL 7 DAY), 'Various canned food items', '123 Main Street, Mumbai', 'Mumbai', 'available', NULL, 0);

-- Sample Notifications
INSERT INTO notifications (user_id, type, title, message, is_read) VALUES
(@donor_id, 'request', 'New Request', 'Hope Foundation requested your Fresh Vegetables donation', FALSE),
(@donor_id, 'delivery', 'Delivery Completed', 'Your Packaged Meals donation has been delivered', TRUE),
(@donor_id, 'system', 'Weekly Report', 'You fed 175 people this week!', FALSE);

COMMIT;

-- Display success message
SELECT 'Database schema created successfully!' AS Status;
