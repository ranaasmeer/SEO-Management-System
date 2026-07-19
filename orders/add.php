<?php 
include('../includes/header.php'); 
include('../includes/sidebar.php'); 
include('../config/db.php');

if($_SESSION['role'] != 'admin'){
    echo "<p style='color:red;'>Access Denied</p>";
    exit();
}

// Fetch clients
$clients = mysqli_query($conn, "SELECT * FROM users WHERE role='client'");

// Fetch freelancers/admins
$admins = mysqli_query($conn, "SELECT * FROM users WHERE role='freelancer' OR role='admin'");

$message = '';
$messageType = '';

if(isset($_POST['submit'])){

    $order_id = mysqli_real_escape_string($conn, $_POST['order_id']);
    $client = mysqli_real_escape_string($conn, $_POST['client']);
    $platform = mysqli_real_escape_string($conn, $_POST['platform']);
    $service = mysqli_real_escape_string($conn, $_POST['service']);
    $order_date = mysqli_real_escape_string($conn, $_POST['order_date']);
    $deadline = mysqli_real_escape_string($conn, $_POST['deadline']);
    $assigned = mysqli_real_escape_string($conn, $_POST['assigned']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);

    // CHECK IF ORDER ID ALREADY EXISTS
    $check_query = mysqli_query($conn, "SELECT id FROM orders WHERE order_id = '$order_id'");
    
    if(mysqli_num_rows($check_query) > 0){
        // Order ID already exists
        $message = "Order ID '$order_id' already exists! Please use a unique Order ID.";
        $messageType = 'error';
    } else {
        // Insert new order
        $query = "INSERT INTO orders 
        (order_id, client_id, platform, service_type, order_date, deadline, admin_assigned, price) 
        VALUES 
        ('$order_id','$client','$platform','$service','$order_date','$deadline','$assigned','$price')";

        if(mysqli_query($conn,$query)){
            $message = "Order Added Successfully! Redirecting...";
            $messageType = 'success';
            echo "<script>setTimeout(function(){ window.location.href = 'index.php'; }, 1500);</script>";
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
    <!-- Page Header -->
    <div class="page-header">
        <div class="header-content">
            <div class="header-icon">
                <i class="fas fa-plus-circle"></i>
            </div>
            <div class="header-text">
                <h2>Add New Order</h2>
                <p>Create a new order and assign it to a client</p>
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

    <!-- Form Container -->
    <div class="form-container">
        <form method="POST" class="order-form" id="orderForm">
            <!-- Order ID Field -->
            <div class="form-group">
                <label for="order_id">
                    <i class="fas fa-hashtag"></i>
                    Order ID
                </label>
                <div class="input-wrapper">
                    <input type="text" id="order_id" name="order_id" placeholder="Enter unique order ID" required 
                           class="form-input" autocomplete="off">
                    <div class="input-focus-border"></div>
                </div>
                <small class="form-hint">Unique identifier for this order</small>
            </div>

            <!-- Client Field -->
            <div class="form-group">
                <label for="client">
                    <i class="fas fa-user"></i>
                    Client
                </label>
                <div class="input-wrapper">
                    <select id="client" name="client" required class="form-select">
                        <option value="">Select a client</option>
                        <?php while($row = mysqli_fetch_assoc($clients)): ?>
                        <option value="<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                    <div class="input-focus-border"></div>
                </div>
                <small class="form-hint">Select the client who ordered this service</small>
            </div>

            <!-- Platform and Service Row -->
            <div class="form-row">
                <div class="form-group">
                    <label for="platform">
                        <i class="fas fa-globe"></i>
                        Platform
                    </label>
                    <div class="input-wrapper">
                        <select id="platform" name="platform" required class="form-select">
                            <option value="Fiverr">Fiverr</option>
                            <option value="Direct">Direct</option>
                        </select>
                        <div class="input-focus-border"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="service">
                        <i class="fas fa-concierge-bell"></i>
                        Service Type
                    </label>
                    <div class="input-wrapper">
                        <select id="service" name="service" required class="form-select">
                            <option value="Guest Post">Guest Post</option>
                            <option value="Link Insertion">Link Insertion</option>
                        </select>
                        <div class="input-focus-border"></div>
                    </div>
                </div>
            </div>

            <!-- Order Date and Deadline Row -->
            <div class="form-row">
                <div class="form-group">
                    <label for="order_date">
                        <i class="fas fa-calendar-plus"></i>
                        Order Date
                    </label>
                    <div class="input-wrapper">
                        <input type="date" id="order_date" name="order_date" required class="form-input">
                        <div class="input-focus-border"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="deadline">
                        <i class="fas fa-calendar-times"></i>
                        Deadline
                    </label>
                    <div class="input-wrapper">
                        <input type="date" id="deadline" name="deadline" required class="form-input">
                        <div class="input-focus-border"></div>
                    </div>
                </div>
            </div>

            <!-- Assign Admin Field -->
            <div class="form-group">
                <label for="assigned">
                    <i class="fas fa-user-tie"></i>
                    Assign Admin/Freelancer
                </label>
                <div class="input-wrapper">
                    <select id="assigned" name="assigned" required class="form-select">
                        <option value="">Select a person to assign</option>
                        <?php while($row = mysqli_fetch_assoc($admins)): ?>
                        <option value="<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                    <div class="input-focus-border"></div>
                </div>
                <small class="form-hint">Assign this order to an admin or freelancer</small>
            </div>

            <!-- Price Field -->
            <div class="form-group">
                <label for="price">
                    <i class="fas fa-dollar-sign"></i>
                    Price
                </label>
                <div class="input-wrapper">
                    <span class="input-prefix">$</span>
                    <input type="number" id="price" name="price" placeholder="0.00" step="0.01" required 
                           class="form-input with-prefix">
                    <div class="input-focus-border"></div>
                </div>
                <small class="form-hint">Enter the order amount in USD</small>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" name="submit" class="btn-submit">
                    <i class="fas fa-save"></i>
                    Create Order
                </button>
                <a href="index.php" class="btn-cancel">
                    <i class="fas fa-times"></i>
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Set default dates
document.addEventListener('DOMContentLoaded', function() {
    // Set order date to today
    const today = new Date().toISOString().split('T')[0];
    const orderDateInput = document.getElementById('order_date');
    if (!orderDateInput.value) {
        orderDateInput.value = today;
    }
    
    // Set deadline to 7 days from now by default
    const deadlineInput = document.getElementById('deadline');
    if (!deadlineInput.value) {
        const deadline = new Date();
        deadline.setDate(deadline.getDate() + 7);
        deadlineInput.value = deadline.toISOString().split('T')[0];
    }
    
    // Real-time validation for Order ID (no spaces, only letters, numbers, hyphens)
    const orderIdInput = document.getElementById('order_id');
    orderIdInput.addEventListener('input', function(e) {
        this.value = this.value.replace(/[^a-zA-Z0-9-]/g, '');
    });
    
    // Price validation
    const priceInput = document.getElementById('price');
    priceInput.addEventListener('input', function(e) {
        if (this.value < 0) this.value = 0;
    });
});
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

    /* Form Container */
    .form-container {
        background: white;
        border-radius: 20px;
        padding: 40px;
        box-shadow: 0 15px 40px rgba(0,0,0,0.1);
        animation: fadeInUp 0.6s ease;
        max-width: 800px;
        margin: 0 auto;
    }

    /* Form Layout */
    .order-form {
        display: flex;
        flex-direction: column;
        gap: 25px;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    /* Labels */
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

    /* Input Wrapper */
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
        font-family: inherit;
    }

    .form-input.with-prefix {
        padding-left: 35px;
    }

    .input-prefix {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #7f8c8d;
        font-weight: 600;
        z-index: 1;
    }

    .form-input:focus, .form-select:focus {
        outline: none;
        border-color: #667eea;
    }

    .form-input:focus + .input-focus-border,
    .form-select:focus + .input-focus-border {
        width: 100%;
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

    /* Select styling */
    .form-select {
        cursor: pointer;
        appearance: none;
        background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="%237f8c8d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>');
        background-repeat: no-repeat;
        background-position: right 15px center;
    }

    /* Form Hints */
    .form-hint {
        font-size: 11px;
        color: #95a5a6;
        margin-top: 5px;
    }

    /* Form Actions */
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

        .form-container {
            padding: 25px;
        }

        .form-row {
            grid-template-columns: 1fr;
            gap: 25px;
        }

        .form-actions {
            flex-direction: column;
        }

        .btn-cancel {
            flex: 1;
        }

        .header-content {
            flex-direction: column;
            text-align: center;
        }
    }
</style>

<?php include('../includes/footer.php'); ?>