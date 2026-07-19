<?php
include('../config/db.php');

header('Content-Type: application/json');

if(isset($_GET['order_id'])){
    $order_id = mysqli_real_escape_string($conn, $_GET['order_id']);
    $query = mysqli_query($conn, "SELECT id FROM payments WHERE order_id = '$order_id'");
    
    if(mysqli_num_rows($query) > 0){
        echo json_encode(['exists' => true]);
    } else {
        echo json_encode(['exists' => false]);
    }
} else {
    echo json_encode(['exists' => false]);
}
?>