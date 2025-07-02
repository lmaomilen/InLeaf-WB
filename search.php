<?php
require_once('db.php');

$q = $_GET['q'] ?? '';
$q = $conn->real_escape_string($q);

$sql = "SELECT id, name, price, image FROM products WHERE name LIKE '%$q%' LIMIT 5";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $images = json_decode($row['image'], true);
        $mainImage = $images[0] ?? 'no-image.png';

        echo "
        <a href='/progetto/product.php?id={$row['id']}' class='flex items-center gap-3 p-2 text-zinc-400 hover:text-blue-900 hover:bg-yellow-100 transition-colors'>
            <img src='/progetto/admin/uploads/{$mainImage}' alt='{$row['name']}' class='w-12 h-12 object-cover rounded mr-3'>
            <div>
                <p class='font-medium'>" . htmlspecialchars($row["name"]) . "</p>
                <p class='text-sm text-green-600'>" . number_format($row["price"], 2, ',', ' ') . " €</p>
            </div>
        </a>";
    }
} else {
    echo "<p class='p-2 text-gray-500'>No results found</p>";
}
?>
