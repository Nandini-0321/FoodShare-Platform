<?php
// Include Controller
require_once '../../controllers/DonorController.php';

// Init Controller
$controller = new DonorController();
$data = $controller->getDashboardData();

// Extract data
$stats = $data['stats'];
$recentDonations = $data['recent_donations'];
$currentUser = $data['user'];

// Page Configuration
$pageTitle = 'My Impact - FoodShare';
$pageHeading = 'My Impact';
$userType = 'donor';
$userName = $currentUser->full_name;

// Start output buffering for content
ob_start();
?>

<!-- Impact Overview -->
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

<!-- Impact Breakdown -->
<div class="grid grid-2 gap-lg mb-lg">
    <!-- Donation Breakdown by Type -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fas fa-chart-pie"></i> Donation Breakdown
            </h2>
        </div>
        <div class="card-body">
            <div style="padding: 1rem;">
                <div style="margin-bottom: 1.5rem;">
                    <div class="flex-between mb-sm">
                        <span style="color: var(--text-secondary);">Cooked Food</span>
                        <span style="font-weight: 600; color: #667eea;">60%</span>
                    </div>
                    <div style="background: var(--surface); height: 8px; border-radius: 10px; overflow: hidden;">
                        <div style="background: linear-gradient(90deg, #667eea 0%, #764ba2 100%); width: 60%; height: 100%;"></div>
                    </div>
                </div>
                
                <div style="margin-bottom: 1.5rem;">
                    <div class="flex-between mb-sm">
                        <span style="color: var(--text-secondary);">Raw Ingredients</span>
                        <span style="font-weight: 600; color: #f64f59;">30%</span>
                    </div>
                    <div style="background: var(--surface); height: 8px; border-radius: 10px; overflow: hidden;">
                        <div style="background: linear-gradient(90deg, #f64f59 0%, #c471ed 100%); width: 30%; height: 100%;"></div>
                    </div>
                </div>
                
                <div>
                    <div class="flex-between mb-sm">
                        <span style="color: var(--text-secondary);">Bakery Items</span>
                        <span style="font-weight: 600; color: #ffa726;">10%</span>
                    </div>
                    <div style="background: var(--surface); height: 8px; border-radius: 10px; overflow: hidden;">
                        <div style="background: linear-gradient(90deg, #ffa726 0%, #fb8c00 100%); width: 10%; height: 100%;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Achievement Badges -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fas fa-trophy"></i> Achievements
            </h2>
        </div>
        <div class="card-body">
            <div class="grid grid-2 gap-md" style="padding: 1rem;">
                <div class="text-center" style="padding: 1rem; background: var(--surface); border-radius: var(--radius-md);">
                    <i class="fas fa-star" style="font-size: 2rem; color: #ffa726; margin-bottom: 0.5rem;"></i>
                    <p style="font-weight: 600; margin-bottom: 0.25rem;">First Donation</p>
                    <p style="font-size: 0.85rem; color: var(--text-secondary);">Unlocked</p>
                </div>
                
                <div class="text-center" style="padding: 1rem; background: var(--surface); border-radius: var(--radius-md);">
                    <i class="fas fa-fire" style="font-size: 2rem; color: #f64f59; margin-bottom: 0.5rem;"></i>
                    <p style="font-weight: 600; margin-bottom: 0.25rem;">10 Donations</p>
                    <p style="font-size: 0.85rem; color: var(--text-secondary);">
                        <?php echo $stats['total_donations'] >= 10 ? 'Unlocked' : ($stats['total_donations'] . '/10'); ?>
                    </p>
                </div>
                
                <div class="text-center" style="padding: 1rem; background: var(--surface); border-radius: var(--radius-md);">
                    <i class="fas fa-heart" style="font-size: 2rem; color: #c471ed; margin-bottom: 0.5rem;"></i>
                    <p style="font-weight: 600; margin-bottom: 0.25rem;">100 Meals</p>
                    <p style="font-size: 0.85rem; color: var(--text-secondary);">
                        <?php echo $stats['meals_provided'] >= 100 ? 'Unlocked' : ($stats['meals_provided'] . '/100'); ?>
                    </p>
                </div>
                
                <div class="text-center" style="padding: 1rem; background: var(--surface); border-radius: var(--radius-md); opacity: 0.5;">
                    <i class="fas fa-crown" style="font-size: 2rem; color: #667eea; margin-bottom: 0.5rem;"></i>
                    <p style="font-weight: 600; margin-bottom: 0.25rem;">Champion</p>
                    <p style="font-size: 0.85rem; color: var(--text-secondary);">
                        <?php echo $stats['total_donations'] >= 50 ? 'Unlocked' : ($stats['total_donations'] . '/50'); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Impact Timeline -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-history"></i> Recent Impact
        </h2>
    </div>
    <div class="card-body">
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Food Donated</th>
                        <th>Quantity</th>
                        <th>People Served</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($recentDonations)): ?>
                        <tr>
                            <td colspan="5" class="text-center" style="padding: 2rem;">
                                <p>No donations yet. Start making an impact today!</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($recentDonations as $donation): ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($donation->created_at)); ?></td>
                                <td><?php echo $donation->food_name; ?></td>
                                <td><?php echo $donation->quantity . ' ' . $donation->unit; ?></td>
                                <td><?php echo $donation->people_served ?? '-'; ?></td>
                                <td>
                                    <?php 
                                        $badgeClass = 'badge-info';
                                        if($donation->status == 'delivered') $badgeClass = 'badge-success';
                                        if($donation->status == 'available') $badgeClass = 'badge-primary';
                                        if($donation->status == 'confirmed') $badgeClass = 'badge-info';
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?>"><?php echo ucfirst($donation->status); ?></span>
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
// Get content and store in variable
$content = ob_get_clean();

// Include the layout
include '../../partials/layout.php';
?>
