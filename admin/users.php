<?php 
include('../includes/header.php'); 
include('../includes/sidebar.php'); 
include('../config/db.php');

// Check if user is admin
if($_SESSION['role'] != 'admin'){
    echo "<p style='color:red;'>Access Denied</p>";
    exit();
}

// First, add status column to users table if not exists
$check_column = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'status'");
if(mysqli_num_rows($check_column) == 0) {
    mysqli_query($conn, "ALTER TABLE users ADD COLUMN status ENUM('active','blocked') DEFAULT 'active'");
}

// Handle Block/Unblock User
$message = '';
$messageType = '';

if(isset($_GET['block_id'])){
    $block_id = mysqli_real_escape_string($conn, $_GET['block_id']);
    
    // Don't allow admin to block themselves
    if($block_id == $_SESSION['user_id']){
        $message = "You cannot block your own account!";
        $messageType = 'error';
    } else {
        $update_query = "UPDATE users SET status = 'blocked' WHERE id = '$block_id'";
        if(mysqli_query($conn, $update_query)){
            $message = "User blocked successfully!";
            $messageType = 'success';
        } else {
            $message = "Error blocking user: " . mysqli_error($conn);
            $messageType = 'error';
        }
    }
}

if(isset($_GET['unblock_id'])){
    $unblock_id = mysqli_real_escape_string($conn, $_GET['unblock_id']);
    $update_query = "UPDATE users SET status = 'active' WHERE id = '$unblock_id'";
    if(mysqli_query($conn, $update_query)){
        $message = "User unblocked successfully!";
        $messageType = 'success';
    } else {
        $message = "Error unblocking user: " . mysqli_error($conn);
        $messageType = 'error';
    }
}

// Handle Delete User
if(isset($_GET['delete_id'])){
    $delete_id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    
    // Don't allow admin to delete themselves
    if($delete_id == $_SESSION['user_id']){
        $message = "You cannot delete your own account!";
        $messageType = 'error';
    } else {
        $check_query = mysqli_query($conn, "SELECT id FROM users WHERE id='$delete_id'");
        if(mysqli_num_rows($check_query) > 0){
            $delete_query = "DELETE FROM users WHERE id='$delete_id'";
            if(mysqli_query($conn, $delete_query)){
                $message = "User deleted successfully!";
                $messageType = 'success';
            } else {
                $message = "Error deleting user: " . mysqli_error($conn);
                $messageType = 'error';
            }
        } else {
            $message = "User not found!";
            $messageType = 'error';
        }
    }
}

// Handle Role Update
if(isset($_POST['update_role'])){
    $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
    $new_role = mysqli_real_escape_string($conn, $_POST['role']);
    
    // Don't allow admin to change their own role
    if($user_id == $_SESSION['user_id']){
        $message = "You cannot change your own role!";
        $messageType = 'error';
    } else {
        $update_query = "UPDATE users SET role='$new_role' WHERE id='$user_id'";
        if(mysqli_query($conn, $update_query)){
            $message = "User role updated successfully!";
            $messageType = 'success';
        } else {
            $message = "Error updating role: " . mysqli_error($conn);
            $messageType = 'error';
        }
    }
}

// Fetch all users
$query = "SELECT * FROM users ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);

// Get statistics
$totalUsers = mysqli_num_rows($result);
$adminCount = 0;
$freelancerCount = 0;
$clientCount = 0;
$activeCount = 0;
$blockedCount = 0;

