<?php
require_once '../../controllers/AuthController.php';

// Check if form is submitted
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $init = new AuthController;
    $data = $init->register();
} else {
    $data = [
        'name' => '',
        'email' => '',
        'password' => '',
        'confirm_password' => '',
        'type' => '',
        'phone' => '',
        'address' => '',
        'city' => '',
        'state' => '',
        'pincode' => '',
        'name_err' => '',
        'email_err' => '',
        'password_err' => '',
        'confirm_password_err' => ''
    ];
}

// Check for role parameter
$preSelectedRole = isset($_GET['role']) ? $_GET['role'] : '';
// If role is set, use it as default type
if($preSelectedRole && $data['type'] == '') {
    $data['type'] = $preSelectedRole;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join FoodShare - Create Account</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Global Styles -->
    <link rel="stylesheet" href="/FoodShare/assets/css/style.css">
    
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background: var(--bg-primary);
            display: flex;
            font-family: var(--font-body);
        }
        
        .split-layout {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }
        
        /* Left Side - Visual */
        .auth-visual {
            flex: 1;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.9), rgba(118, 75, 162, 0.9)), url('https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80');
            background-size: cover;
            background-position: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 4rem;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .auth-visual::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        
        .visual-content {
            position: relative;
            z-index: 1;
            max-width: 600px;
        }
        
        .visual-title {
            font-family: var(--font-heading);
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            line-height: 1.1;
        }
        
        .visual-text {
            font-size: 1.2rem;
            opacity: 0.9;
            line-height: 1.6;
            margin-bottom: 2rem;
        }
        
        .feature-list {
            list-style: none;
            padding: 0;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }
        
        .feature-icon {
            width: 32px;
            height: 32px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
        }
        
        /* Right Side - Form */
        .auth-form-container {
            flex: 1;
            padding: 2rem;
            overflow-y: auto;
            background: var(--bg-secondary);
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .auth-form-wrapper {
            width: 100%;
            max-width: 600px;
            margin: auto;
        }
        
        .form-header {
            margin-bottom: 2rem;
        }
        
        .form-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
            font-family: var(--font-heading);
        }
        
        .form-subtitle {
            color: var(--text-secondary);
        }
        
        /* Selection Grids */
        .selection-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .selection-card {
            background: var(--surface);
            border: 2px solid transparent;
            border-radius: var(--radius-md);
            padding: 1rem;
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .selection-card:hover {
            background: var(--surface-hover);
            transform: translateY(-2px);
        }
        
        .selection-card.active {
            border-color: var(--primary);
            background: rgba(102, 126, 234, 0.1);
        }
        
        .selection-card.active::after {
            content: '\f00c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            top: 5px;
            right: 5px;
            color: var(--primary);
            font-size: 0.8rem;
        }
        
        .selection-icon {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: var(--text-secondary);
            transition: color 0.2s;
        }
        
        .selection-card.active .selection-icon {
            color: var(--primary);
        }
        
        .selection-label {
            font-weight: 500;
            font-size: 0.9rem;
            color: var(--text-primary);
        }
        
        /* Form Sections */
        .form-section {
            background: rgba(255, 255, 255, 0.03);
            border-radius: var(--radius-md);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .details-section {
            display: none;
            animation: slideDown 0.3s ease;
        }
        
        .details-section.active {
            display: block;
        }
        
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .split-layout {
                flex-direction: column;
            }
            
            .auth-visual {
                padding: 3rem 2rem;
                min-height: 300px;
                flex: none;
            }
            
            .visual-title {
                font-size: 2.5rem;
            }
            
            .auth-form-container {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>

<div class="split-layout">
    <!-- Left Side: Visual -->
    <div class="auth-visual">
        <div class="visual-content">
            <h1 class="visual-title">Make a Difference Today</h1>
            <p class="visual-text">Join thousands of donors, NGOs, and volunteers working together to end hunger and reduce food waste.</p>
            
            <ul class="feature-list">
                <li class="feature-item">
                    <div class="feature-icon"><i class="fas fa-utensils"></i></div>
                    <span>Share surplus food instantly</span>
                </li>
                <li class="feature-item">
                    <div class="feature-icon"><i class="fas fa-truck"></i></div>
                    <span>Track donations in real-time</span>
                </li>
                <li class="feature-item">
                    <div class="feature-icon"><i class="fas fa-users"></i></div>
                    <span>Connect with your community</span>
                </li>
            </ul>
        </div>
    </div>
    
    <!-- Right Side: Form -->
    <div class="auth-form-container">
        <div class="auth-form-wrapper">
            <div class="form-header">
                <a href="/FoodShare/" style="text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem; color: var(--primary);">
                    <i class="fas fa-heart"></i> <span style="font-weight: 700; font-family: var(--font-heading);">FoodShare</span>
                </a>
                <h2 class="form-title">Create Account</h2>
                <p class="form-subtitle">Fill in your details to get started</p>
            </div>
            
            <form action="<?php echo $_SERVER['PHP_SELF']; ?><?php echo $preSelectedRole ? '?role='.$preSelectedRole : ''; ?>" method="POST" id="registerForm">
                
                <!-- Role Selection -->
                <div class="form-section" <?php echo $preSelectedRole ? 'style="display:none;"' : ''; ?>>
                    <h3 class="section-title"><i class="fas fa-user-tag"></i> I am a...</h3>
                    <div class="selection-grid">
                        <div class="selection-card <?php echo ($data['type'] == 'donor' || $data['type'] == '') ? 'active' : ''; ?>" onclick="selectType('donor')">
                            <i class="fas fa-hand-holding-heart selection-icon"></i>
                            <div class="selection-label">Donor</div>
                        </div>
                        <div class="selection-card <?php echo ($data['type'] == 'ngo') ? 'active' : ''; ?>" onclick="selectType('ngo')">
                            <i class="fas fa-hands-helping selection-icon"></i>
                            <div class="selection-label">NGO</div>
                        </div>
                        <div class="selection-card <?php echo ($data['type'] == 'volunteer') ? 'active' : ''; ?>" onclick="selectType('volunteer')">
                            <i class="fas fa-biking selection-icon"></i>
                            <div class="selection-label">Volunteer</div>
                        </div>
                    </div>
                    <input type="hidden" name="user_type" id="user_type" value="<?php echo ($data['type'] != '') ? $data['type'] : 'donor'; ?>">
                </div>
                
                <!-- Basic Info -->
                <div class="form-section">
                    <h3 class="section-title"><i class="fas fa-info-circle"></i> Basic Information</h3>
                    <div class="grid grid-2 gap-md">
                        <div class="form-group">
                            <label class="form-label">Full Name / Org Name</label>
                            <input type="text" name="name" class="form-control" value="<?php echo $data['name']; ?>" required>
                            <span class="invalid-feedback"><?php echo $data['name_err']; ?></span>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" value="<?php echo $data['email']; ?>" required>
                            <span class="invalid-feedback"><?php echo $data['email_err']; ?></span>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" name="phone" class="form-control" value="<?php echo $data['phone']; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control" value="<?php echo $data['city']; ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group mt-md">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="2" required><?php echo $data['address']; ?></textarea>
                    </div>
                    
                    <div class="grid grid-2 gap-md mt-md">
                        <div class="form-group">
                            <label class="form-label">State</label>
                            <input type="text" name="state" class="form-control" value="<?php echo $data['state']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Pincode</label>
                            <input type="text" name="pincode" class="form-control" value="<?php echo $data['pincode']; ?>" required>
                        </div>
                    </div>
                </div>
                
                <!-- Role Specific Details -->
                
                <!-- Donor Details -->
                <div id="donor-details" class="details-section active">
                    <div class="form-section">
                        <h3 class="section-title"><i class="fas fa-utensils"></i> Donor Details</h3>
                        <label class="form-label mb-sm">Organization Type</label>
                        <div class="selection-grid">
                            <div class="selection-card active" onclick="selectOption(this, 'org_type', 'individual')">
                                <i class="fas fa-user selection-icon"></i>
                                <div class="selection-label">Individual</div>
                            </div>
                            <div class="selection-card" onclick="selectOption(this, 'org_type', 'restaurant')">
                                <i class="fas fa-utensils selection-icon"></i>
                                <div class="selection-label">Restaurant</div>
                            </div>
                            <div class="selection-card" onclick="selectOption(this, 'org_type', 'hotel')">
                                <i class="fas fa-hotel selection-icon"></i>
                                <div class="selection-label">Hotel</div>
                            </div>
                            <div class="selection-card" onclick="selectOption(this, 'org_type', 'grocery')">
                                <i class="fas fa-shopping-basket selection-icon"></i>
                                <div class="selection-label">Grocery</div>
                            </div>
                        </div>
                        <input type="hidden" name="org_type" id="org_type" value="individual">
                    </div>
                </div>
                
                <!-- NGO Details -->
                <div id="ngo-details" class="details-section">
                    <div class="form-section">
                        <h3 class="section-title"><i class="fas fa-building"></i> NGO Details</h3>
                        <label class="form-label mb-sm">NGO Type</label>
                        <div class="selection-grid">
                            <div class="selection-card active" onclick="selectOption(this, 'ngo_type', 'orphanage')">
                                <i class="fas fa-child selection-icon"></i>
                                <div class="selection-label">Orphanage</div>
                            </div>
                            <div class="selection-card" onclick="selectOption(this, 'ngo_type', 'old_age_home')">
                                <i class="fas fa-wheelchair selection-icon"></i>
                                <div class="selection-label">Old Age Home</div>
                            </div>
                            <div class="selection-card" onclick="selectOption(this, 'ngo_type', 'street_feeding')">
                                <i class="fas fa-bowl-food selection-icon"></i>
                                <div class="selection-label">Street Feeding</div>
                            </div>
                            <div class="selection-card" onclick="selectOption(this, 'ngo_type', 'disaster_relief')">
                                <i class="fas fa-house-damage selection-icon"></i>
                                <div class="selection-label">Disaster Relief</div>
                            </div>
                        </div>
                        <input type="hidden" name="ngo_type" id="ngo_type" value="orphanage">
                        
                        <div class="form-group mt-md">
                            <label class="form-label">Website (Optional)</label>
                            <input type="url" name="website" class="form-control" placeholder="https://...">
                        </div>
                    </div>
                </div>
                
                <!-- Volunteer Details -->
                <div id="volunteer-details" class="details-section">
                    <div class="form-section">
                        <h3 class="section-title"><i class="fas fa-hands-helping"></i> Volunteer Details</h3>
                        
                        <label class="form-label mb-sm">Vehicle Type</label>
                        <div class="selection-grid">
                            <div class="selection-card active" onclick="selectOption(this, 'vehicle_type', 'none')">
                                <i class="fas fa-walking selection-icon"></i>
                                <div class="selection-label">None</div>
                            </div>
                            <div class="selection-card" onclick="selectOption(this, 'vehicle_type', 'bike')">
                                <i class="fas fa-motorcycle selection-icon"></i>
                                <div class="selection-label">Bike</div>
                            </div>
                            <div class="selection-card" onclick="selectOption(this, 'vehicle_type', 'car')">
                                <i class="fas fa-car selection-icon"></i>
                                <div class="selection-label">Car</div>
                            </div>
                            <div class="selection-card" onclick="selectOption(this, 'vehicle_type', 'van')">
                                <i class="fas fa-truck selection-icon"></i>
                                <div class="selection-label">Van</div>
                            </div>
                        </div>
                        <input type="hidden" name="vehicle_type" id="vehicle_type" value="none">
                        
                        <div class="form-group mt-md">
                            <label class="form-label">Vehicle Number (if applicable)</label>
                            <input type="text" name="vehicle_no" class="form-control">
                        </div>
                        
                        <label class="form-label mt-md mb-sm">Availability</label>
                        <div class="selection-grid">
                            <div class="selection-card active" onclick="selectOption(this, 'availability', 'flexible')">
                                <i class="fas fa-clock selection-icon"></i>
                                <div class="selection-label">Flexible</div>
                            </div>
                            <div class="selection-card" onclick="selectOption(this, 'availability', 'weekends')">
                                <i class="fas fa-calendar-week selection-icon"></i>
                                <div class="selection-label">Weekends</div>
                            </div>
                            <div class="selection-card" onclick="selectOption(this, 'availability', 'full_time')">
                                <i class="fas fa-calendar-check selection-icon"></i>
                                <div class="selection-label">Full Time</div>
                            </div>
                        </div>
                        <input type="hidden" name="availability" id="availability" value="flexible">
                    </div>
                </div>
                
                <!-- Security -->
                <div class="form-section">
                    <h3 class="section-title"><i class="fas fa-lock"></i> Security</h3>
                    <div class="grid grid-2 gap-md">
                        <div class="form-group">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" value="<?php echo $data['password']; ?>" required>
                            <span class="invalid-feedback"><?php echo $data['password_err']; ?></span>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="confirm_password" class="form-control" value="<?php echo $data['confirm_password']; ?>" required>
                            <span class="invalid-feedback"><?php echo $data['confirm_password_err']; ?></span>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; justify-content: center; padding: 1rem; font-size: 1.1rem;">
                    Create Account <i class="fas fa-arrow-right" style="margin-left: 0.5rem;"></i>
                </button>
                
                <div class="text-center mt-lg">
                    <p style="color: var(--text-secondary);">Already have an account? <a href="login.php" style="color: var(--primary); font-weight: 600; text-decoration: none;">Login here</a></p>
                </div>
                
            </form>
        </div>
    </div>
</div>

<script>
    function selectType(type) {
        // Update hidden input
        document.getElementById('user_type').value = type;
        
        // Update UI classes for type selector
        // Find the container for type selection (first selection-grid)
        const typeContainer = document.querySelector('.selection-grid');
        const cards = typeContainer.querySelectorAll('.selection-card');
        
        cards.forEach(card => {
            card.classList.remove('active');
            if(card.querySelector('.selection-label').innerText.toLowerCase() === type) {
                card.classList.add('active');
            }
        });
        
        // Show/Hide sections
        document.querySelectorAll('.details-section').forEach(el => el.classList.remove('active'));
        document.getElementById(type + '-details').classList.add('active');
    }

    function selectOption(element, inputId, value) {
        // Update hidden input
        document.getElementById(inputId).value = value;
        
        // Update UI classes
        // Find siblings
        const container = element.parentElement;
        container.querySelectorAll('.selection-card').forEach(el => el.classList.remove('active'));
        element.classList.add('active');
    }

    // Initialize correct section on load
    document.addEventListener('DOMContentLoaded', function() {
        const currentType = document.getElementById('user_type').value;
        selectType(currentType);
    });
</script>

</body>
</html>
