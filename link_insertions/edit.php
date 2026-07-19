<?php 
include('../includes/header.php'); 
include('../includes/sidebar.php'); 
include('../config/db.php');

if($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'freelancer'){
    echo "<p style='color:red;'>Access Denied</p>";
    exit();
}

$id = $_GET['id'];

// Fetch data
$data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM link_insertions WHERE id=$id"));

$message = '';
$messageType = '';

if(isset($_POST['update'])){

    $source = mysqli_real_escape_string($conn,$_POST['source']);
    $target = mysqli_real_escape_string($conn,$_POST['target']);
    $anchor = mysqli_real_escape_string($conn,$_POST['anchor']);
    $instructions = mysqli_real_escape_string($conn,$_POST['instructions']);
    $custom = mysqli_real_escape_string($conn,$_POST['custom']);

    $query = "UPDATE link_insertions SET 
        source_url='$source',
        target_url='$target',
        anchor_text='$anchor',
        instructions='$instructions',
        custom_text='$custom'
        WHERE id=$id";

    if(mysqli_query($conn, $query)){
        $message = "Link Insertion Updated Successfully! Redirecting...";
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
                <i class="fas fa-edit"></i>
            </div>
            <div class="header-text">
                <h2>Edit Link Insertion</h2>
                <p>Update link insertion details</p>
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
        <form method="POST" class="link-insertion-form" id="editLinkInsertionForm">
            
            <!-- Source URL -->
            <div class="form-group">
                <label for="source">
                    <i class="fas fa-link"></i>
                    Source URL
                </label>
                <div class="input-wrapper">
                    <input type="url" id="source" name="source" value="<?php echo htmlspecialchars($data['source_url']); ?>" 
                           placeholder="https://example.com/article" required class="form-input">
                    <div class="input-focus-border"></div>
                </div>
                <small class="form-hint">The website where the link will be placed</small>
            </div>

            <!-- Target URL -->
            <div class="form-group">
                <label for="target">
                    <i class="fas fa-bullseye"></i>
                    Target URL
                </label>
                <div class="input-wrapper">
                    <input type="url" id="target" name="target" value="<?php echo htmlspecialchars($data['target_url']); ?>" 
                           placeholder="https://yourwebsite.com/target" required class="form-input">
                    <div class="input-focus-border"></div>
                </div>
                <small class="form-hint">Your website URL that will be linked</small>
            </div>

            <!-- Anchor Text -->
            <div class="form-group">
                <label for="anchor">
                    <i class="fas fa-anchor"></i>
                    Anchor Text
                </label>
                <div class="input-wrapper">
                    <input type="text" id="anchor" name="anchor" value="<?php echo htmlspecialchars($data['anchor_text']); ?>" 
                           placeholder="Click here to learn more" required class="form-input">
                    <div class="input-focus-border"></div>
                </div>
                <small class="form-hint">The clickable text for the link</small>
            </div>

            <!-- Instructions -->
            <div class="form-group">
                <label for="instructions">
                    <i class="fas fa-clipboard-list"></i>
                    Instructions
                </label>
                <div class="input-wrapper">
                    <textarea id="instructions" name="instructions" placeholder="Special instructions for this link insertion..." 
                              rows="4" class="form-textarea"><?php echo htmlspecialchars($data['instructions']); ?></textarea>
                    <div class="input-focus-border"></div>
                </div>
                <small class="form-hint">Any special requirements or notes for this link insertion</small>
            </div>

            <!-- Custom Text -->
            <div class="form-group">
                <label for="custom">
                    <i class="fas fa-pen-alt"></i>
                    Custom Text
                </label>
                <div class="input-wrapper">
                    <textarea id="custom" name="custom" placeholder="Any additional custom text..." 
                              rows="4" class="form-textarea"><?php echo htmlspecialchars($data['custom_text']); ?></textarea>
                    <div class="input-focus-border"></div>
                </div>
                <small class="form-hint">Additional text or notes for the client</small>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" name="update" class="btn-submit">
                    <i class="fas fa-save"></i>
                    Update Link Insertion
                </button>
                <a href="index.php" class="btn-cancel">
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
    .link-insertion-form {
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

    .form-input, .form-textarea {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e1e8ed;
        border-radius: 10px;
        font-size: 14px;
        transition: all 0.3s ease;
        background: white;
        font-family: inherit;
    }

    .form-textarea {
        resize: vertical;
        min-height: 100px;
    }

    .form-input:focus, .form-textarea:focus {
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
    .form-textarea:focus + .input-focus-border {
        width: 100%;
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