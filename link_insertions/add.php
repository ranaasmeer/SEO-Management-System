<?php 
include('../includes/header.php'); 
include('../includes/sidebar.php'); 
include('../config/db.php');

// Fetch orders (only non-completed orders)
$orders = mysqli_query($conn, "SELECT * FROM orders WHERE status != 'Completed' ORDER BY id DESC");

$message = '';
$messageType = '';

if(isset($_POST['submit'])){
    if(mysqli_num_rows($orders) == 0){
        $message = "Cannot add link insertion. No orders exist.";
        $messageType = 'error';
    } else {
        $order_id = mysqli_real_escape_string($conn, $_POST['order_id']);
        $source = mysqli_real_escape_string($conn, $_POST['source_url']);
        $target = mysqli_real_escape_string($conn, $_POST['target_url']);
        $anchor = mysqli_real_escape_string($conn, $_POST['anchor_text']);
        $instructions = mysqli_real_escape_string($conn, $_POST['instructions']);
        $custom = mysqli_real_escape_string($conn, $_POST['custom_text']);

        // IMAGE UPLOAD (Only Before Image)
        $before = '';
        if($_FILES['before']['name']) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $file_extension = strtolower(pathinfo($_FILES['before']['name'], PATHINFO_EXTENSION));
            
            if(in_array($file_extension, $allowed)) {
                $before = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $_FILES['before']['name']);
                $target_dir = "upload/";
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                move_uploaded_file($_FILES['before']['tmp_name'], $target_dir . $before);
            } else {
                $message = "Invalid file type. Only JPG, PNG, GIF, WEBP are allowed.";
                $messageType = 'error';
            }
        }

        if(empty($message)) {
            $query = "INSERT INTO link_insertions 
            (order_id, source_url, target_url, anchor_text, instructions, custom_text, before_image)
            VALUES 
            ('$order_id','$source','$target','$anchor','$instructions','$custom','$before')";

            if(mysqli_query($conn, $query)) {
                $message = "Link Insertion Added Successfully! Redirecting...";
                $messageType = 'success';
                echo "<script>setTimeout(function(){ window.location.href = 'index.php'; }, 1500);</script>";
            } else {
                $message = "Error: " . mysqli_error($conn);
                $messageType = 'error';
            }
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
                <h2>Add Link Insertion</h2>
                <p>Create a new link insertion campaign</p>
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

    <?php if(mysqli_num_rows($orders) > 0): ?>
    <!-- Form Container -->
    <div class="form-container">
        <form method="POST" enctype="multipart/form-data" class="link-insertion-form" id="linkInsertionForm">
            
            <!-- Order Selection -->
            <div class="form-group">
                <label for="order_id">
                    <i class="fas fa-shopping-cart"></i>
                    Order
                </label>
                <div class="input-wrapper">
                    <select id="order_id" name="order_id" required class="form-select">
                        <option value="">Select an order</option>
                        <?php while($row = mysqli_fetch_assoc($orders)): ?>
                        <option value="<?php echo $row['id']; ?>">
                            #<?php echo htmlspecialchars($row['order_id']); ?> - <?php echo htmlspecialchars($row['service_type']); ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                    <div class="input-focus-border"></div>
                </div>
                <small class="form-hint">Select the order this link insertion belongs to</small>
            </div>

            <!-- Source URL -->
            <div class="form-group">
                <label for="source_url">
                    <i class="fas fa-link"></i>
                    Source URL
                </label>
                <div class="input-wrapper">
                    <input type="url" id="source_url" name="source_url" placeholder="https://example.com/article" 
                           required class="form-input">
                    <div class="input-focus-border"></div>
                </div>
                <small class="form-hint">The website where the link will be placed</small>
            </div>

            <!-- Target URL -->
            <div class="form-group">
                <label for="target_url">
                    <i class="fas fa-bullseye"></i>
                    Target URL
                </label>
                <div class="input-wrapper">
                    <input type="url" id="target_url" name="target_url" placeholder="https://yourwebsite.com/target" 
                           required class="form-input">
                    <div class="input-focus-border"></div>
                </div>
                <small class="form-hint">Your website URL that will be linked</small>
            </div>

            <!-- Anchor Text -->
            <div class="form-group">
                <label for="anchor_text">
                    <i class="fas fa-anchor"></i>
                    Anchor Text
                </label>
                <div class="input-wrapper">
                    <input type="text" id="anchor_text" name="anchor_text" placeholder="Click here to learn more" 
                           required class="form-input">
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
                              rows="3" class="form-textarea"></textarea>
                    <div class="input-focus-border"></div>
                </div>
                <small class="form-hint">Any special requirements or notes for this link insertion</small>
            </div>

            <!-- Custom Text -->
            <div class="form-group">
                <label for="custom_text">
                    <i class="fas fa-pen-alt"></i>
                    Custom Text
                </label>
                <div class="input-wrapper">
                    <textarea id="custom_text" name="custom_text" placeholder="Any additional custom text..." 
                              rows="3" class="form-textarea"></textarea>
                    <div class="input-focus-border"></div>
                </div>
                <small class="form-hint">Additional text or notes for the client</small>
            </div>

            <!-- Before Image Upload -->
            <div class="form-group">
                <label for="before">
                    <i class="fas fa-image"></i>
                    Before Image
                </label>
                <div class="image-upload-section">
                    <div class="file-input-wrapper">
                        <input type="file" name="before" id="before" accept="image/jpeg,image/png,image/gif,image/webp" class="file-input">
                        <label for="before" class="file-label">
                            <i class="fas fa-cloud-upload-alt"></i>
                            Choose Image
                        </label>
                        <span class="file-name" id="file_name">No file chosen</span>
                    </div>
                    <div id="image_preview_container" class="image-preview-container" style="display:none;">
                        <div class="preview-wrapper">
                            <img id="image_preview" src="" alt="Before Image Preview">
                            <span id="remove_image_btn" class="remove-image-btn">
                                <i class="fas fa-times"></i>
                            </span>
                        </div>
                    </div>
                    <small class="form-hint">Upload a screenshot of the page before link insertion (Optional)</small>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" name="submit" class="btn-submit">
                    <i class="fas fa-save"></i>
                    Create Link Insertion
                </button>
                <a href="index.php" class="btn-cancel">
                    <i class="fas fa-times"></i>
                    Cancel
                </a>
            </div>
        </form>
    </div>
    <?php else: ?>
        <div class="empty-state-container">
            <div class="empty-state-content">
                <i class="fas fa-box-open"></i>
                <h4>No Orders Available</h4>
                <p>You need to create an order before adding a link insertion.</p>
                <a href="../orders/add.php" class="btn-primary">
                    <i class="fas fa-plus"></i>
                    Create Order First
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const fileInput = document.getElementById('before');
        const previewContainer = document.getElementById('image_preview_container');
        const previewImg = document.getElementById('image_preview');
        const removeBtn = document.getElementById('remove_image_btn');
        const fileNameSpan = document.getElementById('file_name');
        
        // Update file name and preview
        fileInput.addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                fileNameSpan.textContent = this.files[0].name;
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    previewContainer.style.display = 'block';
                };
                reader.readAsDataURL(this.files[0]);
            } else {
                fileNameSpan.textContent = 'No file chosen';
                previewContainer.style.display = 'none';
                previewImg.src = '';
            }
        });
        
        // Remove image
        if (removeBtn) {
            removeBtn.addEventListener('click', function() {
                fileInput.value = '';
                fileNameSpan.textContent = 'No file chosen';
                previewContainer.style.display = 'none';
                previewImg.src = '';
            });
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

    .form-textarea {
        resize: vertical;
        min-height: 80px;
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

    /* Image Upload Section */
    .image-upload-section {
        border: 2px dashed #e1e8ed;
        border-radius: 15px;
        padding: 20px;
        background: #f8f9fa;
        transition: all 0.3s ease;
    }

    .image-upload-section:hover {
        border-color: #667eea;
        background: #f5f3ff;
    }

    .file-input-wrapper {
        display: flex;
        align-items: center;
        gap: 15px;
        flex-wrap: wrap;
    }

    .file-input {
        display: none;
    }

    .file-label {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .file-label:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }

    .file-name {
        color: #7f8c8d;
        font-size: 13px;
    }

    .image-preview-container {
        margin-top: 20px;
        text-align: center;
    }

    .preview-wrapper {
        position: relative;
        display: inline-block;
    }

    .preview-wrapper img {
        max-width: 250px;
        max-height: 200px;
        border-radius: 10px;
        border: 2px solid #e1e8ed;
        padding: 5px;
        background: white;
        object-fit: contain;
    }

    .remove-image-btn {
        position: absolute;
        top: -10px;
        right: -10px;
        width: 30px;
        height: 30px;
        background: #e74c3c;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .remove-image-btn:hover {
        background: #c0392b;
        transform: scale(1.1);
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

    /* Empty State */
    .empty-state-container {
        background: white;
        border-radius: 20px;
        padding: 60px 40px;
        text-align: center;
        box-shadow: 0 15px 40px rgba(0,0,0,0.1);
        animation: fadeInUp 0.6s ease;
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

    .btn-primary {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        text-decoration: none;
        border-radius: 10px;
        font-weight: 500;
        transition: all 0.3s ease;
        margin-top: 10px;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
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

        .file-input-wrapper {
            flex-direction: column;
            align-items: stretch;
        }
    }
</style>

<?php include('../includes/footer.php'); ?>