$temp_result = mysqli_query($conn, $query);
while($row = mysqli_fetch_assoc($temp_result)) {
    if($row['role'] == 'admin') $adminCount++;
    if($row['role'] == 'freelancer') $freelancerCount++;
    if($row['role'] == 'client') $clientCount++;
    if($row['status'] == 'active') $activeCount++;
    if($row['status'] == 'blocked') $blockedCount++;
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
                <i class="fas fa-users-cog"></i>
            </div>
            <div class="header-text">
                <h2>Admin Panel</h2>
                <p>Manage users, roles, permissions, and access</p>
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
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-info">
                <h4>Total Users</h4>
                <p><?php echo $totalUsers; ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon admin">
                <i class="fas fa-user-shield"></i>
            </div>
            <div class="stat-info">
                <h4>Admins</h4>
                <p><?php echo $adminCount; ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon freelancer">
                <i class="fas fa-user-tie"></i>
            </div>
            <div class="stat-info">
                <h4>Freelancers</h4>
                <p><?php echo $freelancerCount; ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon client">
                <i class="fas fa-user"></i>
            </div>
            <div class="stat-info">
                <h4>Clients</h4>
                <p><?php echo $clientCount; ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon active">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-info">
                <h4>Active</h4>
                <p><?php echo $activeCount; ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon blocked">
                <i class="fas fa-ban"></i>
            </div>
            <div class="stat-info">
                <h4>Blocked</h4>
                <p><?php echo $blockedCount; ?></p>
            </div>
        </div>
    </div>

    <!-- Search and Filter Bar -->
    <div class="filter-bar">
        <div class="search-wrapper">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="searchInput" placeholder="Search by name or email..." class="search-input">
        </div>
        <div class="filter-wrapper">
            <select id="roleFilter" class="filter-select">
                <option value="all">All Roles</option>
                <option value="admin">Admin</option>
                <option value="freelancer">Freelancer</option>
                <option value="client">Client</option>
            </select>
            <select id="statusFilter" class="filter-select">
                <option value="all">All Status</option>
                <option value="active">Active</option>
                <option value="blocked">Blocked</option>
            </select>
        </div>
    </div>

    <!-- Action Bar -->
    <div class="action-bar">
        <a href="add.php" class="btn-add">
            <i class="fas fa-plus"></i>
            Add New User
        </a>
    </div>

    <!-- Table Container -->
    <div class="table-container">
        <table class="users-table" id="usersTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Joined Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php 
            // Reset result pointer
            mysqli_data_seek($result, 0);
            while($row = mysqli_fetch_assoc($result)): 
                $roleClass = '';
                $roleIcon = '';
                if($row['role'] == 'admin') {
                    $roleClass = 'role-admin';
                    $roleIcon = 'fas fa-user-shield';
                } elseif($row['role'] == 'freelancer') {
                    $roleClass = 'role-freelancer';
                    $roleIcon = 'fas fa-user-tie';
                } else {
                    $roleClass = 'role-client';
                    $roleIcon = 'fas fa-user';
                }
                
                $statusClass = ($row['status'] == 'active') ? 'status-active' : 'status-blocked';
                $statusIcon = ($row['status'] == 'active') ? 'fa-check-circle' : 'fa-ban';
                $statusText = ($row['status'] == 'active') ? 'Active' : 'Blocked';
                
                $isCurrentUser = ($row['id'] == $_SESSION['user_id']);
            ?>
                <tr class="table-row" data-role="<?php echo $row['role']; ?>" data-status="<?php echo $row['status']; ?>">
                    <td class="user-id">#<?php echo $row['id']; ?></td>
                    <td class="user-name">
                        <div class="user-info">
                            <div class="user-avatar" style="background: linear-gradient(135deg, <?php 
                                echo ($row['role'] == 'admin') ? '#e74c3c' : (($row['role'] == 'freelancer') ? '#3498db' : '#44bd32'); 
                            ?>, <?php 
                                echo ($row['role'] == 'admin') ? '#c0392b' : (($row['role'] == 'freelancer') ? '#2980b9' : '#2e7d32'); 
                            ?>);">
                                <i class="fas fa-user-circle"></i>
                            </div>
                            <div class="user-details">
                                <span class="user-fullname"><?php echo htmlspecialchars($row['name']); ?></span>
                                <?php if($isCurrentUser): ?>
                                    <span class="current-user-badge">You</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td class="user-email">
                        <a href="mailto:<?php echo htmlspecialchars($row['email']); ?>" class="email-link">
                            <i class="fas fa-envelope"></i>
                            <?php echo htmlspecialchars($row['email']); ?>
                        </a>
                    </td>
                    <td>
                        <?php if(!$isCurrentUser): ?>
