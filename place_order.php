<?php
session_start();
require_once('../db.php');

if (!isset($_SESSION["user_id"])) {
    header("Location: /progetto/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];


$address_type = $_POST['address_type'] ?? 'new';
$payment_method = $_POST['payment_method'] ?? '';
$card_number = $_POST['card_number'] ?? '';
$card_expiry = $_POST['card_expiry'] ?? '';
$card_cvc = $_POST['card_cvc'] ?? '';
$paypal_email = $_POST['paypal_email'] ?? '';
$address_id = null;


if ($payment_method === 'card' && (!$card_number || !$card_expiry || !$card_cvc)) {
    die("Fill in all card details.");
}
if ($payment_method === 'paypal' && empty($paypal_email)) {
    die("Enter PayPal email.");
}


if ($address_type === 'new') {
    $street = trim($_POST['street'] ?? '');
    $postal_code = trim($_POST['postal_code'] ?? '');
    $country = trim($_POST['country'] ?? '');

    if (empty($street) || empty($postal_code) || empty($country)) {
        die("Please fill in all fields of the new address.");
    }

    $stmt = $conn->prepare("INSERT INTO addresses (user_id, street, postal_code, country, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("isss", $user_id, $street, $postal_code, $country);
    $stmt->execute();
    $address_id = $stmt->insert_id;
    $stmt->close();
} elseif ($address_type === 'saved') {
    $saved_address_id = (int)($_POST['saved_address_id'] ?? 0);
    if ($saved_address_id === 0) {
        die("Select a saved address.");
    }
    $address_id = $saved_address_id;
} else {
    die("Incorrect address type.");
}


$cart_items = [];
$cart_query = $conn->prepare("SELECT product_id, quantity FROM cart WHERE user_id = ?");
$cart_query->bind_param("i", $user_id);
$cart_query->execute();
$result = $cart_query->get_result();

while ($row = $result->fetch_assoc()) {
    $cart_items[$row['product_id']] = [
        'quantity' => $row['quantity']
    ];
}
$cart_query->close();

if (empty($cart_items)) {
    die("Your cart is empty.");
}

$total_price = 0;
foreach ($cart_items as $product_id => $item) {
    $check = $conn->prepare("SELECT price FROM products WHERE id = ?");
    $check->bind_param("i", $product_id);
    $check->execute();
    $result = $check->get_result();
    if ($result->num_rows === 0) continue;

    $product_data = $result->fetch_assoc();
    $price = (float)$product_data['price'];
    $check->close();

    $total_price += $price * (int)$item['quantity'];
}
$stmt = $conn->prepare("INSERT INTO orders (user_id, address_id, total_price, created_at) VALUES (?, ?, ?, NOW())");
$stmt->bind_param("iid", $user_id, $address_id, $total_price);
$stmt->execute();
$order_id = $stmt->insert_id;
$stmt->close();

foreach ($cart_items as $product_id => $item) {
    $quantity = (int)$item['quantity'];

    $check = $conn->prepare("SELECT price FROM products WHERE id = ?");
    $check->bind_param("i", $product_id);
    $check->execute();
    $result = $check->get_result();
    if ($result->num_rows === 0) continue;

    $product_data = $result->fetch_assoc();
    $price = (float)$product_data['price'];
    $check->close();

    $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiid", $order_id, $product_id, $quantity, $price);
    $stmt->execute();
    $stmt->close();
}

if (empty($payment_method)) {
    die("payment_method is empty");
}

$stmt = $conn->prepare("INSERT INTO payments (order_id, user_id, payment_method, payment_status, created_at) VALUES (?, ?, ?, 'pending', NOW())");
$stmt->bind_param("iis", $order_id, $user_id, $payment_method);
$stmt->execute();
$stmt->close();


$stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->close();


header("Location: order_confirmation.php?order_id=" . $order_id);
exit();
?>
