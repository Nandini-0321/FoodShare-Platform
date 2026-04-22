<?php
// Include Controller
require_once '../../controllers/VolunteerController.php';

// Init Controller
$controller = new VolunteerController();
$data = $controller->getDashboardData();

// Extract data
$availablePickups = $data['available_pickups'];
$currentUser = $data['user'];

// Page Configuration
$pageTitle = 'Available Pickups - FoodShare';
$pageHeading = 'Available Pickups';
$userType = 'volunteer';
$userName = $currentUser->full_name;

// Start output buffering
ob_start();
?>

<div class="card">
    <div class="card-header flex-between">
        <h2 class="card-title">
            <i class="fas fa-map-marked-alt"></i> Available Pickups
        </h2>
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search location...">
        </div>
    </div>
    
    <div class="card-body">
        <?php if(empty($availablePickups)): ?>
            <div class="text-center" style="padding: 3rem;">
                <i class="fas fa-check-circle" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
                <p>No pickups available right now.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-2 gap-lg">
                <?php foreach($availablePickups as $pickup): ?>
                    <div class="card card-glass" style="border: 1px solid rgba(255,255,255,0.1);">
                        <div class="flex-between mb-md">
                            <span class="badge badge-primary">New Request</span>
                            <span style="font-size: 0.9rem; color: var(--text-secondary);"><?php echo $pickup->pickup_city; ?></span>
                        </div>
                        
                        <div class="grid grid-2 gap-md mb-md">
                            <div>
                                <h5 style="color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase;">From</h5>
                                <p style="font-weight: 600;"><?php echo $pickup->donor_name; ?></p>
                                <p style="font-size: 0.9rem; color: var(--text-secondary);"><?php echo $pickup->pickup_address; ?></p>
                            </div>
                            <div>
                                <h5 style="color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase;">To</h5>
                                <p style="font-weight: 600;"><?php echo $pickup->ngo_name; ?></p>
                                <p style="font-size: 0.9rem; color: var(--text-secondary);"><?php echo $pickup->ngo_address; ?></p>
                            </div>
                        </div>
                        
                        <div class="mb-lg" style="background: var(--surface); padding: var(--spacing-sm); border-radius: var(--radius-sm);">
                            <p style="font-size: 0.9rem;">
                                <i class="fas fa-box"></i> <strong>Item:</strong> <?php echo $pickup->food_name; ?> (<?php echo $pickup->quantity; ?>)
                            </p>
                        </div>
                        
                        <form action="dashboard.php" method="POST">
                            <input type="hidden" name="action" value="accept">
                            <input type="hidden" name="donation_id" value="<?php echo $pickup->id; ?>">
                            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                                Accept Pickup
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include '../../partials/layout.php';
?>
