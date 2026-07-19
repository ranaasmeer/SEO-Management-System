<?php 
include('../includes/header.php'); 
include('../includes/sidebar.php'); 
include('../config/db.php');

// ONLY CLIENT ACCESS
if($_SESSION['role'] != 'client'){
    echo "<p style='color:red;'>Access Denied</p>";
    exit();
}

$user_id = $_SESSION['user_id'];

// FETCH CLIENT ORDERS ONLY
$query = "SELECT * FROM orders WHERE client_id = $user_id ORDER BY id DESC";
$orders = mysqli_query($conn, $query);

// Get statistics
$totalOrders = mysqli_num_rows($orders);
$completedOrders = 0;
$inProgressOrders = 0;
$pendingOrders = 0;

$temp_orders = mysqli_query($conn, $query);
while($order = mysqli_fetch_assoc($temp_orders)) {
    if($order['status'] == 'Completed') $completedOrders++;
    elseif($order['status'] == 'In Progress') $inProgressOrders++;
    else $pendingOrders++;
}
?>

<!-- Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div class="content">
    <!-- Page Header -->
    <div class="page-header">
        <div class="header-content">
            <div class="header-icon">
                <i class="fas fa-user-circle"></i>
            </div>
            <div class="header-text">
                <h2>Client Dashboard</h2>
                <p>Welcome back, <?php echo htmlspecialchars($_SESSION['name']); ?>! Track your orders and their progress.</p>
            </div>
        </div>
    </div>

    <!-- Stats Summary Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="stat-info">
                <h4>Total Orders</h4>
                <p><?php echo $totalOrders; ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon pending">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-info">
                <h4>Pending</h4>
                <p><?php echo $pendingOrders; ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon progress">
                <i class="fas fa-spinner"></i>
            </div>
            <div class="stat-info">
                <h4>In Progress</h4>
                <p><?php echo $inProgressOrders; ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon completed">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-info">
                <h4>Completed</h4>
                <p><?php echo $completedOrders; ?></p>
            </div>
        </div>
    </div>

    <?php if(mysqli_num_rows($orders) == 0): ?>
        <div class="empty-state">
            <div class="empty-state-content">
                <i class="fas fa-box-open"></i>
                <h4>No Orders Yet</h4>
                <p>You don't have any orders at the moment. Check back later!</p>
            </div>
        </div>
    <?php else: ?>
    
    <!-- Orders Table -->
    <div class="table-container">
        <table class="orders-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Service Type</th>
                    <th>Status</th>
                    <th>Completion Date</th>
                    <th>Link Details</th>
                </tr>
            </thead>
            <tbody>
            <?php 
            // Reset result pointer
            mysqli_data_seek($orders, 0);
            while($order = mysqli_fetch_assoc($orders)): 
                $statusClass = '';
                $statusIcon = '';
                if($order['status'] == 'Completed') {
                    $statusClass = 'status-completed';
                    $statusIcon = 'fas fa-check-circle';
                } elseif($order['status'] == 'In Progress') {
                    $statusClass = 'status-progress';
                    $statusIcon = 'fas fa-spinner fa-pulse';
                } else {
                    $statusClass = 'status-pending';
                    $statusIcon = 'fas fa-hourglass-half';
                }
            ?>
                <tr class="table-row">
                    <td class="order-id">
                        <span class="order-badge">#<?php echo htmlspecialchars($order['order_id']); ?></span>
                    </td>
                    <td class="service-cell">
                        <span class="service-badge">
                            <i class="fas <?php echo $order['service_type'] == 'Guest Post' ? 'fa-pen-fancy' : 'fa-link'; ?>"></i>
                            <?php echo htmlspecialchars($order['service_type']); ?>
                        </span>
                    </td>
                    <td>
                        <span class="status-badge <?php echo $statusClass; ?>">
                            <i class="<?php echo $statusIcon; ?>"></i>
                            <?php echo htmlspecialchars($order['status']); ?>
                        </span>
                    </td>
                    <td class="date-cell">
                        <?php if($order['completion_date']): ?>
                            <i class="fas fa-calendar-check"></i>
                            <?php echo date('M d, Y', strtotime($order['completion_date'])); ?>
                        <?php else: ?>
                            <span class="na-text">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="links-cell">
                        <?php 
                        $links = mysqli_query($conn, "SELECT * FROM link_insertions WHERE order_id=".$order['id']);
                        if(mysqli_num_rows($links) == 0){
                            echo '<span class="no-links"><i class="fas fa-link"></i> No links yet</span>';
                        } else {
                            while($link = mysqli_fetch_assoc($links)){
                                echo '<div class="link-card">';
                                echo '<div class="link-info">';
                                echo '<div class="link-row"><i class="fas fa-globe"></i> <strong>Source:</strong> <a href="'.htmlspecialchars($link['source_url']).'" target="_blank" class="link-url">'.htmlspecialchars(substr($link['source_url'], 0, 40)).'...</a></div>';
                                echo '<div class="link-row"><i class="fas fa-anchor"></i> <strong>Anchor:</strong> <span class="anchor-text">'.htmlspecialchars($link['anchor_text']).'</span></div>';
                                echo '</div>';
                                
                                if(!empty($link['before_image']) || !empty($link['after_image'])){
                                    echo '<div class="link-images">';
                                    if(!empty($link['before_image'])){
                                        echo '<div class="image-wrapper">';
                                        echo '<img src="../link_insertions/upload/'.htmlspecialchars($link['before_image']).'" class="lightbox-img" alt="Before Image">';
                                        echo '<span class="image-label">Before</span>';
                                        echo '</div>';
                                    }
                                    if(!empty($link['after_image'])){
                                        echo '<div class="image-wrapper">';
                                        echo '<img src="../link_insertions/upload/'.htmlspecialchars($link['after_image']).'" class="lightbox-img" alt="After Image">';
                                        echo '<span class="image-label">After</span>';
                                        echo '</div>';
                                    }
                                    echo '</div>';
                                }
                                echo '</div>';
                            }
                        }
                        ?>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- Lightbox Overlay -->
