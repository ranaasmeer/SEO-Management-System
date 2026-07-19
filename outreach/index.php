<?php 
include('../includes/header.php'); 
include('../includes/sidebar.php'); 
include('../config/db.php');

// Handle status update
$message = '';
$messageType = '';

if(isset($_POST['update_status'])){
    $outreach_id = $_POST['outreach_id'];
    $new_status = $_POST['response_status'];

    $update_query = "UPDATE outreach SET response_status='$new_status' WHERE id='$outreach_id'";
    if(mysqli_query($conn, $update_query)){
        $message = "Status updated successfully!";
        $messageType = 'success';
    } else {
        $message = "Error updating status: " . mysqli_error($conn);
        $messageType = 'error';
    }
}

// Handle delete
if(isset($_GET['delete_id'])){
    $delete_id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    
    // First check if record exists
    $check_query = mysqli_query($conn, "SELECT id FROM outreach WHERE id='$delete_id'");
    if(mysqli_num_rows($check_query) > 0){
        $delete_query = "DELETE FROM outreach WHERE id='$delete_id'";
        if(mysqli_query($conn, $delete_query)){
            $message = "Outreach record deleted successfully!";
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

// Fetch outreach records with associated order
$query = "SELECT * FROM outreach ORDER BY outreach_date DESC";

$result = mysqli_query($conn, $query);

// Get statistics
$totalCount = mysqli_num_rows($result);
$noResponseCount = 0;
$acceptedCount = 0;
$rejectedCount = 0;

$temp_result = mysqli_query($conn, $query);
while($row = mysqli_fetch_assoc($temp_result)) {
    $status = isset($row['response_status']) ? $row['response_status'] : 'No Response';
    if($status == 'No Response') $noResponseCount++;
    if($status == 'Accepted') $acceptedCount++;
    if($status == 'Rejected') $rejectedCount++;
}
?>

<!-- Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- Sweet Alert for delete confirmation -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="content">
    <!-- Page Header -->
    <div class="page-header">
        <div class="header-content">
            <div class="header-icon">
                <i class="fas fa-envelope-open-text"></i>
            </div>
            <div class="header-text">
                <h2>Outreach CRM</h2>
                <p>Manage your email outreach campaigns</p>
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
                <h4>Total Outreach</h4>
                <p><?php echo $totalCount; ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon pending">
                <i class="fas fa-hourglass-half"></i>
            </div>
            <div class="stat-info">
                <h4>No Response</h4>
                <p><?php echo $noResponseCount; ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon accepted">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-info">
                <h4>Accepted</h4>
                <p><?php echo $acceptedCount; ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon rejected">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="stat-info">
                <h4>Rejected</h4>
                <p><?php echo $rejectedCount; ?></p>
            </div>
        </div>
    </div>

    <!-- Action Bar -->
    <div class="action-bar">
        <?php if($_SESSION['role'] == 'admin'): ?>
        <a href="add.php" class="btn-add">
            <i class="fas fa-plus"></i>
            Add New Outreach
        </a>
        <?php endif; ?>
    </div>

    <!-- Table Container -->
    <div class="table-container">
        <table class="outreach-table">
            <thead>
                <tr>
                    <th>Website</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Outreach Date</th>
                    <th>Follow-up</th>
                    <th>Deal Price</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php 
            // Reset result pointer
            mysqli_data_seek($result, 0);
            while($row = mysqli_fetch_assoc($result)): 

                $status = isset($row['response_status']) ? $row['response_status'] : 'No Response';
                
                $statusClass = '';
                $statusIcon = '';
                if($status == 'No Response') {
                    $statusClass = 'status-no-response';
                    $statusIcon = 'fas fa-hourglass-half';
                } elseif($status == 'Accepted') {
                    $statusClass = 'status-accepted';
                    $statusIcon = 'fas fa-check-circle';
                } elseif($status == 'Rejected') {
                    $statusClass = 'status-rejected';
                    $statusIcon = 'fas fa-times-circle';
                }

                // Follow-up logic
                $followup = '';
                $followupClass = '';
                $followup_days = isset($row['followup_days']) ? intval($row['followup_days']) : 2;
                if($status == 'No Response' && !empty($row['outreach_date'])){
                    $days_since = (strtotime(date('Y-m-d')) - strtotime($row['outreach_date'])) / (60*60*24);
                    if($days_since >= $followup_days){
                        $followup = "Follow-up Due";
                        $followupClass = 'followup-due';
                    } else {
                        $days_left = $followup_days - $days_since;
                        $followup = "Due in " . ceil($days_left) . " days";
                        $followupClass = 'followup-soon';
                    }
                } elseif($status != 'No Response') {
                    $followup = "Completed";
                    $followupClass = 'followup-completed';
                }

                // Website clickable if URL exists
                $website_display = htmlspecialchars($row['website_name']);
                $website_link = !empty($row['website_name']) ? '<a href="' . htmlspecialchars($row['website_name']) . '" target="_blank" class="website-link"><i class="fas fa-external-link-alt"></i> ' . $website_display . '</a>' : $website_display;
            ?>
                <tr class="table-row" data-id="<?php echo $row['id']; ?>">
                    <td class="website-cell"><?php echo $website_link; ?></td>
                    <td class="email-cell">
                        <a href="mailto:<?php echo htmlspecialchars($row['email']); ?>" class="email-link">
                            <i class="fas fa-envelope"></i>
                            <?php echo htmlspecialchars($row['email']); ?>
                        </a>
                    </td>
                    <td>
                        <span class="status-badge <?php echo $statusClass; ?>">
                            <i class="<?php echo $statusIcon; ?>"></i>
                            <?php echo $status; ?>
                        </span>
                    </td>
                    <td class="date-cell">
                        <i class="fas fa-calendar-alt"></i>
                        <?php echo date('M d, Y', strtotime($row['outreach_date'])); ?>
                    </td>
                    <td>
                        <?php if($followup): ?>
                        <span class="followup-badge <?php echo $followupClass; ?>">
                            <i class="fas fa-bell"></i>
                            <?php echo $followup; ?>
                        </span>
                        <?php else: ?>
                            <span class="na-text">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="price-cell">
                        <?php if(!empty($row['deal_price'])): ?>
                        <span class="price-amount">$<?php echo number_format($row['deal_price'], 2); ?></span>
                        <?php else: ?>
                            <span class="na-text">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="actions-cell">
                        <div class="action-buttons">
                            <form method="POST" class="status-form">
                                <input type="hidden" name="outreach_id" value="<?php echo $row['id']; ?>">
                                <div class="status-select-wrapper">
                                    <select name="response_status" class="status-select">
                                        <option value="No Response" <?php if($status=='No Response') echo 'selected'; ?>>No Response</option>
                                        <option value="Accepted" <?php if($status=='Accepted') echo 'selected'; ?>>Accepted</option>
                                        <option value="Rejected" <?php if($status=='Rejected') echo 'selected'; ?>>Rejected</option>
                                    </select>
                                    <button type="submit" name="update_status" class="btn-update" title="Update Status">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </div>
                            </form>
                            <button onclick="confirmDelete(<?php echo $row['id']; ?>)" class="btn-delete" title="Delete Outreach">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
            
            <?php if($totalCount == 0): ?>
                <tr>
                    <td colspan="7" class="empty-state">
                        <div class="empty-state-content">
                            <i class="fas fa-envelope-open-text"></i>
                            <h4>No Outreach Records Found</h4>
                            <p>Click the "Add New Outreach" button to create your first outreach campaign</p>
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
            text: "You won't be able to revert this!",
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
        background: linear-gradient(135deg, #f2994a 0%, #f2c94c 100%);
    }

    .stat-icon.accepted {
        background: linear-gradient(135deg, #44bd32 0%, #2e7d32 100%);
    }

    .stat-icon.rejected {
        background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
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

    .outreach-table {
        width: 100%;
        border-collapse: collapse;
    }

    .outreach-table thead {
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    }

    .outreach-table th {
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

    .outreach-table td {
        padding: 15px;
        vertical-align: middle;
    }

    /* Website Link */
    .website-link {
        color: #3498db;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.3s ease;
    }

    .website-link:hover {
        color: #2980b9;
        text-decoration: underline;
    }

    /* Email Link */
    .email-link {
        color: #2c3e50;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.3s ease;
    }

    .email-link:hover {
        color: #3498db;
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

    .status-no-response {
        background: #fff3cd;
        color: #856404;
    }

    .status-accepted {
        background: #d4edda;
        color: #155724;
    }

    .status-rejected {
        background: #f8d7da;
        color: #721c24;
    }

    /* Date Cell */
    .date-cell {
        color: #7f8c8d;
        font-size: 13px;
    }

    .date-cell i {
        margin-right: 5px;
    }

    /* Follow-up Badge */
    .followup-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }

    .followup-due {
        background: #f8d7da;
        color: #721c24;
    }

    .followup-soon {
        background: #fff3cd;
        color: #856404;
    }

    .followup-completed {
        background: #d4edda;
        color: #155724;
    }

    /* Price Cell */
    .price-cell {
        font-weight: 600;
    }

    .price-amount {
        color: #27ae60;
        font-size: 14px;
    }

    .na-text {
        color: #95a5a6;
        font-size: 13px;
    }

    /* Actions Cell */
    .actions-cell {
        min-width: 160px;
    }

    .action-buttons {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .status-form {
        flex: 1;
        margin: 0;
    }

    .status-select-wrapper {
        display: flex;
        gap: 5px;
        align-items: center;
    }

    .status-select {
        flex: 1;
        padding: 8px 10px;
        border: 1px solid #e1e8ed;
        border-radius: 8px;
        font-size: 13px;
        background: white;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .status-select:focus {
        outline: none;
        border-color: #667eea;
    }

    .btn-update {
        padding: 8px 12px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 12px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: all 0.3s ease;
    }

    .btn-update:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }

    .btn-delete {
        padding: 8px 12px;
        background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 12px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 5px;
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
        .outreach-table {
            display: block;
            overflow-x: auto;
        }
    }

    @media (max-width: 768px) {
        .content {
            padding: 15px;
        }

        .stats-grid {
            grid-template-columns: 1fr 1fr;
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
        
        .status-select-wrapper {
            flex-direction: column;
        }
        
        .btn-update, .btn-delete {
            width: 100%;
            justify-content: center;
        }

        .header-content {
            flex-direction: column;
            text-align: center;
        }
    }

    @media (max-width: 480px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<?php include('../includes/footer.php'); ?>