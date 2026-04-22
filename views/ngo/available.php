<?php
// Include Controller
require_once '../../controllers/NgoController.php';

// Init Controller
$controller = new NgoController();

// Handle Request/Cancel Actions
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['action']) && isset($_POST['request_id'])) {
        $donation_id = $_POST['request_id'];
        
        if($_POST['action'] == 'request') {
            if($controller->requestFood($donation_id)) {
                $successMsg = "Request sent successfully! The donor will be notified.";
            } else {
                $errorMsg = "Failed to send request. You may have already requested this donation.";
            }
        } elseif($_POST['action'] == 'cancel') {
            if($controller->cancelRequest($donation_id)) {
                $successMsg = "Request cancelled successfully.";
            } else {
                $errorMsg = "Failed to cancel request.";
            }
        }
    }
}

// Get available food with distance calculation
$availableFood = $controller->getAvailableFood();

// Page Configuration
$pageTitle = 'Order Food - FoodShare';
$pageHeading = 'Order Food';
$userType = 'ngo';
$userName = $_SESSION['user_name'] ?? 'NGO User';

// Start output buffering
ob_start();
?>

<!-- Status Messages -->
<?php if(isset($successMsg)): ?>
<div class="alert alert-success mb-md">
    <i class="fas fa-check-circle"></i> <?php echo $successMsg; ?>
</div>
<?php endif; ?>

<?php if(isset($errorMsg)): ?>
<div class="alert alert-danger mb-md">
    <i class="fas fa-exclamation-circle"></i> <?php echo $errorMsg; ?>
</div>
<?php endif; ?>

<!-- Filters / Header -->
<div class="flex-between mb-lg">
    <div>
        <h2 style="font-family: var(--font-heading); font-size: 1.8rem;">Hungry for Change?</h2>
        <p style="color: var(--text-secondary);">Discover surplus food available near you.</p>
    </div>
    <div class="search-box" style="width: 300px;">
        <i class="fas fa-search"></i>
        <input type="text" placeholder="Search for food, donors...">
    </div>
</div>

<!-- Food Grid -->
<?php if(empty($availableFood)): ?>
    <div class="text-center" style="padding: 4rem;">
        <div style="background: rgba(255,255,255,0.05); width: 100px; height: 100px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
            <i class="fas fa-utensils" style="font-size: 3rem; color: var(--text-secondary); opacity: 0.5;"></i>
        </div>
        <h3 style="margin-bottom: 0.5rem;">No Food Available</h3>
        <p style="color: var(--text-secondary);">There are no donations available right now. Please check back later.</p>
    </div>
<?php else: ?>
    <div class="grid grid-3 gap-lg">
        <?php foreach($availableFood as $food): ?>
            <div class="card card-hover" style="padding: 0; overflow: hidden; border: none; background: var(--bg-secondary); height: 100%; display: flex; flex-direction: column;">
                <!-- Image Section -->
                <div style="height: 200px; position: relative; overflow: hidden;">
                    <?php
                    // Check if image is a URL (from Unsplash) or local file path
                    $imageSrc = (strpos($food->images, 'http') === 0) 
                        ? $food->images 
                        : '../../assets/images/uploads/' . $food->images;
                    ?>
                    <img src="<?php echo $imageSrc; ?>" alt="<?php echo $food->food_name; ?>" 
                         style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;"
                         onmouseover="this.style.transform='scale(1.05)'"
                         onmouseout="this.style.transform='scale(1)'">
                    
                    <!-- Badges -->
                    <div style="position: absolute; top: 1rem; left: 1rem; display: flex; gap: 0.5rem;">
                        <span class="badge" style="background: rgba(0,0,0,0.7); backdrop-filter: blur(4px); color: white; border: none;">
                            <?php echo ucfirst($food->food_type); ?>
                        </span>
                        <?php if(isset($food->distance) && $food->distance !== null): ?>
                            <span class="badge" style="background: rgba(102, 126, 234, 0.9); color: white; border: none;">
                                <i class="fas fa-location-arrow" style="font-size: 0.7rem;"></i> <?php echo $food->distance; ?> km
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Expiry Warning -->
                    <?php 
                        $hoursRemaining = (strtotime($food->expiry_time) - time()) / 3600;
                        if ($hoursRemaining < 4 && $hoursRemaining > 0):
                    ?>
                        <div style="position: absolute; bottom: 0; left: 0; right: 0; background: rgba(246, 79, 89, 0.9); color: white; padding: 0.25rem 1rem; font-size: 0.8rem; text-align: center;">
                            <i class="fas fa-clock"></i> Expiring Soon
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Content Section -->
                <div style="padding: 1.25rem; flex: 1; display: flex; flex-direction: column;">
                    <div class="flex-between mb-xs">
                        <h3 style="font-size: 1.2rem; font-weight: 700; margin: 0;"><?php echo $food->food_name; ?></h3>
                        <div style="text-align: right;">
                            <span style="font-weight: 700; color: var(--primary); font-size: 1.1rem;"><?php echo $food->quantity; ?></span>
                            <span style="font-size: 0.8rem; color: var(--text-secondary); display: block;"><?php echo $food->unit; ?></span>
                        </div>
                    </div>
                    
                    <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-store" style="color: var(--primary);"></i> 
                        <?php echo $food->organization_name ?? $food->donor_name; ?>
                    </p>
                    
                    <div style="margin-top: auto; padding-top: 1rem; border-top: 1px solid rgba(255,255,255,0.05);">
                        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                            <input type="hidden" name="request_id" value="<?php echo $food->id; ?>">
                            <?php if($controller->checkExistingRequest($food->id)): ?>
                                <input type="hidden" name="action" value="cancel">
                                <button type="submit" class="btn btn-outline" style="width: 100%; justify-content: center; border-color: #f64f59; color: #f64f59;">
                                    <i class="fas fa-times"></i> Cancel Request
                                </button>
                            <?php else: ?>
                                <input type="hidden" name="action" value="request">
                                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 0.8rem;">
                                    Add to Request
                                </button>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include '../../partials/layout.php';
?>
