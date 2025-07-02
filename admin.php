<?php
session_start();
require_once('../db.php');

if (!isset($_SESSION["admin"])) {
    header("Location: ../login.php");
    exit;
}

$order_result = $conn->query("
    SELECT 
        o.id, 
        o.user_id, 
        o.address_id, 
        oi.product_id,
        oi.quantity, 
        o.total_price, 
        o.status, 
        o.created_at,
        pay.payment_method,
        u.username,
        p.name AS product_name,
        p.image AS product_image
    FROM orders o
    JOIN order_items oi ON oi.order_id = o.id
    LEFT JOIN products p ON oi.product_id = p.id
    LEFT JOIN users u ON o.user_id = u.id
    LEFT JOIN payments pay ON o.id = pay.order_id
    GROUP BY o.id
    ORDER BY o.created_at DESC
    LIMIT 5
");



$total_orders = $conn->query("SELECT COUNT(*) as total FROM orders")->fetch_assoc()['total'];
$total_revenue = $conn->query("SELECT SUM(total_price) as total FROM orders")->fetch_assoc()['total'];
$total_users = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];
$total_admins = $conn->query("SELECT COUNT(*) as total FROM users WHERE is_admin = 1")->fetch_assoc()['total'];
$total_products = $conn->query("SELECT COUNT(*) as total FROM products")->fetch_assoc()['total'];
$max_order = $conn->query("SELECT MAX(total_price) as max_total FROM orders")->fetch_assoc()['max_total'];


$category_query = "SELECT DISTINCT category FROM products";
$category_result = $conn->query($category_query);
$categories = [];

if ($category_result) {
    while ($row = $category_result->fetch_assoc()) {
        $categories[] = $row['category'];
    }
}



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

    <style>
    .scrollbar-thin {
        scrollbar-width: thin;
    }
    .scrollbar-thumb-gray-400 {
        scrollbar-color: #9ca3af #f3f4f6;
    }
    </style>

</head>

<?php include '../header.php'; ?>

<body class="bg-[#1A1A23] pt-24 font-sans">
  <div class="max-w-7xl mx-auto px-6">
    <!-- Back Button -->
    <div class="mb-4">
      <button onclick="history.back()" class="inline-flex items-center text-gray-700 hover:text-black text-sm font-medium transition">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
        </svg>
        Back
      </button>
    </div>

    <!-- Dashboard Title -->
    <h1 class="text-4xl font-bold text-center text-white mb-10">Dashboard</h1>

    <!-- Stats Panel -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
      <a href="admin_orders.php" class="bg-white shadow-lg rounded-xl p-6 text-center hover:bg-gray-100 transition">
        <h3 class="text-sm font-semibold text-gray-500">Orders</h3>
        <p class="text-3xl font-bold text-gray-900"><?= $total_orders ?></p>
      </a>
      <div class="bg-white shadow-lg rounded-xl p-6 text-center">
        <h3 class="text-sm font-semibold text-gray-500">Income</h3>
        <p class="text-3xl font-bold text-green-600"><?= number_format($total_revenue, 2, ',', ' ') ?> $</p>
      </div>
      <a href="admin_user_panel.php" class="bg-white shadow-lg rounded-xl p-6 text-center hover:bg-gray-100 transition">
        <h3 class="text-sm font-semibold text-gray-500">Users</h3>
        <p class="text-3xl font-bold text-gray-900"><?= $total_users ?></p>
      </a>
      <a href="#item" class="bg-white shadow-lg rounded-xl p-6 text-center hover:bg-gray-100 transition">
        <h3 class="text-sm font-semibold text-gray-500">Products</h3>
        <p class="text-3xl font-bold text-indigo-600"><?= $total_products ?></p>
      </a>
    </div>

    <!-- Form and Product List -->
    <div class="grid md:grid-cols-2 gap-10">
      <!-- Add Product Form -->
      <div class="bg-white shadow-xl rounded-2xl p-8">
        <h2 class="text-2xl font-semibold mb-6 text-gray-800">Add New Product</h2>
        <form id="addProductForm" enctype="multipart/form-data" method="POST" class="space-y-4">
          <input type="text" name="name" placeholder="Product Name" class="w-full border border-gray-300 p-3 rounded-lg" required>
          <textarea name="description" placeholder="Description" class="w-full border border-gray-300 p-3 rounded-lg resize-none" required></textarea>
          <input type="number" name="price" placeholder="Price" class="w-full border border-gray-300 p-3 rounded-lg" required>

          <select name="category" class="w-full border border-gray-300 p-3 rounded-lg" required>
            <option value="">Select Category</option>
            <?php foreach ($categories as $category): ?>
              <option value="<?php echo htmlspecialchars($category); ?>"><?php echo htmlspecialchars($category); ?></option>
            <?php endforeach; ?>
          </select>

          <div id="dropzone" class="border-2 border-dashed border-gray-400 p-6 rounded-lg text-center cursor-pointer">
            <p class="text-gray-600">Drag and drop images here or click to select</p>
            <input type="file" id="imageInput" name="image[]" multiple accept="image/*" class="hidden" />
          </div>

          <div id="imagePreview" class="flex flex-wrap gap-2"></div>
          <div id="uploadProgress" class="text-sm text-gray-500"></div>

          <button type="submit" class="w-full bg-green-600 text-white py-3 rounded-lg font-semibold hover:bg-green-700 transition">Add Product</button>
        </form>
      </div>

      <!-- Product List -->
      <div id="item">
        <h2 class="text-2xl font-semibold mb-6 text-white">Product List</h2>
        <div class="grid grid-cols-2 gap-4" id="productGrid">
          <?php
          $products = $conn->query("SELECT id, name, image FROM products ORDER BY id DESC");
          while ($p = $products->fetch_assoc()):
              $imgs = json_decode($p['image'], true);
              $thumb = !empty($imgs) ? 'uploads/' . htmlspecialchars($imgs[0]) : 'default.jpg';
          ?>
          <div id="product-<?= $p['id'] ?>" class="relative group bg-white rounded-xl shadow hover:shadow-md transition">
            <a href="admin_item.php?id=<?= $p['id'] ?>">
              <img src="<?= $thumb ?>" class="w-full h-40 object-cover rounded-t-xl" alt="<?= htmlspecialchars($p['name']) ?>">
              <div class="p-4">
                <h3 class="text-md font-semibold text-gray-800 truncate"><?= htmlspecialchars($p['name']) ?></h3>
              </div>
            </a>
            <button onclick="removeProduct(<?= $p['id'] ?>)" class="absolute top-2 right-2 bg-red-600 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition">
              Delete
            </button>
          </div>
          <?php endwhile; ?>
        </div>
      </div>
    </div>
  </div>



    <div id="toast" class="hidden fixed bottom-6 right-6 px-5 py-3 rounded-lg shadow-lg text-white z-50 opacity-0 transition-opacity duration-300"></div>

    <?php include '../footer.php'; ?>
    <script src="admin.js?v=123"></script>

</body>
</html> 
