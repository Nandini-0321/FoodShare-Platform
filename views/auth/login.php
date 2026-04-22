<?php
require_once '../../controllers/AuthController.php';

// Check if form is submitted
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $init = new AuthController;
    $data = $init->login();
} else {
    $data = [
        'email' => '',
        'password' => '',
        'email_err' => '',
        'password_err' => ''
    ];
}

// Check for role parameter
$preSelectedRole = isset($_GET['role']) ? $_GET['role'] : '';
// Set role title for display
$roleTitle = '';
if($preSelectedRole) {
    switch($preSelectedRole) {
        case 'donor':
            $roleTitle = ' as Donor';
            break;
        case 'ngo':
            $roleTitle = ' as NGO';
            break;
        case 'volunteer':
            $roleTitle = ' as Volunteer';
            break;
        case 'admin':
            $roleTitle = ' as Admin';
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FoodShare</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Global Styles -->
    <link rel="stylesheet" href="/FoodShare/assets/css/style.css">
    
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: var(--bg-primary);
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(102, 126, 234, 0.1) 0%, transparent 20%),
                radial-gradient(circle at 90% 80%, rgba(246, 79, 89, 0.1) 0%, transparent 20%);
        }
        
        .auth-card {
            width: 100%;
            max-width: 450px;
            padding: var(--spacing-xl);
            background: var(--bg-secondary);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-xl);
            backdrop-filter: blur(10px);
        }
        
        .auth-header {
            text-align: center;
            margin-bottom: var(--spacing-xl);
        }
        
        .auth-logo {
            font-size: 2rem;
            font-weight: 700;
            font-family: var(--font-heading);
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: var(--spacing-sm);
            display: inline-block;
        }
        
        /* Radio Cards */
        .radio-card {
            cursor: pointer;
            position: relative;
        }
        
        .radio-card input {
            position: absolute;
            opacity: 0;
        }
        
        .radio-card .radio-content {
            background: var(--surface);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--radius-sm);
            padding: var(--spacing-sm);
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
        }
        
        .radio-card input:checked + .radio-content {
            background: rgba(79, 172, 254, 0.1);
            border-color: var(--primary);
            color: var(--primary);
            box-shadow: 0 0 15px rgba(79, 172, 254, 0.3);
        }
        
        .radio-card:hover .radio-content {
            border-color: var(--primary);
            transform: translateY(-2px);
        }
        
        .auth-subtitle {
            color: var(--text-secondary);
        }
        
        .form-footer {
            margin-top: var(--spacing-lg);
            text-align: center;
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
        
        .form-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }
        
        .form-footer a:hover {
            text-decoration: underline;
        }
        
        .alert {
            padding: var(--spacing-sm) var(--spacing-md);
            border-radius: var(--radius-md);
            margin-bottom: var(--spacing-md);
            font-size: 0.9rem;
        }
        
        .alert-success {
            background: rgba(79, 172, 254, 0.1);
            color: #4facfe;
            border: 1px solid rgba(79, 172, 254, 0.2);
        }
        
        .invalid-feedback {
            color: #f64f59;
            font-size: 0.85rem;
            margin-top: 0.25rem;
        }
        
        .form-control.is-invalid {
            border-color: #f64f59;
        }
    </style>
</head>
<body>

    <div class="auth-card animate-fade-in">
        <div class="auth-header">
            <div class="auth-logo">
                <i class="fas fa-heart"></i> FoodShare
            </div>
            <h2 class="auth-title">Welcome Back</h2>
            <p class="auth-subtitle">Login to continue</p>
        </div>
        
        <?php if(isset($_GET['status']) && $_GET['status'] == 'registered'): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Registration successful! Please login.
            </div>
        <?php endif; ?>
        
        <?php if(!empty($data['email_err']) && empty($data['password_err'])): ?>
            <div class="alert alert-danger mb-md" style="background: rgba(246, 79, 89, 0.1); color: #f64f59; border: 1px solid rgba(246, 79, 89, 0.2); padding: 1rem; border-radius: var(--radius-md);">
                <i class="fas fa-exclamation-circle"></i> <?php echo $data['email_err']; ?>
            </div>
        <?php endif; ?>
        
        <form action="<?php echo $_SERVER['PHP_SELF']; ?><?php echo $preSelectedRole ? '?role='.$preSelectedRole : ''; ?>" method="POST">
            
            <?php if(!$preSelectedRole): ?>
            <div class="form-group">
                <label class="form-label">Login As</label>
                <div class="grid grid-3 gap-sm" style="grid-template-columns: repeat(3, 1fr);">
                    <label class="radio-card">
                        <input type="radio" name="user_type" value="donor" checked>
                        <div class="radio-content text-center">
                            <i class="fas fa-hand-holding-heart mb-sm"></i>
                            <span style="font-size: 0.8rem;">Donor</span>
                        </div>
                    </label>
                    <label class="radio-card">
                        <input type="radio" name="user_type" value="ngo">
                        <div class="radio-content text-center">
                            <i class="fas fa-hands-helping mb-sm"></i>
                            <span style="font-size: 0.8rem;">NGO</span>
                        </div>
                    </label>
                    <label class="radio-card">
                        <input type="radio" name="user_type" value="volunteer">
                        <div class="radio-content text-center">
                            <i class="fas fa-biking mb-sm"></i>
                            <span style="font-size: 0.8rem;">Volunteer</span>
                        </div>
                    </label>
                </div>
            </div>
            <?php else: ?>
                <input type="hidden" name="user_type" value="<?php echo $preSelectedRole; ?>">
            <?php endif; ?>

            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control <?php echo (!empty($data['email_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['email']; ?>" placeholder="Enter your email">
                <span class="invalid-feedback"><?php echo $data['email_err']; ?></span>
            </div>
            
            <div class="form-group">
                <div class="flex-between">
                    <label class="form-label">Password</label>
                    <a href="forgot-password.php" style="font-size: 0.85rem; color: var(--primary);">Forgot Password?</a>
                </div>
                <input type="password" name="password" class="form-control <?php echo (!empty($data['password_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['password']; ?>" placeholder="Enter your password">
                <span class="invalid-feedback"><?php echo $data['password_err']; ?></span>
            </div>
            
            <div class="flex-between mb-lg">
                <label class="flex" style="gap: 0.5rem; color: var(--text-secondary); font-size: 0.9rem; cursor: pointer;">
                    <input type="checkbox"> Remember me
                </label>
                <a href="#" style="color: var(--primary); text-decoration: none; font-size: 0.9rem;">Forgot Password?</a>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                Login <i class="fas fa-arrow-right"></i>
            </button>
        </form>
        
        <div class="form-footer">
            Don't have an account? <a href="register.php<?php echo $preSelectedRole ? '?role='.$preSelectedRole : ''; ?>">Register here</a>
            <?php if($preSelectedRole): ?>
                <br><br>
                <a href="select-role.php" style="font-size: 0.85rem; color: var(--text-secondary);"><i class="fas fa-arrow-left"></i> Choose different role</a>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>
