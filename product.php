<?php
session_start();
require_once('db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_review_id'])) {
    $review_id = intval($_POST['delete_review_id']);
    $user_id = $_SESSION['user_id'] ?? null;
    $is_admin = $_SESSION['admin'] ?? false;

    if ($user_id) {
        if ($is_admin) {

            $stmt = $conn->prepare("DELETE FROM reviews WHERE id = ?");
            $stmt->bind_param("i", $review_id);
            $stmt->execute();
        } else {

            $stmt = $conn->prepare("DELETE FROM reviews WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $review_id, $user_id);
            $stmt->execute();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: /progetto/login.php");
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $product_id = intval($_POST['product_id']);
    $rating = intval($_POST['rating']);
    $comment = trim($_POST['comment']);

    if ($rating >= 1 && $rating <= 5 && !empty($comment)) {
        $stmt = $conn->prepare("INSERT INTO reviews (user_id, product_id, rating, comment) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $user_id, $product_id, $rating, $comment);
        $stmt->execute();
    }

}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order_product_id'])) {
    $product_id = intval($_POST['place_order_product_id']);
    $quantity = isset($_POST['quantity']) ? max(1, intval($_POST['quantity'])) : 1;

    if (!isset($_SESSION['user_id'])) {
        header("Location: /progetto/login.php");
        exit;
    }

    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("iii", $quantity, $user_id, $product_id);
        $stmt->execute();
    } else {
        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $user_id, $product_id, $quantity);
        $stmt->execute();
    }

    header("Location: /progetto/cart/checkout.php");
    exit;
}


$is_logged_in = isset($_SESSION['user_id']);
$product_id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_POST['product_id']) ? intval($_POST['product_id']) : 0);
$sql = "SELECT * FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    die("Product not found!");
}

$wishlist_ids = [];

