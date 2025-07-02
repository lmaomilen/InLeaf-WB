<?php
session_start();
require_once('db.php');

if(!isset($_SESSION["user_id"])){
    die("Login your account!");
}

$user_id = $_SESSION["user_id"];

$result = $conn->query("SELECT orders.id, products.name, orders.quantity, orders.total_price, orders.status, orders.created_at 
                        FROM orders 
                        JOIN products ON orders.product_id = products.id 
                        WHERE orders.user_id='$user_id' 
                        ORDER BY orders.created_at DESC");

echo "<h2>My orders</h2>";

if($result->num_rows > 0){
    while($row = $result->fetch_assoc()){
        echo "<p>Order #{$row['id']} - {$row['name']} - ({$row['quantity']} pcs ) - {$row['total_price']} USD - Status: <strong>{$row['status']}</strong> - Date: {$row ['created_at']}</p>";
    }
}else{
    echo "<p>You have no orders yet</p>";
}
$conn->close();
?>