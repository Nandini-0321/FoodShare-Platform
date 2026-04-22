<?php
require_once 'config/database.php';

$conn = getDBConnection();

echo "<h1>FoodShare Feature Setup & Demo Data</h1>";

// 1. Run Schema Migration
echo "<h2>1. Setting up Database Tables...</h2>";
$sqlFile = __DIR__ . '/config/schema_chat_tracking.sql';
if (file_exists($sqlFile)) {
    $sql = file_get_contents($sqlFile);
    $queries = explode(';', $sql);
    foreach ($queries as $query) {
        $query = trim($query);
        if (!empty($query)) {
            try {
                $conn->query($query);
            } catch (Exception $e) {
                // Ignore "table exists"
            }
        }
    }
    echo "<p style='color:green'>Database tables check complete.</p>";
}

// 2. Create/Get Users
echo "<h2>2. Verifying Users...</h2>";

function getOrCreateUser($conn, $type, $name, $email) {
    $password = password_hash('password', PASSWORD_DEFAULT);
    
    $res = $conn->query("SELECT id FROM users WHERE email = '$email'");
    if ($res->num_rows > 0) {
        $id = $res->fetch_assoc()['id'];
        // Update password for existing user to ensure login works
        $conn->query("UPDATE users SET password = '$password' WHERE id = $id");
        return $id;
    }
    
    $conn->query("INSERT INTO users (user_type, email, password, full_name) VALUES ('$type', '$email', '$password', '$name')");
    $id = $conn->insert_id;
    
    // Create role-specific details
    if ($type == 'donor') {
        $conn->query("INSERT INTO donor_details (user_id, organization_name) VALUES ($id, '$name Organization')");
    } elseif ($type == 'ngo') {
        $conn->query("INSERT INTO ngo_details (user_id, ngo_name) VALUES ($id, '$name')");
    } elseif ($type == 'volunteer') {
        $conn->query("INSERT INTO volunteer_details (user_id, vehicle_type) VALUES ($id, 'bike')");
    }
    
    return $id;
}

$donor_id = getOrCreateUser($conn, 'donor', 'John Doe (Donor)', 'donor@demo.com');
$ngo_id = getOrCreateUser($conn, 'ngo', 'Helping Hands (NGO)', 'ngo@demo.com');
$volunteer_id = getOrCreateUser($conn, 'volunteer', 'Mike Smith (Volunteer)', 'volunteer@demo.com');

echo "<p style='color:green'>Users ready: Donor ($donor_id), NGO ($ngo_id), Volunteer ($volunteer_id)</p>";

// 3. Clear old demo data to keep it clean
$conn->query("DELETE FROM donations WHERE donor_id IN ($donor_id)");
$conn->query("DELETE FROM conversations WHERE id > 0");
$conn->query("DELETE FROM requests WHERE id > 0");
$conn->query("DELETE FROM pickups WHERE id > 0");
$conn->query("DELETE FROM delivery_tracking WHERE id > 0");

// 4. Create Comprehensive Demo Scenarios

// ========== DONOR VIEW SCENARIOS ==========

