<?php
if(!isset($conn)){
    include('../config/db.php');
}
?>
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <i class="fas fa-chart-line"></i>
            <span>Navigation</span>
        </div>
        <div class="sidebar-toggle" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </div>
        <div class="sidebar-close" id="sidebarClose">
            <i class="fas fa-times"></i>
        </div>
    </div>

    <div class="sidebar-menu">
        <?php
        // Client view
        if($_SESSION['role'] == 'client'){
            echo '<div class="menu-item">
                    <a href="../client/dashboard.php" class="menu-link">
                        <div class="menu-icon"><i class="fas fa-box"></i></div>
                        <div class="menu-text">My Orders</div>
                    </a>
                  </div>';
        } else {
        ?>
        
        <div class="menu-section">
            <div class="menu-section-title">Main</div>
            
            <div class="menu-item">
                <a href="../dashboard/index.php" class="menu-link">
                    <div class="menu-icon"><i class="fas fa-chart-pie"></i></div>
                    <div class="menu-text">Dashboard</div>
                </a>
            </div>

            <div class="menu-item">
                <a href="../orders/index.php" class="menu-link">
                    <div class="menu-icon"><i class="fas fa-shopping-cart"></i></div>
                    <div class="menu-text">Orders</div>
                    <?php 
                    $pending_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE status='Pending'"))['count'];
                    if($pending_count > 0): ?>
                    <span class="menu-badge"><?php echo $pending_count; ?></span>
                    <?php endif; ?>
                </a>
            </div>

            <div class="menu-item">
                <a href="../link_insertions/index.php" class="menu-link">
                    <div class="menu-icon"><i class="fas fa-link"></i></div>
                    <div class="menu-text">Link Insertions</div>
                </a>
            </div>

            <div class="menu-item">
                <a href="../outreach/index.php" class="menu-link">
                    <div class="menu-icon"><i class="fas fa-envelope-open-text"></i></div>
                    <div class="menu-text">Outreach CRM</div>
                    <?php 
                    $followup_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM outreach WHERE response_status='No Response'"))['count'];
                    if($followup_count > 0): ?>
                    <span class="menu-badge warning"><?php echo $followup_count; ?></span>
                    <?php endif; ?>
                </a>
            </div>

            <div class="menu-divider"></div>

            <div class="menu-section-title">Financial</div>

            <div class="menu-item">
                <a href="../payments/index.php" class="menu-link">
                    <div class="menu-icon"><i class="fas fa-credit-card"></i></div>
                    <div class="menu-text">Payments</div>
                    <?php 
                    $pending_payments = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM payments WHERE payment_status='Pending'"))['count'];
                    if($pending_payments > 0): ?>
                    <span class="menu-badge"><?php echo $pending_payments; ?></span>
                    <?php endif; ?>
                </a>
            </div>

            <div class="menu-item">
                <a href="../expenses/index.php" class="menu-link">
                    <div class="menu-icon"><i class="fas fa-chart-line"></i></div>
                    <div class="menu-text">Expenses</div>
                </a>
            </div>

            <?php if($_SESSION['role'] == 'admin'): ?>
            <div class="menu-divider"></div>

            <div class="menu-section-title">Administration</div>

            <div class="menu-item">
                <a href="../admin/users.php" class="menu-link">
                    <div class="menu-icon"><i class="fas fa-users-cog"></i></div>
                    <div class="menu-text">Admin Panel</div>
                </a>
            </div>
            <?php endif; ?>
        </div>
        
        <?php } ?>
    </div>

    <div class="sidebar-footer">
        <div class="footer-info">
            <i class="fas fa-circle" style="font-size: 8px; color: #44bd32;"></i>
            <span>System Online</span>
        </div>
        <div class="footer-version">
            <i class="fas fa-code-branch"></i>
            <span>v2.0.0</span>
        </div>
    </div>
</div>

