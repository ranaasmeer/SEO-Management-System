<?php 
include('../includes/header.php'); 
include('../includes/sidebar.php'); 
include('../config/db.php');

if($_SESSION['role'] == 'client'){
    echo "<p style='color:red;'>Access Denied</p>";
    exit();
}

// Fetch orders
$orders = mysqli_query($conn, "SELECT * FROM orders ORDER BY id DESC");

$message = '';
$messageType = '';

if($_SERVER['REQUEST_METHOD'] == 'POST'){

    $order_id = mysqli_real_escape_string($conn, $_POST['order_id']);
    $cost = mysqli_real_escape_string($conn, $_POST['cost']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);

    // Check if expense already exists for this order
    $check_query = mysqli_query($conn, "SELECT id FROM expenses WHERE order_id = '$order_id'");
    
    if(mysqli_num_rows($check_query) > 0){
        // Update existing expense
        $query = "UPDATE expenses SET cost = '$cost', description = '$desc' WHERE order_id = '$order_id'";
        $action = "updated";
    } else {
        // Insert new expense
        $query = "INSERT INTO expenses (order_id, cost, description) VALUES ('$order_id','$cost','$desc')";
        $action = "added";
    }

    if(mysqli_query($conn, $query)){
        $message = "Expense $action successfully! Redirecting...";
        $messageType = 'success';
        // Redirect to index.php after 1.5 seconds
        echo "<script>setTimeout(function(){ window.location.href = 'index.php'; }, 1500);</script>";
    } else {
        $message = "Error: " . mysqli_error($conn);
        $messageType = 'error';
    }
}

