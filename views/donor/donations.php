<?php
// Include Controller
require_once '../../controllers/DonorController.php';

// Init Controller
$controller = new DonorController();

// Handle delete action
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_id'])) {
    if($controller->deleteDonation($_POST['delete_id'])) {
        $success_msg = "Donation deleted successfully!";
    } else {
        $error_msg = "Failed to delete donation. It may have already been requested.";
    }
}

// We can reuse getDashboardData or add a specific method for all donations
// For now, let's assume we add a method or just use the model directly in controller
// Let's add a method to controller for this page
$donations = $controller->getAllDonations();

// Page Configuration
$pageTitle = 'My Donations - FoodShare';
$pageHeading = 'My Donations';
$userType = 'donor';
$userName = $_SESSION['user_name'] ?? 'Donor';

// Start output buffering for content
ob_start();
?>

<div class="card">
    <div class="card-header flex-between">
        <h2 class="card-title">
            <i class="fas fa-history"></i> Donation History
        </h2>
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search donations...">
        </div>
    </div>
    
    <?php if(isset($success_msg)): ?>
        <div class="alert alert-success" style="margin: 1rem; background: rgba(79, 172, 254, 0.1); color: #4facfe; border: 1px solid rgba(79, 172, 254, 0.2); padding: 1rem; border-radius: var(--radius-md);">
            <i class="fas fa-check-circle"></i> <?php echo $success_msg; ?>
        </div>
    <?php endif; ?>
    
    <?php if(isset($error_msg)): ?>
        <div class="alert alert-danger" style="margin: 1rem; background: rgba(246, 79, 89, 0.1); color: #f64f59; border: 1px solid rgba(246, 79, 89, 0.2); padding: 1rem; border-radius: var(--radius-md);">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error_msg; ?>
        </div>
    <?php endif; ?>
    
    <div class="card-body">
        <?php if(empty($donations)): ?>
            <div class="text-center" style="padding: 3rem;">
                <i class="fas fa-box-open" style="font-size: 4rem; color: var(--text-secondary); opacity: 0.3; margin-bottom: 1rem;"></i>
                <p style="color: var(--text-secondary); font-size: 1.1rem; margin-bottom: 1rem;">No donations found.</p>
                <a href="new-donation.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Make a Donation
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-3 gap-lg">
                <?php foreach($donations as $donation): ?>
                    <div class="card card-glass" style="padding: 0; overflow: hidden; border: 1px solid rgba(255,255,255,0.1);">
                        <!-- Food Image -->
                        <?php if(!empty($donation->images)): ?>
                            <div style="height: 180px; overflow: hidden; position: relative;">
                                <?php
                                // Check if image is a URL (from Unsplash) or local file path
                                $imageSrc = (strpos($donation->images, 'http') === 0) 
                                    ? $donation->images 
                                    : '../../assets/images/uploads/' . $donation->images;
                                ?>
                                <img src="<?php echo $imageSrc; ?>" alt="<?php echo $donation->food_name; ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                
                                <!-- Status Badge on Image -->
                                <div style="position: absolute; top: 10px; right: 10px;">
                                    <?php 
                                        $badgeClass = 'badge-info';
                                        if($donation->status == 'delivered') $badgeClass = 'badge-success';
                                        if($donation->status == 'available') $badgeClass = 'badge-primary';
                                        if($donation->status == 'expired') $badgeClass = 'badge-danger';
                                        if($donation->status == 'requested') $badgeClass = 'badge-warning';
                                        if($donation->status == 'confirmed') $badgeClass = 'badge-info';
                                        if($donation->status == 'cancelled') $badgeClass = 'badge-secondary';
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?>" style="font-weight: 600;">
                                        <?php echo ucfirst($donation->status); ?>
                                    </span>
                                </div>
                            </div>
                        <?php else: ?>
                            <div style="height: 180px; background: linear-gradient(135deg, var(--surface) 0%, rgba(79, 172, 254, 0.1) 100%); display: flex; align-items: center; justify-content: center; position: relative;">
                                <i class="fas fa-utensils" style="font-size: 3rem; opacity: 0.2;"></i>
                                <div style="position: absolute; top: 10px; right: 10px;">
                        <?php endif; ?>
                        
                        <!-- Card Content -->
                        <div style="padding: var(--spacing-md);">
                            <div class="flex-between mb-sm">
                                <span class="badge badge-ghost" style="font-size: 0.75rem;">
                                    <?php echo ucfirst($donation->food_type); ?>
                                </span>
                                <span style="font-size: 0.75rem; color: var(--text-secondary);">
                                    <i class="fas fa-calendar"></i> <?php echo date('M d', strtotime($donation->created_at)); ?>
                                </span>
                            </div>
                            
                            <h3 style="font-size: 1.2rem; margin-bottom: 0.5rem; font-weight: 600;">
                                <?php echo $donation->food_name; ?>
                            </h3>
                            
                            <div style="margin-bottom: var(--spacing-md); color: var(--text-secondary); font-size: 0.9rem;">
                                <p style="margin-bottom: 0.25rem;">
                                    <i class="fas fa-weight" style="width: 20px;"></i>
                                    <?php echo $donation->quantity . ' ' . $donation->unit; ?>
                                </p>
                                <p style="margin-bottom: 0.25rem;">
                                    <i class="fas fa-clock" style="width: 20px;"></i>
                                    Expires: <?php echo date('M d, H:i', strtotime($donation->expiry_time)); ?>
                                </p>
                                <?php if(!empty($donation->pickup_city)): ?>
                                    <p style="margin-bottom: 0;">
                                        <i class="fas fa-map-marker-alt" style="width: 20px;"></i>
                                        <?php echo $donation->pickup_city; ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="flex gap-sm">
                                <a href="../shared/view-donation.php?id=<?php echo $donation->id; ?>" class="btn btn-sm btn-ghost" style="flex: 1; justify-content: center; text-decoration: none;">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <?php if($donation->status == 'available'): ?>
                                    <a href="edit-donation.php?id=<?php echo $donation->id; ?>" class="btn btn-sm btn-ghost" style="color: #4facfe; text-decoration: none;">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" style="display: inline;" 
                                          onsubmit="return confirm('Are you sure you want to delete this donation?');">
                                        <input type="hidden" name="delete_id" value="<?php echo $donation->id; ?>">
                                        <button type="submit" class="btn btn-sm btn-ghost" style="color: #f64f59;">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                                
                                <?php if(in_array($donation->status, ['confirmed', 'picked_up', 'delivered'])): ?>
                                    <button type="button" class="btn btn-sm btn-ghost" onclick="chatSystem.openChat(<?php echo $donation->id; ?>)" title="Chat" style="color: #ff6b6b;">
                                        <i class="fas fa-comments"></i>
                                    </button>
                                <?php endif; ?>
                                
                                <?php if(in_array($donation->status, ['picked_up', 'delivered'])): ?>
                                    <button type="button" class="btn btn-sm btn-ghost" onclick="trackingSystem.viewTracking(<?php echo $donation->id; ?>)" title="Track" style="color: #4834d4;">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Get content and store in variable
$content = ob_get_clean();

// Include the layout
include '../../partials/layout.php';
?>
