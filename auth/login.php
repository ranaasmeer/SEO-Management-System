<?php
session_start();
include('../config/db.php');
require '../vendor/autoload.php'; // PHPMailer autoload
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// LOGIN
if(isset($_POST['login'])){
    $email = $_POST['email'];
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($conn,$query);
    $user = mysqli_fetch_assoc($result);

if($user && password_verify($password, $user['password'])){
    if($user['status'] == 'blocked'){ // or $user['is_blocked'] == 1
        $error = "Your account has been blocked. Contact admin.";
    } else {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['name'] = $user['name'];

        if($user['role'] == 'client'){
            header("Location: ../client/dashboard.php");
        } else {
            header("Location: ../dashboard/index.php");
        }
        exit;
    }
} else {
    $error = "Invalid Credentials";
}
}

// FUNCTION TO SEND OTP
function sendOTP($email, $code){
    $mail = new PHPMailer(true);
    try{
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = ''; // your email
        $mail->Password = ''; // app password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('','SEO App');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Your Password Reset OTP';
        $mail->Body = "Your OTP is: <b>$code</b>. It expires in 2 minutes.";

        $mail->send();
        return true;
    }catch(Exception $e){
        return false;
    }
}

// STEP 1: FORGOT PASSWORD - SEND OTP
if(isset($_POST['forgot_submit'])){
    $email = $_POST['email'];
    $check = mysqli_query($conn,"SELECT * FROM users WHERE email='$email'");
    if(mysqli_num_rows($check)==0){
        $error = "Email not found!";
    } else {
        $otp = rand(100000,999999);
        $_SESSION['fp_email'] = $email;
        $_SESSION['fp_otp'] = $otp;
        $_SESSION['fp_otp_expiry'] = time() + 120; // 2 mins
        $_SESSION['fp_resend'] = true; // allow 1 resend

        if(sendOTP($email,$otp)){
            $show_otp = true;
        } else {
            $error = "Failed to send OTP. Check email settings.";
        }
    }
}

// RESEND OTP (single attempt)
if(isset($_POST['resend_otp'])){
    if(isset($_SESSION['fp_resend']) && $_SESSION['fp_resend']==true){
        $otp = rand(100000,999999);
        $_SESSION['fp_otp'] = $otp;
        $_SESSION['fp_otp_expiry'] = time() + 120;
        $_SESSION['fp_resend'] = false; // single attempt
        sendOTP($_SESSION['fp_email'],$otp);
        $success = "OTP resent!";
    }
}

// STEP 2: VERIFY OTP
if(isset($_POST['verify_otp'])){
    $entered = $_POST['otp'];
    if(time() > $_SESSION['fp_otp_expiry']){
        $error = "OTP expired! Please resend.";
    } elseif($entered == $_SESSION['fp_otp']){
        $show_new_pass = true;
    } else {
        $error = "Incorrect OTP!";
    }
}

