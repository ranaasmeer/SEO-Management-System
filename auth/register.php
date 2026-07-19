<?php
session_start();
include('../config/db.php');
require '../vendor/autoload.php'; // Composer autoload for PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Function to send verification code
function sendVerificationCode($email, $code) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = ''; // Replace with your email
        $mail->Password = '';  // Replace with app password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('', 'SEO App');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Your Verification Code';
        $mail->Body    = "Your verification code is: <b>$code</b>. It will expire in 2 minutes.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Step 1: Send code when user submits email & name
if(isset($_POST['send_code']) || isset($_POST['resend_code'])){
    $name = $_POST['name'] ?? $_SESSION['reg_name'];
    $email = $_POST['email'] ?? $_SESSION['reg_email'];
    $password = $_POST['password'] ?? $_SESSION['reg_password'];
    $role = $_POST['role'] ?? $_SESSION['reg_role'];

    // Only allow resend once
    if(isset($_POST['resend_code']) && !empty($_SESSION['resend_sent'])){
        $error = "You can resend the code only once.";
        $show_verification = true;
    } else {
        // Check if email exists
        if(!isset($_SESSION['reg_email'])) {
            $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
            if(mysqli_num_rows($check) > 0){
                $error = "Email already exists. Please use another email.";
            }
        }

        // Generate code and save to session
        $code = rand(100000,999999);
        $_SESSION['reg_name'] = $name;
        $_SESSION['reg_email'] = $email;
        $_SESSION['reg_password'] = $password;
        $_SESSION['reg_role'] = $role;
        $_SESSION['reg_code'] = $code;
        $_SESSION['reg_code_expiry'] = time() + 120; // 2 minutes expiry

        if(isset($_POST['resend_code'])) $_SESSION['resend_sent'] = true;

        if(sendVerificationCode($email, $code)){
            $show_verification = true;
        } else {
            $error = "Failed to send verification code. Check email settings.";
        }
    }
}

// Step 2: Verify code
if(isset($_POST['verify_code'])){
    $entered_code = $_POST['verification_code'];
    if(isset($_SESSION['reg_code']) && isset($_SESSION['reg_code_expiry'])){
        if(time() > $_SESSION['reg_code_expiry']){
            $error = "Verification code expired. Please try again.";
            unset($_SESSION['reg_code']);
        } elseif($entered_code == $_SESSION['reg_code']){
            $name = $_SESSION['reg_name'];
            $email = $_SESSION['reg_email'];
            $password = password_hash($_SESSION['reg_password'], PASSWORD_DEFAULT);
            $role = $_SESSION['reg_role'];

            $query = "INSERT INTO users (name,email,password,role) 
                      VALUES ('$name','$email','$password','$role')";
            if(mysqli_query($conn,$query)){
                $success = "Registered Successfully!";
                unset($_SESSION['reg_name'], $_SESSION['reg_email'], $_SESSION['reg_password'], $_SESSION['reg_role'], $_SESSION['reg_code'], $_SESSION['reg_code_expiry'], $_SESSION['resend_sent']);
                header("refresh:1;url=login.php");
            } else {
                $error = "Database error: ".mysqli_error($conn);
            }
        } else {
            $error = "Incorrect verification code.";
        }
    } else {
        $error = "No verification code found. Please submit your email first.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register - SEO App</title>
<style>
/* same styles as before */
* { box-sizing: border-box; margin:0; padding:0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
body { background: linear-gradient(135deg, #ff6a00, #ee0979); height:100vh; display:flex; justify-content:center; align-items:center; }
.register-container { background:#fff; padding:40px 30px; border-radius:15px; box-shadow:0 15px 40px rgba(0,0,0,0.2); width:380px; animation:fadeIn 1s ease forwards; }
.register-container h2 { text-align:center; margin-bottom:25px; color:#ee0979; font-size:28px; }
.register-container input, .register-container select { width:100%; padding:12px 15px; margin:10px 0 20px 0; border:1px solid #ccc; border-radius:8px; font-size:14px; transition: all 0.3s ease; }
.register-container input:focus, .register-container select:focus { border-color:#ee0979; box-shadow:0 0 10px rgba(238,9,121,0.3); outline:none; }
.register-container button { width:100%; padding:12px; border:none; background:#ee0979; color:white; font-size:16px; font-weight:bold; border-radius:8px; cursor:pointer; transition: all 0.3s ease; }
.register-container button:hover { background:#ff6a00; }
.message { padding:10px 15px; margin-bottom:15px; border-radius:5px; text-align:center; }
.success { background:#ddffdd; color:#007700; border-left:5px solid #007700; }
.error { background:#ffdddd; color:#d8000c; border-left:5px solid #d8000c; }
.verification-box { background:#fff; padding:30px; border-radius:12px; box-shadow:0 10px 25px rgba(0,0,0,0.2); text-align:center; margin-top:15px; }
.verification-box input { margin-bottom:15px; }
#timer { font-weight:bold; color:#ee0979; }
#resend-btn { margin-top:10px; background:#007BFF; color:white; }
#resend-btn:hover { background:#0056b3; }
</style>
<script>
let countdown = 120;
function startTimer(){
    const timerEl = document.getElementById('timer');
    const interval = setInterval(() => {
        if(countdown <= 0){
            clearInterval(interval);
            timerEl.innerText = "Expired";
        } else {
            let min = Math.floor(countdown/60);
            let sec = countdown%60;
            timerEl.innerText = min + ":" + (sec<10?'0'+sec:sec);
            countdown--;
        }
    },1000);
}
</script>
</head>
<body>

<div class="register-container">
<h2>Register</h2>

<?php if(!empty($success)) : ?>
    <div class="message success"><?php echo $success; ?></div>
<?php endif; ?>
<?php if(!empty($error)) : ?>
    <div class="message error"><?php echo $error; ?></div>
<?php endif; ?>

<?php if(empty($show_verification)) : ?>
<form method="POST">
    <input type="text" name="name" placeholder="Name" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>

    <select name="role" required>
        <option value="">Select Role</option>
        <option value="client">Client</option>
        <option value="freelancer">Freelancer</option>
    </select>

    <button name="send_code">Register</button>
</form>
<?php else: ?>
<div class="verification-box">
    <p>Enter the verification code sent to <b><?php echo $_SESSION['reg_email']; ?></b></p>
    <form method="POST">
        <input type="text" name="verification_code" placeholder="Enter code" required>
        <button name="verify_code">Verify & Register</button>
    </form>
    <form method="POST">
        <button id="resend-btn" name="resend_code" <?php echo isset($_SESSION['resend_sent'])?'disabled':''; ?>>Resend Code</button>
    </form>
    <p>Code expires in <span id="timer"></span></p>
</div>
<script>startTimer();</script>
<?php endif; ?>

</div>
</body>
</html>