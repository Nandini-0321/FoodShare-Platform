<?php
// Include Controller
require_once '../../controllers/VolunteerController.php';

// Init Controller
$controller = new VolunteerController();
$data = $controller->getAchievements();

$stats = $data['stats'];
$points = $data['points'];
$level = $data['level'];
$nextLevelPoints = $data['next_level_points'];

// Page Configuration
$pageTitle = 'My Achievements - FoodShare';
$pageHeading = 'My Achievements';
$userType = 'volunteer';
$userName = $_SESSION['user_name'] ?? 'Volunteer';

// Start output buffering
ob_start();
?>

<div class="grid grid-3 gap-lg mb-lg">
    <!-- Level Card -->
    <div class="card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
        <div class="card-body text-center" style="padding: 2rem;">
            <i class="fas fa-crown" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.9;"></i>
            <h2 style="font-size: 2.5rem; margin-bottom: 0.5rem;">Level <?php echo $level; ?></h2>
            <p style="opacity: 0.8;">FoodShare Hero</p>
        </div>
    </div>
    
    <!-- Points Card -->
    <div class="card" style="background: linear-gradient(135deg, #f64f59 0%, #c471ed 100%); color: white;">
        <div class="card-body text-center" style="padding: 2rem;">
            <i class="fas fa-star" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.9;"></i>
            <h2 style="font-size: 2.5rem; margin-bottom: 0.5rem;"><?php echo $points; ?></h2>
            <p style="opacity: 0.8;">Total Points</p>
        </div>
    </div>
    
    <!-- Deliveries Card -->
    <div class="card" style="background: linear-gradient(135deg, #ffa726 0%, #fb8c00 100%); color: white;">
        <div class="card-body text-center" style="padding: 2rem;">
            <i class="fas fa-truck" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.9;"></i>
            <h2 style="font-size: 2.5rem; margin-bottom: 0.5rem;"><?php echo $stats['completed_deliveries']; ?></h2>
            <p style="opacity: 0.8;">Deliveries Completed</p>
        </div>
    </div>
</div>

<!-- Progress to Next Level -->
<div class="card mb-lg">
    <div class="card-body">
        <div class="flex-between mb-sm">
            <h4>Progress to Level <?php echo $level + 1; ?></h4>
            <span><?php echo $points; ?> / <?php echo $nextLevelPoints; ?> Points</span>
        </div>
        <div style="height: 10px; background: var(--surface); border-radius: 5px; overflow: hidden;">
            <?php 
                $percentage = ($points / $nextLevelPoints) * 100;
                $percentage = min(100, max(0, $percentage));
            ?>
            <div style="height: 100%; width: <?php echo $percentage; ?>%; background: linear-gradient(90deg, #667eea, #764ba2); border-radius: 5px;"></div>
        </div>
    </div>
</div>

<!-- Badges -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-medal"></i> Earned Badges
        </h2>
    </div>
    <div class="card-body">
        <div class="grid grid-4 gap-md text-center">
            <!-- Badge 1: First Step -->
            <div style="opacity: <?php echo ($stats['completed_deliveries'] >= 1) ? '1' : '0.4'; ?>;">
                <div style="width: 80px; height: 80px; background: #e0f7fa; border-radius: 50%; margin: 0 auto 1rem; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-shoe-prints" style="font-size: 2rem; color: #00bcd4;"></i>
                </div>
                <h4>First Step</h4>
                <p style="font-size: 0.8rem; color: var(--text-secondary);">First Delivery</p>
            </div>
            
            <!-- Badge 2: Helping Hand -->
            <div style="opacity: <?php echo ($stats['completed_deliveries'] >= 10) ? '1' : '0.4'; ?>;">
                <div style="width: 80px; height: 80px; background: #fff3e0; border-radius: 50%; margin: 0 auto 1rem; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-hands-helping" style="font-size: 2rem; color: #ff9800;"></i>
                </div>
                <h4>Helping Hand</h4>
                <p style="font-size: 0.8rem; color: var(--text-secondary);">10 Deliveries</p>
            </div>
            
            <!-- Badge 3: Road Warrior -->
            <div style="opacity: <?php echo ($stats['completed_deliveries'] >= 50) ? '1' : '0.4'; ?>;">
                <div style="width: 80px; height: 80px; background: #e8eaf6; border-radius: 50%; margin: 0 auto 1rem; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-road" style="font-size: 2rem; color: #3f51b5;"></i>
                </div>
                <h4>Road Warrior</h4>
                <p style="font-size: 0.8rem; color: var(--text-secondary);">50 Deliveries</p>
            </div>
            
            <!-- Badge 4: Legend -->
            <div style="opacity: <?php echo ($stats['completed_deliveries'] >= 100) ? '1' : '0.4'; ?>;">
                <div style="width: 80px; height: 80px; background: #fce4ec; border-radius: 50%; margin: 0 auto 1rem; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-crown" style="font-size: 2rem; color: #e91e63;"></i>
                </div>
                <h4>Legend</h4>
                <p style="font-size: 0.8rem; color: var(--text-secondary);">100 Deliveries</p>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include '../../partials/layout.php';
?>
