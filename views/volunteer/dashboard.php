<?php
// Include Controller
require_once '../../controllers/VolunteerController.php';

// Init Controller
$controller = new VolunteerController();

// Handle Actions
if(isset($_POST['action'])) {
    if($_POST['action'] == 'accept' && isset($_POST['donation_id'])) {
        if($controller->acceptPickup($_POST['donation_id'])) {
            $successMsg = "Pickup accepted! Check 'My Tasks' to proceed.";
        } else {
            $errorMsg = "Failed to accept pickup.";
        }
    } elseif($_POST['action'] == 'update_status' && isset($_POST['pickup_id']) && isset($_POST['status'])) {
        if($controller->updateStatus($_POST['pickup_id'], $_POST['status'])) {
            $successMsg = "Status updated successfully!";
        } else {
            $errorMsg = "Failed to update status.";
        }
    }
}

$data = $controller->getDashboardData();

// Extract data
$stats = $data['stats'];
$myTasks = $data['my_tasks'];
$availablePickups = $data['available_pickups'];
$currentUser = $data['user'];

// Page Configuration
$pageTitle = 'Volunteer Dashboard - FoodShare';
$pageHeading = 'Volunteer Dashboard';
$userType = 'volunteer';
$userName = $currentUser->full_name;

// Start output buffering
ob_start();
?>

<!-- Status Messages -->
<?php if(isset($successMsg)): ?>
<div class="alert alert-success mb-md">
    <i class="fas fa-check-circle"></i> <?php echo $successMsg; ?>
</div>
<?php endif; ?>

<!-- Dashboard Stats -->
<div class="grid grid-3 mb-lg">
    <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="stat-value"><?php echo $stats['pending_tasks']; ?></div>
        <div class="stat-label">Pending Tasks</div>
        <i class="fas fa-tasks stat-icon"></i>
    </div>
    
    <div class="stat-card" style="background: linear-gradient(135deg, #f64f59 0%, #c471ed 100%);">
        <div class="stat-value"><?php echo $stats['completed_deliveries']; ?></div>
        <div class="stat-label">Completed Deliveries</div>
        <i class="fas fa-check-double stat-icon"></i>
    </div>
    
    <div class="stat-card" style="background: linear-gradient(135deg, #ffa726 0%, #fb8c00 100%);">
        <?php 
            // Calculate level based on completed deliveries
            $level = floor($stats['completed_deliveries'] / 5) + 1;
        ?>
        <div class="stat-value">Level <?php echo $level; ?></div>
        <div class="stat-label">Volunteer Level</div>
        <i class="fas fa-medal stat-icon"></i>
    </div>
</div>

<!-- My Current Tasks -->
<div class="card mb-lg">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-shipping-fast"></i> My Current Tasks
        </h2>
    </div>
    <div class="card-body">
        <?php if(empty($myTasks)): ?>
            <div class="text-center" style="padding: 2rem;">
                <p>No active tasks. Look for available pickups below!</p>
            </div>
        <?php else: ?>
            <div class="grid grid-2 gap-md">
                <?php foreach($myTasks as $task): 
                    if($task->pickup_status == 'delivered') continue; // Skip completed
                ?>
                    <div class="card card-glass" style="border: 1px solid var(--primary);">
                        <div class="flex-between mb-sm">
                            <span class="badge badge-warning"><?php echo ucfirst(str_replace('_', ' ', $task->pickup_status)); ?></span>
                            <span style="font-size: 0.8rem; color: var(--text-secondary);">ID: #<?php echo $task->donation_id; ?></span>
                        </div>
                        
                        <div class="mb-md">
                            <h4 style="margin-bottom: 0.5rem;">Pickup: <?php echo $task->donor_name; ?></h4>
                            <p style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 0.25rem;">
                                <i class="fas fa-map-marker-alt"></i> <?php echo $task->pickup_address; ?>
                            </p>
                            <p style="font-size: 0.9rem; color: var(--text-secondary);">
                                <i class="fas fa-phone"></i> <?php echo $task->donor_phone; ?>
                            </p>
                        </div>
                        
                        <div class="mb-md">
                            <h4 style="margin-bottom: 0.5rem;">Deliver To: <?php echo $task->ngo_name; ?></h4>
                            <p style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 0.25rem;">
                                <i class="fas fa-map-marker-alt"></i> <?php echo $task->ngo_address; ?>
                            </p>
                            <p style="font-size: 0.9rem; color: var(--text-secondary);">
                                <i class="fas fa-phone"></i> <?php echo $task->ngo_phone; ?>
                            </p>
                        </div>
                        
                        <div class="flex-between" style="gap: 10px; margin-top: 10px;">
                            <!-- Chat Button -->
                            <button type="button" class="btn btn-sm btn-outline" 
                                    onclick="chatSystem.openChat(<?php echo $task->donation_id; ?>)"
                                    style="flex: 1; border-color: #ff6b6b; color: #ff6b6b;">
                                <i class="fas fa-comments"></i> Chat
                            </button>
                            
                            <!-- Tracking Button -->
                            <?php if($task->pickup_status == 'picked_up' || $task->pickup_status == 'assigned'): ?>
                                <button type="button" class="btn btn-sm btn-outline" 
                                        onclick="trackingSystem.startTracking(<?php echo $task->id; ?>, <?php echo $task->donation_id; ?>)"
                                        style="flex: 1; border-color: #4834d4; color: #4834d4;">
                                    <i class="fas fa-location-arrow"></i> Track
                                </button>
                            <?php endif; ?>
                        </div>

                        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" style="margin-top: 10px;">
                            <input type="hidden" name="action" value="update_status">
                            <input type="hidden" name="pickup_id" value="<?php echo $task->id; ?>">
                            
                            <?php if($task->pickup_status == 'assigned'): ?>
                                <button type="submit" name="status" value="picked_up" class="btn btn-primary" style="width: 100%; justify-content: center;">
                                    Mark as Picked Up
                                </button>
                            <?php elseif($task->pickup_status == 'picked_up'): ?>
                                <button type="submit" name="status" value="delivered" class="btn btn-success" style="width: 100%; justify-content: center; background: #4facfe; border: none;">
                                    Mark as Delivered
                                </button>
                            <?php endif; ?>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Available Pickups -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-map-marked-alt"></i> Available Pickups
        </h2>
    </div>
    <div class="card-body">
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>From (Donor)</th>
                        <th>To (NGO)</th>
                        <th>Food Item</th>
                        <th>City</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($availablePickups)): ?>
                        <tr>
                            <td colspan="5" class="text-center" style="padding: 2rem;">
                                <p>No pickups available right now.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($availablePickups as $pickup): ?>
                            <tr>
                                <td><?php echo $pickup->donor_name; ?></td>
                                <td><?php echo $pickup->ngo_name; ?></td>
                                <td><?php echo $pickup->food_name; ?> (<?php echo $pickup->quantity; ?>)</td>
                                <td><?php echo $pickup->pickup_city; ?></td>
                                <td>
                                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                                        <input type="hidden" name="action" value="accept">
                                        <input type="hidden" name="donation_id" value="<?php echo $pickup->id; ?>">
                                        <button type="submit" class="btn btn-sm btn-primary">Accept</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include '../../partials/layout.php';
?>
