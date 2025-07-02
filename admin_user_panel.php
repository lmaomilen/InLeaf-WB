<?php
session_start();
require_once('../db.php');


if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: ../login.php");
    exit;
}

if (isset($_POST['delete_user_id'])) {
    $id_to_delete = intval($_POST['delete_user_id']);

    if ($_SESSION['user_id'] != $id_to_delete) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id_to_delete);
        $stmt->execute();
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user_id'])) {
    $delete_id = intval($_POST['delete_user_id']);
    if ($delete_id !== $_SESSION['user_id']) { 
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();
    }
}


$result = $conn->query("
    SELECT 
        u.id, u.username, u.email, u.avatar, u.is_admin, u.created_at,
        GROUP_CONCAT(
            CONCAT_WS(', ', a.street, a.city, a.postal_code, a.country)
            SEPARATOR ' | '
        ) AS full_address,
        p.payment_method
    FROM users u
    LEFT JOIN addresses a ON u.id = a.user_id
    LEFT JOIN payments p ON u.id = p.user_id
    GROUP BY u.id
    ORDER BY u.id
");




?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>User Management</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>
</head>

<body class="bg-[#1A1A23] min-h-screen pt-[80px]">
    <?php include '../header.php'; ?>

    <div class="max-w-7xl mx-auto px-4 py-10">
    
    <!-- Back Button -->
    <div class="mb-4">
        <a href="admin.php" class="inline-flex items-center text-gray-600 hover:text-black text-sm font-medium transition">
        <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
        </a>
    </div>

    <!-- Title -->
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-white flex items-center"> User Management
        </h1>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto bg-white shadow-lg rounded-2xl">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-100 text-gray-700 text-left">
            <tr>
            <th class="px-4 py-3">#</th>
            <th class="px-4 py-3"> Username</th>
            <th class="px-4 py-3"> Email</th>
            <th class="px-4 py-3"> Address</th>
            <th class="px-4 py-3"> Payment</th>
            <th class="px-4 py-3 text-center">Avatar</th>
            <th class="px-4 py-3 text-center"> Admin</th>
            <th class="px-4 py-3">Created</th>
            <th class="px-4 py-3 text-center">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 text-gray-800">
            <?php while ($user = $result->fetch_assoc()): ?>
            <tr class="even:bg-gray-50">
            <td class="px-4 py-3 font-medium"><?= $user['id'] ?></td>
            <td class="px-4 py-3"><?= htmlspecialchars($user['username']) ?></td>
            <td class="px-4 py-3"><?= htmlspecialchars($user['email']) ?></td>
            <td class="px-4 py-3 whitespace-pre-wrap">
                <?= $user['full_address'] 
                ? nl2br(htmlspecialchars(str_replace(' | ', "\n", $user['full_address']))) 
                : '<span class="text-gray-400 italic">No address</span>' ?>
            </td>
            <td class="px-4 py-3">
                <?= $user['payment_method'] 
                ? htmlspecialchars($user['payment_method']) 
                : '<span class="text-gray-400 italic">None</span>' ?>
            </td>
            <td class="px-4 py-3 text-center">
                <?php if ($user['avatar']): ?>
                <img src="/progetto/uploads/<?= htmlspecialchars($user['avatar']) ?>" alt="avatar" class="w-9 h-9 rounded-full mx-auto border shadow">
                <?php else: ?>
                <span class="text-gray-400">—</span>
                <?php endif; ?>
            </td>
            <td class="px-4 py-3 text-center">
                <?= $user['is_admin'] ? '<span class="text-green-600 font-semibold">Yes</span>' : 'No' ?>
            </td>
            <td class="px-4 py-3"><?= htmlspecialchars($user['created_at']) ?></td>
            <td class="px-4 py-3 text-center">
                <?php if ($_SESSION['user_id'] != $user['id']): ?>
                <form method="POST" onsubmit="return confirm('Are you sure you want to delete user <?= htmlspecialchars($user['username']) ?>?')">
                <input type="hidden" name="delete_user_id" value="<?= $user['id'] ?>">
                <button type="submit" class="text-red-600 hover:text-red-800 font-semibold text-sm">
                    <i class="fas fa-trash-alt mr-1"></i> Delete
                </button>
                </form>
                <?php else: ?>
                <span class="text-gray-400 text-xs italic">You</span>
                <?php endif; ?>
            </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
        </table>
    </div>
    </div>

    <?php include '../footer.php'; ?>
    <script src="admin.js"></script>
</body>
</html>
