<?php
session_start();
require_once('db.php');

if (!$conn) {
    die("Ошибка подключения: " . mysqli_connect_error());
}

$check_column = $conn->query("SHOW COLUMNS FROM products LIKE 'category'");
if ($check_column->num_rows == 0) {
    $conn->query("ALTER TABLE products ADD COLUMN category VARCHAR(50) NOT NULL DEFAULT 'Unisex'");
}

if (!isset($_SESSION['recently_viewed'])) {
    $_SESSION['recently_viewed'] = [];
}

if (isset($_GET['view_product'])) {
    $product_id = intval($_GET['view_product']);
    if (!in_array($product_id, $_SESSION['recently_viewed'])) {
        $_SESSION['recently_viewed'][] = $product_id;
        if (count($_SESSION['recently_viewed']) > 3) {
            array_shift($_SESSION['recently_viewed']);
        }
    }
    header("Location: index.php");
    exit();
}

if (isset($_GET['clear_recent'])) {
    $_SESSION['recently_viewed'] = [];
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>inleaf</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .scrollbar-hide::-webkit-scrollbar {
        display: none;
        }
        .scrollbar-hide {
        -ms-overflow-style: none;
        scrollbar-width: none;
        }
    </style>
</head>

<?php include 'header.php'; ?>

<body class="bg-[#1A1A23] text-white">    
    
    <section class="relative w-full h-[120vh] overflow-hidden bg-[#0E2F0E]">
        
        <div class="absolute inset-0 bg-[url('uploads/screen_upper.jpeg')] bg-cover bg-no-repeat bg-center z-0"></div>
        <div class="absolute inset-0 bg-black/60 z-10"></div>
        <div class="relative z-20 flex flex-col items-center justify-center text-center h-full px-4 text-white">
            <h1 class="text-4xl md:text-6xl font-bold mb-4">inleaf</h1>
            <p class="text-lg md:text-xl mb-6 whitespace-nowrap">your indoor jungle, with room to breathe. Trusted quality. Delivered with care.</p>
            <a href="catalog.php" class="bg-white text-black font-semibold px-6 py-3 rounded hover:bg-gray-200 transition">SHOP</a>
        </div>
    </section>

    <div class="container mx-auto p-4">
        <!-- <div class="flex justify-between mb-4">
            <h2 class="text-2xl font-bold">Sneakers</h2>
            <select id="category-filter" class="border rounded px-4 py-2" onchange="filterByCategory(this)">
                <option value="">All Categories</option>
                <?php
                $category_query = "SELECT name FROM categories ORDER BY name ASC";
                $category_result = $conn->query($category_query);

                if (!$category_result) {
                    die("Ошибка SQL при получении категорий: " . $conn->error);
                }

                while ($category_row = $category_result->fetch_assoc()) {
                    $cat = htmlspecialchars($category_row['name']);
                    $selected = ($cat === $category_filter) ? 'selected' : '';
                    echo "<option value=\"$cat\" $selected>$cat</option>";
                }
                ?>
            </select>
        </div>-->

        <?php

        $sql = "SELECT * FROM products ORDER BY id DESC LIMIT 12";
        $result = $conn->query($sql);

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


        <section class="relative">
            <div class="overflow-x-auto whitespace-nowrap py-4 px-2 flex gap-4 scrollbar-hide" id="product-slider">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <?php
                        $images = json_decode($row['image'], true);
                        $imageSrc = !empty($images) ? 'admin/uploads/' . htmlspecialchars($images[0]) : 'default.jpg';
                        $productId = $row["id"];
                        $isInWishlist = in_array($productId, $wishlist_ids);
                        $heartClass = $isInWishlist ? 'fas text-red-500' : 'far text-gray-400';
                    ?>
                    <div class="snap-start w-72 flex-shrink-0 bg-white p-4 rounded-lg shadow-md">

                        <a href="product.php?id=<?= $productId ?>" class="block hover:shadow-xl transition duration-300">
                            <img src="<?= $imageSrc ?>" class="w-full h-64 object-cover rounded-t-lg" alt="Sneaker">
                            <div class="p-4">
                                <h2 class="text-lg font-bold my-2 text-[#672209] break-words line-clamp-2"><?= htmlspecialchars($row["name"]) ?></h2>
                            </div>
                        </a>
                        <div class="flex justify-between items-center px-4 pb-4">
                            <button class="add-to-wishlist" data-id="<?= $productId ?>">
                                <i class="<?= $heartClass ?> fa-heart text-xl"></i>
                            </button>
                            <button class="add-to-cart bg-[#672209] text-white px-3 py-2 rounded text-sm" data-id="<?= $productId ?>">Add to Cart</button>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </section>
    </div>
                
    <section class="relative w-full h-[90vh] overflow-hidden bg-black">
        <div class="absolute inset-0 bg-[url('uploads/screen_mid.jpeg')] bg-cover bg-no-repeat bg-center z-0"></div>
        <div class="absolute inset-0 bg-black/60 z-10"></div>
        <div class="relative z-20 flex flex-col justify-between h-full px-6 md:px-16 py-12 text-white">
            <div class="text-right self-end">
                <h1 class="text-4xl md:text-6xl font-bold mb-4">Nature belongs inside</h1>
                <p class="text-lg md:text-xl mb-6 max-w-xl">At inLeaf, we believe greenery is not just decoration — it’s a way of life. Each plant we offer is handpicked to fit modern interiors and everyday lifestyles.</p>
            </div>
            <div class="text-left self-start">
                <h1 class="text-4xl md:text-6xl font-bold mb-4">Rooted in Simplicity</h1>
                <p class="text-lg md:text-xl max-w-xl">At inLeaf, every plant is delivered with care — to bring lasting life into your space. Whether you're a seasoned plant lover or just getting started, we’re here to help you grow with confidence.</p>
            </div>
        </div>
    </section>

    <section class="relative w-full bg-[#1A1A23] py-12">
        <h2 class="text-2xl font-bold text-white px-6 mb-4">Our Plant Lovers</h2>
        <div class="relative">
            <div class="overflow-x-auto whitespace-nowrap flex gap-4 px-10 scrollbar-hide" id="user-slider">

                <div class="snap-start w-48 flex-shrink-0 bg-white p-4 rounded-lg shadow-md text-center">
                <img src="uploads/1.jpeg" class="w-32 h-32 object-cover rounded-full mx-auto mb-2" alt="User 1">
                <p class="text-gray-800 font-semibold">@sofi.mln</p>
                </div>

                <div class="snap-start w-48 flex-shrink-0 bg-white p-4 rounded-lg shadow-md text-center">
                <img src="uploads/2.jpeg" class="w-32 h-32 object-cover rounded-full mx-auto mb-2" alt="User 2">
                <p class="text-gray-800 font-semibold">@its.jakeee</p>
                </div>

                <div class="snap-start w-48 flex-shrink-0 bg-white p-4 rounded-lg shadow-md text-center">
                <img src="uploads/3.jpeg" class="w-32 h-32 object-cover rounded-full mx-auto mb-2" alt="User 3">
                <p class="text-gray-800 font-semibold">@nora.wanders</p>
                </div>

                <div class="snap-start w-48 flex-shrink-0 bg-white p-4 rounded-lg shadow-md text-center">
                <img src="uploads/4.jpeg" class="w-32 h-32 object-cover rounded-full mx-auto mb-2" alt="User 3">
                <p class="text-gray-800 font-semibold">@tom.made.it</p>
                </div>

                <div class="snap-start w-48 flex-shrink-0 bg-white p-4 rounded-lg shadow-md text-center">
                <img src="uploads/5.jpeg" class="w-32 h-32 object-cover rounded-full mx-auto mb-2" alt="User 3">
                <p class="text-gray-800 font-semibold">@aline.ray</p>
                </div>

                <div class="snap-start w-48 flex-shrink-0 bg-white p-4 rounded-lg shadow-md text-center">
                <img src="uploads/6.jpeg" class="w-32 h-32 object-cover rounded-full mx-auto mb-2" alt="User 3">
                <p class="text-gray-800 font-semibold">@kev.in.motion</p>
                </div>

                <div class="snap-start w-48 flex-shrink-0 bg-white p-4 rounded-lg shadow-md text-center">
                <img src="uploads/7.jpeg" class="w-32 h-32 object-cover rounded-full mx-auto mb-2" alt="User 3">
                <p class="text-gray-800 font-semibold">@daria.unknown</p>
                </div>

                <div class="snap-start w-48 flex-shrink-0 bg-white p-4 rounded-lg shadow-md text-center">
                <img src="uploads/8.jpeg" class="w-32 h-32 object-cover rounded-full mx-auto mb-2" alt="User 3">
                <p class="text-gray-800 font-semibold">@mika.loves.coffee</p>
                </div>

            </div>
        </div>
    </section>

    <section class="relative w-full h-[90vh] overflow-hidden bg-[#0E2F0E]">
        
        <div class="absolute inset-0 bg-[url('uploads/screen_lower.jpeg')] bg-cover bg-no-repeat bg-center z-0"></div>
        <div class="absolute inset-0 bg-black/60 z-10"></div>
        <div class="relative z-20 flex flex-col items-center justify-center text-center h-full px-4 text-white">
            <h1 class="text-4xl md:text-6xl font-bold mb-4">Wrap Life in Leaves</h1>
            <p class="text-lg md:text-xl mb-6 whitespace-nowrap">For birthdays, new homes, or quiet thank yous — our plants speak softly, grow slowly, and stay for seasons to come.</p>
            <a href="catalog.php" class="bg-white text-black font-semibold px-6 py-3 rounded hover:bg-gray-200 transition">SHOP</a>
        </div>
    </section>

    <?php include 'footer.php'; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.css">
    <script src="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.js"></script>
    <script src="script.js"></script>

    <div id="toast" class="fixed bottom-6 right-6 bg-black text-white px-4 py-2 rounded-lg shadow-md hidden z-50 transition-opacity duration-300"></div>

</body>
</html>
