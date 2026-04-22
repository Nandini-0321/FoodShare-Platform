<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Database.php';

class AuthController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    public function register() {
        // Sanitize POST data
        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        
        // Init data
        $data = [
            'name' => trim($_POST['name']),
            'email' => trim($_POST['email']),
            'password' => trim($_POST['password']),
            'confirm_password' => trim($_POST['confirm_password']),
            'type' => trim($_POST['user_type']),
            'phone' => trim($_POST['phone']),
            'address' => trim($_POST['address']),
            'city' => trim($_POST['city']),
            'state' => trim($_POST['state']),
            'pincode' => trim($_POST['pincode']),
            'name_err' => '',
            'email_err' => '',
            'password_err' => '',
            'confirm_password_err' => ''
        ];
        
        // Validate Email
        if(empty($data['email'])) {
            $data['email_err'] = 'Please enter email';
        } else {
            // Check email
            if($this->userModel->findUserByEmail($data['email'])) {
                $data['email_err'] = 'Email is already taken';
            }
        }
        
        // Validate Name
        if(empty($data['name'])) {
            $data['name_err'] = 'Please enter name';
        }
        
        // Validate Password
        if(empty($data['password'])) {
            $data['password_err'] = 'Please enter password';
        } elseif(strlen($data['password']) < 6) {
            $data['password_err'] = 'Password must be at least 6 characters';
        }
        
        // Validate Confirm Password
        if(empty($data['confirm_password'])) {
            $data['confirm_password_err'] = 'Please confirm password';
        } else {
            if($data['password'] != $data['confirm_password']) {
                $data['confirm_password_err'] = 'Passwords do not match';
            }
        }
        
        // Make sure errors are empty
        if(empty($data['email_err']) && empty($data['name_err']) && empty($data['password_err']) && empty($data['confirm_password_err'])) {
            // Validated
            
            // Hash Password
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // Register User
            $userId = $this->userModel->register($data);
            
            if($userId) {
                // Add specific details based on user type
                if($data['type'] == 'donor') {
                    $donorData = [
                        'org_name' => $_POST['org_name'] ?? '',
                        'org_type' => $_POST['org_type'] ?? 'individual'
                    ];
                    $this->userModel->addDonorDetails($userId, $donorData);
                } elseif($data['type'] == 'ngo') {
                    $ngoData = [
                        'ngo_name' => $data['name'], // Usually same as full name for NGO
                        'reg_no' => !empty($_POST['reg_no']) ? $_POST['reg_no'] : 'NGO_' . uniqid(),
                        'ngo_type' => $_POST['ngo_type'] ?? 'other',
                        'website' => $_POST['website'] ?? ''
                    ];
                    $this->userModel->addNgoDetails($userId, $ngoData);
                } elseif($data['type'] == 'volunteer') {
                    $volData = [
                        'vehicle_type' => $_POST['vehicle_type'] ?? 'none',
                        'vehicle_no' => $_POST['vehicle_no'] ?? '',
                        'availability' => $_POST['availability'] ?? 'flexible'
                    ];
                    $this->userModel->addVolunteerDetails($userId, $volData);
                }
                
                // Redirect to login
                header('location: /Foodshare3/views/auth/login.php?status=registered');
            } else {
                die('Something went wrong');
            }
            
        } else {
            // Load view with errors
            // In a real MVC framework, this would be: $this->view('auth/register', $data);
            // For this simple structure, we'll return data to be used in the view
            return $data;
        }
    }
    
    public function login() {
        // Sanitize POST data
        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        
        // Init data
        $data = [
            'email' => trim($_POST['email']),
            'password' => trim($_POST['password']),
            'user_type' => trim($_POST['user_type'] ?? 'donor'),
            'email_err' => '',
            'password_err' => ''
        ];
        
        // Validate Email
        if(empty($data['email'])) {
            $data['email_err'] = 'Please enter email';
        }
        
        // Validate Password
        if(empty($data['password'])) {
            $data['password_err'] = 'Please enter password';
        }
        
        // Check for user/email
        if($this->userModel->findUserByEmail($data['email'])) {
            // User found
        } else {
            $data['email_err'] = 'No user found';
        }
        
        // Make sure errors are empty
        if(empty($data['email_err']) && empty($data['password_err'])) {
            // Validated
            // Check and set logged in user
            $loggedInUser = $this->userModel->login($data['email'], $data['password']);
            
            if($loggedInUser) {
                // Verify user type matches (optional but good for UX if they selected it)
                if($loggedInUser->user_type != $data['user_type'] && $data['user_type'] != 'admin') { 
                    // Allow admin to login even if they selected something else? No, strict check is better.
                    // Actually, if I am an admin but select 'donor', I should probably be told "You are an Admin, please select Admin".
                    // But for simplicity, let's just check if it matches.
                    
                    // Exception: If I am an admin, I might want to login as admin.
                    // Let's strictly enforce it.
                    if($loggedInUser->user_type != $data['user_type']) {
                         $data['email_err'] = 'This email is registered as ' . ucfirst($loggedInUser->user_type) . ', not ' . ucfirst($data['user_type']);
                         return $data;
                    }
                }
                
                // Create Session
                $this->createUserSession($loggedInUser);
            } else {
                $data['password_err'] = 'Password incorrect';
                return $data;
            }
        } else {
            return $data;
        }
    }
    
    public function createUserSession($user) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_email'] = $user->email;
        $_SESSION['user_name'] = $user->full_name;
        // The codebase interchangeably uses user_type and role. Make them identical.
        $_SESSION['user_type'] = $user->user_type;
        $_SESSION['role'] = $user->user_type;
        
        // Redirect based on user type
        if($user->user_type == 'donor') {
            header('location: /Foodshare3/views/donor/dashboard.php');
        } elseif($user->user_type == 'ngo') {
            header('location: /Foodshare3/views/ngo/dashboard.php');
        } elseif($user->user_type == 'volunteer') {
            header('location: /Foodshare3/views/volunteer/dashboard.php');
        } elseif($user->user_type == 'admin') {
            header('location: /Foodshare3/views/admin/dashboard.php');
        } else {
            header('location: /Foodshare3/index.php');
        }
    }
    
    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        unset($_SESSION['user_id']);
        unset($_SESSION['user_email']);
        unset($_SESSION['user_name']);
        unset($_SESSION['user_type']);
        unset($_SESSION['role']);
        session_destroy();
        header('location: /Foodshare3/views/auth/select-role.php');
    }
    public function forgotPassword() {
        // Sanitize POST data
        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        
        $data = [
            'email' => trim($_POST['email']),
            'email_err' => '',
            'success_msg' => ''
        ];
        
        if(empty($data['email'])) {
            $data['email_err'] = 'Please enter email';
        } else {
            if($this->userModel->findUserByEmail($data['email'])) {
                // User found - generate reset token
                $token = bin2hex(random_bytes(32));
                $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Store token in database (for demo, we'll show the link directly)
                // In production, you would:
                // 1. Store token in database with expiry
                // 2. Send email with reset link
                
                // For demo purposes, show the reset link
                $resetLink = "http://localhost/Foodshare3/views/auth/reset-password.php?token=$token&email=" . urlencode($data['email']);
                $data['success_msg'] = "Password reset link generated! <br><br><strong>Demo Link:</strong><br><a href='$resetLink' style='color:#4facfe;word-break:break-all;'>$resetLink</a><br><br>In production, this would be sent to your email.";
            } else {
                $data['email_err'] = 'No user found with this email';
            }
        }
        
        return $data;
    }
}
