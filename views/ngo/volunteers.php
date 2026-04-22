<?php
// Include Controller
require_once '../../controllers/NgoController.php';

// Init Controller
$controller = new NgoController();
$volunteers = $controller->getVolunteers();

// Page Configuration
$pageTitle = 'Our Volunteers - FoodShare';
$pageHeading = 'Our Volunteers';
$userType = 'ngo';
$userName = $_SESSION['user_name'] ?? 'NGO User';

// Start output buffering
ob_start();
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-users"></i> Volunteers Who Helped Us
        </h2>
    </div>
    <div class="card-body">
        <?php if(empty($volunteers)): ?>
            <div class="text-center" style="padding: 3rem;">
                <i class="fas fa-hands-helping" style="font-size: 3rem; color: var(--text-secondary); opacity: 0.3; margin-bottom: 1rem;"></i>
                <p style="color: var(--text-secondary);">No volunteers have delivered to your organization yet.</p>
                <p style="font-size: 0.9rem; color: var(--text-secondary);">Once you receive donations, volunteer details will appear here.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-3 gap-md">
                <?php foreach($volunteers as $volunteer): ?>
                    <div class="card card-glass">
                        <div class="text-center mb-md">
                            <div style="width: 80px; height: 80px; background: var(--surface); border-radius: 50%; margin: 0 auto 1rem; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-user" style="font-size: 2rem; color: var(--primary);"></i>
                            </div>
                            <h3 style="margin-bottom: 0.25rem;"><?php echo $volunteer->full_name; ?></h3>
                            <div class="rating" style="color: #ffa726; font-size: 0.9rem;">
                                <?php 
                                    $rating = $volunteer->rating ?? 5;
                                    for($i=1; $i<=5; $i++) {
                                        echo $i <= $rating ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                                    }
                                ?>
                            </div>
                        </div>
                        <div style="text-align: center;">
                            <p style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 0.5rem;">
                                <i class="fas fa-shipping-fast"></i> <?php echo $volunteer->deliveries_count ?? 0; ?> Deliveries to us
                            </p>
                            <button class="btn btn-sm btn-outline" style="margin-top: 0.5rem;">View Profile</button>
                        </div>
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
