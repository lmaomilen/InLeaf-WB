<?php require_once('../db.php');?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wishlist</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#111827] text-white pt-[80px] min-h-screen">
    <?php include '../header.php'; ?>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold tracking-tight">Wishlist</h1>
            <button onclick="history.back()" class="inline-flex items-center text-gray-400 hover:text-white text-sm font-medium transition duration-300">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
                Back
            </button>
        </div>

        <div id="wishlist" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <!-- Wishlist items will be dynamically inserted here -->
        </div>
    </div>

    <?php include '../footer.php'; ?>

    <script src="wish.js?v=123"></script>
    <div id="toast" class="fixed bottom-6 right-6 hidden opacity-0 px-5 py-3 rounded-lg shadow-lg text-white z-50 transition-opacity duration-300"></div>
</body>
</html>
