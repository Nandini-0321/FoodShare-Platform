<?php
// Include Controller
require_once '../../controllers/NgoController.php';

// Init Controller
$controller = new NgoController();

// Handle Actions
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['action']) && $_POST['action'] == 'cancel' && isset($_POST['request_id'])) {
        if($controller->cancelRequest($_POST['request_id'])) {
            $successMsg = "Request cancelled successfully.";
        } else {
            $errorMsg = "Failed to cancel request.";
        }
    }
}

$requests = $controller->getRequests();

// Page Configuration
$pageTitle = 'My Requests - FoodShare';
$pageHeading = 'My Requests';
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

<div class="card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-list"></i> Donation Requests History
        </h2>
    </div>
    <div class="card-body">
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date Requested</th>
                        <th>Food Item</th>
                        <th>Donor</th>
                        <th>Status</th>
                        <th>Last Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($requests)): ?>
                        <tr>
                            <td colspan="6" class="text-center" style="padding: 2rem;">
                                <i class="fas fa-clipboard-list" style="font-size: 2rem; color: var(--text-secondary); margin-bottom: 1rem;"></i>
                                <p>No requests found. <a href="available.php">Browse available food</a> to make a request.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($requests as $request): ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($request->requested_at)); ?></td>
                                <td>
                                    <strong><?php echo $request->food_name; ?></strong><br>
                                    <span style="font-size: 0.85rem; color: var(--text-secondary);"><?php echo $request->quantity . ' ' . $request->unit; ?></span>
                                </td>
                                <td><?php echo $request->organization_name ?? $request->donor_name; ?></td>
                                <td>
                                    <?php 
                                        // Use donation_status as the source of truth
                                        $status = $request->donation_status;
                                        
                                        $badgeClass = 'badge-warning'; // Default for available/pending
                                        
                                        if($status == 'requested') $badgeClass = 'badge-warning';
                                        if($status == 'confirmed') $badgeClass = 'badge-primary';
                                        if($status == 'picked_up') $badgeClass = 'badge-info';
                                        if($status == 'delivered') $badgeClass = 'badge-success';
                                        if($status == 'cancelled') $badgeClass = 'badge-danger';
                                        
                                        // Map status for display
                                        $displayStatus = ucfirst($status);
                                        if($status == 'requested') $displayStatus = 'Pending';
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?>"><?php echo $displayStatus; ?></span>
                                </td>
                                <td>
                                    <?php echo $request->responded_at ? date('M d, H:i', strtotime($request->responded_at)) : '-'; ?>
                                </td>
                                <td>
                                    <?php if($request->status == 'pending'): ?>
                                        <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" onsubmit="return confirm('Are you sure you want to cancel this request?');">
                                            <input type="hidden" name="action" value="cancel">
                                            <input type="hidden" name="request_id" value="<?php echo $request->donation_id; ?>">
                                            <button type="submit" class="btn btn-sm btn-ghost" style="color: #f64f59;">
                                                <i class="fas fa-times"></i> Cancel
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <div style="display: flex; gap: 5px;">
                                            <?php if(in_array($status, ['confirmed', 'picked_up', 'delivered'])): ?>
                                                <button type="button" class="btn btn-sm btn-outline" onclick="chatSystem.openChat(<?php echo $request->donation_id; ?>)" title="Chat" style="border-color: #ff6b6b; color: #ff6b6b;">
                                                    <i class="fas fa-comments"></i>
                                                </button>
                                            <?php endif; ?>
                                            
                                            <?php if(in_array($status, ['picked_up', 'delivered'])): ?>
                                                <button type="button" class="btn btn-sm btn-outline" onclick="trackingSystem.viewTracking(<?php echo $request->donation_id; ?>)" title="Track Delivery" style="border-color: #4834d4; color: #4834d4;">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                </button>
                                            <?php endif; ?>
                                            
                                            <a href="../shared/view-donation.php?id=<?php echo $request->donation_id; ?>" class="btn btn-sm btn-ghost" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    <?php endif; ?>
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