<form method="POST" class="role-form" id="roleForm_<?php echo $row['id']; ?>">
    <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
    <input type="hidden" name="update_role" value="1"> <!-- ✅ FIX -->
    
    <div class="role-select-wrapper">
        <select name="role" class="role-select <?php echo $roleClass; ?>" onchange="confirmRoleChange(<?php echo $row['id']; ?>, this.value)">
            <option value="admin" <?php if($row['role'] == 'admin') echo 'selected'; ?>>👑 Admin</option>
            <option value="freelancer" <?php if($row['role'] == 'freelancer') echo 'selected'; ?>>💼 Freelancer</option>
            <option value="client" <?php if($row['role'] == 'client') echo 'selected'; ?>>👤 Client</option>
        </select>
    </div>
</form>
                        <?php else: ?>
                            <span class="role-badge <?php echo $roleClass; ?>">
                                <i class="<?php echo $roleIcon; ?>"></i>
                                <?php echo ucfirst($row['role']); ?>
                            </span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="status-badge <?php echo $statusClass; ?>">
                            <i class="fas <?php echo $statusIcon; ?>"></i>
                            <?php echo $statusText; ?>
                        </span>
                    </td>
                    <td class="date-cell">
                        <i class="fas fa-calendar-alt"></i>
                        <?php echo date('M d, Y', strtotime($row['created_at'])); ?>
                    </td>
                    <td class="actions-cell">
                        <div class="action-buttons">
                            <button onclick="viewDetails(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['name']); ?>', '<?php echo htmlspecialchars($row['email']); ?>', '<?php echo $row['role']; ?>', '<?php echo $row['status']; ?>', '<?php echo date('M d, Y h:i A', strtotime($row['created_at'])); ?>')" class="btn-view" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            
                            <?php if(!$isCurrentUser): ?>
                                <?php if($row['status'] == 'active'): ?>
                                <button onclick="confirmBlock(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['name']); ?>')" class="btn-block" title="Block User">
                                    <i class="fas fa-ban"></i>
                                </button>
                                <?php else: ?>
                                <button onclick="confirmUnblock(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['name']); ?>')" class="btn-unblock" title="Unblock User">
                                    <i class="fas fa-check-circle"></i>
                                </button>
                                <?php endif; ?>
                                
                                <button onclick="confirmDelete(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['name']); ?>')" class="btn-delete" title="Delete User">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
            
            <?php if($totalUsers == 0): ?>
                <tr>
                    <td colspan="7" class="empty-state">
                        <div class="empty-state-content">
                            <i class="fas fa-users"></i>
                            <h4>No Users Found</h4>
                            <p>Click the "Add New User" button to create your first user</p>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- User Details Modal -->
<div id="userModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-user-circle"></i> User Details</h3>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body">
            <div class="detail-row">
                <div class="detail-label"><i class="fas fa-user"></i> Name:</div>
                <div class="detail-value" id="detailName"></div>
            </div>
            <div class="detail-row">
                <div class="detail-label"><i class="fas fa-envelope"></i> Email:</div>
                <div class="detail-value" id="detailEmail"></div>
            </div>
            <div class="detail-row">
                <div class="detail-label"><i class="fas fa-user-tag"></i> Role:</div>
                <div class="detail-value" id="detailRole"></div>
            </div>
            <div class="detail-row">
                <div class="detail-label"><i class="fas fa-toggle-on"></i> Status:</div>
                <div class="detail-value" id="detailStatus"></div>
            </div>
            <div class="detail-row">
                <div class="detail-label"><i class="fas fa-calendar-plus"></i> Joined:</div>
                <div class="detail-value" id="detailJoined"></div>
            </div>
        </div>
    </div>
</div>