<!-- Mobile Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<style>
    /* Sidebar Styles */
    .sidebar {
        position: fixed;
        top: 70px;
        left: 0;
        width: 280px;
        height: calc(100% - 70px);
        background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%);
        color: #e0e0e0;
        box-shadow: 4px 0 20px rgba(0, 0, 0, 0.15);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        z-index: 1000;
        overflow-y: auto;
        overflow-x: hidden;
    }

    /* Sidebar Overlay for Mobile */
    .sidebar-overlay {
        position: fixed;
        top: 70px;
        left: 0;
        width: 100%;
        height: calc(100% - 70px);
        background: rgba(0, 0, 0, 0.5);
        z-index: 999;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }

    .sidebar-overlay.active {
        opacity: 1;
        visibility: visible;
    }

    /* Custom Scrollbar for Sidebar */
    .sidebar::-webkit-scrollbar {
        width: 4px;
    }

    .sidebar::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.05);
    }

    .sidebar::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 4px;
    }

    .sidebar::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.3);
    }

    /* Sidebar Header */
    .sidebar-header {
        padding: 20px 20px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .sidebar-logo {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .sidebar-logo i {
        font-size: 24px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }

    .sidebar-logo span {
        font-size: 16px;
        font-weight: 600;
        color: white;
        letter-spacing: 0.5px;
    }

    .sidebar-toggle {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.05);
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .sidebar-toggle:hover {
        background: rgba(255, 255, 255, 0.1);
        transform: rotate(90deg);
    }

    .sidebar-toggle i {
        font-size: 16px;
        color: #e0e0e0;
    }

    .sidebar-close {
        display: none;
        width: 32px;
        height: 32px;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.05);
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .sidebar-close:hover {
        background: rgba(255, 255, 255, 0.1);
        transform: rotate(90deg);
    }

    .sidebar-close i {
        font-size: 16px;
        color: #e0e0e0;
    }

    /* Menu Sections */
    .sidebar-menu {
        padding: 0 12px;
        padding-bottom: 100px;
    }

    .menu-section {
        margin-bottom: 20px;
    }

    .menu-section-title {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: rgba(255, 255, 255, 0.4);
        padding: 10px 12px;
        margin-bottom: 5px;
    }

    /* Menu Items */
    .menu-item {
        margin-bottom: 4px;
    }

    .menu-link {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 14px;
        color: #e0e0e0;
        text-decoration: none;
        border-radius: 12px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .menu-link::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        width: 0;
        height: 100%;
        background: linear-gradient(90deg, rgba(102, 126, 234, 0.2) 0%, transparent 100%);
        transition: width 0.3s ease;
    }

    .menu-link:hover::before {
        width: 100%;
    }

    .menu-link:hover {
        background: rgba(102, 126, 234, 0.15);
        transform: translateX(5px);
    }

    .menu-icon {
        width: 28px;
        font-size: 18px;
        text-align: center;
        transition: all 0.3s ease;
    }

    .menu-link:hover .menu-icon {
        transform: scale(1.1);
    }

    .menu-text {
        flex: 1;
        font-size: 14px;
        font-weight: 500;
    }

    /* Menu Badge */
    .menu-badge {
        background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        color: white;
        font-size: 10px;
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 20px;
        min-width: 22px;
        text-align: center;
    }

    .menu-badge.warning {
        background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    }

    /* Menu Divider */
    .menu-divider {
        height: 1px;
        background: rgba(255, 255, 255, 0.08);
        margin: 15px 12px;
    }

    /* Active Menu Item */
    .menu-link.active {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.25) 0%, rgba(118, 75, 162, 0.25) 100%);
        color: white;
        border-left: 3px solid #667eea;
    }

    .menu-link.active .menu-icon {
        color: #667eea;
    }

    /* Sidebar Footer */
    .sidebar-footer {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 20px;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
        background: linear-gradient(180deg, transparent, rgba(0, 0, 0, 0.2));
    }

    .footer-info {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 10px;
        font-size: 12px;
        color: rgba(255, 255, 255, 0.6);
    }

    .footer-version {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 11px;
        color: rgba(255, 255, 255, 0.4);
    }

    /* Collapsed Sidebar (Desktop) */
    .sidebar.collapsed {
        width: 80px;
    }

    .sidebar.collapsed .sidebar-logo span,
    .sidebar.collapsed .menu-text,
    .sidebar.collapsed .menu-badge,
    .sidebar.collapsed .menu-section-title,
    .sidebar.collapsed .sidebar-footer .footer-info span,
    .sidebar.collapsed .sidebar-footer .footer-version span {
        display: none;
    }

    .sidebar.collapsed .menu-link {
        justify-content: center;
        padding: 12px;
    }

    .sidebar.collapsed .menu-icon {
        font-size: 20px;
        margin: 0;
    }

    .sidebar.collapsed .menu-link:hover {
        transform: translateX(0);
    }

    .sidebar.collapsed .sidebar-footer .footer-info,
    .sidebar.collapsed .sidebar-footer .footer-version {
        justify-content: center;
    }

    /* Desktop Responsive (Tablets) */
    @media (max-width: 1024px) {
        .sidebar {
            width: 260px;
        }
        
        .sidebar.collapsed {
            width: 70px;
        }
    }

    /* Mobile Responsive */
    @media (max-width: 768px) {
        .sidebar {
            transform: translateX(-100%);
            width: 280px;
            box-shadow: none;
        }
        
        .sidebar.mobile-open {
            transform: translateX(0);
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.3);
        }
        
        .sidebar-toggle {
            display: none;
        }
        
        .sidebar-close {
            display: flex;
        }
        
        .sidebar.collapsed {
            transform: translateX(-100%);
            width: 280px;
        }
        
        .sidebar.collapsed.mobile-open {
            transform: translateX(0);
            width: 280px;
        }
        
        .sidebar.collapsed.mobile-open .sidebar-logo span,
        .sidebar.collapsed.mobile-open .menu-text,
        .sidebar.collapsed.mobile-open .menu-badge,
        .sidebar.collapsed.mobile-open .menu-section-title,
        .sidebar.collapsed.mobile-open .sidebar-footer .footer-info span,
        .sidebar.collapsed.mobile-open .sidebar-footer .footer-version span {
            display: block;
        }
        
        .sidebar.collapsed.mobile-open .menu-link {
            justify-content: flex-start;
            padding: 12px 14px;
        }
        
        .sidebar.collapsed.mobile-open .menu-icon {
            margin: 0;
            font-size: 18px;
        }
        
        .sidebar.collapsed.mobile-open .sidebar-footer .footer-info,
        .sidebar.collapsed.mobile-open .sidebar-footer .footer-version {
            justify-content: flex-start;
        }
    }

    /* Small Mobile Devices */
    @media (max-width: 480px) {
        .sidebar {
            width: 100%;
            max-width: 280px;
        }
        
        .sidebar.mobile-open {
            width: 100%;
            max-width: 280px;
        }
        
        .menu-link {
            padding: 10px 12px;
        }
        
        .menu-text {
            font-size: 13px;
        }
        
        .sidebar-header {
            padding: 15px 15px;
        }
        
        .sidebar-footer {
            padding: 15px;
        }
    }

    /* Animation for menu items */
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(-20px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .menu-item {
        animation: slideIn 0.3s ease forwards;
        animation-delay: calc(var(--item-index, 0) * 0.05s);
        opacity: 0;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Set active menu item based on current URL
        const currentPath = window.location.pathname;
        const menuLinks = document.querySelectorAll('.menu-link');
        
        menuLinks.forEach(link => {
            const href = link.getAttribute('href');
            if (href && currentPath.includes(href.replace('../', ''))) {
                link.classList.add('active');
            }
        });
        
        // Sidebar elements
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarClose = document.getElementById('sidebarClose');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        
        // Desktop toggle (collapse)
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                sidebar.classList.toggle('collapsed');
                // Save state to localStorage
                localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
            });
        }
        
        // Mobile open/close
        function openMobileSidebar() {
            sidebar.classList.add('mobile-open');
            sidebarOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        
        function closeMobileSidebar() {
            sidebar.classList.remove('mobile-open');
            sidebarOverlay.classList.remove('active');
            document.body.style.overflow = '';
        }
        
        // Close button for mobile
        if (sidebarClose) {
            sidebarClose.addEventListener('click', closeMobileSidebar);
        }
        
        // Overlay click to close
        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', closeMobileSidebar);
        }
        
        // Check if we need a hamburger menu button in header
        function addMobileMenuButton() {
            const headerRight = document.querySelector('.header-right');
            if (headerRight && !document.querySelector('.mobile-menu-btn')) {
                const menuBtn = document.createElement('div');
                menuBtn.className = 'mobile-menu-btn';
                menuBtn.innerHTML = '<i class="fas fa-bars"></i>';
                menuBtn.addEventListener('click', openMobileSidebar);
                headerRight.insertBefore(menuBtn, headerRight.firstChild);
            }
        }
        
        // Handle responsive behavior
        function handleResponsive() {
            if (window.innerWidth <= 768) {
                addMobileMenuButton();
                // Restore collapsed state doesn't apply on mobile
                if (sidebar.classList.contains('collapsed')) {
                    sidebar.classList.remove('collapsed');
                }
            } else {
                // Remove mobile menu button if exists
                const mobileBtn = document.querySelector('.mobile-menu-btn');
                if (mobileBtn) mobileBtn.remove();
                
                // Restore collapsed state from localStorage on desktop
                const savedState = localStorage.getItem('sidebarCollapsed');
                if (savedState === 'true') {
                    sidebar.classList.add('collapsed');
                } else {
                    sidebar.classList.remove('collapsed');
                }
                
                // Ensure mobile-open is removed
                if (sidebar.classList.contains('mobile-open')) {
                    sidebar.classList.remove('mobile-open');
                    sidebarOverlay.classList.remove('active');
                    document.body.style.overflow = '';
                }
            }
        }
        
        // Initial call
        handleResponsive();
        
        // Listen for resize
        window.addEventListener('resize', handleResponsive);
        
        // Add animation delay to menu items
        const menuItems = document.querySelectorAll('.menu-item');
        menuItems.forEach((item, index) => {
            item.style.setProperty('--item-index', index);
        });
        
        // Close sidebar when clicking on a link (mobile)
        const allLinks = document.querySelectorAll('.menu-link');
        allLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    closeMobileSidebar();
                }
            });
        });
    });
</script>

<!-- Styles for mobile menu button (add to your header) -->
<style>
    .mobile-menu-btn {
        width: 40px;
        height: 40px;
        display: none;
        align-items: center;
        justify-content: center;
        background: rgba(0, 0, 0, 0.05);
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .mobile-menu-btn:hover {
        background: rgba(0, 0, 0, 0.1);
    }
    
    .mobile-menu-btn i {
        font-size: 20px;
        color: #2c3e50;
    }
    
    @media (max-width: 768px) {
        .mobile-menu-btn {
            display: flex;
        }
    }
</style>