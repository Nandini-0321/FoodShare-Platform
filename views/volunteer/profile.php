<?php
require_once '../../controllers/VolunteerController.php';
require_once '../../partials/layout.php';

// Mock Volunteer Controller if not exists
if (!class_exists('VolunteerController')) {
    class VolunteerController {
        public function getProfile($id) {
            return [
                'full_name' => 'John Doe',
                'vehicle_type' => 'Bike',
                'vehicle_number' => 'KA-01-AB-1234',
                'deliveries_completed' => 25,
                'rating' => 4.8,
                'status' => 'Available',
                'email' => 'volunteer@demo.com',
                'phone' => '9876543210'
            ];
        }
    }
}

$controller = new VolunteerController();
$profile = $controller->getProfile($_SESSION['user_id']);
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-4">
            <div class="card profile-card">
                <div class="card-body text-center">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($profile['full_name']); ?>&background=random" class="rounded-circle mb-3" width="100">
                    <h4><?php echo $profile['full_name']; ?></h4>
                    <p class="text-muted">Volunteer Hero</p>
                    <div class="badge bg-success mb-3"><?php echo $profile['status']; ?></div>
                    <div class="d-grid gap-2">
                        <button class="btn btn-primary">Edit Profile</button>
                    </div>
                </div>
            </div>
            
            <div class="card mt-3">
                <div class="card-body">
                    <h5 class="card-title">Performance</h5>
                    <div class="text-center mb-3">
                        <h2 class="display-4"><?php echo $profile['rating']; ?></h2>
                        <div class="text-warning">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </div>
                        <small class="text-muted">Average Rating</small>
                    </div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Deliveries Completed
                            <span class="badge bg-primary rounded-pill"><?php echo $profile['deliveries_completed']; ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Volunteer Details</h5>
                </div>
                <div class="card-body">
                    <form>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control" value="<?php echo $profile['full_name']; ?>" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" class="form-control" value="<?php echo $profile['phone']; ?>" readonly>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" value="<?php echo $profile['email']; ?>" readonly>
                        </div>
                        
                        <h6 class="mt-4 mb-3">Vehicle Information</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Vehicle Type</label>
                                <input type="text" class="form-control" value="<?php echo $profile['vehicle_type']; ?>" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Vehicle Number</label>
                                <input type="text" class="form-control" value="<?php echo $profile['vehicle_number']; ?>" readonly>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
