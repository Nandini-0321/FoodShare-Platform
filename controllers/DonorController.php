<?php
require_once __DIR__ . '/../models/Donation.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/LocationHelper.php';
require_once __DIR__ . '/../config/ai_config.php';

class DonorController {
    private $donationModel;
    private $userModel;
    
    public function __construct() {
        $this->donationModel = new Donation();
        $this->userModel = new User();
        
        // Ensure user is logged in
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'donor') {
            // Redirect to login if not logged in or not a donor
            header('location: /FoodShare/views/auth/login.php');
            exit;
        }
    }
    
    public function getDashboardData() {
        $userId = $_SESSION['user_id'];
        
        $data = [
            'stats' => $this->donationModel->getDonorStats($userId),
            'recent_donations' => $this->donationModel->getRecentDonationsByDonor($userId),
            'active_requests' => $this->donationModel->getActiveRequests($userId),
            'user' => $this->userModel->getUserById($userId)
        ];
        
        return $data;
    }
    
    public function createDonation() {
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Sanitize POST data
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            
            $data = [
                'donor_id' => $_SESSION['user_id'],
                'food_name' => trim($_POST['food_name']),
                'food_type' => trim($_POST['food_type']),
                'food_category' => trim($_POST['food_category'] ?? 'other'),
                'quantity' => trim($_POST['quantity']),
                'unit' => trim($_POST['unit']),
                'people_served' => trim($_POST['people_served']),
                'expiry_time' => trim($_POST['expiry_date']) . ' ' . trim($_POST['expiry_time']),
                'description' => trim($_POST['description']),
                'pickup_address' => trim($_POST['pickup_address']),
                'pickup_city' => trim($_POST['pickup_city']),
                'food_image' => trim($_POST['food_image_url'] ?? ''),
                'food_name_err' => '',
                'quantity_err' => '',
                'address_err' => '',
                'expiry_err' => ''
            ];
            
            // If no image URL from client (fallback), fetch it server-side
            if(empty($data['food_image'])) {
                $data['food_image'] = $this->fetchFoodImage($data['food_name'], $data['food_category']);
            }
            
            // Geocode pickup address to get coordinates
            $fullAddress = $data['pickup_address'];
            if (!empty($data['pickup_city'])) {
                $fullAddress .= ', ' . $data['pickup_city'];
            }
            
            $coords = LocationHelper::geocodeAddress($fullAddress);
            if ($coords) {
                $data['pickup_latitude'] = $coords['lat'];
                $data['pickup_longitude'] = $coords['lon'];
            } else {
                // If geocoding fails, coordinates will be null
                $data['pickup_latitude'] = null;
                $data['pickup_longitude'] = null;
            }
            
            // Validate
            if(empty($data['food_name'])) $data['food_name_err'] = 'Please enter food name';
            if(empty($data['quantity'])) $data['quantity_err'] = 'Please enter quantity';
            if(empty($data['pickup_address'])) $data['address_err'] = 'Please enter pickup address';
            
            // Validate Expiry
            $expiryTimestamp = strtotime($data['expiry_time']);
            $now = time();
            $sevenDaysFromNow = $now + (7 * 24 * 60 * 60);
            
            if ($expiryTimestamp <= $now) {
                $data['expiry_err'] = 'Expiry time must be in the future';
            } elseif ($expiryTimestamp > $sevenDaysFromNow) {
                $data['expiry_err'] = 'Expiry cannot be more than 7 days from now (food safety)';
            }
            
            if(empty($data['food_name_err']) && empty($data['quantity_err']) && empty($data['address_err']) && empty($data['expiry_err'])) {
                if($this->donationModel->addDonation($data)) {
                    header('location: /FoodShare/views/donor/dashboard.php?status=donation_added');
                } else {
                    die('Something went wrong');
                }
            } else {
                return $data;
            }
        } else {
            // Init data
            $data = [
                'food_name' => '',
                'food_type' => 'cooked',
                'quantity' => '',
                'unit' => 'plates',
                'people_served' => '',
                'expiry_date' => '',
                'expiry_time' => '',
                'description' => '',
                'pickup_address' => '',
                'pickup_city' => '',
                'food_name_err' => '',
                'quantity_err' => '',
                'address_err' => '',
                'expiry_err' => ''
            ];
            
            return $data;
        }
    }
    
    public function getDonationById($id) {
        return $this->donationModel->getDonationById($id);
    }
    
    public function editDonation($id) {
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Sanitize POST data
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            
            $data = [
                'id' => $id,
                'donor_id' => $_SESSION['user_id'],
                'food_name' => trim($_POST['food_name']),
                'food_type' => trim($_POST['food_type']),
                'food_category' => trim($_POST['food_category'] ?? 'other'),
                'quantity' => trim($_POST['quantity']),
                'unit' => trim($_POST['unit']),
                'people_served' => trim($_POST['people_served']),
                'expiry_time' => trim($_POST['expiry_date']) . ' ' . trim($_POST['expiry_time']),
                'description' => trim($_POST['description']),
                'pickup_address' => trim($_POST['pickup_address']),
                'pickup_city' => trim($_POST['pickup_city']),
                'food_image' => trim($_POST['food_image_url'] ?? ''),
                'food_name_err' => '',
                'quantity_err' => '',
                'address_err' => '',
                'expiry_err' => ''
            ];
            
            // If no image URL from client, fetch it server-side
            if(empty($data['food_image'])) {
                $data['food_image'] = $this->fetchFoodImage($data['food_name'], $data['food_category']);
            }
            
            // Geocode pickup address to get coordinates
            $fullAddress = $data['pickup_address'];
            if (!empty($data['pickup_city'])) {
                $fullAddress .= ', ' . $data['pickup_city'];
            }
            
            $coords = LocationHelper::geocodeAddress($fullAddress);
            if ($coords) {
                $data['pickup_latitude'] = $coords['lat'];
                $data['pickup_longitude'] = $coords['lon'];
            } else {
                $data['pickup_latitude'] = null;
                $data['pickup_longitude'] = null;
            }
            
            // Validate
            if(empty($data['food_name'])) $data['food_name_err'] = 'Please enter food name';
            if(empty($data['quantity'])) $data['quantity_err'] = 'Please enter quantity';
            if(empty($data['pickup_address'])) $data['address_err'] = 'Please enter pickup address';
            
            // Validate Expiry
            $expiryTimestamp = strtotime($data['expiry_time']);
            $now = time();
            $sevenDaysFromNow = $now + (7 * 24 * 60 * 60);
            
            if ($expiryTimestamp <= $now) {
                $data['expiry_err'] = 'Expiry time must be in the future';
            } elseif ($expiryTimestamp > $sevenDaysFromNow) {
                $data['expiry_err'] = 'Expiry cannot be more than 7 days from now (food safety)';
            }
            
            if(empty($data['food_name_err']) && empty($data['quantity_err']) && empty($data['address_err']) && empty($data['expiry_err'])) {
                if($this->donationModel->updateDonation($data)) {
                    header('location: /FoodShare/views/donor/donations.php?status=donation_updated');
                } else {
                    die('Something went wrong');
                }
            } else {
                return $data;
            }
        } else {
            // Get existing donation
            $donation = $this->donationModel->getDonationById($id);
            if($donation->donor_id != $_SESSION['user_id'] || $donation->status != 'available') {
                header('location: /FoodShare/views/donor/donations.php');
                exit;
            }
            
            $expiry_parts = explode(' ', $donation->expiry_time);
            
            $data = [
                'id' => $donation->id,
                'food_name' => $donation->food_name,
                'food_type' => $donation->food_type,
                'quantity' => $donation->quantity,
                'unit' => $donation->unit,
                'people_served' => $donation->people_served,
                'expiry_date' => $expiry_parts[0],
                'expiry_time' => $expiry_parts[1],
                'description' => $donation->description,
                'pickup_address' => $donation->pickup_address,
                'pickup_city' => $donation->pickup_city,
                'food_image_url' => $donation->images,
                'food_name_err' => '',
                'quantity_err' => '',
                'address_err' => '',
                'expiry_err' => ''
            ];
            
            return $data;
        }
    }
    
    /**
     * Fetch food image from Unsplash API based on food name and category
     * @param string $foodName The name of the food
     * @param string $category The food category (vegetarian, non_vegetarian, bakery, other)
     * @return string The image URL or category-based fallback
     */
    private function fetchFoodImage($foodName, $category) {
        // Category-based fallback images
        $categoryImages = [
            'vegetarian' => 'https://images.unsplash.com/photo-1540189549336-e6e99c3679fe?w=400&q=80',
            'non_vegetarian' => 'https://images.unsplash.com/photo-1529042410759-befb1204b468?w=400&q=80',
            'bakery' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=400&q=80',
            'other' => 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=400&q=80'
        ];
        
        // Try to get specific image from Unsplash API based on food name
        try {
            // Using Unsplash's public demo access
            // For production, get your own free API key at https://unsplash.com/developers
            $accessKey = 'pVth82E2JRQl6ZSxXpXCpP3eJEXW9C_x4rOxZIgmVCg'; // Demo key - replace with your own
            
            $searchQuery = urlencode($foodName . ' food');
            $url = "https://api.unsplash.com/search/photos?query={$searchQuery}&per_page=1&orientation=landscape&client_id={$accessKey}";
            
            // Use cURL to fetch from Unsplash
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'FoodShare App/1.0');
            curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 5 second timeout
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            // If successful, return the image URL
            if ($httpCode == 200 && $response) {
                $data = json_decode($response, true);
                if (!empty($data['results'][0]['urls']['regular'])) {
                    // Return high-quality image URL with optimized size
                    return $data['results'][0]['urls']['regular'] . '&w=400&q=80';
                }
            }
            
            // Fallback to category-based image if API fails or no results
            return $categoryImages[$category] ?? $categoryImages['other'];
            
        } catch (Exception $e) {
            // On error, return category-based default image
            return $categoryImages[$category] ?? $categoryImages['other'];
        }
    }
    
    public function getAllDonations() {
        $userId = $_SESSION['user_id'];
        return $this->donationModel->getDonationsByDonor($userId);
    }
    
    // Delete a donation
    public function deleteDonation($donation_id) {
        $userId = $_SESSION['user_id'];
        
        if($this->donationModel->deleteDonation($donation_id, $userId)) {
            return true;
        }
        return false;
    }
    
    // Handle request accept/reject
    public function handleRequest($donation_id, $action) {
        $userId = $_SESSION['user_id'];
        
        if($action == 'accept') {
            // Update status to confirmed
            if($this->donationModel->updateDonationStatus($donation_id, 'confirmed', $userId)) {
                return true;
            }
        } elseif($action == 'reject') {
            // Update status back to available and remove assigned NGO  
            // We need to add a method to Donation model for this
            if($this->donationModel->rejectRequest($donation_id, $userId)) {
                return true;
            }
        }
        
        return false;
    }

    // Handle donation cancellation
    public function handleCancellation($donation_id, $reason) {
        $userId = $_SESSION['user_id'];
        return $this->donationModel->cancelDonation($donation_id, $userId, $reason);
    }

    /**
     * Handle AJAX request for image generation
     */
    public function handleImageGenerationRequest() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $foodName = $input['food_name'] ?? '';
        $category = $input['category'] ?? 'other';
        $refresh = $input['refresh'] ?? false;

        if (empty($foodName)) {
            echo json_encode(['success' => false, 'message' => 'Food name is required']);
            exit;
        }

        $images = $this->fetchMultipleFoodImages($foodName, $category, 5, $refresh);

        if (!empty($images)) {
            echo json_encode([
                'success' => true, 
                'image_url' => $images[0],
                'images' => $images
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to generate image']);
        }
        exit;
    }

    /**
     * Fetch multiple food images with caching support
     */
    public function fetchMultipleFoodImages($foodName, $category, $count = 5, $refresh = false) {
        $foodName = trim(strtolower($foodName));
        $cacheDir = __DIR__ . '/../includes/cache/food_images';
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }
        
        $cacheFile = $cacheDir . '/' . md5($foodName) . '.json';
        
        // Cache for 24 hours unless refresh requested
        if (!$refresh && file_exists($cacheFile) && (time() - filemtime($cacheFile) < 86400)) {
            return json_decode(file_get_contents($cacheFile), true);
        }
        
        $accessKey = 'pVth82E2JRQl6ZSxXpXCpP3eJEXW9C_x4rOxZIgmVCg';
        $searchQuery = urlencode($foodName . ' food');
        $page = $refresh ? rand(1, 5) : 1;
        $url = "https://api.unsplash.com/search/photos?query={$searchQuery}&per_page={$count}&page={$page}&orientation=landscape&client_id={$accessKey}";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'FoodShare App/1.0');
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $images = [];
        if ($httpCode == 200 && $response) {
            $data = json_decode($response, true);
            if (!empty($data['results'])) {
                foreach($data['results'] as $result) {
                    $images[] = $result['urls']['regular'] . '&w=600&q=80';
                }
            }
        }
        
        // Fallback to category if no images found
        if (empty($images)) {
            $categoryImages = [
                'cooked' => 'https://images.unsplash.com/photo-1540189549336-e6e99c3679fe?w=400&q=80',
                'raw' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=400&q=80',
                'packaged' => 'https://images.unsplash.com/photo-1542838132-92c53300491e?w=400&q=80',
                'bakery' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=400&q=80',
                'other' => 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=400&q=80'
            ];
            $images[] = $categoryImages[$category] ?? $categoryImages['other'];
        }
        
        file_put_contents($cacheFile, json_encode($images));
        return $images;
    }

    /**
     * Support suggestions for food entry
     */
    public function getFoodSuggestions($query) {
        $foodList = [
            'Biryani', 'Pizza', 'Salad', 'Burger', 'Pasta', 'Fried Rice', 'Chicken Tikka',
            'Paneer Butter Masala', 'Dosa', 'Samosa', 'Cupcake', 'Cookie', 'Whole Wheat Bread',
            'Chocolate Cake', 'Fruit Salad', 'Pancakes', 'Waffles', 'Omelette', 'Sandwich',
            'Smoothie Bowl', 'Noodle Soup', 'Spring Rolls', 'Dim Sum', 'Sushi', 'Taco', 'Nacho',
            'Pesto Pasta', 'Spaghetti Bolognese', 'Caesar Salad', 'Greek Salad', 'Lentil Soup'
        ];
        
        $suggestions = [];
        $query = strtolower($query);
        foreach($foodList as $food) {
            if (stripos($food, $query) !== false) {
                $suggestions[] = $food;
            }
        }
        return array_slice($suggestions, 0, 10);
    }


    /**
     * Generate image using OpenAI DALL-E API
     */
    private function generateAIImage($foodName, $category) {
        // If API key is not set or is the placeholder, return a fallback/mock response
        if (!defined('OPENAI_API_KEY') || OPENAI_API_KEY === 'YOUR_OPENAI_API_KEY') {
            // Simulate delay for realism
            sleep(2); 
            // Return a high-quality Unsplash image as a "mock" AI result so the UI still works
            return $this->fetchFoodImage($foodName, $category);
        }

        $prompt = "Create a clear, realistic photograph that accurately represents the following donated food item: {$foodName}. " .
                  "Show only this food item (or a small group of the same item) on a simple, clean background. " .
                  "The image must clearly match the food type, form, and preparation mentioned in the name (raw/cooked, whole/chopped, packed/loose, veg/non-veg). " .
                  "Do not add any people, logos, brand names, labels, or text. " .
                  "Make it look like a real photo, not a drawing or cartoon.";

        $url = 'https://api.openai.com/v1/images/generations';
        
        $data = [
            'model' => 'dall-e-3',
            'prompt' => $prompt,
            'n' => 1,
            'size' => '1024x1024'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . OPENAI_API_KEY
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode == 200) {
            $result = json_decode($response, true);
            return $result['data'][0]['url'] ?? null;
        }

        return null;
    }
}
