<?php
require_once '../../controllers/DonorController.php';

$controller = new DonorController();
if(!isset($_GET['id'])) {
    header('location: javascript://history.go(-1)');
    exit;
}

$donation = $controller->getDonationById($_GET['id']);
if(!$donation) {
    die("Donation not found");
}

$pageTitle = 'View Donation - FoodShare';
$pageHeading = 'Donation Details';
$userType = $_SESSION['role'] ?? 'guest';
$userName = $_SESSION['user_name'] ?? 'Guest';

ob_start();
?>
<div class="card animate-fade-in" style="max-width: 800px; margin: 0 auto; box-shadow: var(--shadow-xl);">
    <div class="card-header flex-between">
        <h2 style="font-family: var(--font-heading);"><i class="fas fa-eye" style="color: var(--primary);"></i> <?php echo $donation->food_name; ?></h2>
        <a href="javascript:history.back()" class="btn btn-sm btn-ghost"><i class="fas fa-arrow-left"></i> Go Back</a>
    </div>
    <div class="card-body">
        <?php
        $img_src = !empty($donation->images) ? ((strpos($donation->images, 'http') === 0) ? $donation->images : '../../assets/images/uploads/' . $donation->images) : 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600&q=80';
        ?>
        <div style="height: 300px; border-radius: var(--radius-lg); overflow: hidden; margin-bottom: 2rem;">
            <img src="<?php echo $img_src; ?>" style="width: 100%; height: 100%; object-fit: cover;" alt="Food Image">
        </div>
        
        <div class="grid grid-2 gap-lg mb-lg">
            <div>
                <h4 style="color: var(--text-secondary); font-size: 0.9rem; text-transform: uppercase;">Details</h4>
                <ul style="list-style: none; padding: 0;">
                    <li style="margin-bottom: 0.5rem;"><strong>Type:</strong> <?php echo ucfirst($donation->food_type); ?></li>
                    <li style="margin-bottom: 0.5rem;"><strong>Quantity:</strong> <?php echo $donation->quantity . ' ' . $donation->unit; ?></li>
                    <li style="margin-bottom: 0.5rem;"><strong>Serves:</strong> <?php echo $donation->people_served ?? 'Unknown'; ?> People</li>
                    <li style="margin-bottom: 0.5rem;"><strong>Status:</strong> <span class="badge badge-info"><?php echo ucfirst($donation->status); ?></span></li>
                </ul>
            </div>
            <div>
                <h4 style="color: var(--text-secondary); font-size: 0.9rem; text-transform: uppercase;">Logistics</h4>
                <ul style="list-style: none; padding: 0;">
                    <li style="margin-bottom: 0.5rem;"><strong>Listed On:</strong> <?php echo date('M d, Y h:i A', strtotime($donation->created_at)); ?></li>
                    <li style="margin-bottom: 0.5rem;"><strong>Expires On:</strong> <span style="color: #f64f59; font-weight: bold;"><?php echo date('M d, Y h:i A', strtotime($donation->expiry_time)); ?></span></li>
                    <li style="margin-bottom: 0.5rem;"><strong>Pickup City:</strong> <?php echo $donation->pickup_city; ?></li>
                </ul>
            </div>
        </div>
        
        <div style="background: rgba(255,255,255,0.05); padding: 1.5rem; border-radius: var(--radius-md); margin-bottom: 1rem;">
            <h4 style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 0.5rem;">Description</h4>
            <p><?php echo nl2br($donation->description) ?: 'No additional description provided.'; ?></p>
        </div>
        
        <div style="background: rgba(255,255,255,0.05); padding: 1.5rem; border-radius: var(--radius-md);">
            <h4 style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 0.5rem;">Full Address</h4>
            <p><i class="fas fa-map-marker-alt"></i> <?php echo $donation->pickup_address . ', ' . $donation->pickup_city; ?></p>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean();
include '../../partials/layout.php';
?>
