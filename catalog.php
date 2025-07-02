<?php
session_start();
require_once('db.php');

$category_query = "SELECT DISTINCT category FROM products ORDER BY category ASC";
$category_result = $conn->query($category_query);
$categories = [];

if ($category_result) {
    while ($row = $category_result->fetch_assoc()) {
        $categories[] = $row['category'];
    }
}

$category_filter = $_GET['category'] ?? '';

$sql = "SELECT * FROM products";
$params = [];

if (!empty($category_filter)) {
    $sql .= " WHERE category = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $category_filter);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql .= " ORDER BY id DESC LIMIT 12";
    $result = $conn->query($sql);
}

$wishlist_ids = [];
if (isset($_SESSION["user_id"])) {
    $user_id = $_SESSION["user_id"];
    $wish_sql = "SELECT product_id FROM wishlist WHERE user_id = ?";
    $stmt = $conn->prepare($wish_sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $wish_result = $stmt->get_result();
    while ($wish = $wish_result->fetch_assoc()) {
        $wishlist_ids[] = $wish["product_id"];
    }
} elseif (isset($_SESSION["wishlist"])) {
    $wishlist_ids = array_keys($_SESSION["wishlist"]);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catalog</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body class="bg-[#111827] text-white pt-[80px]">
    <?php include 'header.php'; ?>

    <div class="max-w-7xl mx-auto px-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
            <h2 class="text-3xl font-bold text-white mb-4 md:mb-0">🛍️ Catalog</h2>
            <form method="GET" action="catalog.php" class="flex items-center gap-2">
                <select name="category" class="border border-gray-300 rounded-md px-4 py-2 bg-[#672209] text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat) ?>" <?= ($cat === $category_filter) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php while ($row = $result->fetch_assoc()): ?>
                <?php
                    $images = json_decode($row['image'], true);
                    $imageSrc = !empty($images) ? 'admin/uploads/' . htmlspecialchars($images[0]) : 'default.jpg';
                    $productId = $row["id"];
                    $isInWishlist = in_array($productId, $wishlist_ids);
                    $heartClass = $isInWishlist ? 'fas text-red-500' : 'far text-gray-400';
                ?>
                <div class="bg-white rounded-xl shadow hover:shadow-lg transition p-4 flex flex-col justify-between">
                    <a href="product.php?id=<?= $productId ?>" class="block mb-4">
                        <img src="<?= $imageSrc ?>" class="w-full h-64 object-cover rounded-md mb-3" alt="<?= htmlspecialchars($row["name"]) ?>">
                        <h3 class="text-lg font-semibold text-[#672209] line-clamp-2"><?= htmlspecialchars($row["name"]) ?></h3>
                        <p class="text-sm text-gray-500">Category: <?= htmlspecialchars($row["category"]) ?></p>
                        <p class="text-[#672209] font-semibold mt-1">$<?= number_format($row["price"], 2) ?></p>
                    </a>
                    <div class="flex justify-between items-center">
                        <button class="add-to-wishlist" data-id="<?= $productId ?>">
                            <i class="<?= $heartClass ?> fa-heart text-xl"></i>
                        </button>
                        <button class="add-to-cart bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm transition" data-id="<?= $productId ?>">
                            Add to cart
                        </button>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

    </div>

    <?php include 'footer.php'; ?>

    <script src="script.js"></script>
    <div id="toast" class="fixed bottom-6 right-6 bg-black text-white px-4 py-2 rounded-lg shadow-md hidden z-50 transition-opacity duration-300"></div>
</body>
</html>