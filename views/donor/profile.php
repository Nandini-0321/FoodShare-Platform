<?php
// Include required files
require_once '../../models/User.php';

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'donor') {
    header('location: /FoodShare/views/auth/login.php');
    exit;
}

$userModel = new User();
$userId = $_SESSION['user_id'];
$currentUser = $userModel->getUserById($userId);

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['update_profile'])) {
        // Sanitize POST data
        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        
        $updateData = [
            'full_name' => trim($_POST['full_name']),
            'email' => trim($_POST['email']),
            'phone' => trim($_POST['phone']),
            'organization_name' => trim($_POST['organization_name'] ?? ''),
            'address' => trim($_POST['address']),
            'city' => trim($_POST['city']),
            'state' => trim($_POST['state']),
            'pincode' => trim($_POST['pincode'])
        ];
        
        if($userModel->updateUser($userId, $updateData)) {
            $success_msg = "Profile updated successfully!";
            $currentUser = $userModel->getUserById($userId); // Refresh user data
            $_SESSION['user_name'] = $updateData['full_name']; // Update session
        } else {
            $error_msg = "Failed to update profile. Please try again.";
        }
    } elseif(isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Verify current password
        if($userModel->verifyPassword($userId, $current_password)) {
            if($new_password === $confirm_password) {
                if(strlen($new_password) >= 6) {
                    if($userModel->updatePassword($userId, $new_password)) {
                        $success_msg = "Password changed successfully!";
                    } else {
                        $error_msg = "Failed to change password.";
                    }
                } else {
                    $error_msg = "Password must be at least 6 characters.";
                }
            } else {
                $error_msg = "New passwords do not match.";
            }
        } else {
            $error_msg = "Current password is incorrect.";
        }
    }
}

// Page Configuration
$pageTitle = 'My Profile - FoodShare';
$pageHeading = 'My Profile';
$userType = 'donor';
$userName = $currentUser->full_name;

// Start output buffering for content
ob_start();
?>

<style>
    .profile-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: var(--radius-lg);
        padding: 3rem 2rem;
        color: white;
        display: flex;
        align-items: center;
        gap: 2rem;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
    }
    
    .profile-header::after {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }
    
    .profile-avatar {
        width: 100px;
        height: 100px;
        background: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        color: #667eea;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        position: relative;
        z-index: 1;
    }
    
    .profile-info {
        position: relative;
        z-index: 1;
    }
    
    .profile-name {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        font-family: var(--font-heading);
    }
    
    .profile-role {
        font-size: 1rem;
        opacity: 0.9;
        background: rgba(255,255,255,0.2);
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        display: inline-block;
    }
    
    .info-group {
        margin-bottom: 1.5rem;
    }
    
    .info-label {
        color: var(--text-secondary);
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.25rem;
    }
    
    .info-value {
        font-size: 1.1rem;
        color: var(--text-primary);
        font-weight: 500;
        border-bottom: 1px solid rgba(255,255,255,0.05);
        padding-bottom: 0.5rem;
    }
    
    .edit-mode input.info-value {
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 4px;
        padding: 0.5rem;
        width: 100%;
        color: white;
    }
    
    .edit-mode input.info-value:focus {
        border-color: var(--primary);
        outline: none;
    }
    
    .section-title {
        font-size: 1.2rem;
        font-weight: 600;
        margin-bottom: 1.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        color: var(--primary);
    }
</style>

<!-- Status Messages -->
<?php if(isset($success_msg)): ?>
<div class="alert alert-success mb-md">
    <i class="fas fa-check-circle"></i> <?php echo $success_msg; ?>
</div>
<?php endif; ?>

<?php if(isset($error_msg)): ?>
<div class="alert alert-danger mb-md">
    <i class="fas fa-exclamation-circle"></i> <?php echo $error_msg; ?>
</div>
<?php endif; ?>

<div class="profile-header">
    <div class="profile-avatar">
        <i class="fas fa-user"></i>
    </div>
    <div class="profile-info">
        <h1 class="profile-name"><?php echo $currentUser->full_name; ?></h1>
        <span class="profile-role"><i class="fas fa-hand-holding-heart"></i> Donor Account</span>
        <div style="margin-top: 0.5rem; font-size: 0.9rem; opacity: 0.8;">
            Member since <?php echo date('F Y', strtotime($currentUser->created_at)); ?>
        </div>
    </div>
</div>

