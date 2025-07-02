<?php
require_once('db.php');

$path_prefix = '';
if (strpos($_SERVER['PHP_SELF'], '/cart/') !== false || 
    strpos($_SERVER['PHP_SELF'], '/wish/') !== false || 
    strpos($_SERVER['PHP_SELF'], '/profile/') !== false ||
    strpos($_SERVER['PHP_SELF'], '/admin/') !== false) {
    $path_prefix = '../';
}
?>

<header id="main-header" class="fixed top-0 left-0 w-full z-50 transition-transform duration-300 transform bg-[var(--red)] bg-opacity-80 shadow-md px-6 py-3 flex flex-wrap items-center justify-between text-black backdrop-blur-md">
  
    
    <div class="flex items-center space-x-2">
        <a href="<?= $path_prefix ?>index.php" class="flex items-center">
            <img src="/progetto/uploads/logo.png" alt="Logo" class="w-10 h-10 opacity-90 transition duration-300 hover:brightness-125 hover:invert hover:hue-rotate-[70deg]" />
        </a>
    </div>


    
    <div class="relative max-w-xs w-full md:w-64 mr-auto ml-6 mt-2 md:mt-0">
        <input type="text" id="search-input" placeholder="Search..."
            class="w-full px-3 py-2 border border-zinc-300 rounded-md text-sm text-gray-700 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-yellow-100" />
        <div id="search-results"
            class="absolute top-full left-0 right-0 bg-white border border-zinc-200 rounded shadow-md mt-1 hidden z-50 max-h-72 overflow-y-auto text-black">
        </div>
    </div>

    <nav class="flex items-center space-x-6 text-sm font-medium mt-2 md:mt-0 mr-auto ml-2">
        <a href="<?= $path_prefix ?>index.php" class="text-zinc-100 hover:text-yellow-200 transition">HOME</a>
        <a href="<?= $path_prefix ?>catalog.php" class="text-zinc-100 hover:text-yellow-200 transition">SHOP</a>
        <a href="#footer" class="text-zinc-100 hover:text-yellow-200 transition">INFO</a>
    </nav>

    
    <div class="flex items-center space-x-4 text-xl mt-2 md:mt-0">
        <a href="<?= $path_prefix ?>wish/wish.php" class='text-zinc-100 hover:text-yellow-200'><i class="fa-regular fa-heart"></i></a>
        <a href="<?= $path_prefix ?>cart/cart.php" class='text-zinc-100 hover:text-yellow-200'><i class="fa-solid fa-cart-plus"></i></a>

        <?php if (isset($_SESSION["user_id"])): ?>
            <a href="<?= $path_prefix ?>profile/profile.php" class='text-zinc-100 hover:text-yellow-200'><i class="fa-regular fa-user"></i></a>
            <a href="<?= $path_prefix ?>logout.php" class='text-zinc-100 hover:text-yellow-200'><i class="fas fa-sign-out-alt"></i></a>

            <?php if (isset($_SESSION["admin"]) && $_SESSION["admin"] === true): ?>
                <a href="<?= $path_prefix ?>admin/admin.php" class='text-zinc-100 hover:text-yellow-300'><i class="fa-solid fa-hammer"></i></a>
            <?php endif; ?>

        <?php else: ?>
            <a href="<?= $path_prefix ?>login.php" class='text-zinc-100 hover:text-yellow-200'><i class="fas fa-sign-in-alt"></i></a>
            <a href="<?= $path_prefix ?>register.php" class='text-zinc-100 hover:text-yellow-200'><i class="fas fa-user-plus"></i></a>
        <?php endif; ?>
    </div>
</header>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById('search-input');
    const searchResults = document.getElementById('search-results');

    if (searchInput && searchResults) {
        searchInput.addEventListener('keyup', function () {
            const query = this.value.trim();

            if (query.length > 2) {
                fetch('<?= $path_prefix ?>search.php?q=' + encodeURIComponent(query))
                    .then(res => res.text())
                    .then(data => {
                        searchResults.innerHTML = data;
                        searchResults.classList.remove('hidden');
                    });
            } else {
                searchResults.classList.add('hidden');
            }
        });

        document.addEventListener('click', function (e) {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.classList.add('hidden');
            }
        });
    }
});
</script>

