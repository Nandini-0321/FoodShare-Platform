<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Role - FoodShare</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg-primary);
            padding: 2rem;
        }
        
        .role-container {
            max-width: 1000px;
            width: 100%;
        }
        
        .role-card {
            background: var(--surface);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: var(--radius-lg);
            padding: 2rem;
            text-align: center;
            transition: var(--transition);
            cursor: pointer;
            position: relative;
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        
        .role-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary);
            box-shadow: var(--shadow-lg);
        }
        
        .role-icon {
            font-size: 3rem;
            margin-bottom: 1.5rem;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(255,255,255,0.05);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }
        
        .role-card:hover .role-icon {
            background: var(--primary);
            color: white;
        }
        
        .role-title {
            font-family: var(--font-heading);
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }
        
        .role-desc {
            color: var(--text-secondary);
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }
        
        .role-actions {
            display: flex;
            gap: 1rem;
            opacity: 0;
            transform: translateY(10px);
            transition: var(--transition);
        }
        
        .role-card:hover .role-actions {
            opacity: 1;
            transform: translateY(0);
        }
        
        .btn-role {
            padding: 0.5rem 1.5rem;
            border-radius: var(--radius-full);
            font-size: 0.9rem;
            text-decoration: none;
            transition: var(--transition);
        }
        
        .btn-login {
            background: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
        }
        
        .btn-login:hover {
            background: rgba(79, 172, 254, 0.1);
        }
        
        .btn-register {
            background: var(--primary);
            color: white;
            border: 1px solid var(--primary);
        }
        
        .btn-register:hover {
            background: var(--primary-dark);
        }

        /* For mobile, always show actions */
        @media (max-width: 768px) {
            .role-actions {
                opacity: 1;
                transform: translateY(0);
                margin-top: 1rem;
            }
        }
    </style>
</head>
<body>

    <div class="role-container animate-fade-in">
        <div class="text-center mb-xl">
            <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem; background: var(--gradient-primary); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Welcome to FoodShare</h1>
            <p style="color: var(--text-secondary); font-size: 1.1rem;">Choose your role to get started</p>
        </div>
        
        <div class="grid grid-3 gap-lg">
            <!-- Donor -->
            <div class="role-card">
                <div class="role-icon" style="color: #4facfe;">
                    <i class="fas fa-hand-holding-heart"></i>
                </div>
                <h2 class="role-title">Donor</h2>
                <p class="role-desc">Donate food to help those in need. Restaurants, hotels, and individuals.</p>
                <div class="role-actions">
                    <a href="login.php?role=donor" class="btn-role btn-login">Login</a>
                    <a href="register.php?role=donor" class="btn-role btn-register">Register</a>
                </div>
            </div>
            
            <!-- NGO -->
            <div class="role-card">
                <div class="role-icon" style="color: #f64f59;">
                    <i class="fas fa-hands-helping"></i>
                </div>
                <h2 class="role-title">NGO / Receiver</h2>
                <p class="role-desc">Request food donations for your organization or community.</p>
                <div class="role-actions">
                    <a href="login.php?role=ngo" class="btn-role btn-login">Login</a>
                    <a href="register.php?role=ngo" class="btn-role btn-register">Register</a>
                </div>
            </div>
            
            <!-- Volunteer -->
            <div class="role-card">
                <div class="role-icon" style="color: #c471ed;">
                    <i class="fas fa-biking"></i>
                </div>
                <h2 class="role-title">Volunteer</h2>
                <p class="role-desc">Help deliver food from donors to NGOs. Earn points and rewards.</p>
                <div class="role-actions">
                    <a href="login.php?role=volunteer" class="btn-role btn-login">Login</a>
                    <a href="register.php?role=volunteer" class="btn-role btn-register">Register</a>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-xl">
            <a href="login.php?role=admin" style="color: var(--text-muted); font-size: 0.9rem; text-decoration: none; opacity: 0.5; transition: 0.3s;">
                <i class="fas fa-user-shield"></i> Admin Access
            </a>
        </div>
    </div>

</body>
</html>
