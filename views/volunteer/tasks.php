<?php
// Include Controller
require_once '../../controllers/VolunteerController.php';

// Init Controller
$controller = new VolunteerController();

// Handle Actions
if(isset($_POST['action']) && $_POST['action'] == 'update_status') {
    if($controller->updateStatus($_POST['pickup_id'], $_POST['status'])) {
        $successMsg = "Status updated successfully!";
    } else {
        $errorMsg = "Failed to update status.";
    }
}

$data = $controller->getDashboardData();
$myTasks = $data['my_tasks'];
$currentUser = $data['user'];

// Page Configuration
$pageTitle = 'My Tasks - FoodShare';
$pageHeading = 'My Tasks';
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

<div class="card">
    <div class="card-header flex-between">
        <h2 class="card-title">
            <i class="fas fa-tasks"></i> My Delivery Tasks
        </h2>
        <div class="flex gap-sm">
            <button class="btn btn-sm btn-primary">Active</button>
            <button class="btn btn-sm btn-ghost">History</button>
        </div>
    </div>
    
    <div class="card-body">
        <?php if(empty($myTasks)): ?>
            <div class="text-center" style="padding: 3rem;">
                <i class="fas fa-clipboard-list" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
                <p>You don't have any tasks yet.</p>
                <a href="pickups.php" class="btn btn-primary mt-md">Find Pickups</a>
            </div>
        <?php else: ?>
            <div class="grid grid-2 gap-lg">
                <?php foreach($myTasks as $task): ?>
                    <div class="card card-glass" style="border: 1px solid <?php echo $task->pickup_status == 'delivered' ? 'rgba(255,255,255,0.1)' : 'var(--primary)'; ?>;">
                        <div class="flex-between mb-md">
                            <?php 
                                $badgeClass = 'badge-warning';
                                if($task->pickup_status == 'delivered') $badgeClass = 'badge-success';
                                if($task->pickup_status == 'picked_up') $badgeClass = 'badge-info';
                            ?>
                            <span class="badge <?php echo $badgeClass; ?>"><?php echo ucfirst(str_replace('_', ' ', $task->pickup_status)); ?></span>
                            <span style="font-size: 0.8rem; color: var(--text-secondary);"><?php echo date('M d, H:i', strtotime($task->created_at)); ?></span>
                        </div>
                        
                        <div class="mb-md">
                            <div class="flex gap-sm mb-sm">
                                <div style="width: 30px; text-align: center;"><i class="fas fa-user-circle" style="color: var(--primary);"></i></div>
                                <div>
                                    <p style="font-weight: 600; font-size: 0.9rem;">From: <?php echo $task->donor_name; ?></p>
                                    <p style="font-size: 0.85rem; color: var(--text-secondary);"><?php echo $task->pickup_address; ?></p>
                                    <p style="font-size: 0.85rem; color: var(--text-secondary);"><i class="fas fa-phone-alt"></i> <?php echo $task->donor_phone; ?></p>
                                </div>
                            </div>
                            
                            <div class="flex gap-sm">
                                <div style="width: 30px; text-align: center;"><i class="fas fa-hand-holding-heart" style="color: var(--secondary);"></i></div>
                                <div>
                                    <p style="font-weight: 600; font-size: 0.9rem;">To: <?php echo $task->ngo_name; ?></p>
                                    <p style="font-size: 0.85rem; color: var(--text-secondary);"><?php echo $task->ngo_address; ?></p>
                                    <p style="font-size: 0.85rem; color: var(--text-secondary);"><i class="fas fa-phone-alt"></i> <?php echo $task->ngo_phone; ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-lg" style="background: var(--surface); padding: var(--spacing-sm); border-radius: var(--radius-sm);">
                            <p style="font-size: 0.9rem;">
                                <i class="fas fa-box"></i> <strong>Item:</strong> <?php echo $task->food_name; ?> (<?php echo $task->quantity; ?>)
                            </p>
                        </div>
                        
                        <?php if($task->pickup_status != 'delivered'): ?>
                        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                            <input type="hidden" name="action" value="update_status">
                            <input type="hidden" name="pickup_id" value="<?php echo $task->id; ?>">
                            
                            <?php if($task->pickup_status == 'assigned'): ?>
                                <button type="submit" name="status" value="picked_up" class="btn btn-primary" style="width: 100%; justify-content: center;">
                                    <i class="fas fa-box-open"></i> Mark as Picked Up
                                </button>
                            <?php elseif($task->pickup_status == 'picked_up'): ?>
                                <button type="submit" name="status" value="delivered" class="btn btn-success" style="width: 100%; justify-content: center; background: #4facfe; border: none;">
                                    <i class="fas fa-check"></i> Mark as Delivered
                                </button>
                            <?php endif; ?>
                        </form>
                        <?php else: ?>
                            <button class="btn btn-outline" disabled style="width: 100%; justify-content: center; opacity: 0.5;">
                                <i class="fas fa-check-double"></i> Completed
                            </button>
                        <?php endif; ?>
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
