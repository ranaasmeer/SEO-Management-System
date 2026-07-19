<?php 
include('../includes/header.php'); 
include('../includes/sidebar.php'); 
include('../config/db.php');

// Handle Delete action
$message = '';
$messageType = '';

if(isset($_GET['delete_id'])){
    $delete_id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    
    // First check if record exists
    $check_query = mysqli_query($conn, "SELECT id FROM expenses WHERE id='$delete_id'");
    if(mysqli_num_rows($check_query) > 0){
        $delete_query = "DELETE FROM expenses WHERE id='$delete_id'";
        if(mysqli_query($conn, $delete_query)){
            $message = "Expense record deleted successfully!";
            $messageType = 'success';
        } else {
            $message = "Error deleting record: " . mysqli_error($conn);
            $messageType = 'error';
        }
    } else {
        $message = "Record not found!";
        $messageType = 'error';
    }
}

// TOTAL REVENUE (Only from completed orders) - Handle NULL properly
$rev = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(price), 0) as total FROM orders WHERE status = 'Completed'"));
$total_revenue = $rev['total'];

// TOTAL EXPENSES - Handle NULL properly
$exp = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(cost), 0) as total FROM expenses"));
$total_expenses = $exp['total'];

// PROFIT
$profit = $total_revenue - $total_expenses;

// Profit Margin Percentage - Prevent division by zero
$profit_margin = 0;
if($total_revenue > 0) {
    $profit_margin = ($profit / $total_revenue) * 100;
}

// EXPENSE LIST with LEFT JOIN to show orders even if no expenses
$query = "SELECT expenses.*, orders.order_id 
          FROM expenses
          LEFT JOIN orders ON expenses.order_id = orders.id
          ORDER BY expenses.id DESC";

$result = mysqli_query($conn, $query);

// Get expense statistics
$totalExpenseCount = mysqli_num_rows($result);
$avgExpense = ($totalExpenseCount > 0 && $total_expenses > 0) ? $total_expenses / $totalExpenseCount : 0;

// For the health bar - Prevent division by zero
$totalSum = $total_revenue + $total_expenses;
$revenuePercent = ($totalSum > 0) ? ($total_revenue / $totalSum) * 100 : 0;
$expensePercent = ($totalSum > 0) ? ($total_expenses / $totalSum) * 100 : 0;
?>

<!-- Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- Sweet Alert for confirmation -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="content">
    <!-- Page Header -->
    <div class="page-header">
        <div class="header-content">
            <div class="header-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="header-text">
                <h2>Expenses & Profit</h2>
                <p>Track your financial performance and manage expenses</p>
            </div>
        </div>
    </div>

    <!-- Message Alert -->
    <?php if($message): ?>
    <div class="alert alert-<?php echo $messageType; ?>">
        <i class="fas <?php echo $messageType == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
        <?php echo $message; ?>
    </div>
    <?php endif; ?>

    <!-- Stats Summary Cards -->
    <div class="stats-grid">
        <div class="stat-card revenue">
            <div class="stat-icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-info">
                <h4>Total Revenue</h4>
                <p>$<?php echo number_format($total_revenue, 2); ?></p>
                <small>From completed orders</small>
            </div>
        </div>
        
        <div class="stat-card expenses">
            <div class="stat-icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="stat-info">
                <h4>Total Expenses</h4>
                <p>$<?php echo number_format($total_expenses, 2); ?></p>
                <small><?php echo $totalExpenseCount; ?> expense records</small>
            </div>
        </div>
        
        <div class="stat-card profit">
            <div class="stat-icon">
                <i class="fas fa-chart-bar"></i>
            </div>
            <div class="stat-info">
                <h4>Net Profit</h4>
                <p class="<?php echo $profit >= 0 ? 'profit-positive' : 'profit-negative'; ?>">
                    $<?php echo number_format($profit, 2); ?>
                </p>
                <small>Revenue - Expenses</small>
            </div>
        </div>
        
        <div class="stat-card margin">
            <div class="stat-icon">
                <i class="fas fa-percent"></i>
            </div>
            <div class="stat-info">
                <h4>Profit Margin</h4>
                <p class="<?php echo $profit_margin >= 0 ? 'profit-positive' : 'profit-negative'; ?>">
                    <?php echo number_format($profit_margin, 1); ?>%
                </p>
                <small>Net profit / Revenue</small>
            </div>
        </div>
    </div>

    <!-- Financial Health Bar - Only show if there's data -->
    <?php if($total_revenue > 0 || $total_expenses > 0): ?>
    <div class="financial-health">
        <div class="health-bar-container">
            <div class="health-label">Revenue vs Expenses Ratio</div>
            <div class="health-bar-wrapper">
                <div class="health-bar-revenue" style="width: <?php echo min($revenuePercent, 100); ?>%">
                    <?php if($revenuePercent > 10): ?>
                    <span>Revenue <?php echo number_format($revenuePercent, 1); ?>%</span>
                    <?php endif; ?>
                </div>
                <div class="health-bar-expenses" style="width: <?php echo min($expensePercent, 100); ?>%">
                    <?php if($expensePercent > 10): ?>
                    <span>Expenses <?php echo number_format($expensePercent, 1); ?>%</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="financial-health empty-health">
        <div class="health-bar-container">
            <div class="health-label">Revenue vs Expenses Ratio</div>
            <div class="empty-health-message">
                <i class="fas fa-chart-simple"></i>
                <p>No financial data available yet. Add revenue from completed orders or record expenses to see analytics.</p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Action Bar -->
    <div class="action-bar">
        <a href="add.php" class="btn-add">
            <i class="fas fa-plus"></i>
            Add New Expense
        </a>
    </div>

    <!-- Table Container -->
    <div class="table-container">
        <table class="expenses-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Cost</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if($totalExpenseCount > 0): ?>
                <?php 
                // Reset result pointer
                mysqli_data_seek($result, 0);
                while($row = mysqli_fetch_assoc($result)): 
                    if(empty($row['id'])) continue; // Skip rows without expenses
                ?>
                <tr class="table-row" data-id="<?php echo $row['id']; ?>">
                    <td class="order-id">
                        <span class="order-badge">#<?php echo htmlspecialchars($row['order_id']); ?></span>
                    </td>
                    <td class="cost-cell">
                        <span class="cost-amount">$<?php echo number_format($row['cost'], 2); ?></span>
                    </td>
                    <td class="description-cell">
                        <div class="description-wrapper">
                            <i class="fas fa-file-alt"></i>
                            <?php echo htmlspecialchars($row['description']); ?>
                        </div>
                    </td>
                    <td class="actions-cell">
                        <button onclick="confirmDelete(<?php echo $row['id']; ?>)" class="btn-delete" title="Delete Expense">
                            <i class="fas fa-trash-alt"></i>
                            Delete
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="empty-state">
                        <div class="empty-state-content">
                            <i class="fas fa-receipt"></i>
                            <h4>No Expenses Found</h4>
                            <p>Click the "Add New Expense" button to record your first expense</p>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function confirmDelete(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this! This expense record will be permanently deleted.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74c3c',
            cancelButtonColor: '#95a5a6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
            background: 'white',
            backdrop: true,
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'index.php?delete_id=' + id;
            }
        });
    }
