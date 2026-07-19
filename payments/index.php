<?php  
include('../includes/header.php'); 
include('../includes/sidebar.php'); 
include('../config/db.php');

// 1️⃣ Auto-update all pending payments whose clearance date has passed
mysqli_query($conn, "
    UPDATE payments 
    SET payment_status = 'Cleared' 
    WHERE payment_status = 'Pending' 
    AND clearance_date <= CURDATE()
");

// 2️⃣ Handle Withdraw action
$message = '';
$messageType = '';

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['withdraw_payment'])){

    $payment_id = intval($_POST['payment_id']);

    $query = "UPDATE payments 
              SET payment_status = 'Withdrawn' 
              WHERE id = $payment_id";

    if(mysqli_query($conn, $query)){
        $message = "Payment withdrawn successfully!";
        $messageType = 'success';
    } else {
        $message = "Error: " . mysqli_error($conn);
        $messageType = 'error';
    }
}

// 3️⃣ Handle Delete action
if(isset($_GET['delete_id'])){
    $delete_id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    
    // First check if record exists
    $check_query = mysqli_query($conn, "SELECT id FROM payments WHERE id='$delete_id'");
    if(mysqli_num_rows($check_query) > 0){
        $delete_query = "DELETE FROM payments WHERE id='$delete_id'";
        if(mysqli_query($conn, $delete_query)){
            $message = "Payment record deleted successfully!";
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

// 4️⃣ Fetch payments
$query = "SELECT payments.*, orders.order_id 
          FROM payments
          LEFT JOIN orders ON payments.order_id = orders.id
          ORDER BY payments.id DESC";
$result = mysqli_query($conn, $query);

// Get statistics
$totalPayments = mysqli_num_rows($result);
$totalAmount = 0;
$pendingCount = 0;
$clearedCount = 0;
$withdrawnCount = 0;

$temp_result = mysqli_query($conn, $query);
while($row = mysqli_fetch_assoc($temp_result)) {
    $totalAmount += $row['amount'];
    if($row['payment_status'] == 'Pending') $pendingCount++;
    if($row['payment_status'] == 'Cleared') $clearedCount++;
    if($row['payment_status'] == 'Withdrawn') $withdrawnCount++;
}
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
                <i class="fas fa-credit-card"></i>
            </div>
            <div class="header-text">
                <h2>Payments Management</h2>
                <p>Track and manage all payment transactions</p>
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
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-info">
                <h4>Total Payments</h4>
                <p><?php echo $totalPayments; ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon total">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-info">
                <h4>Total Amount</h4>
                <p>$<?php echo number_format($totalAmount, 2); ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon pending">
                <i class="fas fa-hourglass-half"></i>
            </div>
            <div class="stat-info">
                <h4>Pending</h4>
                <p><?php echo $pendingCount; ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon cleared">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-info">
                <h4>Cleared</h4>
                <p><?php echo $clearedCount; ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon withdrawn">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="stat-info">
                <h4>Withdrawn</h4>
                <p><?php echo $withdrawnCount; ?></p>
            </div>
        </div>
    </div>

    <!-- Action Bar -->
    <div class="action-bar">
        <?php if($_SESSION['role'] == 'admin'): ?>
        <a href="add.php" class="btn-add">
            <i class="fas fa-plus"></i>
            Add New Payment
        </a>
        <?php endif; ?>
    </div>

    <!-- Table Container -->
    <div class="table-container">
        <table class="payments-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Amount</th>
                    <th>Completion Date</th>
                    <th>Clearance Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php 
            // Reset result pointer
            mysqli_data_seek($result, 0);
            while($row = mysqli_fetch_assoc($result)): 
                $status = $row['payment_status'];
                
                $statusClass = '';
                $statusIcon = '';
                $statusText = '';
                
                if($status == 'Pending') {
                    $statusClass = 'status-pending';
                    $statusIcon = 'fas fa-hourglass-half';
                    $statusText = 'Pending';
                } elseif($status == 'Cleared') {
                    $statusClass = 'status-cleared';
                    $statusIcon = 'fas fa-check-circle';
                    $statusText = 'Cleared (Ready)';
                } elseif($status == 'Withdrawn') {
                    $statusClass = 'status-withdrawn';
                    $statusIcon = 'fas fa-money-bill-wave';
                    $statusText = 'Withdrawn';
                }
                
                // Check if clearance date is approaching
                $clearanceDate = strtotime($row['clearance_date']);
                $today = strtotime(date('Y-m-d'));
                $daysUntilClearance = ceil(($clearanceDate - $today) / (60 * 60 * 24));
                
                $clearanceClass = '';
                $clearanceWarning = '';
                if($status == 'Pending' && $daysUntilClearance <= 3 && $daysUntilClearance > 0) {
                    $clearanceClass = 'clearance-warning';
                    $clearanceWarning = '<span class="warning-badge"><i class="fas fa-exclamation-triangle"></i> ' . $daysUntilClearance . ' days left</span>';
                } elseif($status == 'Pending' && $daysUntilClearance <= 0) {
                    $clearanceClass = 'clearance-overdue';
                    $clearanceWarning = '<span class="overdue-badge"><i class="fas fa-clock"></i> Ready for clearance</span>';
                }
            ?>
                <tr class="table-row" data-id="<?php echo $row['id']; ?>">
                    <td class="order-id">
                        <span class="order-badge">#<?php echo htmlspecialchars($row['order_id']); ?></span>
                    </td>
                    <td class="amount-cell">
                        <span class="amount-amount">$<?php echo number_format($row['amount'], 2); ?></span>
                    </td>
                    <td class="date-cell">
                        <i class="fas fa-calendar-check"></i>
                        <?php echo date('M d, Y', strtotime($row['completion_date'])); ?>
                    </td>
                    <td class="date-cell <?php echo $clearanceClass; ?>">
                        <i class="fas fa-calendar-alt"></i>
                        <?php echo date('M d, Y', strtotime($row['clearance_date'])); ?>
                        <?php echo $clearanceWarning; ?>
                    </td>
                    <td>
                        <span class="status-badge <?php echo $statusClass; ?>">
                            <i class="<?php echo $statusIcon; ?>"></i>
                            <?php echo $statusText; ?>
                        </span>
                    </td>
                    <td class="actions-cell">
                        <div class="action-buttons">
                            <?php if($status == 'Cleared'): ?>
                          <form method="POST" class="withdraw-form">
    <input type="hidden" name="payment_id" value="<?php echo $row['id']; ?>">
    <input type="hidden" name="withdraw_payment" value="1"> <!-- ✅ ADD THIS -->

    <button type="button" 
        class="btn-withdraw" 
        onclick="confirmWithdraw(this)">
        <i class="fas fa-hand-holding-usd"></i>
        Withdraw
    </button>
</form>
                            <?php endif; ?>
                            <button onclick="confirmDelete(<?php echo $row['id']; ?>)" class="btn-delete" title="Delete Payment">
                                <i class="fas fa-trash-alt"></i>
                                Delete
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
            
            <?php if($totalPayments == 0): ?>
                <tr>
                    <td colspan="6" class="empty-state">
                        <div class="empty-state-content">
                            <i class="fas fa-credit-card"></i>
                            <h4>No Payments Found</h4>
                            <p>Click the "Add New Payment" button to create your first payment record</p>
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
            text: "You won't be able to revert this! This payment record will be permanently deleted.",
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
    
function confirmWithdraw(btn) {
    Swal.fire({
        title: 'Confirm Withdrawal',
        text: "Are you sure you want to withdraw this payment?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#e84118',
        cancelButtonColor: '#95a5a6',
        confirmButtonText: 'Yes, withdraw it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            btn.closest('form').submit(); // ✅ always correct
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

    .stat-icon.total {
        background: linear-gradient(135deg, #44bd32 0%, #2e7d32 100%);
    }

    .stat-icon.pending {
        background: linear-gradient(135deg, #f2994a 0%, #f2c94c 100%);
    }

    .stat-icon.cleared {
        background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    }

    .stat-icon.withdrawn {
        background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);
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

    .payments-table {
        width: 100%;
        border-collapse: collapse;
    }

    .payments-table thead {
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    }

    .payments-table th {
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

    .payments-table td {
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

    /* Amount Cell */
    .amount-amount {
        font-size: 16px;
        font-weight: 700;
        color: #27ae60;
    }

    /* Date Cell */
    .date-cell {
        color: #7f8c8d;
        font-size: 13px;
    }

    .date-cell i {
        margin-right: 5px;
    }

    .clearance-warning {
        color: #f39c12;
        font-weight: 500;
    }

    .clearance-overdue {
        color: #e74c3c;
        font-weight: 600;
    }

    .warning-badge {
        display: inline-block;
        margin-left: 8px;
        padding: 2px 6px;
        background: #f39c12;
        color: white;
        border-radius: 4px;
        font-size: 10px;
        font-weight: 500;
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

    .status-cleared {
        background: #d4edda;
        color: #155724;
    }

    .status-withdrawn {
        background: #d1ecf1;
        color: #0c5460;
    }

    /* Actions Cell */
    .actions-cell {
        min-width: 180px;
    }

    .action-buttons {
        display: flex;
        gap: 8px;
        align-items: center;
        flex-wrap: wrap;
    }

    .btn-withdraw {
        padding: 8px 16px;
        background: linear-gradient(135deg, #e84118 0%, #c0392b 100%);
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

    .btn-withdraw:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(232, 65, 24, 0.4);
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

    .na-text {
        color: #95a5a6;
        font-size: 13px;
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
        .payments-table {
            display: block;
            overflow-x: auto;
        }
    }

    @media (max-width: 768px) {
        .content {
            padding: 15px;
        }

        .stats-grid {
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        }

        .action-bar {
            justify-content: stretch;
        }

        .btn-add {
            width: 100%;
            justify-content: center;
        }

        .action-buttons {
            flex-direction: column;
        }
        
        .btn-withdraw, .btn-delete {
            width: 100%;
            justify-content: center;
        }

        .header-content {
            flex-direction: column;
            text-align: center;
        }
    }
</style>

<?php include('../includes/footer.php'); ?>