// STEP 3: SAVE NEW PASSWORD
if(isset($_POST['save_new_password'])){
    $new_pass = password_hash($_POST['new_password'],PASSWORD_DEFAULT);
    $email = $_SESSION['fp_email'];
    $update = mysqli_query($conn,"UPDATE users SET password='$new_pass' WHERE email='$email'");
    if($update){
        unset($_SESSION['fp_email'],$_SESSION['fp_otp'],$_SESSION['fp_otp_expiry'],$_SESSION['fp_resend']);
        $success = "Password updated successfully!";
        header("refresh:1;url=login.php");
    } else {
        $error = "Database error: ".mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - SEO App</title>
<style>
* { box-sizing: border-box; margin:0; padding:0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
body { background: linear-gradient(135deg, #6a11cb, #2575fc); height:100vh; display:flex; justify-content:center; align-items:center; color:#333; }
.login-container { background:#fff; padding:40px 30px; border-radius:15px; box-shadow:0 15px 40px rgba(0,0,0,0.2); width:350px; animation:fadeIn 1s ease forwards; text-align:center; }
.login-container h2 { margin-bottom:25px; color:#2575fc; font-size:28px; }
.login-container input { width:100%; padding:12px 15px; margin:10px 0 20px 0; border:1px solid #ccc; border-radius:8px; transition: all 0.3s ease; font-size:14px; }
.login-container input:focus { border-color:#2575fc; box-shadow:0 0 10px rgba(37,117,252,0.3); outline:none; }
.login-container button { width:100%; padding:12px; border:none; background:#2575fc; color:white; font-size:16px; font-weight:bold; border-radius:8px; cursor:pointer; transition: all 0.3s ease; }
.login-container button:hover { background:#6a11cb; }
.error { background:#ffdddd; color:#d8000c; border-left:5px solid #d8000c; padding:10px 15px; margin-bottom:15px; border-radius:5px; text-align:left; }
.success { background:#ddffdd; color:#007700; border-left:5px solid #007700; padding:10px 15px; margin-bottom:15px; border-radius:5px; text-align:left; }
.register-link { margin-top:15px; font-size:14px; }
.register-link a { color:#2575fc; text-decoration:none; font-weight:bold; transition:0.3s; }
.register-link a:hover { color:#6a11cb; text-decoration:underline; }
.forgot-link { display:block; margin-top:10px; font-size:14px; cursor:pointer; color:#2575fc; text-decoration:underline; transition:0.3s; }
.forgot-link:hover { color:#6a11cb; }
.verification-box { background:#fff; padding:30px; border-radius:12px; box-shadow:0 10px 25px rgba(0,0,0,0.2); text-align:center; margin-top:15px; }
#timer { font-weight:bold; color:#ee0979; }
@keyframes fadeIn { from { opacity:0; transform:translateY(-20px); } to { opacity:1; transform:translateY(0); } }
</style>
<script>
let countdown = 120;
function startTimer(){
    const timerEl = document.getElementById('timer');
    const interval = setInterval(()=>{
        if(countdown<=0){ clearInterval(interval); timerEl.innerText="Expired"; }
        else{ let min=Math.floor(countdown/60); let sec=countdown%60; timerEl.innerText=min+":"+(sec<10?'0'+sec:sec); countdown--; }
    },1000);
}
function showForgot(){ document.getElementById('login-form').style.display='none'; document.getElementById('forgot-form').style.display='block'; }
</script>
</head>
<body>

<div class="login-container">
<h2>Login</h2>

<?php if(!empty($error)) echo "<div class='error'>$error</div>"; ?>
<?php if(!empty($success)) echo "<div class='success'>$success</div>"; ?>

<!-- LOGIN FORM -->
<form method="POST" id="login-form" <?php echo isset($show_otp) || isset($show_new_pass)?'style="display:none;"':''; ?>>
<input type="email" name="email" placeholder="Email" required>
<input type="password" name="password" placeholder="Password" required>
<button name="login">Login</button>
<span class="forgot-link" onclick="showForgot()">Forgot Password?</span>
</form>

<!-- FORGOT PASSWORD EMAIL INPUT -->
<form method="POST" id="forgot-form" style="display:none;">
<input type="email" name="email" placeholder="Enter your email" required>
<button name="forgot_submit">Ok</button>
</form>

<!-- OTP VERIFICATION -->
<?php if(isset($show_otp)): ?>
<div class="verification-box">
<p>Enter the OTP sent to <b><?php echo $_SESSION['fp_email']; ?></b></p>
<form method="POST">
<input type="text" name="otp" placeholder="Enter OTP" required>
<button name="verify_otp">Verify OTP</button>
</form>
<p>Expires in <span id="timer"></span></p>
<form method="POST">
<?php if($_SESSION['fp_resend']): ?><button name="resend_otp">Resend OTP</button><?php endif; ?>
</form>
</div>
<script>startTimer();</script>
<?php endif; ?>

<!-- NEW PASSWORD -->
<?php if(isset($show_new_pass)): ?>
<form method="POST" style="margin-top:15px;">
<input type="password" name="new_password" placeholder="Enter new password" required>
<button name="save_new_password">Save Changes</button>
</form>
<?php endif; ?>

<div class="register-link">
Don't have an account? <a href="register.php">Register Here</a>
</div>
</div>

</body>
</html>