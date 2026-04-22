<?php
// Include Controller
require_once '../../controllers/NgoController.php';

// Init Controller
$controller = new NgoController();
$data = $controller->getDashboardData();

// Extract data
$stats = $data['stats'];
$myRequests = $data['my_requests'];
$availableFood = $data['available_food'];
$currentUser = $data['user'];

// Page Configuration
$pageTitle = 'NGO Dashboard - FoodShare';
$pageHeading = 'NGO Dashboard';
$userType = 'ngo';
$userName = $currentUser->full_name;

// Start output buffering
ob_start();
?>

<!-- Dashboard Stats -->
<div class="grid grid-3 mb-lg">
    <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="stat-value"><?php echo $stats['active_requests']; ?></div>
        <div class="stat-label">Active Requests</div>
        <i class="fas fa-clock stat-icon"></i>
    </div>
    
    <div class="stat-card" style="background: linear-gradient(135deg, #f64f59 0%, #c471ed 100%);">
        <div class="stat-value"><?php echo $stats['received_donations']; ?></div>
        <div class="stat-label">Donations Received</div>
        <i class="fas fa-box-open stat-icon"></i>
    </div>
    
    <div class="stat-card" style="background: linear-gradient(135deg, #ffa726 0%, #fb8c00 100%);">
        <div class="stat-value"><?php echo $stats['people_fed']; ?></div>
        <div class="stat-label">People Fed</div>
        <i class="fas fa-users stat-icon"></i>
    </div>
</div>

<!-- Available Food Near You -->
<div class="card mb-lg">
    <div class="card-header flex-between">
        <h2 class="card-title">
            <i class="fas fa-map-marker-alt"></i> Available Food Near You
        </h2>
        <a href="available.php" class="btn btn-sm btn-primary">View All</a>
    </div>
    <div class="card-body">
        <div class="grid grid-3 gap-md">
            <?php if(empty($availableFood)): ?>
                <div class="text-center" style="grid-column: 1/-1; padding: 2rem;">
                    <p>No food donations available right now.</p>
                </div>
            <?php else: ?>
                <?php 
                // Show only first 3
                $count = 0;
                foreach($availableFood as $food): 
                    if($count >= 3) break;
                    $count++;
                ?>
                    <div class="card card-glass" style="padding: var(--spacing-md);">
                        <div class="flex-between mb-sm">
                            <span class="badge badge-primary"><?php echo ucfirst($food->food_type); ?></span>
                            <span style="font-size: 0.8rem; color: var(--text-secondary);"><i class="fas fa-clock"></i> <?php echo date('H:i', strtotime($food->expiry_time)); ?></span>
                        </div>
                        <h3 style="font-size: 1.1rem; margin-bottom: 0.5rem;"><?php echo $food->food_name; ?></h3>
                        <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: var(--spacing-sm);">
                            <i class="fas fa-store"></i> <?php echo $food->organization_name ?? $food->donor_name; ?><br>
                            <i class="fas fa-weight"></i> <?php echo $food->quantity . ' ' . $food->unit; ?>
                        </p>
                        <a href="available.php" class="btn btn-sm btn-outline" style="width: 100%; justify-content: center;">Request</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- My Requests -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-list"></i> My Requests
        </h2>
    </div>
    <div class="card-body">
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Food Item</th>
                        <th>Donor</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($myRequests)): ?>
                        <tr>
                            <td colspan="5" class="text-center" style="padding: 2rem;">
                                <p>You haven't requested any donations yet.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($myRequests as $request): ?>
                            <tr>
                                <td><?php echo date('M d', strtotime($request->requested_at)); ?></td>
                                <td><?php echo $request->food_name; ?></td>
                                <td><?php echo $request->organization_name ?? $request->donor_name; ?></td>
                                <td>
                                    <?php 
                                        $badgeClass = 'badge-warning';
                                        if($request->status == 'delivered') $badgeClass = 'badge-success';
                                        if($request->status == 'picked_up') $badgeClass = 'badge-info';
                                        if($request->status == 'confirmed') $badgeClass = 'badge-primary';
                                        if($request->status == 'cancelled') $badgeClass = 'badge-danger';
                                        if($request->status == 'rejected') $badgeClass = 'badge-danger';
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?>"><?php echo ucfirst($request->status); ?></span>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 5px;">
                                        <a href="view-donor.php?id=<?php echo $request->donor_id; ?>" class="btn btn-sm btn-outline" title="View Donor">
                                            <i class="fas fa-user"></i>
                                        </a>
                                        
                                        <?php if(in_array($request->status, ['confirmed', 'picked_up', 'delivered'])): ?>
                                            <button class="btn btn-sm btn-outline" onclick="chatSystem.openChat(<?php echo $request->donation_id; ?>)" title="Chat" style="border-color: #ff6b6b; color: #ff6b6b;">
                                                <i class="fas fa-comments"></i>
                                            </button>
                                            
                                            <?php if(in_array($request->status, ['picked_up', 'delivered'])): ?>
                                                <button class="btn btn-sm btn-outline" onclick="trackingSystem.viewTracking(<?php echo $request->donation_id; ?>)" title="Track Delivery" style="border-color: #4834d4; color: #4834d4;">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                </button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
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
