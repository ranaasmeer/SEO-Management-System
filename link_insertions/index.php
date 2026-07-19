<?php 
include('../includes/header.php'); 
include('../includes/sidebar.php'); 
include('../config/db.php');

                // Function to get correct image path
if (!function_exists('getImagePath')) {
    function getImagePath($image) {
        if(empty($image)) return null;
        if(file_exists("upload/" . $image)) return "upload/" . $image;
        if(file_exists("../link_insertions/upload/" . $image)) return "../link_insertions/upload/" . $image;
        return null;
    }
}

// Auto-update link insertion status based on order status
mysqli_query($conn, "
UPDATE link_insertions li
JOIN orders o ON li.order_id = o.id
SET li.status = 'Done'
WHERE o.status = 'Completed'
");

// FIXED: Added this query to set status to 'Pending' when order is not completed
mysqli_query($conn, "
UPDATE link_insertions li
JOIN orders o ON li.order_id = o.id
SET li.status = 'Pending'
WHERE o.status != 'Completed'
");

// Fetch link insertions with associated order
$query = "SELECT li.*, o.order_id, o.status AS order_status
          FROM link_insertions li
          JOIN orders o ON li.order_id = o.id
          ORDER BY li.id DESC";
$result = mysqli_query($conn, $query);

// Get statistics
$totalCount = mysqli_num_rows($result);
$pendingCount = 0;
$doneCount = 0;

$temp_result = mysqli_query($conn, $query);
while($row = mysqli_fetch_assoc($temp_result)) {
    if($row['status'] == 'Pending') $pendingCount++;
    if($row['status'] == 'Done') $doneCount++;
}
?>

<!-- Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div class="content">
    <!-- Page Header -->
    <div class="page-header">
        <div class="header-content">
            <div class="header-icon">
                <i class="fas fa-link"></i>
            </div>
            <div class="header-text">
                <h2>Link Insertions</h2>
                <p>Manage all your link insertion campaigns</p>
            </div>
        </div>
    </div>

    <!-- Stats Summary Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-info">
                <h4>Total Insertions</h4>
                <p><?php echo $totalCount; ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon pending">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-info">
                <h4>Pending</h4>
                <p><?php echo $pendingCount; ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon completed">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-info">
                <h4>Completed</h4>
                <p><?php echo $doneCount; ?></p>
            </div>
        </div>
    </div>

    <!-- Action Bar -->
    <div class="action-bar">
        <?php if($_SESSION['role'] == 'admin'): ?>
        <a href="add.php" class="btn-add">
            <i class="fas fa-plus"></i>
            Add New Link Insertion
        </a>
        <?php endif; ?>
    </div>

    <!-- Table Container -->
    <div class="table-container">
        <table class="link-insertions-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Source URL</th>
                    <th>Anchor Text</th>
                    <th>Status</th>
                    <th>Images</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php 
            // Reset result pointer
            mysqli_data_seek($result, 0);
            while($row = mysqli_fetch_assoc($result)):
                $status = !empty($row['status']) ? $row['status'] : 'Pending';
                
                $statusClass = '';
                $statusIcon = '';
                if($status == 'Pending') {
                    $statusClass = 'status-pending';
                    $statusIcon = 'fas fa-hourglass-half';
                } elseif($status == 'Done') {
                    $statusClass = 'status-done';
                    $statusIcon = 'fas fa-check-circle';
                } elseif($status == 'Rejected') {
                    $statusClass = 'status-rejected';
                    $statusIcon = 'fas fa-times-circle';
                }
                

                
                $before_image = getImagePath($row['before_image']);
                $after_image = getImagePath($row['after_image']);
            ?>
                <tr class="table-row">
                    <td class="order-id">
                        <span class="order-badge">#<?php echo htmlspecialchars($row['order_id']); ?></span>
                    </td>
                    <td class="source-url">
                        <?php if($row['source_url']): ?>
                        <a href="<?php echo htmlspecialchars($row['source_url']); ?>" target="_blank" class="url-link">
                            <i class="fas fa-external-link-alt"></i>
                            <?php echo htmlspecialchars(substr($row['source_url'], 0, 40)) . (strlen($row['source_url']) > 40 ? '...' : ''); ?>
                        </a>
                        <?php else: ?>
                            <span class="na-text">N/A</span>
                        <?php endif; ?>
                    </td>
                    <td class="anchor-text">
                        <span class="anchor-badge">
                            <i class="fas fa-anchor"></i>
                            <?php echo htmlspecialchars($row['anchor_text']); ?>
                        </span>
                    </td>
                    <td>
                        <span class="status-badge <?php echo $statusClass; ?>">
                            <i class="<?php echo $statusIcon; ?>"></i>
                            <?php echo htmlspecialchars($status); ?>
                        </span>
                    </td>
                    <td class="images-cell">
                        <div class="images-wrapper">
                            <?php if($before_image): ?>
                            <a href="<?php echo $before_image; ?>" target="_blank" class="image-link" title="Before Image">
                                <img src="<?php echo $before_image; ?>" width="45" height="45" class="thumbnail" alt="Before">
                                <span class="image-label">Before</span>
                            </a>
                            <?php endif; ?>

                            <?php if($after_image): ?>
                            <a href="<?php echo $after_image; ?>" target="_blank" class="image-link" title="After Image">
                                <img src="<?php echo $after_image; ?>" width="45" height="45" class="thumbnail" alt="After">
                                <span class="image-label">After</span>
                            </a>
                            <?php endif; ?>
                            
                            <?php if(!$before_image && !$after_image): ?>
                                <span class="no-image">
                                    <i class="fas fa-image"></i>
                                    No images
                                </span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <a href="edit.php?id=<?php echo $row['id']; ?>" class="action-btn edit-btn" title="Edit Link Insertion">
                            <i class="fas fa-edit"></i>
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
            
            <?php if($totalCount == 0): ?>
                <tr>
                    <td colspan="6" class="empty-state">
                        <div class="empty-state-content">
                            <i class="fas fa-link"></i>
                            <h4>No Link Insertions Found</h4>
                            <p>Click the "Add New Link Insertion" button to create your first link insertion</p>
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

    .link-insertions-table {
        width: 100%;
        border-collapse: collapse;
    }

    .link-insertions-table thead {
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    }

    .link-insertions-table th {
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

    .link-insertions-table td {
        padding: 15px;
        vertical-align: middle;
    }

    /* Order Badge */
    .order-id {
        font-weight: 600;
    }

    .order-badge {
        display: inline-block;
        padding: 5px 12px;
        background: #ecf0f1;
        border-radius: 20px;
        font-size: 13px;
        color: #2c3e50;
    }

    /* Source URL */
    .url-link {
        color: #3498db;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        transition: all 0.3s ease;
    }

    .url-link:hover {
        color: #2980b9;
        text-decoration: underline;
    }

    /* Anchor Text */
    .anchor-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 12px;
        background: #f8f9fa;
        border-radius: 20px;
        font-size: 13px;
        color: #2c3e50;
    }

    .anchor-badge i {
        color: #9b59b6;
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

    .status-done {
        background: #d4edda;
        color: #155724;
    }

    .status-rejected {
        background: #f8d7da;
        color: #721c24;
    }

    /* Images Cell */
    .images-cell {
        min-width: 130px;
    }

    .images-wrapper {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }

    .image-link {
        position: relative;
        display: inline-block;
        text-decoration: none;
    }

    .thumbnail {
        border-radius: 8px;
        border: 2px solid #e1e8ed;
        object-fit: cover;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .thumbnail:hover {
        transform: scale(1.5);
        border-color: #667eea;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        z-index: 10;
        position: relative;
    }

    .image-label {
        position: absolute;
        bottom: -20px;
        left: 50%;
        transform: translateX(-50%);
        font-size: 10px;
        color: #7f8c8d;
        white-space: nowrap;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .image-link:hover .image-label {
        opacity: 1;
    }

    .no-image {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 12px;
        color: #95a5a6;
    }

    /* Action Button */
    .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 35px;
        height: 35px;
        background: #3498db;
        color: white;
        border-radius: 8px;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .action-btn:hover {
        background: #2980b9;
        transform: translateY(-2px);
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

        .link-insertions-table {
            display: block;
            overflow-x: auto;
        }

        .header-content {
            flex-direction: column;
            text-align: center;
        }

        .images-wrapper {
            flex-direction: column;
        }
    }
</style>

<?php include('../includes/footer.php'); ?>