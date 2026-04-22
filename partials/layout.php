<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'FoodShare - Connecting Hearts Through Food'; ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <!-- Global Styles -->
    <link rel="stylesheet" href="/Foodshare3/assets/css/style.css">
    <link rel="stylesheet" href="/Foodshare3/assets/css/chat.css">
    <link rel="stylesheet" href="/Foodshare3/assets/css/tracking.css">
    
    <?php if (isset($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <script>
        // Global variables for JS
        window.CURRENT_USER_ID = <?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'null'; ?>;
        window.CURRENT_USER_NAME = "<?php echo isset($_SESSION['full_name']) ? addslashes($_SESSION['full_name']) : 'User'; ?>";
    </script>

    <!-- Sidebar Navigation -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <i class="fas fa-heart"></i>
                <span>FoodShare</span>
            </div>
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
        </div>
        
        <nav class="sidebar-nav">
            <ul>
                <?php
                $menuItems = [
                    'donor' => [
                        ['icon' => 'fa-house', 'label' => 'Dashboard', 'url' => '/Foodshare3/views/donor/dashboard.php', 'active' => true],
                        ['icon' => 'fa-plus-circle', 'label' => 'New Donation', 'url' => '/Foodshare3/views/donor/new-donation.php'],
                        ['icon' => 'fa-clock-rotate-left', 'label' => 'My Donations', 'url' => '/Foodshare3/views/donor/donations.php'],
                        ['icon' => 'fa-chart-line', 'label' => 'Impact', 'url' => '/Foodshare3/views/donor/impact.php'],
                        ['icon' => 'fa-user', 'label' => 'Profile', 'url' => '/Foodshare3/views/donor/profile.php'],
                    ],
                    'ngo' => [
                        ['icon' => 'fa-house', 'label' => 'Dashboard', 'url' => '/Foodshare3/views/ngo/dashboard.php', 'active' => true],
                        ['icon' => 'fa-box', 'label' => 'Available Food', 'url' => '/Foodshare3/views/ngo/available-food.php'],
                        ['icon' => 'fa-check-circle', 'label' => 'My Requests', 'url' => '/Foodshare3/views/ngo/requests.php'],
                        ['icon' => 'fa-users', 'label' => 'Volunteers', 'url' => '/Foodshare3/views/ngo/volunteers.php'],
                        ['icon' => 'fa-chart-pie', 'label' => 'Reports', 'url' => '/Foodshare3/views/ngo/reports.php'],
                        ['icon' => 'fa-id-card', 'label' => 'Profile', 'url' => '/Foodshare3/views/ngo/profile.php'],
                    ],
                    'volunteer' => [
                        ['icon' => 'fa-house', 'label' => 'Dashboard', 'url' => '/Foodshare3/views/volunteer/dashboard.php', 'active' => true],
                        ['icon' => 'fa-tasks', 'label' => 'My Tasks', 'url' => '/Foodshare3/views/volunteer/tasks.php'],
                        ['icon' => 'fa-map-marked-alt', 'label' => 'Pickups', 'url' => '/Foodshare3/views/volunteer/pickups.php'],
                        ['icon' => 'fa-trophy', 'label' => 'Achievements', 'url' => '/Foodshare3/views/volunteer/achievements.php'],
                        ['icon' => 'fa-id-card', 'label' => 'Profile', 'url' => '/Foodshare3/views/volunteer/profile.php'],
                    ]
                ];
                
                $userType = $_SESSION['role'] ?? 'donor';
                $currentMenu = $menuItems[$userType] ?? $menuItems['donor'];
                
                foreach ($currentMenu as $item):
                    $activeClass = (isset($item['active']) && $item['active']) ? 'active' : '';
                    // Simple active check based on URL
                    if(strpos($_SERVER['REQUEST_URI'], $item['url']) !== false) {
                        $activeClass = 'active';
                    }
                ?>
                    <li>
                        <a href="<?php echo $item['url']; ?>" class="nav-link <?php echo $activeClass; ?>">
                            <i class="fas <?php echo $item['icon']; ?>"></i>
                            <span><?php echo $item['label']; ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
        
        <div class="sidebar-footer">
            <a href="/Foodshare3/logout.php" class="nav-link logout-link">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </aside>
    
    <!-- Main Content Area -->
    <main class="main-content" id="mainContent">
        <!-- Top Header -->
        <header class="top-header">
            <div class="header-left">
                <button class="mobile-menu-toggle" id="mobileMenuToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="page-title"><?php echo $pageHeading ?? 'Dashboard'; ?></h1>
            </div>
            
            <div class="header-right">
                <!-- Search -->
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search...">
                </div>
                
                <!-- Notifications -->
                <div class="notification-wrapper" style="position: relative; margin-right: 15px; cursor: pointer;" onclick="toggleNotifications()">
                    <div class="header-icon notification-icon">
                        <i class="fas fa-bell"></i>
                        <span id="notification-badge" class="notification-badge" style="display: none;">0</span>
                    </div>
                    <!-- Notification Dropdown -->
                    <div id="notification-dropdown" style="display: none; position: absolute; top: 40px; right: 0; width: 300px; background: var(--bg-secondary); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.2); z-index: 1000; max-height: 400px; overflow-y: auto;">
                        <div style="padding: 10px; border-bottom: 1px solid rgba(255,255,255,0.1); font-weight: bold;">Notifications</div>
                        <div id="notification-list">
                            <div style="padding: 15px; text-align: center; color: #999;">Loading...</div>
                        </div>
                        <div style="padding: 10px; text-align: center; border-top: 1px solid rgba(255,255,255,0.1); cursor: pointer; color: #4facfe;" onclick="markAllRead()">Mark all as read</div>
                    </div>
                </div>
                
                <!-- Chat Icon -->
                <div class="header-icon chat-trigger" onclick="window.chatSystem.toggleChat()" style="margin-right: 15px; cursor: pointer; position: relative;">
                    <i class="fas fa-comment-alt"></i>
                    <span id="chat-total-unread" class="notification-badge" style="display: none; background: #f64f59;">0</span>
                </div>
                
                <!-- User Profile -->
                <div class="user-profile" onclick="toggleUserMenu()">
                    <img src="/FoodShare/assets/images/user-placeholder.png" alt="User" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22%3E%3Ccircle cx=%2250%22 cy=%2250%22 r=%2250%22 fill=%22%23667eea%22/%3E%3Ctext x=%2250%22 y=%2250%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22white%22 font-size=%2240%22 font-family=%22Arial%22%3EU%3C/text%3E%3C/svg%3E'">
                    <div class="user-info">
                        <span class="user-name"><?php echo $_SESSION['full_name'] ?? 'User'; ?></span>
                        <span class="user-role"><?php echo ucfirst($_SESSION['role'] ?? 'Guest'); ?></span>
                    </div>
                    
                    <!-- Dropdown Menu -->
                    <div id="user-dropdown" class="user-dropdown-menu" style="display: none; position: absolute; top: 60px; right: 20px; background: white; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); width: 150px; z-index: 1000; overflow: hidden;">
                        <?php 
                            $role = $_SESSION['role'] ?? 'donor';
                            $profileUrl = "/Foodshare3/views/$role/profile.php";
                        ?>
                        <a href="<?php echo $profileUrl; ?>" style="display: block; padding: 10px 15px; color: #333; text-decoration: none; border-bottom: 1px solid #eee; transition: background 0.2s;">
                            <i class="fas fa-user" style="margin-right: 8px; color: #667eea;"></i> Profile
                        </a>
                        <a href="/Foodshare3/logout.php" style="display: block; padding: 10px 15px; color: #dc3545; text-decoration: none; transition: background 0.2s;">
                            <i class="fas fa-sign-out-alt" style="margin-right: 8px;"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <script>
        function toggleUserMenu() {
            const menu = document.getElementById('user-dropdown');
            if (menu.style.display === 'none') {
                menu.style.display = 'block';
            } else {
                menu.style.display = 'none';
            }
        }
        
        // Close dropdowns when clicking outside
        window.onclick = function(event) {
            if (!event.target.closest('.user-profile')) {
                const menu = document.getElementById('user-dropdown');
                if (menu && menu.style.display === 'block') {
                    menu.style.display = 'none';
                }
            }
            if (!event.target.closest('.notification-wrapper')) {
                const notifDropdown = document.getElementById('notification-dropdown');
                if (notifDropdown && notifDropdown.style.display === 'block') {
                    notifDropdown.style.display = 'none';
                }
            }
        }
        </script>
        <div class="content-wrapper">
            <?php echo $content ?? ''; ?>
        </div>
    </main>
    
    <!-- Global JS -->
    <script src="/Foodshare3/assets/js/main.js"></script>
    
    <!-- Feature Scripts -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
    <script src="/Foodshare3/assets/js/chat.js"></script>
    <script src="/Foodshare3/assets/js/tracking.js"></script>
    
    <?php if (isset($additionalJS)): ?>
        <?php foreach ($additionalJS as $js): ?>
            <script src="<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
