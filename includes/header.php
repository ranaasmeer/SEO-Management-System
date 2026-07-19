<?php include('auth_check.php'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SEO Management Dashboard</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(135deg, #f5f7fa 0%, #eef2f7 100%);
        min-height: 100vh;
    }

    /* HEADER */
    .header {
        height: 70px;
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(10px);
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 30px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1000;
        border-bottom: 1px solid rgba(102, 126, 234, 0.1);
    }

    /* LOGO / TITLE */
    .logo {
        display: flex;
        align-items: center;
        gap: 12px;
        text-decoration: none;
    }

    .logo-icon {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        animation: pulse 2s infinite;
    }

    .logo-icon i {
        font-size: 22px;
        color: white;
    }

    .logo-text h3 {
        margin: 0;
        color: #2c3e50;
        font-size: 18px;
        font-weight: 700;
        letter-spacing: -0.3px;
    }

    .logo-text p {
        margin: 0;
        color: #7f8c8d;
        font-size: 11px;
        font-weight: 500;
        letter-spacing: 0.5px;
    }

    /* Hover effect on logo */
    .logo {
        transition: all 0.3s ease;
    }

    .logo:hover {
        transform: translateY(-2px);
    }

    .logo:hover .logo-icon {
        transform: scale(1.05);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }

    /* RIGHT SIDE */
    .header-right {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    /* USER SECTION */
    .user-section {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 5px 15px 5px 10px;
        background: #f8f9fa;
        border-radius: 40px;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .user-section:hover {
        background: #eef2f7;
        transform: translateY(-2px);
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    .user-avatar i {
        font-size: 20px;
        color: white;
    }

    .user-info {
        display: flex;
        flex-direction: column;
    }

    .user-name {
        font-size: 13px;
        font-weight: 600;
        color: #2c3e50;
    }

    .user-role {
        font-size: 10px;
        color: #7f8c8d;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Role Badge Colors */
    .role-admin {
        background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    }

    .role-freelancer {
        background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    }

    .role-client {
        background: linear-gradient(135deg, #44bd32 0%, #2e7d32 100%);
    }

    /* LOGOUT BUTTON */
    .logout-btn {
        text-decoration: none;
        padding: 10px 20px;
        background: linear-gradient(135deg, #e84118 0%, #c0392b 100%);
        color: white;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
    }

    /* Hover animation */
    .logout-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(232, 65, 24, 0.4);
    }

    /* MAIN LAYOUT */
    .container {
        display: flex;
        margin-top: 70px;
        min-height: calc(100vh - 70px);
    }

    /* SIDEBAR SPACE RESERVED */
    .sidebar {
        width: 260px;
        min-height: 100%;
        position: fixed;
        left: 0;
        top: 70px;
        bottom: 0;
        background: white;
        box-shadow: 2px 0 20px rgba(0, 0, 0, 0.05);
        overflow-y: auto;
        transition: all 0.3s ease;
    }

    /* CONTENT AREA */
    .content {
        flex: 1;
        padding: 30px;
        margin-left: 260px;
        box-sizing: border-box;
        min-height: calc(100vh - 70px);
        transition: all 0.3s ease;
    }

    /* Custom Scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    ::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }

    /* User Dropdown Menu */
    .user-dropdown {
        position: relative;
    }

    .dropdown-menu {
        position: absolute;
        top: 60px;
        right: 0;
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        width: 240px;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-10px);
        transition: all 0.3s ease;
        z-index: 1000;
    }

    .user-dropdown.active .dropdown-menu {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    .dropdown-header {
        padding: 15px;
        border-bottom: 1px solid #ecf0f1;
    }

    .dropdown-header .dropdown-name {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 5px;
    }

    .dropdown-header .dropdown-email {
        font-size: 11px;
        color: #7f8c8d;
    }

    .dropdown-item {
        padding: 12px 15px;
        display: flex;
        align-items: center;
        gap: 10px;
        color: #2c3e50;
        text-decoration: none;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .dropdown-item:hover {
        background: #f8f9fa;
        padding-left: 20px;
    }

    .dropdown-item i {
        width: 20px;
        color: #667eea;
    }

    .dropdown-divider {
        height: 1px;
        background: #ecf0f1;
        margin: 5px 0;
    }

    /* Animations */
    @keyframes pulse {
        0%, 100% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.05);
        }
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* RESPONSIVE */
    @media (max-width: 768px) {
        .header {
            padding: 0 15px;
        }

        .logo-text h3 {
            font-size: 14px;
        }

        .logo-text p {
            display: none;
        }

        .logo-icon {
            width: 35px;
            height: 35px;
        }

        .logo-icon i {
            font-size: 18px;
        }

        .user-name, .user-role {
            display: none;
        }

        .user-section {
            padding: 5px 10px;
        }

        .sidebar {
            transform: translateX(-100%);
            position: fixed;
            z-index: 999;
        }

        .sidebar.open {
            transform: translateX(0);
        }

        .content {
            margin-left: 0;
            padding: 20px;
        }

        .logout-btn span {
            display: none;
        }

        .logout-btn i {
            margin: 0;
        }

        .logout-btn {
            padding: 10px 12px;
        }
    }

    @media (min-width: 769px) {
        .sidebar {
            transform: translateX(0) !important;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // User dropdown toggle
        const userSection = document.querySelector('.user-section');
        if (userSection) {
            userSection.addEventListener('click', function(e) {
                e.stopPropagation();
                const dropdown = this.parentElement;
                dropdown.classList.toggle('active');
            });
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function() {
            const dropdown = document.querySelector('.user-dropdown');
            if (dropdown && dropdown.classList.contains('active')) {
                dropdown.classList.remove('active');
            }
        });

        // Mobile sidebar toggle
        const menuToggle = document.querySelector('.menu-toggle');
        const sidebar = document.querySelector('.sidebar');
        
        if (menuToggle && sidebar) {
            menuToggle.addEventListener('click', function() {
                sidebar.classList.toggle('open');
            });
        }
    });
</script>

</head>
<body>

<div class="header">
    <!-- LEFT SIDE - LOGO -->
    <a href="../dashboard/index.php" class="logo">
        <div class="logo-icon">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="logo-text">
            <h3>SEO Management System</h3>
            <p>Admin Dashboard</p>
        </div>
    </a>

    <!-- RIGHT SIDE -->
    <div class="header-right">
        <div class="user-dropdown">
            <div class="user-section">
                <div class="user-avatar <?php 
                    $roleClass = '';
                    if($_SESSION['role'] == 'admin') $roleClass = 'role-admin';
                    elseif($_SESSION['role'] == 'freelancer') $roleClass = 'role-freelancer';
                    else $roleClass = 'role-client';
                    echo $roleClass;
                ?>">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div class="user-info">
                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?></span>
                    <span class="user-role"><?php echo htmlspecialchars($_SESSION['role'] ?? 'client'); ?></span>
                </div>
                <i class="fas fa-chevron-down" style="font-size: 12px; color: #7f8c8d;"></i>
            </div>
            
            <!-- Dropdown Menu -->
            <div class="dropdown-menu">
                <div class="dropdown-header">
                    <div class="dropdown-name"><?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?></div>
                    <div class="dropdown-email"><?php echo htmlspecialchars($_SESSION['email'] ?? 'user@example.com'); ?></div>
                </div>
                <div class="dropdown-divider"></div>
                <a href="#" onclick="return false;" class="dropdown-item">
                    <i class="fas fa-user"></i>
                    <span>My Profile</span>
                </a>
                <a href="#" onclick="return false;" class="dropdown-item">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
                <div class="dropdown-divider"></div>
                <a href="../auth/logout.php" class="dropdown-item">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>

        <a href="../auth/logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</div>

<div class="container">