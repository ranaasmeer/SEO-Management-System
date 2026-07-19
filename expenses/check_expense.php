<?php
include('../config/db.php');

header('Content-Type: application/json');

if(isset($_GET['order_id'])){
    $order_id = mysqli_real_escape_string($conn, $_GET['order_id']);
    $query = mysqli_query($conn, "SELECT cost, description FROM expenses WHERE order_id = '$order_id'");
    
    if(mysqli_num_rows($query) > 0){
        $data = mysqli_fetch_assoc($query);
        echo json_encode([
            'exists' => true,
            'cost' => $data['cost'],
            'description' => $data['description']
        ]);
    } else {
        echo json_encode(['exists' => false]);
    }
} else {
    echo json_encode(['exists' => false]);
}
?>