<?php 
ob_start(); // Add output buffering at the very top
include('../includes/header.php'); 
include('../includes/sidebar.php'); 
include('../config/db.php');

if($_SESSION['role'] != 'admin'){
    echo "<p style='color:red;'>Access Denied</p>";
    exit();
}

$id = $_GET['id'];

// Fetch order
$order = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM orders WHERE id=$id"));

// Fetch existing after image from link_insertion table
$link_insertion_query = mysqli_query($conn, "SELECT after_image FROM link_insertions WHERE order_id = $id");
$existing_image = mysqli_fetch_assoc($link_insertion_query);
$after_image_path = $existing_image ? $existing_image['after_image'] : null;

// Fetch clients
$clients = mysqli_query($conn, "SELECT * FROM users WHERE role='client'");

// Fetch freelancers/admins
$admins = mysqli_query($conn, "SELECT * FROM users WHERE role='freelancer' OR role='admin'");

$message = '';
$messageType = '';
$redirect_success = false;

if(isset($_POST['update'])){

    $status = $_POST['status'];
    $submitted_image = isset($_POST['existing_image']) ? $_POST['existing_image'] : null;
    $image_to_save = $submitted_image;

    // Handle image upload if a new file is provided
    if(isset($_FILES['after_image']) && $_FILES['after_image']['error'] == 0) {
        $target_dir = "../link_insertions/upload/"; // correct folder

        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_extension = pathinfo($_FILES["after_image"]["name"], PATHINFO_EXTENSION);
        $unique_name = time() . '_' . uniqid() . '.' . $file_extension;

        // IMPORTANT: store ONLY filename (like before_image)
        $image_to_save = $unique_name;

        $target_file = $target_dir . $unique_name;

        if(move_uploaded_file($_FILES["after_image"]["tmp_name"], $target_file)) {
            // delete old file correctly
            if($after_image_path && file_exists("../link_insertions/upload/".$after_image_path)) {
                unlink("../link_insertions/upload/".$after_image_path);
            }
        }
    }

    // Save/Update after image in link_insertion table
    if($image_to_save) {
        $check_query = mysqli_query($conn, "SELECT id FROM link_insertions WHERE order_id = $id");
        if(mysqli_num_rows($check_query) > 0) {
            mysqli_query($conn, "UPDATE link_insertions SET after_image='".mysqli_real_escape_string($conn, $image_to_save)."' WHERE order_id=$id");
        } else {
            mysqli_query($conn, "INSERT INTO link_insertions (order_id, after_image) VALUES ($id, '".mysqli_real_escape_string($conn, $image_to_save)."')");
        }
    } elseif($submitted_image === '') {
        // ONLY remove image, NOT the whole row
        mysqli_query($conn, "UPDATE link_insertions SET after_image='' WHERE order_id=$id");

        // delete file from folder
        if($after_image_path && file_exists("../link_insertions/upload/".$after_image_path)) {
            unlink("../link_insertions/upload/".$after_image_path);
        }
    }

    // AUTO COMPLETION LOGIC
    $completion_date = ($status == 'Completed') ? date('Y-m-d') : NULL;

    $query = "UPDATE orders SET 
        order_id='".mysqli_real_escape_string($conn,$_POST['order_id'])."',
        client_id='".mysqli_real_escape_string($conn,$_POST['client'])."',
        platform='".mysqli_real_escape_string($conn,$_POST['platform'])."',
        service_type='".mysqli_real_escape_string($conn,$_POST['service'])."',
        order_date='".mysqli_real_escape_string($conn,$_POST['order_date'])."',
        deadline='".mysqli_real_escape_string($conn,$_POST['deadline'])."',
        admin_assigned='".mysqli_real_escape_string($conn,$_POST['assigned'])."',
        price='".mysqli_real_escape_string($conn,$_POST['price'])."',
        status='".mysqli_real_escape_string($conn,$_POST['status'])."',
        completion_date='$completion_date'
        WHERE id=$id";

    if(mysqli_query($conn,$query)){
        $message = "Order updated successfully! Redirecting...";
        $messageType = 'success';
        $redirect_success = true;
    } else {
        $message = "Error updating order: " . mysqli_error($conn);
        $messageType = 'error';
        $redirect_success = false;
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
                <h2>Edit Order</h2>
                <p>Update order details and manage after image</p>
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
        <form method="POST" enctype="multipart/form-data" class="order-form" id="orderForm">

            <!-- Order ID Field -->
            <div class="form-group">
                <label for="order_id">
                    <i class="fas fa-hashtag"></i>
                    Order ID
                </label>
                <div class="input-wrapper">
                    <input type="text" id="order_id" name="order_id" value="<?php echo htmlspecialchars($order['order_id']); ?>" 
                           required class="form-input">
                    <div class="input-focus-border"></div>
                </div>
            </div>

            <!-- Client Field -->
            <div class="form-group">
                <label for="client">
                    <i class="fas fa-user"></i>
                    Client
                </label>
                <div class="input-wrapper">
                    <select id="client" name="client" required class="form-select">
                        <?php while($row = mysqli_fetch_assoc($clients)): ?>
                        <option value="<?php echo $row['id']; ?>" <?php if($row['id']==$order['client_id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($row['name']); ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                    <div class="input-focus-border"></div>
                </div>
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
                            <option value="Fiverr" <?php if($order['platform']=='Fiverr') echo 'selected'; ?>>Fiverr</option>
                            <option value="Direct" <?php if($order['platform']=='Direct') echo 'selected'; ?>>Direct</option>
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
                            <option value="Guest Post" <?php if($order['service_type']=='Guest Post') echo 'selected'; ?>>Guest Post</option>
                            <option value="Link Insertion" <?php if($order['service_type']=='Link Insertion') echo 'selected'; ?>>Link Insertion</option>
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
                        <input type="date" id="order_date" name="order_date" value="<?php echo $order['order_date']; ?>" 
                               required class="form-input">
                        <div class="input-focus-border"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="deadline">
                        <i class="fas fa-calendar-times"></i>
                        Deadline
                    </label>
                    <div class="input-wrapper">
                        <input type="date" id="deadline" name="deadline" value="<?php echo $order['deadline']; ?>" 
                               required class="form-input">
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
                        <?php while($row = mysqli_fetch_assoc($admins)): ?>
                        <option value="<?php echo $row['id']; ?>" <?php if($row['id']==$order['admin_assigned']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($row['name']); ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                    <div class="input-focus-border"></div>
                </div>
            </div>

            <!-- Price Field -->
            <div class="form-group">
                <label for="price">
                    <i class="fas fa-dollar-sign"></i>
                    Price
                </label>
                <div class="input-wrapper">
                    <span class="input-prefix">$</span>
                    <input type="number" id="price" name="price" value="<?php echo $order['price']; ?>" 
                           step="0.01" required class="form-input with-prefix">
                    <div class="input-focus-border"></div>
                </div>
            </div>

            <!-- After Image Upload Section -->
            <div class="form-group">
                <label for="after_image">
                    <i class="fas fa-image"></i>
                    After Image
                </label>
                <div class="image-upload-section">
                    <div class="file-input-wrapper">
                        <input type="file" name="after_image" id="after_image" accept="image/*" class="file-input">
                        <label for="after_image" class="file-label">
                            <i class="fas fa-cloud-upload-alt"></i>
                            Choose Image
                        </label>
                        <span class="file-name" id="file_name">No file chosen</span>
                    </div>
                    
                    <div id="image_preview_container" style="<?php echo $after_image_path ? '' : 'display:none;'; ?>" class="image-preview-container">
                        <div class="preview-wrapper">
                            <img id="image_preview" 
                                 src="<?php echo $after_image_path ? '../link_insertions/upload/'.htmlspecialchars($after_image_path) : ''; ?>" 
                                 alt="After Image Preview">
                            <span id="delete_image_btn" class="delete-image-btn">
                                <i class="fas fa-times"></i>
                            </span>
                        </div>
                    </div>
                    <input type="hidden" name="existing_image" id="existing_image" value="<?php echo htmlspecialchars($after_image_path); ?>">
                    <div id="file_status" class="file-status">
                        <?php echo $after_image_path ? 'Image uploaded' : 'No file is chosen'; ?>
                    </div>
                </div>
                <small class="form-hint">Upload an image showing the completed work (Required for Completed status)</small>
            </div>

            <!-- Status Field -->
            <div class="form-group">
                <label for="status_select">
                    <i class="fas fa-tasks"></i>
                    Status
                </label>
                <div class="input-wrapper">
                    <select name="status" id="status_select" required class="form-select">
                        <option value="Pending" <?php if($order['status']=='Pending') echo 'selected'; ?>>Pending</option>
                        <option value="In Progress" <?php if($order['status']=='In Progress') echo 'selected'; ?>>In Progress</option>
                        <option value="Completed" <?php if($order['status']=='Completed') echo 'selected'; ?>>Completed</option>
                    </select>
                    <div class="input-focus-border"></div>
                </div>
                <small class="form-hint">Note: Completed status requires an After Image to be uploaded</small>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="submit" name="update" class="btn-submit">
                    <i class="fas fa-save"></i>
                    Update Order
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
        const statusSelect = document.getElementById('status_select');
        const fileInput = document.getElementById('after_image');
        const previewContainer = document.getElementById('image_preview_container');
        const previewImg = document.getElementById('image_preview');
        const deleteBtn = document.getElementById('delete_image_btn');
        const existingImageInput = document.getElementById('existing_image');
        const fileStatusDiv = document.getElementById('file_status');
        const fileNameSpan = document.getElementById('file_name');
        
        // Function to update completed status availability based on image presence
        function updateStatusAvailability() {
            const hasImage =
                (fileInput.files && fileInput.files.length > 0) ||  // NEW upload
                (existingImageInput.value && existingImageInput.value !== ''); // OLD image

            const completedOption = Array.from(statusSelect.options)
                .find(opt => opt.value === 'Completed');

            if (hasImage) {
                completedOption.disabled = false;
            } else {
                completedOption.disabled = true;
                if (statusSelect.value === 'Completed') {
                    statusSelect.value = 'Pending';
                }
            }
        }
        
        // Update file name display
        fileInput.addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                fileNameSpan.textContent = this.files[0].name;
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    previewContainer.style.display = 'block';
                    existingImageInput.value = '';
                    fileStatusDiv.innerHTML = 'New image selected';
                    updateStatusAvailability();
                };
                reader.readAsDataURL(this.files[0]);
            } else {
                fileNameSpan.textContent = 'No file chosen';
                if (!existingImageInput.value) {
                    previewContainer.style.display = 'none';
                    previewImg.src = '';
                    fileStatusDiv.innerHTML = 'No file is chosen';
                    updateStatusAvailability();
                }
            }
        });
        
        // Handle delete button click
        if (deleteBtn) {
            deleteBtn.addEventListener('click', function() {
                previewContainer.style.display = 'none';
                previewImg.src = '';
                fileInput.value = '';
                fileNameSpan.textContent = 'No file chosen';
                existingImageInput.value = '';
                fileStatusDiv.innerHTML = 'No file is chosen';
                updateStatusAvailability();
            });
        }
        
        // Initially set status select based on existing image
        const hasExistingImage = (existingImageInput.value && existingImageInput.value !== '');
        const completedOption = Array.from(statusSelect.options).find(opt => opt.value === 'Completed');
        
        if (hasExistingImage) {
            completedOption.disabled = false;
            if (previewContainer.style.display !== 'none') {
                fileStatusDiv.innerHTML = 'Image uploaded';
            }
        } else {
            completedOption.disabled = true;
            if (statusSelect.value === 'Completed') {
                statusSelect.value = 'Pending';
            }
            fileStatusDiv.innerHTML = 'No file is chosen';
        }
        
        // Also handle case when there is preview but no existing image value (new upload scenario)
        if (previewImg.src && previewImg.src !== window.location.href && previewImg.src !== '' && !existingImageInput.value) {
            fileStatusDiv.innerHTML = 'New image selected';
            completedOption.disabled = false;
        }
        
        // Force re-check on status change
        statusSelect.addEventListener('change', function() {
            if (this.value === 'Completed') {
                const hasImage =
                    (fileInput.files && fileInput.files.length > 0) ||
                    (existingImageInput.value && existingImageInput.value !== '');

                if (!hasImage) {
                    alert('Please upload an After Image before marking order as Completed.');
                    this.value = 'Pending';
                }
            }
        });
        
        // Initial call to set correct status availability
        updateStatusAvailability();
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

    .delete-image-btn {
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

    .delete-image-btn:hover {
        background: #c0392b;
        transform: scale(1.1);
    }

    .file-status {
        margin-top: 10px;
        font-size: 12px;
        color: #95a5a6;
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

        .file-input-wrapper {
            flex-direction: column;
            align-items: stretch;
        }
    }
</style>

<?php if($redirect_success): ?>
<script>
    setTimeout(function() {
        window.location.href = 'index.php';
    }, 1500);
</script>
<?php endif; ?>

<?php include('../includes/footer.php'); 
ob_end_flush(); // End output buffering
?>