// Scenario 1: Available - Fresh Vegetables (NGO can request)
$conn->query("
    INSERT INTO donations (donor_id, food_type, food_name, quantity, unit, expiry_time, pickup_address, pickup_city, status, description, images, people_served) 
    VALUES ($donor_id, 'raw', 'Fresh Mixed Vegetables', '15', 'kg', DATE_ADD(NOW(), INTERVAL 6 HOUR), '45 Market Road, Andheri', 'Mumbai', 'available', 'Fresh carrots, beans, tomatoes, and leafy greens from farm surplus. Perfect for community kitchens.', 'https://images.unsplash.com/photo-1540189549336-e6e99c3679fe?w=400&q=80', 30)
");

// Scenario 2: Available - Packaged Snacks
$conn->query("
    INSERT INTO donations (donor_id, food_type, food_name, quantity, unit, expiry_time, pickup_address, pickup_city, status, description, images, people_served) 
    VALUES ($donor_id, 'packaged', 'Biscuit & Snack Packets', '200', 'packets', DATE_ADD(NOW(), INTERVAL 15 DAY), '12 Corporate Plaza, BKC', 'Mumbai', 'available', 'Assorted biscuits and healthy snacks. Sealed packets, suitable for distribution.', 'https://images.unsplash.com/photo-1558961363-fa8fdf82db35?w=400&q=80', 100)
");

// Scenario 3: Requested - Wedding Surplus (Donor needs to accept/reject)
$conn->query("
    INSERT INTO donations (donor_id, food_type, food_name, quantity, unit, expiry_time, pickup_address, pickup_city, status, assigned_ngo_id, description, images, people_served) 
    VALUES ($donor_id, 'cooked', 'Paneer Butter Masala & Naan', '40', 'plates', DATE_ADD(NOW(), INTERVAL 3 HOUR), '78 Wedding Hall, Juhu', 'Mumbai', 'requested', $ngo_id, 'Freshly prepared North Indian meal from wedding reception. Hot and hygienically packed.', 'https://images.unsplash.com/photo-1631452180519-c014fe946bc7?w=400&q=80', 40)
");
$requested_donation_id = $conn->insert_id;
$conn->query("INSERT INTO requests (donation_id, ngo_id, status) VALUES ($requested_donation_id, $ngo_id, 'pending')");

// Scenario 4: Confirmed - Ready for Chat & Pickup
$conn->query("
    INSERT INTO donations (donor_id, food_type, food_name, quantity, unit, expiry_time, pickup_address, pickup_city, status, assigned_ngo_id, description, images, people_served) 
    VALUES ($donor_id, 'bakery', 'Assorted Fresh Bread & Buns', '80', 'pieces', DATE_ADD(NOW(), INTERVAL 1 DAY), '23 Bakery Street, Bandra', 'Mumbai', 'confirmed', $ngo_id, 'Whole wheat bread, burger buns, and dinner rolls. Baked this morning.', 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=400&q=80', 50)
");
$chat_donation_id = $conn->insert_id;
$conn->query("INSERT INTO requests (donation_id, ngo_id, status) VALUES ($chat_donation_id, $ngo_id, 'confirmed')");
// Create Conversation with participants
$conn->query("INSERT INTO conversations (donation_id, title) VALUES ($chat_donation_id, 'Chat: Assorted Fresh Bread & Buns')");
$conv_id = $conn->insert_id;
$conn->query("INSERT INTO chat_participants (conversation_id, user_id, user_role) VALUES ($conv_id, $donor_id, 'donor')");
$conn->query("INSERT INTO chat_participants (conversation_id, user_id, user_role) VALUES ($conv_id, $ngo_id, 'ngo')");
$conn->query("INSERT INTO chat_messages (conversation_id, sender_id, sender_name, message, is_bot) VALUES ($conv_id, NULL, 'FoodShare Assistant', '👋 Welcome! This donation has been confirmed. Please coordinate pickup timing and location.', 1)");
$conn->query("INSERT INTO chat_messages (conversation_id, sender_id, sender_name, message) VALUES ($conv_id, NULL, 'Helping Hands (NGO)', 'Thank you! We can pick this up by 2 PM today. Is that convenient?')");

// Scenario 5: Picked Up - Live Tracking Active
$conn->query("
    INSERT INTO donations (donor_id, food_type, food_name, quantity, unit, expiry_time, pickup_address, pickup_city, status, assigned_ngo_id, assigned_volunteer_id, description, images, people_served) 
    VALUES ($donor_id, 'cooked', 'Chicken Biryani & Raita', '60', 'plates', DATE_ADD(NOW(), INTERVAL 4 HOUR), '56 Restaurant Avenue, Powai', 'Mumbai', 'picked_up', $ngo_id, $volunteer_id, 'Authentic Hyderabadi biryani with raita and salad. Restaurant surplus from lunch service.', 'https://images.unsplash.com/photo-1563379091339-03b21ab4a4f8?w=400&q=80', 60)
");
$track_donation_id = $conn->insert_id;
$conn->query("INSERT INTO requests (donation_id, ngo_id, status) VALUES ($track_donation_id, $ngo_id, 'picked_up')");
$conn->query("INSERT INTO pickups (donation_id, volunteer_id, pickup_status) VALUES ($track_donation_id, $volunteer_id, 'picked_up')");
$pickup_id = $conn->insert_id;
// Create active tracking with multiple location points for realistic route
$conn->query("INSERT INTO delivery_tracking (pickup_id, donation_id, volunteer_id, status) VALUES ($pickup_id, $track_donation_id, $volunteer_id, 'active')");
$track_id = $conn->insert_id;
// Add multiple location updates to show movement
$conn->query("INSERT INTO location_updates (tracking_id, latitude, longitude, speed, created_at) VALUES 
    ($track_id, 19.1197, 72.9078, 25, DATE_SUB(NOW(), INTERVAL 10 MINUTE)),
    ($track_id, 19.1150, 72.9050, 30, DATE_SUB(NOW(), INTERVAL 8 MINUTE)),
    ($track_id, 19.1100, 72.9000, 35, DATE_SUB(NOW(), INTERVAL 5 MINUTE)),
    ($track_id, 19.1050, 72.8950, 28, DATE_SUB(NOW(), INTERVAL 2 MINUTE)),
    ($track_id, 19.1000, 72.8900, 20, NOW())
");
// Add conversation for this donation too
$conn->query("INSERT INTO conversations (donation_id, title) VALUES ($track_donation_id, 'Chat: Chicken Biryani')");
$conv_id2 = $conn->insert_id;
$conn->query("INSERT INTO chat_participants (conversation_id, user_id, user_role) VALUES ($conv_id2, $donor_id, 'donor')");
$conn->query("INSERT INTO chat_participants (conversation_id, user_id, user_role) VALUES ($conv_id2, $ngo_id, 'ngo')");
$conn->query("INSERT INTO chat_participants (conversation_id, user_id, user_role) VALUES ($conv_id2, $volunteer_id, 'volunteer')");
$conn->query("INSERT INTO chat_messages (conversation_id, sender_id, sender_name, message) VALUES ($conv_id2, NULL, 'Mike Smith (Volunteer)', 'Picked up the biryani. On my way to delivery location. ETA 15 minutes.')");

// Scenario 6: Delivered - Completed (History)
$conn->query("
    INSERT INTO donations (donor_id, food_type, food_name, quantity, unit, expiry_time, pickup_address, pickup_city, status, assigned_ngo_id, assigned_volunteer_id, description, images, people_served) 
    VALUES ($donor_id, 'raw', 'Premium Basmati Rice', '25', 'kg', DATE_ADD(NOW(), INTERVAL 60 DAY), '34 Wholesale Market, Dadar', 'Mumbai', 'delivered', $ngo_id, $volunteer_id, 'Premium quality basmati rice. Sealed bags, ideal for community cooking.', 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=400&q=80', 125)
");
$delivered_id = $conn->insert_id;
$conn->query("INSERT INTO requests (donation_id, ngo_id, status) VALUES ($delivered_id, $ngo_id, 'delivered')");

// Scenario 7: Delivered - Another completed
$conn->query("
    INSERT INTO donations (donor_id, food_type, food_name, quantity, unit, expiry_time, pickup_address, pickup_city, status, assigned_ngo_id, assigned_volunteer_id, description, images, people_served) 
    VALUES ($donor_id, 'packaged', 'Canned Fruits & Vegetables', '150', 'cans', DATE_ADD(NOW(), INTERVAL 180 DAY), '67 Supermarket, Malad', 'Mumbai', 'delivered', $ngo_id, $volunteer_id, 'Assorted canned goods - corn, peas, peaches, pineapple. Long shelf life.', 'https://images.unsplash.com/photo-1610832958506-aa56368176cf?w=400&q=80', 75)
");
$delivered_id2 = $conn->insert_id;
$conn->query("INSERT INTO requests (donation_id, ngo_id, status) VALUES ($delivered_id2, $ngo_id, 'delivered')");

// Scenario 8: Delivered - Third completed for good history
$conn->query("
    INSERT INTO donations (donor_id, food_type, food_name, quantity, unit, expiry_time, pickup_address, pickup_city, status, assigned_ngo_id, assigned_volunteer_id, description, images, people_served) 
    VALUES ($donor_id, 'cooked', 'Dal Tadka & Steamed Rice', '50', 'plates', DATE_ADD(NOW(), INTERVAL 2 HOUR), '89 Corporate Cafeteria, Lower Parel', 'Mumbai', 'delivered', $ngo_id, $volunteer_id, 'Nutritious lentil curry with rice. Cafeteria surplus from employee lunch.', 'https://images.unsplash.com/photo-1546833999-b9f581a1996d?w=400&q=80', 50)
");
$delivered_id3 = $conn->insert_id;
$conn->query("INSERT INTO requests (donation_id, ngo_id, status) VALUES ($delivered_id3, $ngo_id, 'delivered')");

echo "<h2>3. Setup Complete!</h2>";
echo "<p style='color:green'><strong>✅ Created 8 unique donations across all statuses:</strong></p>";
echo "<ul>
    <li>2 Available (for NGO to request)</li>
    <li>1 Requested (for Donor to accept/reject)</li>
    <li>1 Confirmed (with active chat)</li>
    <li>1 Picked Up (with live tracking & chat)</li>
    <li>3 Delivered (history)</li>
</ul>";

echo "<p><strong>🎯 Demo Features:</strong></p>";
echo "<ul>
    <li>✓ Unique food items (vegetables, bakery, cooked meals, packaged goods)</li>
    <li>✓ Realistic images for all donations</li>
    <li>✓ Active chat conversations with messages</li>
    <li>✓ Live tracking with route history (5 location points)</li>
    <li>✓ All features visible across Donor, NGO, and Volunteer roles</li>
</ul>";

echo "<p><strong>Login Credentials:</strong><br>
<code>Donor: donor@demo.com / password</code><br>
<code>NGO: ngo@demo.com / password</code><br>
<code>Volunteer: volunteer@demo.com / password</code></p>";

echo "<p><a href='views/auth/login.php' style='display:inline-block; padding:10px 20px; background:#4facfe; color:white; text-decoration:none; border-radius:5px;'>Go to Login Page</a></p>";
?>