// Get total orders count
$totalOrders = mysqli_num_rows($orders);
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
                <h2>Manage Expense</h2>
                <p>Add new expenses or update existing ones</p>
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
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="stat-info">
                <h4>Total Orders</h4>
                <p><?php echo $totalOrders; ?></p>
                <small>Available for expense tracking</small>
            </div>
        </div>
        
        <!-- Update Mode Card (Hidden by default, shows when editing) -->
        <div class="stat-card update-mode-card" id="updateModeCard" style="display: none;">
            <div class="stat-icon update-icon">
                <i class="fas fa-edit"></i>
            </div>
            <div class="stat-info">
                <h4>Update Mode</h4>
                <p id="updateModeText">Editing Existing</p>
                <small>This order already has an expense</small>
            </div>
        </div>
    </div>

    <!-- Form Container -->
    <div class="form-container">
        <form method="POST" class="expense-form" id="expenseForm">
            
            <!-- Order Selection -->
            <div class="form-group">
                <label for="order_id">
                    <i class="fas fa-shopping-cart"></i>
                    Select Order
                </label>
                <div class="input-wrapper">
                    <select name="order_id" id="order_id" required class="form-select">
                        <option value="">Choose an order</option>
                        <?php 
                        // Reset result pointer
                        mysqli_data_seek($orders, 0);
                        while($row = mysqli_fetch_assoc($orders)): ?>
                        <option value="<?php echo $row['id']; ?>">
                           #<?php echo htmlspecialchars($row['order_id']); ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                    <div class="input-focus-border"></div>
                </div>
                <small class="form-hint">Select the order this expense is related to</small>
            </div>

            <!-- Cost Field -->
            <div class="form-group">
                <label for="cost">
                    <i class="fas fa-dollar-sign"></i>
                    Cost Amount
                </label>
                <div class="input-wrapper">
                    <span class="input-prefix">$</span>
                    <input type="number" name="cost" id="cost" placeholder="0.00" 
                           step="0.01" required class="form-input with-prefix">
                    <div class="input-focus-border"></div>
                </div>
                <small class="form-hint">Enter the expense amount in USD</small>
            </div>

            <!-- Description Field -->
            <div class="form-group">
                <label for="description">
                    <i class="fas fa-file-alt"></i>
                    Description
                </label>
                <div class="input-wrapper">
                    <textarea name="description" id="description" placeholder="Enter detailed description of the expense..." 
                              rows="4" class="form-textarea"></textarea>
                    <div class="input-focus-border"></div>
                </div>
                <small class="form-hint">Provide a clear description of what this expense is for</small>
            </div>

            <!-- Update Warning Box (Hidden by default) -->
            <div class="update-warning" id="updateWarning" style="display: none;">
                <div class="warning-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="warning-content">
                    <strong>Update Mode Active:</strong> This order already has an expense record. Submitting will <strong>UPDATE</strong> the existing record instead of creating a new one.
                </div>
            </div>

            <!-- Info Box -->
            <div class="info-box">
                <div class="info-icon">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div class="info-content">
                    <h4>Expense Information</h4>
                    <p>Expenses are automatically deducted from your revenue to calculate net profit.</p>
                    <ul>
                        <li><i class="fas fa-check-circle"></i> Track all business costs</li>
                        <li><i class="fas fa-chart-line"></i> Monitor profit margins</li>
                        <li><i class="fas fa-receipt"></i> Keep detailed records</li>
                        <li><i class="fas fa-edit"></i> Update existing expenses by selecting the same order</li>
                    </ul>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" name="submit" class="btn-submit" id="submitBtn">
                    <i class="fas fa-save"></i>
                    Add Expense
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
    document.addEventListener('DOMContentLoaded', function() {
        const costInput = document.getElementById('cost');
        const descriptionInput = document.getElementById('description');
        const orderSelect = document.getElementById('order_id');
        const updateWarning = document.getElementById('updateWarning');
        const updateModeCard = document.getElementById('updateModeCard');
        const submitBtn = document.getElementById('submitBtn');
        
        // Function to check if order already has an expense
        function checkExistingExpense(orderId) {
            if(!orderId) {
                updateWarning.style.display = 'none';
                updateModeCard.style.display = 'none';
                submitBtn.innerHTML = '<i class="fas fa-save"></i> Add Expense';
                // Clear fields
                document.getElementById('cost').value = '';
                document.getElementById('description').value = '';
                return;
            }
            
            // Show loading state
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Checking...';
            submitBtn.disabled = true;
            
            // AJAX call to check if expense exists
            fetch(`check_expense.php?order_id=${orderId}`)
                .then(response => response.json())
                .then(data => {
                    if(data.exists) {
                        // Show update mode UI
                        updateWarning.style.display = 'flex';
                        updateModeCard.style.display = 'flex';
                        submitBtn.innerHTML = '<i class="fas fa-edit"></i> Update Expense';
                        // Pre-fill existing data
                        if(data.cost) document.getElementById('cost').value = data.cost;
                        if(data.description) document.getElementById('description').value = data.description;
                        // Update the card text
                        document.getElementById('updateModeText').innerHTML = `Order #${orderSelect.options[orderSelect.selectedIndex].text.replace('#', '')}`;
                    } else {
                        // Show add mode UI
                        updateWarning.style.display = 'none';
                        updateModeCard.style.display = 'none';
                        submitBtn.innerHTML = '<i class="fas fa-save"></i> Add Expense';
                        // Clear fields for new expense
                        document.getElementById('cost').value = '';
                        document.getElementById('description').value = '';
                    }
                    submitBtn.disabled = false;
                })
                .catch(error => {
                    console.error('Error:', error);
                    submitBtn.innerHTML = '<i class="fas fa-save"></i> Add Expense';
                    submitBtn.disabled = false;
                });
        }
        
        // Add event listener for order selection change
        orderSelect.addEventListener('change', function() {
            checkExistingExpense(this.value);
        });
        
        // Cost validation
        costInput.addEventListener('input', function() {
            if (this.value < 0) {
                this.value = 0;
            }
            // Add visual feedback
            if (this.value > 0) {
                this.style.borderColor = '#44bd32';
            } else {
                this.style.borderColor = '#e1e8ed';
            }
        });
        
        // Description character counter
        descriptionInput.addEventListener('input', function() {
            const charCount = this.value.length;
            let counter = document.querySelector('.char-counter');
            
            if (!counter) {
                counter = document.createElement('small');
                counter.className = 'char-counter';
                counter.style.cssText = 'display: block; font-size: 10px; color: #95a5a6; margin-top: 5px;';
                this.parentElement.appendChild(counter);
            }
            
            counter.innerHTML = `${charCount} characters`;
            
            if (charCount > 200) {
                counter.style.color = '#e74c3c';
            } else {
                counter.style.color = '#95a5a6';
            }
        });
        
        // Add animation on form submission
        const form = document.getElementById('expenseForm');
        form.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('.btn-submit');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            submitBtn.disabled = true;
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

    .stat-card.update-mode-card {
        border: 2px solid #f39c12;
        animation: pulse-border 1s ease;
    }

    @keyframes pulse-border {
        0%, 100% { border-color: #f39c12; }
        50% { border-color: #e67e22; }
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

    .stat-icon.update-icon {
        background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
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
    .expense-form {
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

    .form-input, .form-select, .form-textarea {
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

    .form-textarea {
        resize: vertical;
        min-height: 100px;
    }

    .form-input:focus, .form-select:focus, .form-textarea:focus {
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
    .form-select:focus + .input-focus-border,
    .form-textarea:focus + .input-focus-border {
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

    /* Update Warning Box */
    .update-warning {
        background: #fff3cd;
        border-left: 4px solid #f39c12;
        border-radius: 10px;
        padding: 15px;
        display: flex;
        gap: 12px;
        align-items: center;
        animation: fadeInUp 0.3s ease;
    }

    .warning-icon {
        width: 35px;
        height: 35px;
        background: rgba(243, 156, 18, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .warning-icon i {
        font-size: 18px;
        color: #f39c12;
    }

    .warning-content {
        flex: 1;
        font-size: 13px;
        color: #856404;
    }

    .warning-content strong {
        font-weight: 700;
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
        opacity: 0.7;
        cursor: not-allowed;
        transform: none;
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
        
        .update-warning {
            flex-direction: column;
            text-align: center;
        }
    }
</style>

<?php include('../includes/footer.php'); ?>