<?php
require_once '../../controllers/NGOController.php';
require_once '../../partials/layout.php';

// Mock NGO Controller if not exists (for demo)
if (!class_exists('NGOController')) {
    class NGOController {
        public function getProfile($id) {
            // Mock data
            return [
                'ngo_name' => 'Helping Hands Foundation',
                'registration_number' => 'NGO-8822',
                'address' => '123 Charity Lane, Bangalore',
                'phone' => '9876543210',
                'email' => 'ngo@demo.com',
                'impact_stats' => [
                    'meals_served' => 1250,
                    'donations_received' => 45,
                    'people_helped' => 300
                ]
            ];
        }
    }
}

$controller = new NGOController();
$profile = $controller->getProfile($_SESSION['user_id']);
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-4">
            <div class="card profile-card">
                <div class="card-body text-center">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($profile['ngo_name']); ?>&background=random" class="rounded-circle mb-3" width="100">
                    <h4><?php echo $profile['ngo_name']; ?></h4>
                    <p class="text-muted">Registered NGO</p>
                    <div class="d-grid gap-2">
                        <button class="btn btn-primary">Edit Profile</button>
                    </div>
                </div>
            </div>
            
            <div class="card mt-3">
                <div class="card-body">
                    <h5 class="card-title">Impact Stats</h5>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Meals Served
                            <span class="badge bg-primary rounded-pill"><?php echo $profile['impact_stats']['meals_served']; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Donations Received
                            <span class="badge bg-success rounded-pill"><?php echo $profile['impact_stats']['donations_received']; ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Organization Details</h5>
                </div>
                <div class="card-body">
                    <form>
                        <div class="mb-3">
                            <label class="form-label">Organization Name</label>
                            <input type="text" class="form-control" value="<?php echo $profile['ngo_name']; ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Registration Number</label>
                            <input type="text" class="form-control" value="<?php echo $profile['registration_number']; ?>" readonly>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" value="<?php echo $profile['email']; ?>" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" class="form-control" value="<?php echo $profile['phone']; ?>" readonly>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" rows="3" readonly><?php echo $profile['address']; ?></textarea>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