</script>

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

    /* Alert Messages */
    .alert {
        padding: 15px 20px;
        border-radius: 12px;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 12px;
        animation: fadeInUp 0.5s ease;
    }

    .alert i {
        font-size: 20px;
    }

    .alert-success {
        background: #d4edda;
        border-left: 4px solid #28a745;
        color: #155724;
    }

    .alert-error {
        background: #f8d7da;
        border-left: 4px solid #dc3545;
        color: #721c24;
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
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
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .stat-card.revenue .stat-icon {
        background: linear-gradient(135deg, #44bd32 0%, #2e7d32 100%);
    }

    .stat-card.expenses .stat-icon {
        background: linear-gradient(135deg, #e84118 0%, #c0392b 100%);
    }

    .stat-card.profit .stat-icon {
        background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    }

    .stat-card.margin .stat-icon {
        background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);
    }

    .stat-icon i {
        font-size: 24px;
        color: white;
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
        font-size: 24px;
        font-weight: bold;
    }

    .stat-info small {
        font-size: 11px;
        color: #95a5a6;
        display: block;
        margin-top: 5px;
    }

    .profit-positive {
        color: #44bd32;
    }

    .profit-negative {
        color: #e84118;
    }

    /* Financial Health Bar */
    .financial-health {
        background: white;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 30px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        animation: fadeInUp 0.6s ease;
    }

    .financial-health.empty-health {
        text-align: center;
    }

    .empty-health-message {
        padding: 30px;
        color: #95a5a6;
    }

    .empty-health-message i {
        font-size: 48px;
        margin-bottom: 15px;
        display: block;
    }

    .empty-health-message p {
        margin: 0;
        font-size: 14px;
    }

    .health-bar-container {
        width: 100%;
    }

    .health-label {
        font-size: 13px;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 10px;
    }

    .health-bar-wrapper {
        display: flex;
        height: 40px;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
    }

    .health-bar-revenue {
        background: linear-gradient(135deg, #44bd32 0%, #2e7d32 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: width 0.5s ease;
        min-width: 30px;
    }

    .health-bar-revenue span {
        color: white;
        font-size: 12px;
        font-weight: 600;
    }

    .health-bar-expenses {
        background: linear-gradient(135deg, #e84118 0%, #c0392b 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: width 0.5s ease;
        min-width: 30px;
    }

    .health-bar-expenses span {
        color: white;
        font-size: 12px;
        font-weight: 600;
    }

    /* Action Bar */
    .action-bar {
        margin-bottom: 30px;
        display: flex;
        justify-content: flex-end;
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

    .expenses-table {
        width: 100%;
        border-collapse: collapse;
    }

    .expenses-table thead {
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    }

    .expenses-table th {
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

    .expenses-table td {
        padding: 15px;
        vertical-align: middle;
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

    /* Cost Cell */
    .cost-amount {
        font-size: 16px;
        font-weight: 700;
        color: #e84118;
    }

    /* Description Cell */
    .description-wrapper {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #2c3e50;
    }

    .description-wrapper i {
        color: #667eea;
        font-size: 14px;
    }

    /* Actions Cell */
    .actions-cell {
        min-width: 100px;
    }

    .btn-delete {
        padding: 8px 16px;
        background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 12px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.3s ease;
    }

    .btn-delete:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(231, 76, 60, 0.4);
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
    @media (max-width: 1024px) {
        .expenses-table {
            display: block;
            overflow-x: auto;
        }
    }

    @media (max-width: 768px) {
        .content {
            padding: 15px;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .action-bar {
            justify-content: stretch;
        }

        .btn-add {
            width: 100%;
            justify-content: center;
        }

        .header-content {
            flex-direction: column;
            text-align: center;
        }

        .health-bar-wrapper {
            flex-direction: column;
            height: auto;
        }

        .health-bar-revenue, .health-bar-expenses {
            padding: 8px;
            justify-content: center;
        }

        .btn-delete {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<?php include('../includes/footer.php'); ?>