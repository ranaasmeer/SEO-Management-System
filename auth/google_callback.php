<?php
session_start();
include('../config/db.php');

require '../vendor/autoload.php'; // if using Google Client Library

$client = new Google_Client();
$client->setClientId("YOUR_CLIENT_ID");
$client->setClientSecret("YOUR_CLIENT_SECRET");
$client->setRedirectUri("http://localhost/seo_app/auth/google_callback.php");

$client->addScope("email");
$client->addScope("profile");

if(isset($_GET['code'])){

    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $client->setAccessToken($token['access_token']);

    $google_service = new Google_Service_Oauth2($client);
    $data = $google_service->userinfo->get();

    $email = $data->email;
    $name = $data->name;

    // CHECK USER
    $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");

    if(mysqli_num_rows($check) > 0){
        $user = mysqli_fetch_assoc($check);
    } else {
        // AUTO REGISTER
        mysqli_query($conn, "INSERT INTO users (name,email,role) VALUES ('$name','$email','client')");
        $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE email='$email'"));
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role'] = $user['role'];

    header("Location: ../client/dashboard.php");
}
?>