<?php
session_start();
require_once('../db.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();
$addresses = $conn->query("SELECT * FROM addresses WHERE user_id = $user_id");
$order_query = "
    SELECT o.id AS order_id, o.total_price, o.status, o.created_at,
           oi.quantity, p.name AS product_name, p.image AS product_image, p.id AS product_id
    FROM orders o
    JOIN order_items oi ON oi.order_id = o.id
    JOIN products p ON p.id = oi.product_id
    WHERE o.user_id = $user_id
    ORDER BY o.created_at DESC
    LIMIT 5
";
$order_result = $conn->query($order_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Profile | SneakUP</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-900 text-white min-h-screen pt-[80px]">
<?php include '../header.php'; ?>

<div class="max-w-7xl mx-auto px-6 py-8">
  <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-10">
    <h1 class="text-4xl font-bold">Welcome, <?= htmlspecialchars($user['username']) ?></h1>
    <a href="../logout.php" class="mt-4 md:mt-0 bg-red-600 hover:bg-red-700 px-5 py-2 rounded-lg text-white">Logout</a>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="col-span-1 bg-gray-800 p-6 rounded-xl shadow-md">
      <div class="flex flex-col items-center">
        <?php
        $avatarPath = (!empty($user['avatar'])) ? htmlspecialchars($user['avatar']) : "uploads/default.png";
        ?>
        <img id="userAvatar" src="/progetto/<?= $avatarPath ?>" alt="Avatar" class="w-28 h-28 rounded-full border-4 border-blue-500 mb-4">
        <h3 class="text-2xl font-semibold mb-1"><?= htmlspecialchars($user['username']) ?></h3>
        <p class="text-gray-400 text-sm mb-4"><?= htmlspecialchars($user['email']) ?></p>
        <form id="avatarForm" enctype="multipart/form-data" class="w-full space-y-3">
          <label for="avatarInput" class="cursor-pointer block w-full bg-gray-700 px-4 py-2 rounded-lg hover:bg-gray-600 text-center">Upload Photo
            <input type="file" name="avatar" id="avatarInput" class="hidden">
          </label>
          <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-lg">Update Avatar</button>
        </form>
        <button id="toggleUserUpdate" class="mt-4 w-full bg-green-600 hover:bg-green-700 px-4 py-2 rounded-lg">Edit Profile</button>
      </div>
      <div id="userUpdateWrapper" class="hidden mt-6">
        <form id="userUpdateForm" class="space-y-3">
          <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" class="w-full p-3 rounded bg-gray-700 text-white" placeholder="Username" required>
          <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="w-full p-3 rounded bg-gray-700 text-white" placeholder="Email" required>
          <input type="password" name="new_password" class="w-full p-3 rounded bg-gray-700 text-white" placeholder="New password (optional)">
          <button type="submit" class="w-full bg-green-600 hover:bg-green-700 px-4 py-2 rounded-lg">Update Info</button>
        </form>
      </div>
    </div>

    <div class="col-span-2 space-y-8">
      <div class="bg-gray-800 p-6 rounded-xl shadow-md">
        <h2 class="text-2xl font-bold mb-4">My Orders</h2>
        <?php if ($order_result->num_rows > 0): ?>
          <div class="overflow-x-auto">
            <table class="min-w-full table-auto">
              <thead class="bg-gray-700">
                <tr>
                  <th class="px-4 py-2 text-left">#</th>
                  <th class="px-4 py-2 text-left">Item</th>
                  <th class="px-4 py-2 text-center">Qty</th>
                  <th class="px-4 py-2 text-left">Sum</th>
                  <th class="px-4 py-2 text-left">Status</th>
                  <th class="px-4 py-2 text-left">Date</th>
                  <th class="px-4 py-2 text-left">Action</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-600">
                <?php while ($order = $order_result->fetch_assoc()): ?>
                  <tr>
                    <td class="px-4 py-2">#<?= $order['order_id'] ?></td>
                    <td class="px-4 py-2 flex items-center gap-3">
                      <?php
                      $imgs = json_decode($order['product_image'], true);
                      $thumb = !empty($imgs) ? '/progetto/admin/uploads/' . htmlspecialchars($imgs[0]) : 'default.jpg';
                      ?>
                      <img src="<?= $thumb ?>" alt="" class="w-10 h-10 object-cover rounded">
                      <a href="../product.php?id=<?= $order['product_id'] ?>" class="hover:underline"><?= htmlspecialchars($order['product_name']) ?></a>
                    </td>
                    <td class="px-4 py-2 text-center"><?= $order['quantity'] ?></td>
                    <td class="px-4 py-2"><?= number_format($order['total_price'], 2, ',', ' ') ?> €</td>
                    <td class="px-4 py-2 capitalize"><?= htmlspecialchars($order['status']) ?></td>
                    <td class="px-4 py-2"><?= date("d.m.Y", strtotime($order['created_at'])) ?></td>
                    <td class="px-4 py-2">
                      <form class="delete-order-form" method="POST">
                        <input type="hidden" name="delete_order_id" value="<?= $order['order_id'] ?>">
                        <button type="submit" class="text-red-400 hover:underline text-sm">Delete</button>
                      </form>
                    </td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <p class="text-gray-400">You have no orders yet.</p>
        <?php endif; ?>
      </div>

      <div class="bg-gray-800 p-6 rounded-xl shadow-md">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-2xl font-bold">My Addresses</h2>
          <button id="toggleAddressForm" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded">+</button>
        </div>

        <div id="addressList" class="space-y-4">
          <?php if ($addresses->num_rows > 0): ?>
            <?php while ($row = $addresses->fetch_assoc()): ?>
              <form class="address-item grid grid-cols-1 md:grid-cols-5 gap-2 bg-gray-700 p-4 rounded" data-id="<?= $row['id'] ?>">
                <input type="text" name="street" class="p-2 rounded bg-gray-800 text-white" value="<?= htmlspecialchars($row['street']) ?>" required>
                <input type="text" name="city" class="p-2 rounded bg-gray-800 text-white" value="<?= htmlspecialchars($row['city']) ?>" required>
                <input type="text" name="postal_code" class="p-2 rounded bg-gray-800 text-white" value="<?= htmlspecialchars($row['postal_code']) ?>" required>
                <input type="text" name="country" class="p-2 rounded bg-gray-800 text-white" value="<?= htmlspecialchars($row['country']) ?>" required>
                <div class="flex gap-2">
                  <button type="button" class="update-address bg-green-600 px-3 py-1 rounded text-white">Save</button>
                  <button type="button" class="delete-address bg-red-600 px-3 py-1 rounded text-white" data-id="<?= $row['id'] ?>">Delete</button>
                </div>
              </form>
            <?php endwhile; ?>
          <?php else: ?>
            <p class="text-gray-400">You have not added any addresses yet.</p>
          <?php endif; ?>
        </div>

        <form id="addAddressForm" class="mt-6 hidden space-y-2">
          <input type="text" name="street" placeholder="Street, building" required class="w-full p-2 rounded bg-gray-700 text-white">
          <input type="text" name="city" placeholder="City" required class="w-full p-2 rounded bg-gray-700 text-white">
          <input type="text" name="postal_code" placeholder="Postal code" required class="w-full p-2 rounded bg-gray-700 text-white">
          <input type="text" name="country" placeholder="Country" required class="w-full p-2 rounded bg-gray-700 text-white">
          <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-lg">Add address</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include '../footer.php'; ?>
<script src="profile.js"></script>
</body>
</html>
<?php $conn->close(); ?>