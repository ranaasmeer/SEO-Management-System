<?php 
include('../includes/header.php'); 
include('../includes/sidebar.php'); 
include('../config/db.php');

// Get search term safely
$search = mysqli_real_escape_string($conn, $_GET['search'] ?? '');

// Build query
$query = "SELECT orders.*, users.name as client_name 
          FROM orders 
          JOIN users ON orders.client_id = users.id";

if(!empty($search)){
    $query .= " WHERE orders.order_id LIKE '%$search%' OR users.name LIKE '%$search%'";
}

// Add ordering
$query .= " ORDER BY orders.id DESC";

// Execute query
$result = mysqli_query($conn, $query);
?>

<!-- Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div class="content">
    <!-- Header Section with Animation -->
    <div class="page-header">
        <div class="header-content">
            <div class="header-icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="header-text">
                <h2>Orders Management</h2>
                <p>Manage and track all your orders in one place</p>
            </div>
        </div>
    </div>

    <!-- Stats Summary Cards -->
    <?php 
    // Get statistics for summary
    $totalOrders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM orders"))['total'];
    $completedOrders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM orders WHERE status='Completed'"))['total'];
    $pendingOrders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM orders WHERE status='Pending'"))['total'];
    $totalRevenue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(price) as total FROM orders WHERE status='Completed'"))['total'];
    ?>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-info">
                <h4>Total Orders</h4>
                <p><?php echo $totalOrders; ?></p>
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
            <div class="stat-icon revenue">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-info">
                <h4>Total Revenue</h4>
                <p>$<?php echo number_format($totalRevenue ?? 0, 2); ?></p>
            </div>
        </div>
    </div>

    <!-- Search and Action Bar -->
    <div class="action-bar">
        <form method="GET" class="search-form">
            <div class="search-wrapper">
                <i class="fas fa-search search-icon"></i>
                <input type="text" name="search" placeholder="Search by Order ID or Client Name..." 
                       value="<?php echo htmlspecialchars($search); ?>" 
                       class="search-input">
                <?php if(!empty($search)): ?>
                    <a href="index.php" class="clear-search">
                        <i class="fas fa-times"></i>
                    </a>
                <?php endif; ?>
            </div>
            <button type="submit" class="btn-search">
                <i class="fas fa-search"></i> Search
            </button>
        </form>
        <?php if($_SESSION['role'] == 'admin'): ?>
        <a href="add.php" class="btn-add">
            <i class="fas fa-plus"></i> Add New Order
        </a>
        <?php endif; ?>
    </div>

    <!-- Orders Table -->
    <div class="table-container">
        <table class="orders-table">
            <thead>
                <tr>
                    <th>Actions</th>
                    <th>Order ID</th>
                    <th>Client</th>
                    <th>Service</th>
                    <th>Status</th>
                    <th>Deadline</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
            <?php if($result && mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): 
                    $statusClass = '';
                    $statusIcon = '';
                    if($row['status'] == 'Completed') {
                        $statusClass = 'status-completed';
                        $statusIcon = 'fas fa-check-circle';
                    } elseif($row['status'] == 'In Progress') {
                        $statusClass = 'status-progress';
                        $statusIcon = 'fas fa-spinner fa-pulse';
                    } else {
                        $statusClass = 'status-pending';
                        $statusIcon = 'fas fa-hourglass-half';
                    }
                    
                    // Check if deadline is overdue
                    $deadlineClass = '';
                    if($row['deadline'] < date('Y-m-d') && $row['status'] != 'Completed') {
                        $deadlineClass = 'deadline-overdue';
                    }
                ?>
                    <tr class="table-row">
                        <td class="action-buttons">
                            <a href="edit.php?id=<?php echo $row['id']; ?>" class="action-btn edit-btn" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="delete.php?id=<?php echo $row['id']; ?>" class="action-btn delete-btn" title="Delete" onclick="return confirm('Are you sure you want to delete this order?')">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                            <a href="invoice.php?id=<?php echo $row['id']; ?>" target="_blank" class="action-btn invoice-btn" title="Invoice">
                                <i class="fas fa-file-invoice"></i>
                            </a>
                        </td>
                        <td class="order-id">#<?php echo htmlspecialchars($row['order_id']); ?></td>
                        <td>
                            <div class="client-info">
                                <i class="fas fa-user-circle"></i>
                                <?php echo htmlspecialchars($row['client_name']); ?>
                            </div>
                        </td>
                        <td>
                            <span class="service-badge">
                                <i class="fas <?php echo $row['service_type'] == 'Guest Post' ? 'fa-pen-fancy' : 'fa-link'; ?>"></i>
                                <?php echo htmlspecialchars($row['service_type']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="status-badge <?php echo $statusClass; ?>">
                                <i class="<?php echo $statusIcon; ?>"></i>
                                <?php echo htmlspecialchars($row['status']); ?>
                            </span>
                        </td>
                        <td class="<?php echo $deadlineClass; ?>">
                            <i class="fas fa-calendar-alt"></i>
                            <?php echo date('M d, Y', strtotime($row['deadline'])); ?>
                            <?php if($deadlineClass): ?>
                                <span class="overdue-badge">Overdue</span>
                            <?php endif; ?>
                        </td>
                        <td class="price-cell">
                            <span class="price-amount">$<?php echo number_format($row['price'], 2); ?></span>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="empty-state">
                        <div class="empty-state-content">
                            <i class="fas fa-box-open"></i>
                            <h4>No Orders Found</h4>
                            <p>Click the "Add New Order" button to create your first order</p>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
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
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 20px;
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
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
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
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
        animation: fadeInUp 0.5s ease;
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

    .stat-icon.completed {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }

    .stat-icon.pending {
        background: linear-gradient(135deg, #f2994a 0%, #f2c94c 100%);
    }

    .stat-icon.revenue {
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

    /* Action Bar */
    .action-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        margin-bottom: 30px;
        flex-wrap: wrap;
    }

    .search-form {
        display: flex;
        gap: 10px;
        flex: 1;
        max-width: 500px;
    }

    .search-wrapper {
        flex: 1;
        position: relative;
    }

    .search-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #95a5a6;
    }

    .search-input {
        width: 100%;
        padding: 12px 15px 12px 45px;
        border: 2px solid #e1e8ed;
        border-radius: 10px;
        font-size: 14px;
        transition: all 0.3s ease;
        background: white;
    }

    .search-input:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .clear-search {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #95a5a6;
        text-decoration: none;
        padding: 5px;
    }

    .clear-search:hover {
        color: #e74c3c;
    }

    .btn-search {
        padding: 12px 24px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-search:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }

    .btn-add {
        padding: 12px 24px;
        background: linear-gradient(135deg, #44bd32 0%, #2e7d32 100%);
        color: white;
        text-decoration: none;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-add:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(68, 189, 50, 0.4);
    }

    /* Table Container */
    .table-container {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        animation: fadeInUp 0.6s ease;
    }

    .orders-table {
        width: 100%;
        border-collapse: collapse;
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
        transform: scale(1.01);
    }

    .orders-table td {
        padding: 15px;
        vertical-align: middle;
    }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 8px;
    }

    .action-btn {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .edit-btn {
        background: #3498db;
        color: white;
    }

    .edit-btn:hover {
        background: #2980b9;
        transform: translateY(-2px);
    }

    .delete-btn {
        background: #e74c3c;
        color: white;
    }

    .delete-btn:hover {
        background: #c0392b;
        transform: translateY(-2px);
    }

    .invoice-btn {
        background: #00b894;
        color: white;
    }

    .invoice-btn:hover {
        background: #019874;
        transform: translateY(-2px);
    }

    /* Order ID */
    .order-id {
        font-weight: 600;
        color: #2c3e50;
    }

    /* Client Info */
    .client-info {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .client-info i {
        font-size: 18px;
        color: #3498db;
    }

    /* Service Badge */
    .service-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 12px;
        background: #ecf0f1;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
        color: #2c3e50;
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

    .status-completed {
        background: #d4edda;
        color: #155724;
    }

    .status-progress {
        background: #d1ecf1;
        color: #0c5460;
    }

    .status-pending {
        background: #fff3cd;
        color: #856404;
    }

    /* Deadline */
    .deadline-overdue {
        color: #e74c3c;
        font-weight: 600;
    }

    .overdue-badge {
        display: inline-block;
        margin-left: 8px;
        padding: 2px 6px;
        background: #e74c3c;
        color: white;
        border-radius: 4px;
        font-size: 10px;
        font-weight: 500;
    }

    /* Price Cell */
    .price-cell {
        font-weight: 600;
    }

    .price-amount {
        color: #2c3e50;
        font-size: 16px;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px !important;
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

    /* Responsive Design */
    @media (max-width: 768px) {
        .content {
            padding: 15px;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .action-bar {
            flex-direction: column;
        }

        .search-form {
            max-width: 100%;
            width: 100%;
        }

        .orders-table {
            display: block;
            overflow-x: auto;
        }

        .header-content {
            flex-direction: column;
            text-align: center;
        }
    }
</style>

<?php include('../includes/footer.php'); ?>