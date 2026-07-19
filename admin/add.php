<?php 
include('../includes/header.php'); 
include('../includes/sidebar.php'); 
include('../config/db.php');

if($_SESSION['role'] != 'admin'){
    echo "<p style='color:red;'>Access Denied</p>";
    exit();
}

$message = '';
$messageType = '';

if(isset($_POST['submit'])){
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    
    // Check if email already exists
    $check_query = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
    if(mysqli_num_rows($check_query) > 0){
        $message = "Email already exists! Please use a different email.";
        $messageType = 'error';
    } else {
        $query = "INSERT INTO users (name, email, password, role) VALUES ('$name','$email','$password','$role')";
        if(mysqli_query($conn, $query)){
            $message = "User added successfully! Redirecting...";
            $messageType = 'success';
            echo "<script>setTimeout(function(){ window.location.href = 'users.php'; }, 1500);</script>";
        } else {
            $message = "Error: " . mysqli_error($conn);
            $messageType = 'error';
        }
    }
}
?>

<!-- Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div class="content">
    <div class="page-header">
        <div class="header-content">
            <div class="header-icon">
                <i class="fas fa-user-plus"></i>
            </div>
            <div class="header-text">
                <h2>Add New User</h2>
                <p>Create a new user account</p>
            </div>
        </div>
    </div>

    <?php if($message): ?>
    <div class="alert alert-<?php echo $messageType; ?>">
        <i class="fas <?php echo $messageType == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
        <?php echo $message; ?>
    </div>
    <?php endif; ?>

    <div class="form-container">
        <form method="POST" class="user-form">
            <div class="form-group">
                <label for="name">
                    <i class="fas fa-user"></i>
                    Full Name
                </label>
                <div class="input-wrapper">
                    <input type="text" id="name" name="name" placeholder="Enter full name" required class="form-input">
                    <div class="input-focus-border"></div>
                </div>
            </div>

            <div class="form-group">
                <label for="email">
                    <i class="fas fa-envelope"></i>
                    Email Address
                </label>
                <div class="input-wrapper">
                    <input type="email" id="email" name="email" placeholder="user@example.com" required class="form-input">
                    <div class="input-focus-border"></div>
                </div>
            </div>

            <div class="form-group">
                <label for="password">
                    <i class="fas fa-lock"></i>
                    Password
                </label>
                <div class="input-wrapper">
                    <input type="password" id="password" name="password" placeholder="Enter password" required class="form-input">
                    <div class="input-focus-border"></div>
                </div>
                <small class="form-hint">Minimum 6 characters recommended</small>
            </div>

            <div class="form-group">
                <label for="role">
                    <i class="fas fa-user-tag"></i>
                    User Role
                </label>
                <div class="input-wrapper">
                    <select name="role" id="role" required class="form-select">
                        <option value="client">Client</option>
                        <option value="freelancer">Freelancer</option>
                        <option value="admin">Admin</option>
                    </select>
                    <div class="input-focus-border"></div>
                </div>
                <small class="form-hint">
                    <strong>Admin:</strong> Full access<br>
                    <strong>Freelancer:</strong> Can manage assigned orders<br>
                    <strong>Client:</strong> Can only view their orders
                </small>
            </div>

            <div class="form-actions">
                <button type="submit" name="submit" class="btn-submit">
                    <i class="fas fa-save"></i>
                    Create User
                </button>
                <a href="users.php" class="btn-cancel">
                    <i class="fas fa-times"></i>
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<style>
    .content {
        padding: 30px;
        min-height: 100vh;
        background: linear-gradient(135deg, #f5f7fa 0%, #eef2f7 100%);
    }

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

    .alert {
        padding: 15px 20px;
        border-radius: 12px;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 12px;
        animation: fadeInUp 0.5s ease;
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

    .form-container {
        background: white;
        border-radius: 20px;
        padding: 40px;
        box-shadow: 0 15px 40px rgba(0,0,0,0.1);
        animation: fadeInUp 0.6s ease;
        max-width: 500px;
        margin: 0 auto;
    }

    .user-form {
        display: flex;
        flex-direction: column;
        gap: 25px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .form-group label {
        font-size: 14px;
        font-weight: 600;
        color: #2c3e50;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .form-group label i {
        color: #667eea;
        font-size: 14px;
    }

    .input-wrapper {
        position: relative;
        width: 100%;
    }

    .form-input, .form-select {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e1e8ed;
        border-radius: 10px;
        font-size: 14px;
        transition: all 0.3s ease;
        background: white;
    }

    .form-input:focus, .form-select:focus {
        outline: none;
        border-color: #667eea;
    }

    .input-focus-border {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 0;
        height: 2px;
        background: linear-gradient(90deg, #667eea, #764ba2);
        transition: width 0.3s ease;
        border-radius: 2px;
    }

    .form-input:focus + .input-focus-border,
    .form-select:focus + .input-focus-border {
        width: 100%;
    }

    .form-select {
        cursor: pointer;
        appearance: none;
        background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="%237f8c8d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>');
        background-repeat: no-repeat;
        background-position: right 15px center;
    }

    .form-hint {
        font-size: 11px;
        color: #95a5a6;
        margin-top: 5px;
        line-height: 1.4;
    }

    .form-actions {
        display: flex;
        gap: 15px;
        margin-top: 10px;
    }

    .btn-submit, .btn-cancel {
        padding: 12px 30px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: none;
    }

    .btn-submit {
        background: linear-gradient(135deg, #44bd32 0%, #2e7d32 100%);
        color: white;
        flex: 1;
        justify-content: center;
    }

    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(68, 189, 50, 0.4);
    }

    .btn-cancel {
        background: #ecf0f1;
        color: #7f8c8d;
        justify-content: center;
        flex: 0.5;
    }

    .btn-cancel:hover {
        background: #e74c3c;
        color: white;
        transform: translateY(-2px);
    }

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

    @media (max-width: 768px) {
        .content { padding: 15px; }
        .form-container { padding: 25px; }
        .form-actions { flex-direction: column; }
        .btn-cancel { flex: 1; }
        .header-content { flex-direction: column; text-align: center; }
    }
</style>

<?php include('../includes/footer.php'); ?>