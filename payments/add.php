<?php 
include('../includes/header.php'); 
include('../includes/sidebar.php'); 
include('../config/db.php');

if($_SESSION['role'] != 'admin'){
    echo "<p style='color:red;'>Access Denied</p>";
    exit();
}

// Fetch completed orders ONLY (with price)
$orders = mysqli_query($conn, "SELECT id, order_id, price FROM orders WHERE status='Completed' ORDER BY id DESC");

$message = '';
$messageType = '';

if(isset($_POST['submit'])){

    $order_id = mysqli_real_escape_string($conn, $_POST['order_id']);

    // Check if payment already exists for this order
    $check_query = mysqli_query($conn, "SELECT id FROM payments WHERE order_id = '$order_id'");
    
    if(mysqli_num_rows($check_query) > 0){
        // Payment already exists
        $order_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT order_id FROM orders WHERE id=$order_id"));
        $message = "Payment for Order #" . $order_info['order_id'] . " already exists! You cannot add duplicate payment.";
        $messageType = 'error';
    } else {
        // 🔒 Always get correct order data from DB (secure)
        $order = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM orders WHERE id=$order_id"));

        $completion = $order['completion_date'];
        $amount = $order['price']; // FORCE correct price

        // AUTO: clearance = +14 days
        $clearance = date('Y-m-d', strtotime($completion . ' +14 days'));

        $query = "INSERT INTO payments 
        (order_id, completion_date, clearance_date, amount)
        VALUES 
        ('$order_id','$completion','$clearance','$amount')";

        if(mysqli_query($conn, $query)){
            $message = "Payment Added Successfully! Redirecting...";
            $messageType = 'success';
            echo "<script>setTimeout(function(){ window.location.href = 'index.php'; }, 1500);</script>";
        } else {
            $message = "Error: " . mysqli_error($conn);
            $messageType = 'error';
        }
    }
}

