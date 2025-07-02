<?php
session_start();
require_once('../db.php');

if (!isset($_SESSION["admin"]) || $_SESSION["admin"] !== true) {
    die("Access denied!");
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["order_id"], $_POST["status"])) {
    $order_id = intval($_POST["order_id"]);
    $status = $_POST["status"];
    $conn->query("UPDATE orders SET status='$status' WHERE id='$order_id'");
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete_order_id"])) {
    $order_id = intval($_POST["delete_order_id"]);
    $conn->query("DELETE FROM orders WHERE id='$order_id'");
}


$user_filter = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
$date_filter = isset($_GET['date']) ? $_GET['date'] : null;


$query = "
    SELECT 
        orders.id, 
        users.username, 
        orders.user_id, 
        products.name AS product_name, 
        order_items.quantity, 
        orders.total_price, 
        orders.status, 
        orders.created_at,
        payments.payment_method,
        products.image AS product_image,
        products.id AS product_id
    FROM orders 
    JOIN order_items ON orders.id = order_items.order_id
    JOIN products ON order_items.product_id = products.id 
    JOIN users ON orders.user_id = users.id
    LEFT JOIN payments ON payments.order_id = orders.id
    WHERE 1=1
";
$user_search = $_GET['user_search'] ?? null;

if ($user_search !== null && $user_search !== '') {
    $escaped_search = $conn->real_escape_string($user_search);
    if (is_numeric($escaped_search)) {
        $query .= " AND users.id = '$escaped_search'";
    } else {
        $query .= " AND users.username LIKE '%$escaped_search%'";
    }
}


if ($user_filter) {
    $query .= " AND orders.user_id = '$user_filter'";
}
if ($date_filter) {
    $query .= " AND DATE(orders.created_at) = '$date_filter'";
}

$query .= " ORDER BY orders.created_at DESC";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Admin Orders</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
</head>

<body class="bg-[#1A1A23] pt-[80px] min-h-screen">
<?php include '../header.php'; ?>

<div class="max-w-7xl mx-auto px-4 py-10">

  <!-- Back -->
  <div class="mb-4">
    <a href="admin.php" class="inline-flex items-center text-gray-600 hover:text-black text-sm font-medium transition">
      <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
    </a>
  </div>

  <!-- Title -->
  <div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold text-white flex items-center"> Orders Overview
    </h1>
  </div>

  <!-- Filters -->
  <form method="get" class="mb-6 flex flex-wrap gap-4 items-center">
    <input type="text" name="user_search" placeholder="Username or ID" value="<?= htmlspecialchars($_GET['user_search'] ?? '') ?>" class="p-2 border border-gray-300 rounded-md w-60 shadow-sm" />
    <input type="text" id="datePicker" name="date" placeholder=" Date" value="<?= htmlspecialchars($date_filter) ?>" class="p-2 border border-gray-300 rounded-md w-44 shadow-sm" />
    <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded-lg hover:bg-indigo-700 transition">Search</button>
  </form>

  <!-- Table -->
  <div class="bg-white rounded-xl shadow-lg overflow-x-auto">
    <table class="min-w-full table-auto text-sm text-gray-800">
      <thead class="bg-gray-100 text-left">
        <tr>
          <th class="px-4 py-3">#</th>
          <th class="px-4 py-3">User</th>
          <th class="px-4 py-3"> Product</th>
          <th class="px-4 py-3"> Qty</th>
          <th class="px-4 py-3"> Total</th>
          <th class="px-4 py-3"> Payment</th>
          <th class="px-4 py-3"> Date</th>
          <th class="px-4 py-3"> Status</th>
          <th class="px-4 py-3 text-center">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($order = $result->fetch_assoc()): ?>
        <tr class="even:bg-gray-50 border-b">
          <td class="px-4 py-3 font-semibold"><?= $order['id'] ?></td>
          <td class="px-4 py-3"><?= htmlspecialchars($order['username']) ?></td>
          <td class="px-4 py-3">
            <a href="/progetto/product.php?id=<?= $order['product_id'] ?>" class="flex items-center gap-2 hover:underline">
              <?php
                $imgs = json_decode($order['product_image'], true);
                $thumb = !empty($imgs) ? '/progetto/admin/uploads/' . htmlspecialchars($imgs[0]) : '/progetto/images/default.jpg';
              ?>
              <img src="<?= $thumb ?>" class="w-10 h-10 object-cover rounded-lg border" alt="">
              <span><?= htmlspecialchars($order['product_name']) ?></span>
            </a>
          </td>
          <td class="px-4 py-3"><?= $order['quantity'] ?></td>
          <td class="px-4 py-3"><?= number_format($order['total_price'], 2, ',', ' ') ?> €</td>
          <td class="px-4 py-3"><?= htmlspecialchars($order['payment_method'] ?? '—') ?></td>
          <td class="px-4 py-3"><?= date("Y-m-d H:i", strtotime($order['created_at'])) ?></td>
          <td class="px-4 py-3">
            <form method="post" class="flex items-center gap-2">
              <input type="hidden" name="order_id" value="<?= $order['id'] ?>" />
              <select name="status" class="p-1 rounded-md border border-gray-300 shadow-sm">
                <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="shipped" <?= $order['status'] == 'shipped' ? 'selected' : '' ?>>Shipped</option>
                <option value="delivered" <?= $order['status'] == 'delivered' ? 'selected' : '' ?>>Delivered</option>
              </select>
              <button type="submit" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">Update</button>
            </form>
          </td>
          <td class="px-4 py-3 text-center">
            <form method="post" onsubmit="return confirm('Are you sure you want to delete this order?')">
              <input type="hidden" name="delete_order_id" value="<?= $order['id'] ?>" />
              <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-semibold">
                <i class="fas fa-trash-alt"></i>
              </button>
            </form>
          </td>
        </tr>
        <?php endwhile; ?>
        <?php else: ?>
        <tr>
          <td colspan="9" class="px-4 py-6 text-center text-gray-400 italic">No orders found.</td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include '../footer.php'; ?>
<script src="admin.js"></script>
<script>
  flatpickr("#datePicker", {
    dateFormat: "Y-m-d",
    allowInput: true,
    maxDate: "today"
  });
</script>
</body>
</html>


<?php $conn->close(); ?>
