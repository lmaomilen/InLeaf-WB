function showToast(message, type = "info") {
    const toast = document.getElementById('toast');
    toast.innerText = message;

    toast.className = "fixed bottom-6 right-6 px-5 py-3 rounded-lg shadow-lg text-white z-50 transition-opacity duration-300";

    switch (type) {
        case "success":
            toast.classList.add("bg-green-600");
            break;
        case "error":
            toast.classList.add("bg-red-600");
            break;
        case "info":
        default:
            toast.classList.add("bg-blue-600");
            break;
    }

    toast.classList.remove('hidden', 'opacity-0');
    toast.classList.add('opacity-100');

    setTimeout(() => {
        toast.classList.add('opacity-0');
        setTimeout(() => {
            toast.classList.add('hidden');
        }, 300);
    }, 3000);
}

document.addEventListener("DOMContentLoaded", function () {
    loadWishlist();

    document.addEventListener("click", function (event) {
        if (event.target.classList.contains("remove-from-wishlist")) {
            let productId = event.target.getAttribute("data-id");

            fetch(`wish_handler.php?action=remove&id=${productId}`)
                .then(response => response.json())
                .then(data => {
                    showToast(data.message, "info");
                    loadWishlist();
                });
        }
    });
});




function loadWishlist() {
    fetch("wish_handler.php?action=view")
        .then(response => response.json())
        .then(data => {
            let container = document.getElementById("wishlist");
            container.innerHTML = "";

            if (data.status === "success" && data.wishlist.length > 0) {
                data.wishlist.forEach(item => {
                    let image = "default.jpg";
                    if (item.image && item.image.startsWith("[")) {
                        const images = JSON.parse(item.image);
                        image = images.length ? "/progetto/admin/uploads/" + images[0] : "/progetto/assets/default.jpg";

                    }

                    container.innerHTML += `
                        <div class="bg-white p-4 rounded-lg shadow-md">
                            <a href="../product.php?id=${item.id}" class="block hover:shadow-xl transition duration-300">
                                <div class="w-full aspect-[4/5] bg-gray-100 overflow-hidden flex justify-center items-center">
                                    <img src="${image}" alt="${item.name}" class="object-contain h-full w-full">
                                </div>
                                <h3 class="text-lg font-bold my-2 text-blue-900">${item.name}</h3>
                                <p class="text-yellow-500 font-semibold mb-1">$${item.price}</p>
                            </a>
                            <div class="flex justify-between items-center mt-2">
                                <button class="remove-from-wishlist bg-red-500 text-white px-4 py-2 rounded" data-id="${item.id}">
                                    Delete
                                </button>
                                <button class="add-to-wishlist ml-2" data-id="${item.id}">
                                    <i class="fas fa-heart text-red-500 text-2xl"></i>
                                </button>
                            </div>
                        </div>
                    `;




                });
            } else {
                container.innerHTML = `
                                <div class="text-center text-gray-600 py-16">
                                    <div class="flex justify-center mb-6">
                                    <img src="/progetto/uploads/svg.png" alt="Empty wishlist" class="w-40 h-40 opacity-80" />
                                    </div>
                                    <h2 class="text-2xl font-semibold mb-2">Your wishlist is empty</h2>
                                    <p class="mb-6 text-gray-500">Looks like you haven't added anything yet. Find your favorite sneakers now!</p>
                                    <a href="../catalog.php" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded transition-all duration-300">
                                    Go to Catalog
                                    </a>
                                </div>`;

            }
        });
}