// Get total completed orders count
$totalCompleted = mysqli_num_rows($orders);
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
                <h2>Add Payment</h2>
                <p>Create payment records for completed orders</p>
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
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-info">
                <h4>Completed Orders</h4>
                <p><?php echo $totalCompleted; ?></p>
                <small>Available for payment</small>
            </div>
        </div>
        
        <!-- Warning Card for Existing Payments -->
        <div class="stat-card warning-card" id="warningCard" style="display: none;">
            <div class="stat-icon warning-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-info">
                <h4>Payment Exists</h4>
                <p id="warningText">Already Added</p>
                <small>This order already has a payment record</small>
            </div>
        </div>
    </div>

    <!-- Form Container -->
    <div class="form-container">
        <form method="POST" class="payment-form" id="paymentForm">
            
            <!-- Order Selection -->
            <div class="form-group">
                <label for="order_id">
                    <i class="fas fa-shopping-cart"></i>
                    Select Completed Order
                </label>
                <div class="input-wrapper">
                    <select name="order_id" id="order_id" required class="form-select">
                        <option value="">Choose an order</option>
                        <?php 
                        // Reset result pointer
                        mysqli_data_seek($orders, 0);
                        while($row = mysqli_fetch_assoc($orders)): ?>
                        <option value="<?php echo $row['id']; ?>" data-price="<?php echo $row['price']; ?>" data-order="<?php echo $row['order_id']; ?>">
                            #<?php echo htmlspecialchars($row['order_id']); ?> - $<?php echo number_format($row['price'], 2); ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                    <div class="input-focus-border"></div>
                </div>
                <small class="form-hint">Only completed orders are available for payment</small>
            </div>

            <!-- Amount Field -->
            <div class="form-group">
                <label for="amount">
                    <i class="fas fa-dollar-sign"></i>
                    Payment Amount
                </label>
                <div class="input-wrapper">
                    <span class="input-prefix">$</span>
                    <input type="number" name="amount" id="amount" placeholder="0.00" 
                           step="0.01" class="form-input with-prefix" readonly>
                    <div class="input-focus-border"></div>
                </div>
                <small class="form-hint">Amount is automatically set from the order price</small>
            </div>

            <!-- Existing Payment Warning Box -->
            <div class="existing-warning" id="existingWarning" style="display: none;">
                <div class="warning-icon">
                    <i class="fas fa-ban"></i>
                </div>
                <div class="warning-content">
                    <strong>Payment Already Exists!</strong>
                    <p>A payment record for this order has already been created. You cannot add another payment for the same order.</p>
                </div>
            </div>

            <!-- Info Box -->
            <div class="info-box">
                <div class="info-icon">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div class="info-content">
                    <h4>Payment Information</h4>
                    <p>When you add a payment for a completed order:</p>
                    <ul>
                        <li><i class="fas fa-calendar-check"></i> Completion date is taken from the order</li>
                        <li><i class="fas fa-calendar-alt"></i> Clearance date is automatically set to +14 days</li>
                        <li><i class="fas fa-lock"></i> Amount is locked to the order price</li>
                        <li><i class="fas fa-ban"></i> Each order can only have ONE payment record</li>
                    </ul>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" name="submit" class="btn-submit" id="submitBtn">
                    <i class="fas fa-save"></i>
                    Add Payment
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
document.addEventListener("DOMContentLoaded", function(){

    const orderSelect = document.getElementById("order_id");
    const amountInput = document.getElementById("amount");
    const existingWarning = document.getElementById("existingWarning");
    const warningCard = document.getElementById("warningCard");
    const warningText = document.getElementById("warningText");
    const submitBtn = document.getElementById("submitBtn");

    function setAmount(){
        const selected = orderSelect.options[orderSelect.selectedIndex];
        const price = selected.getAttribute("data-price");
        amountInput.value = price ? price : '';
        
        // Add visual feedback when order is selected
        if(price && price > 0){
            amountInput.style.borderColor = "#44bd32";
            amountInput.style.backgroundColor = "#f0fff4";
        } else {
            amountInput.style.borderColor = "#e1e8ed";
            amountInput.style.backgroundColor = "white";
        }
    }

    // Function to check if payment already exists for selected order
    function checkExistingPayment(orderId, orderNumber) {
        if(!orderId) {
            existingWarning.style.display = 'none';
            warningCard.style.display = 'none';
            submitBtn.disabled = false;
            submitBtn.style.opacity = '1';
            return;
        }
        
        // Show loading state
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Checking...';
        submitBtn.disabled = true;
        submitBtn.style.opacity = '0.7';
        
        // AJAX call to check if payment exists
        fetch(`check_payment.php?order_id=${orderId}`)
            .then(response => response.json())
            .then(data => {
                if(data.exists) {
                    // Show warning that payment already exists
                    existingWarning.style.display = 'flex';
                    warningCard.style.display = 'flex';
                    warningText.innerHTML = `Order #${orderNumber}`;
                    submitBtn.innerHTML = '<i class="fas fa-ban"></i> Payment Already Exists';
                    submitBtn.disabled = true;
                    submitBtn.style.opacity = '0.5';
                    submitBtn.style.background = 'linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%)';
                } else {
                    // Hide warning, enable submit
                    existingWarning.style.display = 'none';
                    warningCard.style.display = 'none';
                    submitBtn.innerHTML = '<i class="fas fa-save"></i> Add Payment';
                    submitBtn.disabled = false;
                    submitBtn.style.opacity = '1';
                    submitBtn.style.background = 'linear-gradient(135deg, #44bd32 0%, #2e7d32 100%)';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                submitBtn.innerHTML = '<i class="fas fa-save"></i> Add Payment';
                submitBtn.disabled = false;
                submitBtn.style.opacity = '1';
            });
    }

    // Set on page load if there's a default selection
    if(orderSelect.selectedIndex > 0){
        const selected = orderSelect.options[orderSelect.selectedIndex];
        const orderId = selected.value;
        const orderNumber = selected.getAttribute('data-order');
        setAmount();
        checkExistingPayment(orderId, orderNumber);
    }

    // Update when order changes
    orderSelect.addEventListener("change", function() {
        const selected = this.options[this.selectedIndex];
        const orderId = selected.value;
        const orderNumber = selected.getAttribute('data-order');
        setAmount();
        checkExistingPayment(orderId, orderNumber);
        
        // Add animation effect
        amountInput.style.transform = "scale(1.02)";
        setTimeout(() => {
            amountInput.style.transform = "scale(1)";
        }, 200);
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

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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
        max-width: 300px;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.15);
    }

    .stat-card.warning-card {
        border: 2px solid #e74c3c;
        animation: pulse-border 1s ease;
    }

    @keyframes pulse-border {
        0%, 100% { border-color: #e74c3c; }
        50% { border-color: #c0392b; }
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #44bd32 0%, #2e7d32 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .stat-icon.warning-icon {
        background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
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
        font-size: 28px;
        font-weight: bold;
        color: #2c3e50;
    }

    .stat-info small {
        font-size: 11px;
        color: #95a5a6;
        display: block;
        margin-top: 5px;
    }

    /* Form Container */
    .form-container {
        background: white;
        border-radius: 20px;
        padding: 40px;
        box-shadow: 0 15px 40px rgba(0,0,0,0.1);
        animation: fadeInUp 0.6s ease;
        max-width: 600px;
        margin: 0 auto;
    }

    /* Form Layout */
    .payment-form {
        display: flex;
        flex-direction: column;
        gap: 25px;
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

    /* Select styling */
    .form-select {
        cursor: pointer;
        appearance: none;
        background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="%237f8c8d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>');
        background-repeat: no-repeat;
        background-position: right 15px center;
    }

    /* Existing Payment Warning Box */
    .existing-warning {
        background: #f8d7da;
        border-left: 4px solid #e74c3c;
        border-radius: 10px;
        padding: 15px;
        display: flex;
        gap: 12px;
        align-items: center;
        animation: fadeInUp 0.3s ease;
    }

    .existing-warning .warning-icon {
        width: 35px;
        height: 35px;
        background: rgba(231, 76, 60, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .existing-warning .warning-icon i {
        font-size: 18px;
        color: #e74c3c;
    }

    .existing-warning .warning-content {
        flex: 1;
    }

    .existing-warning .warning-content strong {
        font-size: 14px;
        color: #721c24;
        display: block;
        margin-bottom: 5px;
    }

    .existing-warning .warning-content p {
        font-size: 12px;
        color: #721c24;
        margin: 0;
    }

    /* Info Box */
    .info-box {
        background: linear-gradient(135deg, #f8f9fa 0%, #eef2f7 100%);
        border-radius: 12px;
        padding: 20px;
        display: flex;
        gap: 15px;
        border-left: 4px solid #667eea;
    }

    .info-icon {
        width: 40px;
        height: 40px;
        background: rgba(102, 126, 234, 0.1);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .info-icon i {
        font-size: 20px;
        color: #667eea;
    }

    .info-content h4 {
        margin: 0 0 8px 0;
        font-size: 14px;
        color: #2c3e50;
    }

    .info-content p {
        margin: 0 0 8px 0;
        font-size: 12px;
        color: #7f8c8d;
    }

    .info-content ul {
        margin: 0;
        padding-left: 20px;
    }

    .info-content ul li {
        font-size: 12px;
        color: #7f8c8d;
        margin-bottom: 5px;
        list-style: none;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .info-content ul li i {
        color: #44bd32;
        font-size: 10px;
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

    .btn-submit:disabled {
        cursor: not-allowed;
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

        .info-box {
            flex-direction: column;
        }

        .info-icon {
            align-self: center;
        }
        
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .stat-card {
            max-width: 100%;
        }
        
        .existing-warning {
            flex-direction: column;
            text-align: center;
        }
    }
</style>

<?php include('../includes/footer.php'); ?>