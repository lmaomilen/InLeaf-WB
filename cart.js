function loadCart() {
    fetch("cart_handler.php?action=view")
        .then(response => response.json())
        .then(data => {
            console.log("CART DATA:", data);

            let cartContainer = document.getElementById("cart");
            let checkoutButton = document.getElementById("checkout-button");

            console.log("checkoutButton:", checkoutButton);

            if (!cartContainer) {
                console.warn("Контейнер для корзины не найден!");
                return;
            }

            cartContainer.innerHTML = "";

            if (data.status === "success" && data.cart.length > 0) {
                data.cart.forEach(item => {
                    let imageSrc = "/progetto/admin/uploads/default.jpg";

                    if (item.image && item.image.startsWith("[")) {
                        try {
                            const images = JSON.parse(item.image);
                            if (images.length > 0) {
                                imageSrc = "/progetto/admin/uploads/" + images[0];
                            }
                        } catch (e) {
                            console.error("Ошибка при разборе JSON изображения:", e);
                        }
                    } else if (item.image && !item.image.startsWith("[")) {
                        imageSrc = "/progetto/admin/uploads/" + item.image;
                    }

                    cartContainer.innerHTML += `
                                <div class="bg-white p-4 rounded-lg shadow-md">
                                    <a href="../product.php?id=${item.id}" class="block hover:shadow-xl transition duration-300">
                                    <img src="${imageSrc}" alt="${item.name}" class="w-full max-w-xs mx-auto h-auto mb-4">
                                    <h3 class="text-lg font-bold mb-2 text-blue-900">${item.name}</h3>
                                    </a>
                                    <div class="flex items-center justify-between mt-2">
                                    <div class="flex items-center gap-2">
                                        <button class="decrease bg-gray-300 px-2 py-1 rounded text-sm" data-id="${item.id}">-</button>
                                        <span class="font-medium">${item.quantity}</span>
                                        <button class="increase bg-gray-300 px-2 py-1 rounded text-sm" data-id="${item.id}">+</button>
                                    </div>
                                    <p class="text-green-600 font-semibold">$${(item.price * item.quantity).toFixed(2)}</p>
                                    </div>
                                    <div class="flex justify-end mt-2">
                                    <button class="remove-from-cart bg-red-500 text-white px-4 py-2 rounded text-sm" data-id="${item.id}">
                                        Delete
                                    </button>
                                    </div>
                                </div>
                                `;



                
                });

                if (checkoutButton) {
                    console.log("Показываем кнопку оформления");
                    checkoutButton.classList.remove("hidden");
                }

                document.querySelectorAll(".remove-from-cart").forEach(button => {
                    button.addEventListener("click", function() {
                        let productId = this.getAttribute("data-id");
                        fetch(`cart_handler.php?action=remove&id=${productId}`)
                            .then(response => response.json())
                            .then(() => loadCart());
                    });
                });
                // Увеличить количество
                document.querySelectorAll(".increase").forEach(button => {
                    button.addEventListener("click", function () {
                        const productId = this.getAttribute("data-id");
                        fetch(`cart_handler.php?action=increase&id=${productId}`)
                            .then(res => res.json())
                            .then(() => loadCart());
                    });
                });

                // Уменьшить количество
                document.querySelectorAll(".decrease").forEach(button => {
                    button.addEventListener("click", function () {
                        const productId = this.getAttribute("data-id");
                        fetch(`cart_handler.php?action=decrease&id=${productId}`)
                            .then(res => res.json())
                            .then(() => loadCart());
                    });
                });
            } else {
                cartContainer.innerHTML =`
                                <div class="text-center text-gray-600 py-16">
                                    <div class="flex justify-center mb-6">
                                    <img src="/progetto/uploads/svg.png" alt="Cart is empty" class="w-40 h-40 opacity-80" />
                                    </div>
                                    <h2 class="text-2xl font-semibold mb-2">Your cart is empty</h2>
                                    <p class="mb-6 text-gray-500">Looks like you haven't added anything yet. Find your favorite sneakers now!</p>
                                    <a href="../catalog.php" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded transition-all duration-300">
                                    Go to Catalog
                                    </a>
                                </div>`;
                if (checkoutButton) {
                    console.log("🕳️ Скрываем кнопку оформления — корзина пуста");
                    checkoutButton.classList.add("hidden");
                }
            }
        });
}

function checkout() {
    window.location.href = "checkout.php"; 
}

document.addEventListener("DOMContentLoaded", function () {
    console.log("DOM загружен");
    loadCart();


    const checkoutButton = document.getElementById("checkout-button");
    if (checkoutButton) {
        checkoutButton.addEventListener("click", checkout);
    } else {
        console.warn("Кнопка оформления не найдена при загрузке!");
    }
});
