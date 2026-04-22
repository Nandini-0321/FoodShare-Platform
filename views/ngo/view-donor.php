<?php
// Include required files
require_once '../../models/User.php';
require_once '../../models/Donation.php';

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is an NGO
if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'ngo') {
    header('location: /FoodShare/views/auth/login.php');
    exit;
}

// Check if ID is provided
if(!isset($_GET['id'])) {
    header('location: dashboard.php');
    exit;
}

$donorId = $_GET['id'];
$userModel = new User();
$donationModel = new Donation();

// Get Donor Details
$donor = $userModel->getUserById($donorId);

if(!$donor || $donor->user_type != 'donor') {
    // Invalid donor
    header('location: dashboard.php');
    exit;
}

// Get Donor Stats
$stats = $donationModel->getDonorStats($donorId);

// Page Configuration
$pageTitle = 'Donor Profile - FoodShare';
$pageHeading = 'Donor Profile';
$userType = 'ngo';
$userName = $_SESSION['user_name'] ?? 'NGO User';

// Start output buffering
ob_start();
?>

<div class="mb-lg">
    <a href="dashboard.php" class="btn btn-ghost">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
</div>

<div class="card mb-lg" style="overflow: hidden;">
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 3rem 2rem; color: white; text-align: center;">
        <div style="width: 100px; height: 100px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; color: #667eea; margin: 0 auto 1rem; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
            <i class="fas fa-user"></i>
        </div>
        <h1 style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;"><?php echo $donor->organization_name ?: $donor->full_name; ?></h1>
        <div style="opacity: 0.9;">
            <?php if($donor->organization_name): ?>
                <span class="badge" style="background: rgba(255,255,255,0.2); color: white; border: none;"><?php echo ucfirst($donor->organization_type); ?></span>
            <?php else: ?>
                <span class="badge" style="background: rgba(255,255,255,0.2); color: white; border: none;">Individual Donor</span>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Donor Stats -->
    <div class="grid grid-3" style="border-bottom: 1px solid rgba(255,255,255,0.1);">
        <div style="padding: 1.5rem; text-align: center; border-right: 1px solid rgba(255,255,255,0.1);">
            <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary);"><?php echo $stats['total_donations']; ?></div>
            <div style="font-size: 0.9rem; color: var(--text-secondary);">Donations</div>
        </div>
        <div style="padding: 1.5rem; text-align: center; border-right: 1px solid rgba(255,255,255,0.1);">
            <div style="font-size: 1.5rem; font-weight: 700; color: var(--success);"><?php echo $stats['meals_provided']; ?></div>
            <div style="font-size: 0.9rem; color: var(--text-secondary);">Meals Provided</div>
        </div>
        <div style="padding: 1.5rem; text-align: center;">
            <div style="font-size: 1.5rem; font-weight: 700; color: var(--warning);"><?php echo $donor->rating ?? 'New'; ?></div>
            <div style="font-size: 0.9rem; color: var(--text-secondary);">Rating</div>
        </div>
    </div>
    
    <div class="card-body">
        <h3 style="font-size: 1.2rem; margin-bottom: 1.5rem; color: var(--text-primary); border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 0.5rem;">
            <i class="fas fa-info-circle"></i> Contact Information
        </h3>
        
        <div class="grid grid-2 gap-lg">
            <div>
                <label style="display: block; color: var(--text-secondary); font-size: 0.85rem; margin-bottom: 0.25rem;">Contact Person</label>
                <div style="font-size: 1.1rem;"><?php echo $donor->full_name; ?></div>
            </div>
            
            <div>
                <label style="display: block; color: var(--text-secondary); font-size: 0.85rem; margin-bottom: 0.25rem;">Email Address</label>
                <div style="font-size: 1.1rem;">
                    <a href="mailto:<?php echo $donor->email; ?>" style="color: var(--primary); text-decoration: none;">
                        <?php echo $donor->email; ?>
                    </a>
                </div>
            </div>
            
            <div>
                <label style="display: block; color: var(--text-secondary); font-size: 0.85rem; margin-bottom: 0.25rem;">Phone Number</label>
                <div style="font-size: 1.1rem;">
                    <a href="tel:<?php echo $donor->phone; ?>" style="color: var(--primary); text-decoration: none;">
                        <?php echo $donor->phone; ?>
                    </a>
                </div>
            </div>
            
            <div>
                <label style="display: block; color: var(--text-secondary); font-size: 0.85rem; margin-bottom: 0.25rem;">Location</label>
                <div style="font-size: 1.1rem;">
                    <?php echo $donor->city; ?>, <?php echo $donor->state; ?>
                </div>
            </div>
            
            <div style="grid-column: span 2;">
                <label style="display: block; color: var(--text-secondary); font-size: 0.85rem; margin-bottom: 0.25rem;">Full Address</label>
                <div style="font-size: 1.1rem; background: rgba(255,255,255,0.05); padding: 1rem; border-radius: var(--radius-md);">
                    <i class="fas fa-map-marker-alt" style="color: var(--danger); margin-right: 0.5rem;"></i>
                    <?php echo $donor->address; ?>
                    <?php if($donor->pincode): ?> - <?php echo $donor->pincode; ?><?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include '../../partials/layout.php';
?>
