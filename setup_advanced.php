<?php
require_once 'config/database.php';

$conn = getDBConnection();

echo "<h1>FoodShare Advanced Karnataka Demo Setup</h1>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;} .info{color:blue;}</style>";

// 1. Setup Tables
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
    echo "<p class='success'>✓ Database tables ready</p>";
}

// 2. Clear existing demo data
echo "<h2>2. Clearing old demo data...</h2>";
$conn->query("DELETE FROM donations WHERE id > 0");
$conn->query("DELETE FROM conversations WHERE id > 0");
$conn->query("DELETE FROM requests WHERE id > 0");
$conn->query("DELETE FROM pickups WHERE id > 0");
$conn->query("DELETE FROM delivery_tracking WHERE id > 0");
$conn->query("DELETE FROM users WHERE id > 3"); // Keep first 3 if they exist
echo "<p class='success'>✓ Cleared old data</p>";

// 3. Create Users across Karnataka
echo "<h2>3. Creating Users across Karnataka...</h2>";

function createUser($conn, $type, $name, $email, $phone, $address, $city, $state = 'Karnataka', $pincode = '560001', $org_name = null) {
    $password = password_hash('password', PASSWORD_DEFAULT);
    
    // Check if user exists
    $res = $conn->query("SELECT id FROM users WHERE email = '$email'");
    if ($res->num_rows > 0) {
        $id = $res->fetch_assoc()['id'];
        $conn->query("UPDATE users SET password = '$password', phone = '$phone', address = '$address', city = '$city', state = '$state', pincode = '$pincode' WHERE id = $id");
        return $id;
    }
    
    $conn->query("INSERT INTO users (user_type, email, password, full_name, phone, address, city, state, pincode) 
                  VALUES ('$type', '$email', '$password', '$name', '$phone', '$address', '$city', '$state', '$pincode')");
    $id = $conn->insert_id;
    
    // Create role-specific details
    if ($type == 'donor') {
        $org = $org_name ?? "$name Organization";
        $conn->query("INSERT INTO donor_details (user_id, organization_name, organization_type) VALUES ($id, '$org', 'restaurant')");
    } elseif ($type == 'ngo') {
        $conn->query("INSERT INTO ngo_details (user_id, ngo_name, registration_number, ngo_type) VALUES ($id, '$org_name', 'NGO" . rand(1000,9999) . "', 'food_bank')");
    } elseif ($type == 'volunteer') {
        $conn->query("INSERT INTO volunteer_details (user_id, vehicle_type, vehicle_number) VALUES ($id, 'bike', 'KA" . rand(10,99) . "AB" . rand(1000,9999) . "')");
    }
    
    return $id;
}

// DONORS across Karnataka
$donors = [];
$donors[] = createUser($conn, 'donor', 'Bangalore Grand Hotel', 'donor1@demo.com', '9876543210', 'MG Road, Bangalore', 'Bangalore', 'Karnataka', '560001', 'Grand Hotel & Resorts');
$donors[] = createUser($conn, 'donor', 'Mysore Palace Restaurant', 'donor2@demo.com', '9876543211', 'Sayyaji Rao Road, Mysore', 'Mysore', 'Karnataka', '570001', 'Palace Restaurant');
$donors[] = createUser($conn, 'donor', 'Mangalore Coastal Kitchen', 'donor3@demo.com', '9876543212', 'Hampankatta, Mangalore', 'Mangalore', 'Karnataka', '575001', 'Coastal Kitchen');
$donors[] = createUser($conn, 'donor', 'Hubli Corporate Cafeteria', 'donor4@demo.com', '9876543213', 'Vidyanagar, Hubli', 'Hubli', 'Karnataka', '580021', 'TechPark Cafeteria');
$donors[] = createUser($conn, 'donor', 'Belgaum Fresh Bakery', 'donor5@demo.com', '9876543214', 'Tilakwadi, Belgaum', 'Belgaum', 'Karnataka', '590006', 'Fresh Bakery');
$donors[] = createUser($conn, 'donor', 'Tumkur Farm Produce', 'donor6@demo.com', '9876543215', 'BH Road, Tumkur', 'Tumkur', 'Karnataka', '572101', 'Green Farm Foods');

echo "<p class='info'>Created " . count($donors) . " donors across Karnataka</p>";

// NGOs across Karnataka
$ngos = [];
$ngos[] = createUser($conn, 'ngo', 'Akshaya Patra Bangalore', 'ngo1@demo.com', '9876544210', 'Rajajinagar, Bangalore', 'Bangalore', 'Karnataka', '560010', 'Akshaya Patra Foundation');
$ngos[] = createUser($conn, 'ngo', 'Mysore Seva Trust', 'ngo2@demo.com', '9876544211', 'Chamundi Hill Road, Mysore', 'Mysore', 'Karnataka', '570004', 'Mysore Seva Trust');
$ngos[] = createUser($conn, 'ngo', 'Mangalore Hope Foundation', 'ngo3@demo.com', '9876544212', 'Kadri, Mangalore', 'Mangalore', 'Karnataka', '575002', 'Hope Foundation');
$ngos[] = createUser($conn, 'ngo', 'Hubli Community Kitchen', 'ngo4@demo.com', '9876544213', 'Gokul Road, Hubli', 'Hubli', 'Karnataka', '580030', 'Community Kitchen Hubli');
$ngos[] = createUser($conn, 'ngo', 'Belgaum Welfare Society', 'ngo5@demo.com', '9876544214', 'Camp Area, Belgaum', 'Belgaum', 'Karnataka', '590001', 'Welfare Society');
$ngos[] = createUser($conn, 'ngo', 'Tumkur Rural Aid', 'ngo6@demo.com', '9876544215', 'Gandhi Road, Tumkur', 'Tumkur', 'Karnataka', '572102', 'Rural Aid Foundation');

echo "<p class='info'>Created " . count($ngos) . " NGOs across Karnataka</p>";

// VOLUNTEERS across Karnataka
$volunteers = [];
$volunteers[] = createUser($conn, 'volunteer', 'Rajesh Kumar', 'volunteer1@demo.com', '9876545210', 'Indiranagar, Bangalore', 'Bangalore', 'Karnataka', '560038');
$volunteers[] = createUser($conn, 'volunteer', 'Priya Sharma', 'volunteer2@demo.com', '9876545211', 'Koramangala, Bangalore', 'Bangalore', 'Karnataka', '560034');
$volunteers[] = createUser($conn, 'volunteer', 'Arun Gowda', 'volunteer3@demo.com', '9876545212', 'Jayalakshmipuram, Mysore', 'Mysore', 'Karnataka', '570012');
$volunteers[] = createUser($conn, 'volunteer', 'Sneha Rao', 'volunteer4@demo.com', '9876545213', 'Bejai, Mangalore', 'Mangalore', 'Karnataka', '575004');
$volunteers[] = createUser($conn, 'volunteer', 'Kiran Patil', 'volunteer5@demo.com', '9876545214', 'Unkal, Hubli', 'Hubli', 'Karnataka', '580031');

echo "<p class='info'>Created " . count($volunteers) . " volunteers</p>";

// 4. Create diverse food donations
echo "<h2>4. Creating Food Donations...</h2>";

$foodItems = [
    ['type' => 'cooked', 'name' => 'Vegetable Biryani & Raita', 'qty' => '50', 'unit' => 'plates', 'img' => 'https://images.unsplash.com/photo-1563379091339-03b21ab4a4f8?w=400&q=80', 'people' => 50],
    ['type' => 'cooked', 'name' => 'Masala Dosa & Sambar', 'qty' => '40', 'unit' => 'plates', 'img' => 'https://images.unsplash.com/photo-1630383249896-424e482df921?w=400&q=80', 'people' => 40],
    ['type' => 'cooked', 'name' => 'Dal Tadka & Rice', 'qty' => '60', 'unit' => 'plates', 'img' => 'https://images.unsplash.com/photo-1546833999-b9f581a1996d?w=400&q=80', 'people' => 60],
    ['type' => 'cooked', 'name' => 'Paneer Butter Masala & Naan', 'qty' => '35', 'unit' => 'plates', 'img' => 'https://images.unsplash.com/photo-1631452180519-c014fe946bc7?w=400&q=80', 'people' => 35],
    ['type' => 'raw', 'name' => 'Premium Basmati Rice', 'qty' => '25', 'unit' => 'kg', 'img' => 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=400&q=80', 'people' => 125],
    ['type' => 'raw', 'name' => 'Fresh Mixed Vegetables', 'qty' => '20', 'unit' => 'kg', 'img' => 'https://images.unsplash.com/photo-1540189549336-e6e99c3679fe?w=400&q=80', 'people' => 40],
    ['type' => 'raw', 'name' => 'Toor Dal & Moong Dal', 'qty' => '15', 'unit' => 'kg', 'img' => 'https://images.unsplash.com/photo-1596797038530-2c107229654b?w=400&q=80', 'people' => 75],
    ['type' => 'bakery', 'name' => 'Fresh Bread Loaves', 'qty' => '80', 'unit' => 'pieces', 'img' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=400&q=80', 'people' => 80],
    ['type' => 'bakery', 'name' => 'Assorted Buns & Rolls', 'qty' => '100', 'unit' => 'pieces', 'img' => 'https://images.unsplash.com/photo-1608198093002-ad4e1ba5f5d2?w=400&q=80', 'people' => 50],
    ['type' => 'bakery', 'name' => 'Cake & Pastries', 'qty' => '30', 'unit' => 'pieces', 'img' => 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=400&q=80', 'people' => 30],
    ['type' => 'packaged', 'name' => 'Biscuit Packets', 'qty' => '200', 'unit' => 'packets', 'img' => 'https://images.unsplash.com/photo-1558961363-fa8fdf82db35?w=400&q=80', 'people' => 100],
    ['type' => 'packaged', 'name' => 'Canned Fruits & Vegetables', 'qty' => '150', 'unit' => 'cans', 'img' => 'https://images.unsplash.com/photo-1610832958506-aa56368176cf?w=400&q=80', 'people' => 75],
    ['type' => 'packaged', 'name' => 'Instant Noodles & Pasta', 'qty' => '120', 'unit' => 'packets', 'img' => 'https://images.unsplash.com/photo-1612874742237-6526221588e3?w=400&q=80', 'people' => 60],
    ['type' => 'cooked', 'name' => 'Idli & Vada with Chutney', 'qty' => '45', 'unit' => 'plates', 'img' => 'https://images.unsplash.com/photo-1606491956689-2ea866880c84?w=400&q=80', 'people' => 45],
    ['type' => 'cooked', 'name' => 'Chicken Biryani', 'qty' => '40', 'unit' => 'plates', 'img' => 'https://images.unsplash.com/photo-1563379091339-03b21ab4a4f8?w=400&q=80', 'people' => 40],
];

$donationCount = 0;

// Create donations with various statuses
foreach ($donors as $idx => $donor_id) {
    $foodIdx = $idx % count($foodItems);
    $food = $foodItems[$foodIdx];
    $ngo_id = $ngos[$idx % count($ngos)];
    
    // Get donor city
    $donorInfo = $conn->query("SELECT city, address FROM users WHERE id = $donor_id")->fetch_assoc();
    
    // Status 1: Available (2 donations)
    if ($idx < 2) {
        $conn->query("INSERT INTO donations (donor_id, food_type, food_name, quantity, unit, expiry_time, pickup_address, pickup_city, status, description, images, people_served) 
                      VALUES ($donor_id, '{$food['type']}', '{$food['name']}', '{$food['qty']}', '{$food['unit']}', DATE_ADD(NOW(), INTERVAL 6 HOUR), '{$donorInfo['address']}', '{$donorInfo['city']}', 'available', 'Fresh and hygienically packed', '{$food['img']}', {$food['people']})");
        $donationCount++;
    }
    // Status 2: Requested (1 donation)
    elseif ($idx == 2) {
        $conn->query("INSERT INTO donations (donor_id, food_type, food_name, quantity, unit, expiry_time, pickup_address, pickup_city, status, assigned_ngo_id, description, images, people_served) 
                      VALUES ($donor_id, '{$food['type']}', '{$food['name']}', '{$food['qty']}', '{$food['unit']}', DATE_ADD(NOW(), INTERVAL 4 HOUR), '{$donorInfo['address']}', '{$donorInfo['city']}', 'requested', $ngo_id, 'Awaiting donor confirmation', '{$food['img']}', {$food['people']})");
        $donation_id = $conn->insert_id;
        $conn->query("INSERT INTO requests (donation_id, ngo_id, status) VALUES ($donation_id, $ngo_id, 'pending')");
        $donationCount++;
    }
    // Status 3: Confirmed with Chat (2 donations)
    elseif ($idx >= 3 && $idx < 5) {
        $conn->query("INSERT INTO donations (donor_id, food_type, food_name, quantity, unit, expiry_time, pickup_address, pickup_city, status, assigned_ngo_id, description, images, people_served) 
                      VALUES ($donor_id, '{$food['type']}', '{$food['name']}', '{$food['qty']}', '{$food['unit']}', DATE_ADD(NOW(), INTERVAL 5 HOUR), '{$donorInfo['address']}', '{$donorInfo['city']}', 'confirmed', $ngo_id, 'Confirmed and ready for pickup', '{$food['img']}', {$food['people']})");
        $donation_id = $conn->insert_id;
        $conn->query("INSERT INTO requests (donation_id, ngo_id, status) VALUES ($donation_id, $ngo_id, 'confirmed')");
        
        // Create conversation
        $conn->query("INSERT INTO conversations (donation_id, title) VALUES ($donation_id, 'Chat: {$food['name']}')");
        $conv_id = $conn->insert_id;
        $conn->query("INSERT INTO chat_participants (conversation_id, user_id, user_role) VALUES ($conv_id, $donor_id, 'donor')");
        $conn->query("INSERT INTO chat_participants (conversation_id, user_id, user_role) VALUES ($conv_id, $ngo_id, 'ngo')");
        $conn->query("INSERT INTO chat_messages (conversation_id, sender_id, sender_name, message, is_bot) VALUES ($conv_id, NULL, 'FoodShare Assistant', 'Donation confirmed! Coordinate pickup details.', 1)");
        $donationCount++;
    }
    // Status 4: Picked Up with Tracking (1 donation)
    elseif ($idx == 5) {
        $volunteer_id = $volunteers[0];
        $conn->query("INSERT INTO donations (donor_id, food_type, food_name, quantity, unit, expiry_time, pickup_address, pickup_city, status, assigned_ngo_id, assigned_volunteer_id, description, images, people_served) 
                      VALUES ($donor_id, '{$food['type']}', '{$food['name']}', '{$food['qty']}', '{$food['unit']}', DATE_ADD(NOW(), INTERVAL 3 HOUR), '{$donorInfo['address']}', '{$donorInfo['city']}', 'picked_up', $ngo_id, $volunteer_id, 'In transit to NGO', '{$food['img']}', {$food['people']})");
        $donation_id = $conn->insert_id;
        $conn->query("INSERT INTO requests (donation_id, ngo_id, status) VALUES ($donation_id, $ngo_id, 'picked_up')");
        $conn->query("INSERT INTO pickups (donation_id, volunteer_id, pickup_status) VALUES ($donation_id, $volunteer_id, 'picked_up')");
        $pickup_id = $conn->insert_id;
        
        // Create tracking with route
        $conn->query("INSERT INTO delivery_tracking (pickup_id, donation_id, volunteer_id, status) VALUES ($pickup_id, $donation_id, $volunteer_id, 'active')");
        $track_id = $conn->insert_id;
        $conn->query("INSERT INTO location_updates (tracking_id, latitude, longitude, speed, created_at) VALUES 
            ($track_id, 12.9716, 77.5946, 25, DATE_SUB(NOW(), INTERVAL 10 MINUTE)),
            ($track_id, 12.9650, 77.5900, 30, DATE_SUB(NOW(), INTERVAL 7 MINUTE)),
            ($track_id, 12.9600, 77.5850, 28, DATE_SUB(NOW(), INTERVAL 4 MINUTE)),
            ($track_id, 12.9550, 77.5800, 20, NOW())
        ");
        $donationCount++;
    }
}

echo "<p class='success'>✓ Created $donationCount diverse donations</p>";

echo "<h2>5. Setup Complete!</h2>";
echo "<div style='background:#e8f5e9;padding:20px;border-radius:8px;margin:20px 0;'>";
echo "<h3>🎉 Karnataka Multi-User Demo Ready!</h3>";
echo "<p><strong>Users Created:</strong></p>";
echo "<ul>";
echo "<li>6 Donors across Bangalore, Mysore, Mangalore, Hubli, Belgaum, Tumkur</li>";
echo "<li>6 NGOs across same districts</li>";
echo "<li>5 Volunteers</li>";
echo "</ul>";
echo "<p><strong>Features:</strong></p>";
echo "<ul>";
echo "<li>✓ Location-based nearby matching</li>";
echo "<li>✓ 15+ unique food items</li>";
echo "<li>✓ Cross-district scenarios</li>";
echo "<li>✓ Active chats & tracking</li>";
echo "</ul>";
echo "</div>";

echo "<h3>Login Credentials (all passwords: <code>password</code>):</h3>";
echo "<table border='1' cellpadding='10' style='border-collapse:collapse;'>";
echo "<tr><th>Role</th><th>Email</th><th>Location</th></tr>";
for ($i = 1; $i <= 6; $i++) {
    $cities = ['Bangalore', 'Mysore', 'Mangalore', 'Hubli', 'Belgaum', 'Tumkur'];
    echo "<tr><td>Donor</td><td>donor{$i}@demo.com</td><td>{$cities[$i-1]}</td></tr>";
}
for ($i = 1; $i <= 6; $i++) {
    $cities = ['Bangalore', 'Mysore', 'Mangalore', 'Hubli', 'Belgaum', 'Tumkur'];
    echo "<tr><td>NGO</td><td>ngo{$i}@demo.com</td><td>{$cities[$i-1]}</td></tr>";
}
for ($i = 1; $i <= 5; $i++) {
    echo "<tr><td>Volunteer</td><td>volunteer{$i}@demo.com</td><td>Various</td></tr>";
}
echo "</table>";

echo "<p style='margin-top:30px;'><a href='views/auth/login.php' style='display:inline-block;padding:15px 30px;background:#4facfe;color:white;text-decoration:none;border-radius:8px;font-size:18px;'>🚀 Go to Login Page</a></p>";
?>
