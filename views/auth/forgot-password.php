<?php
require_once '../../controllers/AuthController.php';

$data = ['email' => '', 'email_err' => '', 'success_msg' => ''];

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $controller = new AuthController();
    $data = $controller->forgotPassword();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - FoodShare</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Global Styles -->
    <link rel="stylesheet" href="/Foodshare3/assets/css/style.css">
    
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
        
        .alert-danger {
            background: rgba(246, 79, 89, 0.1);
            color: #f64f59;
            border: 1px solid rgba(246, 79, 89, 0.2);
        }
        
        .invalid-feedback {
            color: #f64f59;
            font-size: 0.85rem;
            margin-top: 0.25rem;
        }
        
        .form-control.is-invalid {
            border-color: #f64f59;
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
    </style>
</head>
<body>

    <div class="auth-card animate-fade-in">
        <div class="auth-header">
            <div class="auth-logo">
                <i class="fas fa-heart"></i> FoodShare
            </div>
            <h2 class="auth-title">Forgot Password</h2>
            <p class="auth-subtitle">Enter your email to reset your password</p>
        </div>
        
        <?php if(!empty($data['success_msg'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $data['success_msg']; ?>
            </div>
        <?php endif; ?>
        
        <?php if(!empty($data['email_err'])): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $data['email_err']; ?>
            </div>
        <?php endif; ?>
        
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control <?php echo (!empty($data['email_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['email']; ?>" placeholder="Enter your registered email" required>
                <span class="invalid-feedback"><?php echo $data['email_err']; ?></span>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                <i class="fas fa-paper-plane"></i> Send Reset Link
            </button>
        </form>
        
        <div class="form-footer">
            Remember your password? <a href="login.php">Login here</a>
        </div>
    </div>

</body>
</html>
