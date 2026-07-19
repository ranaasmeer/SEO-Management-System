<?php 
include('../includes/header.php'); 
include('../includes/sidebar.php'); 
include('../config/db.php');

if($_SESSION['role'] == 'client'){
    echo "<div style='color:red; padding:20px;'>Access Denied</div>";
    exit();
}


$message = '';
$messageType = '';

if(isset($_POST['submit'])){

    $website = mysqli_real_escape_string($conn, $_POST['website']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $date = mysqli_real_escape_string($conn, $_POST['outreach_date']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $followup_days = mysqli_real_escape_string($conn, $_POST['followup_days']);

$query = "INSERT INTO outreach 
(website_name, email, outreach_date, deal_price, followup_days)
VALUES 
('$website','$email','$date','$price', '$followup_days')";

    if(mysqli_query($conn, $query)){
        $message = "Outreach Added Successfully! Redirecting...";
        $messageType = 'success';
        echo "<script>setTimeout(function(){ window.location.href = 'index.php'; }, 1500);</script>";
    } else {
        $message = "Error: " . mysqli_error($conn);
        $messageType = 'error';
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
                <h2>Add Outreach</h2>
                <p>Create a new email outreach campaign</p>
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
        <form method="POST" class="outreach-form" id="outreachForm">
            
            <!-- Website Name -->
            <div class="form-group">
                <label for="website">
                    <i class="fas fa-globe"></i>
                    Website Name
                </label>
                <div class="input-wrapper">
                    <input type="text" id="website" name="website" placeholder="https://example.com" 
                           required class="form-input">
                    <div class="input-focus-border"></div>
                </div>
                <small class="form-hint">Full URL of the website you're reaching out to</small>
            </div>

            <!-- Email -->
            <div class="form-group">
                <label for="email">
                    <i class="fas fa-envelope"></i>
                    Email Address
                </label>
                <div class="input-wrapper">
                    <input type="email" id="email" name="email" placeholder="contact@example.com" 
                           required class="form-input">
                    <div class="input-focus-border"></div>
                </div>
                <small class="form-hint">Contact email for outreach</small>
            </div>



            <!-- Outreach Date and Follow-up Days Row -->
            <div class="form-row">
                <div class="form-group">
                    <label for="outreach_date">
                        <i class="fas fa-calendar-alt"></i>
                        Outreach Date
                    </label>
                    <div class="input-wrapper">
                        <input type="date" id="outreach_date" name="outreach_date" 
                               required class="form-input">
                        <div class="input-focus-border"></div>
                    </div>
                    <small class="form-hint">Date when outreach was sent</small>
                </div>

                <div class="form-group">
                    <label for="followup_days">
                        <i class="fas fa-clock"></i>
                        Follow-up Days
                    </label>
                    <div class="input-wrapper">
                        <input type="number" id="followup_days" name="followup_days" value="2" 
                               min="1" max="30" class="form-input">
                        <div class="input-focus-border"></div>
                    </div>
                    <small class="form-hint">Days after which to follow up (default: 2)</small>
                </div>
            </div>

            <!-- Deal Price -->
            <div class="form-group">
                <label for="price">
                    <i class="fas fa-dollar-sign"></i>
                    Deal Price
                </label>
                <div class="input-wrapper">
                    <span class="input-prefix">$</span>
                    <input type="number" id="price" name="price" placeholder="0.00" 
                           step="0.01" class="form-input with-prefix">
                    <div class="input-focus-border"></div>
                </div>
                <small class="form-hint">Proposed deal amount (optional)</small>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" name="submit" class="btn-submit">
                    <i class="fas fa-save"></i>
                    Create Outreach
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
        // Set default date to today
        const dateInput = document.getElementById('outreach_date');
        if (!dateInput.value) {
            const today = new Date().toISOString().split('T')[0];
            dateInput.value = today;
        }
        
        // Website URL validation
        const websiteInput = document.getElementById('website');
        websiteInput.addEventListener('blur', function() {
            let url = this.value;
            if (url && !url.startsWith('http://') && !url.startsWith('https://')) {
                this.value = 'https://' + url;
            }
        });
        
        // Email validation
        const emailInput = document.getElementById('email');
        emailInput.addEventListener('blur', function() {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (this.value && !emailRegex.test(this.value)) {
                this.style.borderColor = '#e74c3c';
                showTooltip(this, 'Please enter a valid email address');
            } else {
                this.style.borderColor = '#e1e8ed';
                hideTooltip(this);
            }
        });
        
        function showTooltip(input, message) {
            let tooltip = input.parentElement.querySelector('.error-tooltip');
            if (!tooltip) {
                tooltip = document.createElement('small');
                tooltip.className = 'error-tooltip';
                tooltip.style.color = '#e74c3c';
                tooltip.style.fontSize = '11px';
                tooltip.style.marginTop = '5px';
                tooltip.style.display = 'block';
                input.parentElement.appendChild(tooltip);
            }
            tooltip.innerHTML = '<i class="fas fa-exclamation-triangle"></i> ' + message;
        }
        
        function hideTooltip(input) {
            const tooltip = input.parentElement.querySelector('.error-tooltip');
            if (tooltip) tooltip.remove();
        }
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
        max-width: 700px;
        margin: 0 auto;
    }

    /* Form Layout */
    .outreach-form {
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