<div class="grid grid-3 gap-lg">
    <!-- Main Profile Details (2/3 width) -->
    <div style="grid-column: span 2;">
        <div class="card">
            <div class="card-header flex-between">
                <h2 class="card-title"><i class="fas fa-id-card"></i> Account Details</h2>
                <button type="button" class="btn btn-outline btn-sm" id="editToggleBtn" onclick="toggleEditMode()">
                    <i class="fas fa-edit"></i> Edit Profile
                </button>
            </div>
            
            <div class="card-body">
                <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>" id="profileForm">
                    
                    <h3 class="section-title">Personal Information</h3>
                    <div class="grid grid-2 gap-lg mb-lg">
                        <div class="info-group">
                            <div class="info-label">Full Name</div>
                            <div class="view-mode info-value"><?php echo $currentUser->full_name; ?></div>
                            <div class="edit-mode" style="display:none;">
                                <input type="text" name="full_name" class="info-value" value="<?php echo $currentUser->full_name; ?>" required>
                            </div>
                        </div>
                        
                        <div class="info-group">
                            <div class="info-label">Email Address</div>
                            <div class="view-mode info-value"><?php echo $currentUser->email; ?></div>
                            <div class="edit-mode" style="display:none;">
                                <input type="email" name="email" class="info-value" value="<?php echo $currentUser->email; ?>" required>
                            </div>
                        </div>
                        
                        <div class="info-group">
                            <div class="info-label">Phone Number</div>
                            <div class="view-mode info-value"><?php echo $currentUser->phone; ?></div>
                            <div class="edit-mode" style="display:none;">
                                <input type="tel" name="phone" class="info-value" value="<?php echo $currentUser->phone; ?>" required>
                            </div>
                        </div>
                        
                        <div class="info-group">
                            <div class="info-label">Organization Name</div>
                            <div class="view-mode info-value"><?php echo $currentUser->organization_name ?: 'N/A'; ?></div>
                            <div class="edit-mode" style="display:none;">
                                <input type="text" name="organization_name" class="info-value" value="<?php echo $currentUser->organization_name; ?>">
                            </div>
                        </div>
                    </div>
                    
                    <h3 class="section-title">Address Details</h3>
                    <div class="grid grid-2 gap-lg">
                        <div class="info-group" style="grid-column: span 2;">
                            <div class="info-label">Address</div>
                            <div class="view-mode info-value"><?php echo $currentUser->address; ?></div>
                            <div class="edit-mode" style="display:none;">
                                <textarea name="address" class="info-value" rows="2" required><?php echo $currentUser->address; ?></textarea>
                            </div>
                        </div>
                        
                        <div class="info-group">
                            <div class="info-label">City</div>
                            <div class="view-mode info-value"><?php echo $currentUser->city; ?></div>
                            <div class="edit-mode" style="display:none;">
                                <input type="text" name="city" class="info-value" value="<?php echo $currentUser->city; ?>" required>
                            </div>
                        </div>
                        
                        <div class="info-group">
                            <div class="info-label">State</div>
                            <div class="view-mode info-value"><?php echo $currentUser->state; ?></div>
                            <div class="edit-mode" style="display:none;">
                                <input type="text" name="state" class="info-value" value="<?php echo $currentUser->state; ?>" required>
                            </div>
                        </div>
                        
                        <div class="info-group">
                            <div class="info-label">Pincode</div>
                            <div class="view-mode info-value"><?php echo $currentUser->pincode; ?></div>
                            <div class="edit-mode" style="display:none;">
                                <input type="text" name="pincode" class="info-value" value="<?php echo $currentUser->pincode; ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="edit-mode mt-lg" style="display:none;">
                        <div class="flex-end gap-md">
                            <button type="button" class="btn btn-ghost" onclick="toggleEditMode()">Cancel</button>
                            <button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Security (1/3 width) -->
    <div>
        <div class="card">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-lock"></i> Security</h2>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                    <div class="form-group">
                        <label class="form-label">Current Password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">New Password</label>
                        <input type="password" name="new_password" class="form-control" minlength="6" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" minlength="6" required>
                    </div>
                    
                    <button type="submit" name="change_password" class="btn btn-secondary" style="width: 100%; justify-content: center; margin-top: 1rem;">
                        Update Password
                    </button>
                </form>
            </div>
        </div>
        
        <div class="card mt-lg" style="background: rgba(246, 79, 89, 0.1); border: 1px solid rgba(246, 79, 89, 0.2);">
            <div class="card-body">
                <h3 style="color: #f64f59; font-size: 1.1rem; margin-bottom: 0.5rem;">Danger Zone</h3>
                <p style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 1rem;">Once you delete your account, there is no going back. Please be certain.</p>
                <button class="btn btn-outline" style="width: 100%; border-color: #f64f59; color: #f64f59;">Delete Account</button>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleEditMode() {
        const viewModes = document.querySelectorAll('.view-mode');
        const editModes = document.querySelectorAll('.edit-mode');
        const btn = document.getElementById('editToggleBtn');
        
        if(btn.innerText.includes('Edit')) {
            // Switch to Edit
            viewModes.forEach(el => el.style.display = 'none');
            editModes.forEach(el => el.style.display = 'block');
            btn.innerHTML = '<i class="fas fa-times"></i> Cancel';
        } else {
            // Switch to View
            viewModes.forEach(el => el.style.display = 'block');
            editModes.forEach(el => el.style.display = 'none');
            btn.innerHTML = '<i class="fas fa-edit"></i> Edit Profile';
        }
    }
</script>

<?php
// Get content and store in variable
$content = ob_get_clean();

// Include the layout
include '../../partials/layout.php';
?>
