<?php
session_start();
require_once('../db.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: /progetto/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = $_GET['order_id'] ?? null;
$stmt = $conn->prepare("
    SELECT o.id AS order_id, a.street, a.postal_code, a.country, p.payment_method, p.payment_status, p.created_at
    FROM orders o
    JOIN addresses a ON o.address_id = a.id
    LEFT JOIN payments p ON p.order_id = o.id
    WHERE o.id = ? AND o.user_id = ?
");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();

if (!$order_id) {
    die("ID заказа не указан.");
}





$items_stmt = $conn->prepare("
    SELECT oi.quantity, oi.price, p.name, p.image
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
");
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();
$items = $items_result->fetch_all(MYSQLI_ASSOC);
$items_stmt->close();


if (!$order) {
    die("Заказ не найден.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Confirmation</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
</head>
<body class="bg-[#111827] pt-[80px] min-h-screen flex flex-col">
    <?php include '../header.php'; ?>

    <main class="container mx-auto px-4 py-10 flex-grow">
        <div class="bg-white p-8 rounded-2xl shadow-xl max-w-3xl mx-auto">

            <!-- Header confirmation -->
            <div class="flex items-center justify-center text-[#672209] mb-6">
                <h1 class="text-3xl font-bold">Order Successfully Placed</h1>
            </div>

            <!-- Order details -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-gray-700 text-sm sm:text-base">
                <div class="flex items-start gap-3">
                    <div>
                        <p class="font-medium">Order ID</p>
                        <p class="text-gray-600"><?= htmlspecialchars($order['order_id']) ?></p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <div>
                        <p class="font-medium">Date</p>
                        <p class="text-gray-600"><?= htmlspecialchars($order['created_at']) ?></p>
                    </div>
                </div>
                <div class="flex items-start gap-3 sm:col-span-2">
                    <div>
                        <p class="font-medium">Delivery Address</p>
                        <p class="text-gray-600">
                            <?= htmlspecialchars($order['street']) ?>,
                            <?= htmlspecialchars($order['postal_code']) ?>,
                            <?= htmlspecialchars($order['country']) ?>
                        </p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <div>
                        <p class="font-medium">Payment Method</p>
                        <p class="text-gray-600"><?= htmlspecialchars($order['payment_method']) ?></p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <div>
                        <p class="font-medium">Payment Status</p>
                        <p class="text-gray-600"><?= htmlspecialchars($order['payment_status']) ?></p>
                    </div>
                </div>
            </div>

            <!-- Products list -->
            <div class="mt-8">
                <h2 class="text-xl font-bold mb-4">Products</h2>
                <div class="space-y-4">
                    <?php foreach ($items as $item): ?>
                        <?php
                            $images = json_decode($item['image'], true);
                            $imgSrc = !empty($images) ? '/progetto/admin/uploads/' . htmlspecialchars($images[0]) : '/progetto/images/default.jpg';
                        ?>
                        <div class="flex items-center gap-4 border rounded-lg p-3 bg-gray-50">
                            <img src="<?= $imgSrc ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="w-16 h-16 object-cover rounded-lg border" />
                            <div class="flex-grow">
                                <p class="font-semibold"><?= htmlspecialchars($item['name']) ?></p>
                                <p class="text-gray-500 text-sm">Qty: <?= $item['quantity'] ?> × <?= number_format($item['price'], 2) ?> $</p>
                            </div>
                            <div class="font-bold text-gray-800 text-sm whitespace-nowrap">
                                <?= number_format($item['price'] * $item['quantity'], 2) ?> $
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Back button -->
            <div class="mt-10 text-center">
                <a href="/progetto/index.php" class="inline-block bg-[#672209] hover:bg-yellow-700 text-white font-medium px-6 py-3 rounded-xl transition-all duration-300">
                    Back to Homepage
                </a>
            </div>
        </div>
    </main>

    <?php include '../footer.php'; ?>
</body>
</html>