<div class="lightbox-overlay" id="lightbox">
    <div class="lightbox-content">
        <img src="" id="lightbox-img">
        <button class="lightbox-close" id="lightboxClose">&times;</button>
    </div>
</div>

<style>
    .content {
        padding: 30px;
        min-height: 100vh;
        background: linear-gradient(135deg, #f5f7fa 0%, #eef2f7 100%);
    }

    /* Page Header */
    .page-header {
        background: linear-gradient(135deg, #44bd32 0%, #2e7d32 100%);
        border-radius: 20px;
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: 0 10px 30px rgba(68, 189, 50, 0.3);
        animation: slideDown 0.5s ease;
    }

    .header-content {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .header-icon {
        width: 60px;
        height: 60px;
        background: rgba(255,255,255,0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        animation: pulse 2s infinite;
    }

    .header-icon i {
        font-size: 30px;
        color: white;
    }

    .header-text h2 {
        color: white;
        margin: 0 0 5px 0;
        font-size: 28px;
    }

    .header-text p {
        color: rgba(255,255,255,0.9);
        margin: 0;
        font-size: 14px;
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        gap: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.15);
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .stat-icon i {
        font-size: 24px;
        color: white;
    }

    .stat-icon.pending {
        background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    }

    .stat-icon.progress {
        background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    }

    .stat-icon.completed {
        background: linear-gradient(135deg, #44bd32 0%, #2e7d32 100%);
    }

    .stat-info h4 {
        margin: 0 0 5px 0;
        font-size: 12px;
        color: #7f8c8d;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .stat-info p {
        margin: 0;
        font-size: 28px;
        font-weight: bold;
        color: #2c3e50;
    }

    /* Table Container */
    .table-container {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        animation: fadeInUp 0.6s ease;
        overflow-x: auto;
    }

    .orders-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 800px;
    }

    .orders-table thead {
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    }

    .orders-table th {
        padding: 15px;
        color: white;
        font-weight: 600;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 1px;
        text-align: left;
    }

    .table-row {
        border-bottom: 1px solid #ecf0f1;
        transition: all 0.3s ease;
    }

    .table-row:hover {
        background: #f8f9fa;
    }

    .orders-table td {
        padding: 15px;
        vertical-align: top;
    }

    /* Order Badge */
    .order-badge {
        display: inline-block;
        padding: 5px 12px;
        background: #ecf0f1;
        border-radius: 20px;
        font-size: 13px;
        color: #2c3e50;
        font-weight: 600;
    }

    /* Service Badge */
    .service-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 12px;
        background: #e8eaf6;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
        color: #3949ab;
    }

    /* Status Badge */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }

    .status-pending {
        background: #fff3cd;
        color: #856404;
    }

    .status-progress {
        background: #d1ecf1;
        color: #0c5460;
    }

    .status-completed {
        background: #d4edda;
        color: #155724;
    }

    /* Date Cell */
    .date-cell {
        color: #7f8c8d;
        font-size: 13px;
    }

    .date-cell i {
        margin-right: 5px;
    }

    .na-text {
        color: #95a5a6;
    }

    /* Links Cell */
    .links-cell {
        min-width: 300px;
    }

    .link-card {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 12px;
        margin-bottom: 10px;
        transition: all 0.3s ease;
    }

    .link-card:hover {
        background: #eef2f7;
        transform: translateX(3px);
    }

    .link-info {
        margin-bottom: 10px;
    }

    .link-row {
        font-size: 12px;
        margin-bottom: 5px;
        color: #2c3e50;
    }

    .link-row i {
        width: 20px;
        color: #667eea;
    }

    .link-url {
        color: #3498db;
        text-decoration: none;
    }

    .link-url:hover {
        text-decoration: underline;
    }

    .anchor-text {
        color: #2c3e50;
        font-weight: 500;
    }

    .link-images {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 8px;
    }

    .image-wrapper {
        position: relative;
        display: inline-block;
    }

    .lightbox-img {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid #e1e8ed;
    }

    .lightbox-img:hover {
        transform: scale(1.1);
        border-color: #667eea;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }

    .image-label {
        position: absolute;
        bottom: -18px;
        left: 50%;
        transform: translateX(-50%);
        font-size: 9px;
        color: #7f8c8d;
        white-space: nowrap;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .image-wrapper:hover .image-label {
        opacity: 1;
    }

    .no-links {
        color: #95a5a6;
        font-size: 12px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    /* Empty State */
    .empty-state {
        background: white;
        border-radius: 20px;
        padding: 60px 40px;
        text-align: center;
        box-shadow: 0 15px 40px rgba(0,0,0,0.1);
        animation: fadeInUp 0.6s ease;
    }

    .empty-state-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 15px;
    }

    .empty-state-content i {
        font-size: 64px;
        color: #bdc3c7;
    }

    .empty-state-content h4 {
        margin: 0;
        font-size: 20px;
        color: #2c3e50;
    }

    .empty-state-content p {
        margin: 0;
        color: #7f8c8d;
    }

    /* Lightbox */
    .lightbox-overlay {
        display: none;
        position: fixed;
        z-index: 9999;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.9);
        justify-content: center;
        align-items: center;
        animation: fadeIn 0.3s ease;
    }

    .lightbox-overlay.show {
        display: flex;
    }

    .lightbox-content {
        position: relative;
        max-width: 90%;
        max-height: 90%;
    }

    #lightbox-img {
        max-width: 100%;
        max-height: 85vh;
        border-radius: 10px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.3);
    }

    .lightbox-close {
        position: absolute;
        top: -40px;
        right: -40px;
        background: none;
        border: none;
        color: white;
        font-size: 35px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .lightbox-close:hover {
        transform: scale(1.2);
        color: #e74c3c;
    }

    /* Animations */
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes pulse {
        0%, 100% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.05);
        }
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .content {
            padding: 15px;
        }

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .stat-card {
            padding: 15px;
        }

        .stat-icon {
            width: 40px;
            height: 40px;
        }

        .stat-icon i {
            font-size: 18px;
        }

        .stat-info p {
            font-size: 22px;
        }

        .header-content {
            flex-direction: column;
            text-align: center;
        }

        .orders-table {
            font-size: 13px;
        }

        .orders-table th, .orders-table td {
            padding: 10px;
        }
    }

    @media (max-width: 480px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- Lightbox JS -->
<script>
const lightbox = document.getElementById('lightbox');
const lightboxImg = document.getElementById('lightbox-img');
const lightboxClose = document.getElementById('lightboxClose');

document.querySelectorAll('.lightbox-img').forEach(img => {
    img.addEventListener('click', () => {
        lightboxImg.src = img.src;
        lightbox.classList.add('show');
    });
});

function closeLightbox() {
    lightbox.classList.remove('show');
}

lightbox.addEventListener('click', (e) => {
    if (e.target === lightbox) {
        closeLightbox();
    }
});

lightboxClose.addEventListener('click', closeLightbox);

// Close with Escape key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && lightbox.classList.contains('show')) {
        closeLightbox();
    }
});
</script>

<?php include('../includes/footer.php'); ?>