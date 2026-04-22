<?php
// Include Controller
require_once '../../controllers/DonorController.php';

// Init Controller
$controller = new DonorController();

// Handle request accept/reject
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['accept_request'])) {
        if($controller->handleRequest($_POST['donation_id'], 'accept')) {
            $status_msg = "Request accepted! The NGO has been notified.";
            $status_type = "success";
        } else {
            $status_msg = "Failed to accept request.";
            $status_type = "error";
        }
    } elseif(isset($_POST['reject_request'])) {
        if($controller->handleRequest($_POST['donation_id'], 'reject')) {
            $status_msg = "Request rejected.";
            $status_type = "success";
        } else {
            $status_msg = "Failed to reject request.";
            $status_type = "error";
        }
    } elseif(isset($_POST['cancel_donation'])) {
        $reason = $_POST['cancel_reason'] ?? 'Cancelled by donor';
        if($controller->handleCancellation($_POST['donation_id'], $reason)) {
            $status_msg = "Donation cancelled successfully.";
            $status_type = "success";
        } else {
            $status_msg = "Failed to cancel donation.";
            $status_type = "error";
        }
    }
}

$data = $controller->getDashboardData();

// Debug: Check what we're getting
// echo "Debug - Active requests count: " . count($data['active_requests']) . "<br>";
//  if(!empty($data['active_requests'])) {
//     echo "First request: <pre>" . print_r($data['active_requests'][0], true) . "</pre>";
// }


// Extract data for view
$stats = $data['stats'];
$recentDonations = $data['recent_donations'];
$activeRequests = $data['active_requests'];
$currentUser = $data['user'];

// Page Configuration
$pageTitle = 'Donor Dashboard - FoodShare';
$pageHeading = 'Donor Dashboard';
$userType = 'donor';
$userName = $currentUser->full_name;

// Start output buffering for content
ob_start();
?>

<!-- Status Messages -->
<?php if(isset($_GET['status']) && $_GET['status'] == 'donation_added'): ?>
<div class="alert alert-success mb-md" style="background: rgba(79, 172, 254, 0.1); color: #4facfe; border: 1px solid rgba(79, 172, 254, 0.2); padding: 1rem; border-radius: var(--radius-md);">
    <i class="fas fa-check-circle"></i> Donation added successfully! Thank you for your kindness.
</div>
<?php endif; ?>

<?php if(isset($success_msg)): ?>
<div class="alert alert-success mb-md" style="background: rgba(79, 172, 254, 0.1); color: #4facfe; border: 1px solid rgba(79, 172, 254, 0.2); padding: 1rem; border-radius: var(--radius-md);">
    <i class="fas fa-check-circle"></i> <?php echo $success_msg; ?>
</div>
<?php endif; ?>

<?php if(isset($error_msg)): ?>
<div class="alert alert-danger mb-md" style="background: rgba(246, 79, 89, 0.1); color: #f64f59; border: 1px solid rgba(246, 79, 89, 0.2); padding: 1rem; border-radius: var(--radius-md);">
    <i class="fas fa-exclamation-circle"></i> <?php echo $error_msg; ?>
</div>
<?php endif; ?>

<!-- Dashboard Stats -->
<div class="grid grid-4 mb-lg">
    <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="stat-value"><?php echo $stats['total_donations']; ?></div>
        <div class="stat-label">Total Donations</div>
        <i class="fas fa-gift stat-icon"></i>
    </div>
    
    <div class="stat-card" style="background: linear-gradient(135deg, #f64f59 0%, #c471ed 100%);">
        <div class="stat-value"><?php echo $stats['meals_provided']; ?></div>
        <div class="stat-label">Meals Provided</div>
        <i class="fas fa-utensils stat-icon"></i>
    </div>
    
    <div class="stat-card" style="background: linear-gradient(135deg, #ffa726 0%, #fb8c00 100%);">
        <div class="stat-value"><?php echo $stats['ngos_helped']; ?></div>
        <div class="stat-label">NGOs Helped</div>
        <i class="fas fa-hands-helping stat-icon"></i>
    </div>
    
    <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #4facfe 100%);">
        <div class="stat-value"><?php echo round($stats['impact_score']); ?>%</div>
        <div class="stat-label">Impact Score</div>
        <i class="fas fa-chart-line stat-icon"></i>
    </div>
</div>

<!-- Quick Actions -->
<div class="card mb-lg">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-bolt"></i> Quick Actions
        </h2>
    </div>
    <div class="card-body">
        <div class="grid grid-3 gap-md">
            <a href="new-donation.php" class="btn btn-primary" style="justify-content: center;">
                <i class="fas fa-plus-circle"></i>
                New Donation
            </a>
            <a href="donations.php" class="btn btn-secondary" style="justify-content: center;">
                <i class="fas fa-history"></i>
                View History
            </a>
            <a href="impact.php" class="btn btn-accent" style="justify-content: center;">
                <i class="fas fa-chart-bar"></i>
                View Reports
            </a>
        </div>
    </div>
</div>