<script>
    // Search and Filter functionality
    document.getElementById('searchInput').addEventListener('keyup', filterTable);
    document.getElementById('roleFilter').addEventListener('change', filterTable);
    document.getElementById('statusFilter').addEventListener('change', filterTable);
    
    function filterTable() {
        const searchValue = document.getElementById('searchInput').value.toLowerCase();
        const roleValue = document.getElementById('roleFilter').value;
        const statusValue = document.getElementById('statusFilter').value;
        const rows = document.querySelectorAll('#usersTable tbody tr');
        
        rows.forEach(row => {
            if (row.classList.contains('empty-state')) return;
            
            const name = row.querySelector('.user-fullname')?.innerText.toLowerCase() || '';
            const email = row.querySelector('.email-link')?.innerText.toLowerCase() || '';
            const userRole = row.getAttribute('data-role');
            const userStatus = row.getAttribute('data-status');
            
            const matchesSearch = name.includes(searchValue) || email.includes(searchValue);
            const matchesRole = roleValue === 'all' || userRole === roleValue;
            const matchesStatus = statusValue === 'all' || userStatus === statusValue;
            
            if (matchesSearch && matchesRole && matchesStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
    
    // View Details Modal
    const modal = document.getElementById('userModal');
    const closeBtn = document.getElementsByClassName('close')[0];
    
    function viewDetails(id, name, email, role, status, joined) {
        document.getElementById('detailName').innerHTML = name;
        document.getElementById('detailEmail').innerHTML = email;
        
        let roleHtml = '';
        if(role === 'admin') roleHtml = '<span class="role-badge role-admin"><i class="fas fa-user-shield"></i> Admin</span>';
        else if(role === 'freelancer') roleHtml = '<span class="role-badge role-freelancer"><i class="fas fa-user-tie"></i> Freelancer</span>';
        else roleHtml = '<span class="role-badge role-client"><i class="fas fa-user"></i> Client</span>';
        document.getElementById('detailRole').innerHTML = roleHtml;
        
        let statusHtml = '';
        if(status === 'active') statusHtml = '<span class="status-badge status-active"><i class="fas fa-check-circle"></i> Active</span>';
        else statusHtml = '<span class="status-badge status-blocked"><i class="fas fa-ban"></i> Blocked</span>';
        document.getElementById('detailStatus').innerHTML = statusHtml;
        
        document.getElementById('detailJoined').innerHTML = joined;
        modal.style.display = 'block';
    }
    
    closeBtn.onclick = function() {
        modal.style.display = 'none';
    }
    
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
    
    // Action Confirmations
    function confirmDelete(id, name) {
        Swal.fire({
            title: 'Delete User',
            text: `Are you sure you want to delete "${name}"? This action cannot be undone.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74c3c',
            cancelButtonColor: '#95a5a6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
            background: 'white',
            backdrop: true
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'users.php?delete_id=' + id;
            }
        });
    }
    
    function confirmBlock(id, name) {
        Swal.fire({
            title: 'Block User',
            text: `Are you sure you want to block "${name}"? Blocked users cannot access the system.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74c3c',
            cancelButtonColor: '#95a5a6',
            confirmButtonText: 'Yes, block user!',
            cancelButtonText: 'Cancel',
            background: 'white',
            backdrop: true
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'users.php?block_id=' + id;
            }
        });
    }
    
    function confirmUnblock(id, name) {
        Swal.fire({
            title: 'Unblock User',
            text: `Are you sure you want to unblock "${name}"? They will be able to access the system again.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#44bd32',
            cancelButtonColor: '#95a5a6',
            confirmButtonText: 'Yes, unblock user!',
            cancelButtonText: 'Cancel',
            background: 'white',
            backdrop: true
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'users.php?unblock_id=' + id;
            }
        });
    }
    
    function confirmRoleChange(id, newRole) {
        let roleText = '';
        if(newRole == 'admin') roleText = 'Admin';
        else if(newRole == 'freelancer') roleText = 'Freelancer';
        else roleText = 'Client';
        
        Swal.fire({
            title: 'Change Role',
            text: `Are you sure you want to change this user's role to "${roleText}"?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3498db',
            cancelButtonColor: '#95a5a6',
            confirmButtonText: 'Yes, change it!',
            cancelButtonText: 'Cancel',
            background: 'white',
            backdrop: true
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('roleForm_' + id).submit();
            } else {
                location.reload();
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
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
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

    .stat-icon.admin {
        background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    }

    .stat-icon.freelancer {
        background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    }

    .stat-icon.client {
        background: linear-gradient(135deg, #44bd32 0%, #2e7d32 100%);
    }

    .stat-icon.active {
        background: linear-gradient(135deg, #44bd32 0%, #2e7d32 100%);
    }

    .stat-icon.blocked {
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

    /* Filter Bar */
    .filter-bar {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .search-wrapper {
        flex: 2;
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
    }

    .search-input:focus {
        outline: none;
        border-color: #667eea;
    }

    .filter-wrapper {
        display: flex;
        gap: 10px;
    }

    .filter-select {
        padding: 12px 15px;
        border: 2px solid #e1e8ed;
        border-radius: 10px;
        font-size: 14px;
        background: white;
        cursor: pointer;
    }

    .filter-select:focus {
        outline: none;
        border-color: #667eea;
    }

    /* Action Bar */
    .action-bar {
        margin-bottom: 20px;
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

    .users-table {
        width: 100%;
        border-collapse: collapse;
    }

    .users-table thead {
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    }

    .users-table th {
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

    .users-table td {
        padding: 15px;
        vertical-align: middle;
    }

    /* User Info */
    .user-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .user-avatar i {
        font-size: 20px;
        color: white;
    }

    .user-details {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .user-fullname {
        font-weight: 600;
        color: #2c3e50;
    }

    .current-user-badge {
        display: inline-block;
        padding: 2px 8px;
        background: #3498db;
        color: white;
        border-radius: 4px;
        font-size: 10px;
        font-weight: 600;
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

    /* Role Badge & Select */
    .role-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
    }

    .role-admin {
        background: #f8d7da;
        color: #721c24;
    }

    .role-freelancer {
        background: #d1ecf1;
        color: #0c5460;
    }

    .role-client {
        background: #d4edda;
        color: #155724;
    }

    .role-select-wrapper {
        position: relative;
        display: inline-block;
    }

    .role-select {
        padding: 6px 12px;
        border-radius: 20px;
        border: 1px solid #e1e8ed;
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        background: white;
    }

    .role-select.role-admin {
        background: #f8d7da;
        color: #721c24;
        border-color: #f5c6cb;
    }

    .role-select.role-freelancer {
        background: #d1ecf1;
        color: #0c5460;
        border-color: #bee5eb;
    }

    .role-select.role-client {
        background: #d4edda;
        color: #155724;
        border-color: #c3e6cb;
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

    .status-active {
        background: #d4edda;
        color: #155724;
    }

    .status-blocked {
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

    /* Actions Cell */
    .actions-cell {
        min-width: 130px;
    }

    .action-buttons {
        display: flex;
        gap: 6px;
        align-items: center;
        flex-wrap: wrap;
    }

    .btn-view, .btn-block, .btn-unblock, .btn-delete {
        padding: 8px 12px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 12px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: all 0.3s ease;
        color: white;
    }

    .btn-view {
        background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    }

    .btn-view:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
    }

    .btn-block {
        background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    }

    .btn-block:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(231, 76, 60, 0.4);
    }

    .btn-unblock {
        background: linear-gradient(135deg, #44bd32 0%, #2e7d32 100%);
    }

    .btn-unblock:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(68, 189, 50, 0.4);
    }

    .btn-delete {
        background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    }

    .btn-delete:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(231, 76, 60, 0.4);
    }

    /* Modal */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        animation: fadeIn 0.3s ease;
    }

    .modal-content {
        background-color: white;
        margin: 10% auto;
        padding: 0;
        border-radius: 20px;
        width: 450px;
        max-width: 90%;
        animation: slideUp 0.3s ease;
        overflow: hidden;
    }

    .modal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 20px;
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-header h3 {
        margin: 0;
        font-size: 20px;
    }

    .modal-header h3 i {
        margin-right: 10px;
    }

    .close {
        color: white;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .close:hover {
        transform: scale(1.2);
    }

    .modal-body {
        padding: 25px;
    }

    .detail-row {
        display: flex;
        padding: 12px 0;
        border-bottom: 1px solid #ecf0f1;
    }

    .detail-row:last-child {
        border-bottom: none;
    }

    .detail-label {
        width: 100px;
        font-weight: 600;
        color: #2c3e50;
    }

    .detail-label i {
        width: 20px;
        color: #667eea;
    }

    .detail-value {
        flex: 1;
        color: #7f8c8d;
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
        from { opacity: 0; transform: translateY(-30px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideUp {
        from { transform: translateY(50px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    /* Responsive Design */
    @media (max-width: 1024px) {
        .users-table {
            display: block;
            overflow-x: auto;
        }
    }

    @media (max-width: 768px) {
        .content {
            padding: 15px;
        }

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .filter-bar {
            flex-direction: column;
        }

        .filter-wrapper {
            flex-direction: column;
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
        
        .btn-view, .btn-block, .btn-unblock, .btn-delete {
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