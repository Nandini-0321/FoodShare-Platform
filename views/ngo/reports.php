<?php
// Include Controller
require_once '../../controllers/NgoController.php';

// Init Controller
$controller = new NgoController();
$stats = $controller->getStats();

// Page Configuration
$pageTitle = 'Impact Reports - FoodShare';
$pageHeading = 'Impact Reports';
$userType = 'ngo';
$userName = $_SESSION['user_name'] ?? 'NGO User';

// Start output buffering
ob_start();
?>

<div class="grid grid-2 gap-lg mb-lg">
    <!-- Key Metrics -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fas fa-chart-pie"></i> Key Metrics
            </h2>
        </div>
        <div class="card-body">
            <div class="grid grid-2 gap-md">
                <div class="stat-box" style="background: rgba(102, 126, 234, 0.1); padding: 1rem; border-radius: var(--radius-md); text-align: center;">
                    <h3 style="font-size: 2rem; color: #667eea; margin-bottom: 0.5rem;"><?php echo $stats['received_donations']; ?></h3>
                    <p style="color: var(--text-secondary); font-size: 0.9rem;">Total Donations Received</p>
                </div>
                <div class="stat-box" style="background: rgba(246, 79, 89, 0.1); padding: 1rem; border-radius: var(--radius-md); text-align: center;">
                    <h3 style="font-size: 2rem; color: #f64f59; margin-bottom: 0.5rem;"><?php echo $stats['people_fed']; ?></h3>
                    <p style="color: var(--text-secondary); font-size: 0.9rem;">People Fed</p>
                </div>
                <div class="stat-box" style="background: rgba(255, 167, 38, 0.1); padding: 1rem; border-radius: var(--radius-md); text-align: center;">
                    <h3 style="font-size: 2rem; color: #ffa726; margin-bottom: 0.5rem;"><?php echo $stats['active_requests']; ?></h3>
                    <p style="color: var(--text-secondary); font-size: 0.9rem;">Active Requests</p>
                </div>
                <div class="stat-box" style="background: rgba(56, 239, 125, 0.1); padding: 1rem; border-radius: var(--radius-md); text-align: center;">
                    <h3 style="font-size: 2rem; color: #38ef7d; margin-bottom: 0.5rem;">0</h3>
                    <p style="color: var(--text-secondary); font-size: 0.9rem;">Food Waste Saved (kg)</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Monthly Trend (Placeholder Chart) -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fas fa-chart-line"></i> Monthly Trend
            </h2>
        </div>
        <div class="card-body" style="display: flex; align-items: center; justify-content: center; height: 300px;">
            <div style="text-align: center; color: var(--text-secondary);">
                <i class="fas fa-chart-area" style="font-size: 4rem; opacity: 0.2; margin-bottom: 1rem;"></i>
                <p>Chart visualization coming soon</p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-history"></i> Recent Activity
        </h2>
    </div>
    <div class="card-body">
        <p style="color: var(--text-secondary); text-align: center; padding: 2rem;">Activity log will appear here.</p>
    </div>
</div>

<?php
$content = ob_get_clean();
include '../../partials/layout.php';
?>
