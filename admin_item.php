<?php
session_start();
require_once('../db.php');

if (!isset($_SESSION["admin"])) {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['id'])) {
    die("Product ID missing");
}

$id = (int)$_GET['id'];
$product_query = $conn->prepare("SELECT * FROM products WHERE id = ?");
$product_query->bind_param("i", $id);
$product_query->execute();
$product_result = $product_query->get_result();

if ($product_result->num_rows === 0) {
    die("Product not found");
}

$product = $product_result->fetch_assoc();
$images = json_decode($product['image'], true) ?? [];

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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Edit Product</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>
</head>

<body class="bg-[#111827] min-h-screen pt-[80px]">
  <div class="max-w-5xl mx-auto px-4 py-10">
    
    <!-- Back link -->
    <div class="mb-6">
      <a href="admin.php" class="inline-flex items-center text-gray-600 hover:text-black text-sm font-medium transition">
        <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
      </a>
    </div>

    <!-- Card -->
    <div class="bg-white rounded-2xl shadow-xl p-8 space-y-6">
      <div class="flex items-center gap-4 mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Edit Product #<?= $product['id'] ?></h1>
      </div>

      <form id="editProductForm" action="admin_handler.php?action=edit_product" method="POST" enctype="multipart/form-data" class="space-y-6">
        <input type="hidden" name="id" value="<?= $product['id'] ?>"/>

        <!-- Product Name -->
        <div>
          <label class="block font-semibold text-gray-700 mb-1">Product Name</label>
          <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" class="w-full border p-3 rounded-lg" required>
        </div>

        <!-- Description -->
        <div>
          <label class="block font-semibold text-gray-700 mb-1">Description</label>
          <textarea name="description" rows="4" class="w-full border p-3 rounded-lg resize-none" required><?= htmlspecialchars($product['description']) ?></textarea>
        </div>

        <!-- Price -->
        <div>
          <label class="block font-semibold text-gray-700 mb-1">Price ($)</label>
          <input type="number" step="0.01" name="price" value="<?= $product['price'] ?>" class="w-full border p-3 rounded-lg" required>
        </div>

        <!-- Category -->
        <div>
          <label class="block font-semibold text-gray-700 mb-1">Category</label>
          <select name="category" class="w-full border p-3 rounded-lg" required>
            <option value="">Choose category</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= htmlspecialchars($cat) ?>" <?= $cat == $product['category'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Current Images -->
        <div>
          <label class="block font-semibold text-gray-700 mb-2">Current Images</label>
          <div class="flex flex-wrap gap-4">
            <?php foreach ($images as $img): ?>
              <div class="relative group" data-image-wrapper="<?= htmlspecialchars($img) ?>">
                <img src="uploads/<?= htmlspecialchars($img) ?>" class="w-24 h-24 object-cover rounded-lg border" alt="Image">
                <button type="button"
                        class="absolute top-1 right-1 bg-red-600 text-white w-6 h-6 rounded-full text-xs hidden group-hover:flex items-center justify-center delete-image-btn"
                        data-product-id="<?= $product['id'] ?>"
                        data-image="<?= htmlspecialchars($img) ?>">
                        &times;
                </button>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Upload new images -->
        <div>
          <label class="block font-semibold text-gray-700 mb-1">Upload New Images</label>
          <input type="file" name="image[]" multiple class="w-full border p-3 rounded-lg">
        </div>

        <!-- Save button -->
        <div class="text-right">
          <button type="submit" class="bg-[#672209] hover:bg-yellow-700 text-white font-semibold px-6 py-3 rounded-lg transition-all">
            <i class="fas fa-save mr-2"></i> Save Changes
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Toast -->
  <div id="toast" class="fixed bottom-6 right-6 px-5 py-3 rounded-lg shadow-lg text-white hidden opacity-0 z-50 transition-opacity duration-300"></div>

  <script src="admin.js"></script>
</body>
</html>