<!-- Recent Donations & Active Requests -->
<div class="grid grid-2 gap-lg">
    <!-- Recent Donations -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fas fa-history"></i> Recent Donations
            </h2>
            <a href="donations.php" class="btn btn-sm btn-ghost">View All</a>
        </div>
        <div class="card-body">
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Food</th>
                            <th>Qty</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($recentDonations)): ?>
                            <tr>
                                <td colspan="4" class="text-center" style="padding: 2rem;">
                                    <i class="fas fa-box-open" style="font-size: 2rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
                                    <p>No donations yet. Make your first one!</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($recentDonations as $donation): ?>
                                <tr>
                                    <td><?php echo date('M d', strtotime($donation->created_at)); ?></td>
                                    <td><?php echo $donation->food_name; ?></td>
                                    <td><?php echo $donation->quantity . ' ' . $donation->unit; ?></td>
                                    <td>
                                        <?php 
                                            $badgeClass = 'badge-info';
                                            if($donation->status == 'delivered') $badgeClass = 'badge-success';
                                            if($donation->status == 'available') $badgeClass = 'badge-primary';
                                            if($donation->status == 'expired') $badgeClass = 'badge-danger';
                                            if($donation->status == 'requested') $badgeClass = 'badge-warning';
                                            if($donation->status == 'confirmed') $badgeClass = 'badge-info';
                                            if($donation->status == 'cancelled') $badgeClass = 'badge-secondary';
                                        ?>
                                        <span class="badge <?php echo $badgeClass; ?>"><?php echo ucfirst($donation->status); ?></span>
                                        
                                        <!-- Action Buttons -->
                                        <?php if(in_array($donation->status, ['confirmed', 'picked_up', 'delivered'])): ?>
                                            <div style="display: inline-flex; gap: 5px; margin-left: 10px;">
                                                <button class="btn btn-sm btn-ghost" onclick="chatSystem.openChat(<?php echo $donation->id; ?>)" title="Chat">
                                                    <i class="fas fa-comments" style="color: #ff6b6b;"></i>
                                                </button>
                                                <?php if(in_array($donation->status, ['picked_up', 'delivered'])): ?>
                                                    <button class="btn btn-sm btn-ghost" onclick="trackingSystem.viewTracking(<?php echo $donation->id; ?>)" title="Track Delivery">
                                                        <i class="fas fa-map-marker-alt" style="color: #4834d4;"></i>
                                                    </button>
                                                <?php endif; ?>
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
    
    <!-- Active Requests -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fas fa-bell"></i> Active Requests
            </h2>
            <span class="badge badge-primary"><?php echo count($activeRequests); ?> Active</span>
        </div>
        <div class="card-body">
            <div class="request-list">
                <?php if(empty($activeRequests)): ?>
                    <div class="text-center" style="padding: 2rem;">
                        <i class="fas fa-check-circle" style="font-size: 2rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
                        <p>No active requests at the moment.</p>
                    </div>
                <?php else: ?>
                    <?php foreach($activeRequests as $request): ?>
                        <div class="request-item" style="background: var(--surface); padding: var(--spacing-md); border-radius: var(--radius-md); margin-bottom: var(--spacing-sm);">
                            <div class="flex-between mb-sm">
                                <div>
                                    <h4 style="margin-bottom: 0.25rem; font-weight: 600;"><?php echo $request->ngo_name ?? 'NGO Request'; ?></h4>
                                    <p style="color: var(--text-secondary); font-size: 0.85rem; margin: 0;">
                                        <i class="fas fa-tag"></i> <?php echo ucfirst($request->ngo_type ?? 'Organization'); ?>
                                    </p>
                                </div>
                                <span class="badge badge-warning">Pending</span>
                            </div>
                            <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: var(--spacing-sm);">
                                Request for: <?php echo $request->food_name; ?>
                            </p>
                            <div class="flex gap-sm">
                                <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" style="display: inline; flex: 1;">
                                    <input type="hidden" name="donation_id" value="<?php echo $request->id; ?>">
                                    <button type="submit" name="accept_request" class="btn btn-sm" style="width: 100%; background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; justify-content: center;">
                                        <i class="fas fa-check"></i> Accept
                                    </button>
                                </form>
                                <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" style="display: inline; flex: 1;" onsubmit="return confirm('Are you sure you want to reject this request?');">
                                    <input type="hidden" name="donation_id" value="<?php echo $request->id; ?>">
                                    <button type="submit" name="reject_request" class="btn btn-sm" style="width: 100%; background: linear-gradient(135deg, #f64f59 0%, #c471ed 100%); color: white; justify-content: center;">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                </form>
                            </div>
                            <!-- Cancellation Form (Hidden by default, could be a modal, but for now simple button) -->
                            <!-- Actually, 'Reject' is essentially cancelling the request before acceptance. 
                                 'Cancel' is for AFTER acceptance (confirmed status). 
                                 So we need to check status. -->
                            <?php if($request->status == 'confirmed'): ?>
                                <div style="margin-top: 0.5rem;">
                                    <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" onsubmit="return confirm('Are you sure you want to cancel this confirmed donation?');">
                                        <input type="hidden" name="donation_id" value="<?php echo $request->id; ?>">
                                        <input type="hidden" name="cancel_reason" value="Cancelled by donor">
                                        <button type="submit" name="cancel_donation" class="btn btn-sm btn-ghost" style="width: 100%; color: var(--text-secondary); justify-content: center;">
                                            <i class="fas fa-ban"></i> Cancel Donation
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
// Get content and store in variable
$content = ob_get_clean();

// Include the layout
include '../../partials/layout.php';
?>
