<?php 
session_start();
require_once('../db.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart | inLeaf</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#111827] text-white pt-[80px] min-h-screen">
    <?php include '../header.php'; ?>

    <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-bold">Your Cart</h1>
            <button onclick="history.back()" class="inline-flex items-center text-green-300 hover:text-white text-sm font-medium transition duration-300">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
                Back
            </button>
        </div>

        <div id="cart" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <!-- Cart items will be dynamically inserted here -->
        </div>

        <div class="mt-8 text-center">
            <button id="checkout-button" class="bg-green-600 hover:bg-green-700 transition text-white font-semibold px-6 py-3 rounded-lg hidden">
                Proceed to Checkout
            </button>
        </div>
    </section>

    <?php include '../footer.php'; ?>

    <script src="cart.js?v=123" defer></script>
</body>
</html>