if ($is_logged_in) {
    $stmt = $conn->prepare("SELECT product_id FROM wishlist WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $wishlist_result = $stmt->get_result();
    while ($row = $wishlist_result->fetch_assoc()) {
        $wishlist_ids[] = $row['product_id'];
    }
}

$isInWishlist = in_array($product_id, $wishlist_ids);
$heartClass = $isInWishlist ? 'fas text-red-500' : 'far text-gray-400';


$name = htmlspecialchars($product['name']);
$price = htmlspecialchars($product['price']);
$description = nl2br(htmlspecialchars($product['description']));
$id = $product['id'];

if (!isset($_SESSION['recently_viewed'])) {
    $_SESSION['recently_viewed'] = [];
}
if (!in_array($product_id, $_SESSION['recently_viewed'])) {
    $_SESSION['recently_viewed'][] = $product_id;
    if (count($_SESSION['recently_viewed']) > 4) {
        array_shift($_SESSION['recently_viewed']);
    }
}


$images = json_decode($product['image'], true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $name ?> - SneakUP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body class="bg-[#111827] pt-[80px]">
    <div class="p-4">
    <button onclick="history.back()" class="inline-flex items-center text-gray-700 hover:text-black text-sm font-medium transition duration-300">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
        </svg>
        Back
    </button>
    </div>
    <?php include 'header.php'; ?>

    <div class="container mx-auto px-4 py-3">
        <div class="bg-white shadow-lg rounded-lg p-6 grid grid-cols-1 md:grid-cols-2 gap-8">

            <div>
                <div class="swiper mySwiper">
                    <div class="swiper-wrapper">
                    <?php if (!empty($images)): ?>
                        <?php foreach ($images as $image): ?>
                            <div class='swiper-slide'><img src='/progetto/admin/uploads/<?php echo htmlspecialchars($image); ?>' alt='Product Image' class='w-full max-w-md mx-auto'></div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center text-gray-500">There are no images for this product.</div>
                    <?php endif; ?>
                    </div>
                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
                    <div class="swiper-pagination"></div>
                </div>
            </div>

            <div>
                <h1 class="text-3xl font-bold mb-2"><?= $name ?></h1>
                <p class="mb-6"><?= $description ?></p>
                <p class="text-[#672209] text-xl font-semibold mb-4">$<?= $price ?></p>
                <div class="mb-6">
                    <h3 class="text-lg font-semibold mb-2">Plant Sizes</h3>
                    <table class="w-full text-center text-sm border border-gray-300 rounded overflow-hidden">
                        <thead class="bg-gray-100">
                        <tr>
                            <th class="px-2 py-1 border border-gray-300">XS</th>
                            <th class="px-2 py-1 border border-gray-300">S</th>
                            <th class="px-2 py-1 border border-gray-300">M</th>
                            <th class="px-2 py-1 border border-gray-300">L</th>
                            <th class="px-2 py-1 border border-gray-300">Xl</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td class="px-2 py-2 border border-gray-300">✓</td>
                            <td class="px-2 py-2 border border-gray-300">✓</td>
                            <td class="px-2 py-2 border border-gray-300">✓</td>
                            <td class="px-2 py-2 border border-gray-300">✓</td>
                            <td class="px-2 py-2 border border-gray-300">✓</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                

                <div class="flex flex-wrap items-center gap-4 mb-4">
                    <form method="POST" class="flex items-center gap-3">
                        <input type="hidden" name="place_order_product_id" value="<?= $product['id'] ?>">
                        <button type="button" class="add-to-wishlist" data-id="<?= $product_id ?>">
                            <i class="<?= $heartClass ?> fa-heart text-2xl transition duration-200"></i>
                        </button>

                        <button type="button" class="bg-[#672209] hover:bg-blue-600 text-white px-5 py-2 rounded add-to-cart transition" data-id="<?= $product['id']; ?>">
                            Add to Cart
                        </button>

                        <div class="flex items-center border rounded overflow-hidden">
                            <button type="button" class="qty-decrease px-3 py-2 bg-gray-200 hover:bg-gray-300">−</button>
                            <input type="number" name="quantity" value="1" min="1" class="w-12 text-center border-l border-r outline-none" id="qtyInput">
                            <button type="button" class="qty-increase px-3 py-2 bg-gray-200 hover:bg-gray-300">+</button>
                        </div>

                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded transition">
                            Checkout
                        </button>
                    </form>


                </div>


            </div>
        </div>


        <div class="bg-white shadow-md rounded-lg p-6 mt-10">
            <?php if ($is_logged_in): ?>
            <h2 class="text-2xl font-semibold mb-4">Leave a Review</h2>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="submit_review" value="1">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <label class="block">
                Rating:
                <div class="flex items-center mt-2 space-x-1">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                    <i class="fa-regular fa-star text-2xl text-black cursor-pointer" data-value="<?= $i ?>"></i>
                    <?php endfor; ?>
                </div>
                <input type="hidden" name="rating" id="ratingInput" value="0">
                </label>
                <label class="block">
                Comment:
                <textarea name="comment" rows="4" required class="block w-full mt-1 border rounded p-2"></textarea>
                </label>
                <button type="submit" class="bg-[#672209] text-white px-4 py-2 rounded">Submit Review</button>
            </form>
            <?php else: ?>
            <p class="mt-6 text-gray-700">Please <a href="/progetto/login.php" class="text-blue-600 underline">log in</a> to leave a review.</p>
            <?php endif; ?>

            <div class="mt-8">
            <h2 class="text-2xl font-semibold mb-4">Reviews</h2>
            <?php
            $stmt = $conn->prepare("
                SELECT r.id, r.rating, r.comment, r.created_at, r.user_id, u.username
                FROM reviews r
                JOIN users u ON r.user_id = u.id
                WHERE r.product_id = ?
                ORDER BY r.created_at DESC
            ");

            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $reviews = $stmt->get_result();
            $conn->close();
            if ($reviews->num_rows > 0): ?>
                <?php while ($review = $reviews->fetch_assoc()): ?>
                    <div class="bg-gray-100 p-4 rounded mb-3">
                        <p class="text-yellow-600 font-bold">
                            <?= str_repeat("★", $review['rating']) . str_repeat("☆", 5 - $review['rating']) ?>
                        </p>
                        <p class="italic"><?= htmlspecialchars($review['comment']) ?></p>
                        <p class="text-sm text-gray-600 mt-1">
                            — <?= htmlspecialchars($review['username']) ?> on <?= date("F j, Y", strtotime($review['created_at'])) ?>
                        </p>

                        <?php if (
                            (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $review['user_id']) ||
                            (isset($_SESSION['admin']) && $_SESSION['admin'] === true)
                        ): ?>
                            <form method="POST" class="mt-2">
                                <input type="hidden" name="delete_review_id" value="<?= $review['id'] ?>">
                                <button type="submit" class="text-red-500 text-sm hover:underline">Delete</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-gray-600">No reviews yet. Be the first to write one!</p>
            <?php endif; ?>
            </div>
        </div>
        </div>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.js"></script>
    <script src="script.js"></script>
    <div id="toast" class="fixed bottom-6 right-6 bg-black text-white px-4 py-2 rounded-lg shadow-md hidden z-50 transition-opacity duration-300"></div>
</body>
</html>
