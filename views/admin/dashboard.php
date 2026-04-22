<?php
// Simple Admin Dashboard
session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header('location: /FoodShare/views/auth/login.php');
    exit;
}

$pageTitle = 'Admin Dashboard - FoodShare';
$userType = 'admin';
$userName = $_SESSION['user_name'];

// Include layout manually since it might expect specific variables or paths
// We'll just use a simplified version of the layout structure for now or try to include it
// Let's try to include the main layout but we need to ensure it handles 'admin' type in sidebar
// The layout.php likely needs an update to show admin links if we want it perfect, 
// but for now let's just show a basic page.

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="app-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <i class="fas fa-heart-circle-bolt"></i>
                    <span>FoodShare</span>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item active">
                    <i class="fas fa-th-large"></i>
                    <span>Dashboard</span>
                </a>
                <a href="#" class="nav-item">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </a>
                <a href="#" class="nav-item">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
                <a href="../../views/logout.php" class="nav-item">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="top-header">
                <button id="sidebarToggle" class="btn-icon mobile-only">
                    <i class="fas fa-bars"></i>
                </button>
                
                <h1 class="page-title">Admin Dashboard</h1>
                
                <div class="header-actions">
                    <div class="user-profile">
                        <div class="avatar">
                            <?php echo strtoupper(substr($userName, 0, 1)); ?>
                        </div>
                        <span class="user-name"><?php echo $userName; ?></span>
                    </div>
                </div>
            </header>

            <div class="content-wrapper">
                <div class="card">
                    <div class="card-body text-center" style="padding: 4rem;">
                        <i class="fas fa-user-shield" style="font-size: 4rem; color: var(--primary); margin-bottom: 1rem;"></i>
                        <h2>Welcome, Admin!</h2>
                        <p style="color: var(--text-secondary);">This is the administrative control panel.</p>
                        <div class="mt-lg">
                            <button class="btn btn-primary">Manage Users</button>
                            <button class="btn btn-outline">System Reports</button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../../assets/js/main.js"></script>
</body>
</html>
