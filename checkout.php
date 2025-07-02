<?php
session_start();
require_once('../db.php');

if (!isset($_SESSION["user_id"])) {
    header("Location: /progetto/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$cart_items = [];
$cart_query = $conn->prepare("SELECT c.product_id, c.quantity, p.name, p.price, p.image 
                              FROM cart c 
                              JOIN products p ON c.product_id = p.id 
                              WHERE c.user_id = ?");
$cart_query->bind_param("i", $user_id);
$cart_query->execute();
$result = $cart_query->get_result();

while ($row = $result->fetch_assoc()) {
    $cart_items[] = $row;
}
$cart_query->close();

$addresses = [];
$addr_result = $conn->prepare("SELECT * FROM addresses WHERE user_id = ?");
$addr_result->bind_param("i", $user_id);
$addr_result->execute();
$addr_data = $addr_result->get_result();
while ($row = $addr_data->fetch_assoc()) {
    $addresses[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#111827] pt-[80px]">
<?php include '../header.php'; ?>

<div class="container mx-auto p-4 grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Order Summary -->
    <aside class="lg:col-span-1 bg-white p-6 rounded-2xl shadow-xl">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Order Summary</h2>
        <?php if (!empty($cart_items)): ?>
            <?php $total = 0; ?>
            <ul class="space-y-4">
                <?php foreach ($cart_items as $item): ?>
                    <?php
                        $qty = $item['quantity'];
                        $subtotal = $item['price'] * $qty;
                        $total += $subtotal;
                        $images = json_decode($item['image'], true);
                        $img = !empty($images) ? '/progetto/admin/uploads/' . htmlspecialchars($images[0]) : 'default.jpg';
                    ?>
                    <li class="flex items-center">
                        <img src="<?= $img ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="w-16 h-16 object-cover rounded-xl shadow mr-4">
                        <div>
                            <h3 class="font-semibold text-sm text-gray-800 line-clamp-1"><?= htmlspecialchars($item['name']) ?></h3>
                            <p class="text-xs text-gray-500">x<?= $qty ?> — $<?= number_format($subtotal, 2) ?></p>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="border-t pt-4 mt-6 text-lg font-semibold flex justify-between">
                <span>Total:</span>
                <span>$<?= number_format($total, 2) ?></span>
            </div>
        <?php else: ?>
            <p class="text-gray-500">Your cart is empty.</p>
        <?php endif; ?>
    </aside>

    <!-- Checkout Form -->
    <form id="checkout-form" action="place_order.php" method="POST" onsubmit="return validateForm()" class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-xl space-y-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">Delivery & Payment</h1>
            <span class="text-sm text-gray-500" id="progress-text">Step 1 of 2</span>
        </div>
        <div class="w-full bg-gray-200 h-2 rounded-full">
            <div id="progress-bar" class="bg-[#672209] h-2 rounded-full transition-all duration-300" style="width: 50%;"></div>
        </div>

        <!-- Step 1: Address -->
        <div class="step active" id="step-1">
            <h2 class="text-lg font-semibold mb-2">Choose Address</h2>
            <div class="space-y-4">
                <label><input type="radio" name="address_type" value="new" checked class="mr-2">New Address</label>
                <label><input type="radio" name="address_type" value="saved" class="mr-2">Saved Address</label>

                <div id="new-address-fields" class="space-y-2">
                    <input type="text" name="street" placeholder="Street" class="input">
                    <input type="text" name="postal_code" placeholder="Postal Code" class="input">
                    <input type="text" name="country" placeholder="Country" class="input">
                </div>

                <div id="saved-address-fields" class="hidden">
                    <?php if (!empty($addresses)): ?>
                        <select name="saved_address_id" class="input">
                            <option value="">Choose Address</option>
                            <?php foreach ($addresses as $addr): ?>
                                <option value="<?= $addr['id'] ?>">
                                    <?= htmlspecialchars($addr['street']) ?>, <?= $addr['postal_code'] ?>, <?= $addr['country'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <p class="text-gray-500">No saved addresses found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Step 2: Payment -->
        <div class="step" id="step-2">
            <h2 class="text-lg font-semibold mb-2">💳 Payment Method</h2>
            <select name="payment_method" id="payment-method" class="input">
                <option value="" disabled selected>Choose payment method</option>
                <option value="cash">Cash</option>
                <option value="card">Card</option>
                <option value="paypal">PayPal</option>
            </select>

            <div id="card-fields" class="hidden space-y-2">
                <input type="text" name="card_number" placeholder="Card Number" class="input" maxlength="19">
                <div class="flex space-x-2">
                    <input type="text" name="card_expiry" placeholder="MM/YY" class="input" maxlength="5">
                    <input type="text" name="card_cvc" placeholder="CVC" class="input" maxlength="4">
                </div>
                <input type="text" name="card_holder" placeholder="Cardholder Name" class="input">
            </div>

            <div id="paypal-fields" class="hidden space-y-2">
                <input type="email" name="paypal_email" placeholder="PayPal Email" class="input">
                <input type="text" name="paypal_owner" placeholder="Account Holder Name" class="input">
            </div>

            <button type="submit" class="w-full bg-green-600 text-white py-2 rounded-lg hover:bg-green-700 mt-4">Confirm & Pay ✅</button>
        </div>

        <div class="flex justify-between">
            <button type="button" onclick="prevStep()" class="bg-gray-400 text-white px-4 py-2 rounded-lg">Back</button>
            <button type="button" onclick="nextStep()" id="next-btn" class="bg-blue-600 text-white px-4 py-2 rounded-lg">Next</button>
        </div>
    </form>
</div>

<?php include '../footer.php'; ?>
<script src="checkout.js"></script>
<style>
    .input {
        @apply w-full p-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400;
    }
</style>
</body>
</html>