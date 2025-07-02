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
    const categoryFilter = document.getElementById("category-filter");
    if (categoryFilter) {
        categoryFilter.addEventListener("change", function () {
            const selectedCategory = this.value;
            if (!selectedCategory) {
                window.location.href = "index.php";
            } else {
                window.location.href = `index.php?category=${encodeURIComponent(selectedCategory)}`;
            }       
        });
    }

    if (document.querySelector(".mySwiper")) {
        var swiper = new Swiper(".mySwiper", {
            loop: true,
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev",
            },
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
        });
    }

    const searchInput = document.getElementById('search-input');
    const searchResults = document.getElementById('search-results');

    if (searchInput) {
        searchInput.addEventListener('keyup', function () {
            let query = this.value.trim();

            if (query.length > 2) {
                fetch('search.php?q=' + encodeURIComponent(query))
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

document.addEventListener("click", function (event) {
    // Wishlist
    const wishlistBtn = event.target.closest(".add-to-wishlist");

    if (wishlistBtn) {
        const productId = wishlistBtn.getAttribute("data-id");
        const icon = wishlistBtn.querySelector("i");
        const isAdded = icon.classList.contains("fas");

        fetch(`wish/wish_handler.php?action=${isAdded ? 'remove' : 'add'}&id=${productId}`)
            .then(response => response.json())
            .then(data => {
                showToast(data.message, isAdded ? "info" : "success");
                icon.classList.toggle("fas");
                icon.classList.toggle("far");
                icon.classList.toggle("text-red-500");
                icon.classList.toggle("text-gray-400");
            })
            .catch(error => console.error("Wishlist error:", error));
    }


    if (event.target.classList.contains("add-to-cart")) {
        const productId = event.target.getAttribute("data-id");
        const qtyInput = document.getElementById("qtyInput");
        const quantity = qtyInput ? parseInt(qtyInput.value || "1") : 1;

        fetch(`cart/cart_handler.php?action=add&id=${productId}`)
            .then(response => response.json())
            .then(data => {
                showToast(data.message, "success");
            })
            .catch(error => console.error("Cart error:", error));
    }
});


let lastScrollTop = 0;
const header = document.getElementById('main-header');
let ticking = false;

window.addEventListener('scroll', () => {
if (!ticking) {
    window.requestAnimationFrame(() => {
     const currentScroll = window.pageYOffset || document.documentElement.scrollTop;

    if (currentScroll > lastScrollTop && currentScroll > 50) {
      header.classList.add('-translate-y-full');
    } else if (currentScroll < lastScrollTop) {

      header.classList.remove('-translate-y-full');
    }

    lastScrollTop = currentScroll <= 0 ? 0 : currentScroll;
    ticking = false;
    });

    ticking = true;
}
});


document.addEventListener("DOMContentLoaded", function () {

    function addSliderArrows(containerId) {
        const container = document.getElementById(containerId); 
        if (!container) return;

        const leftBtn = document.createElement("button");
        leftBtn.innerHTML = '<i class="fa-solid fa-arrow-left"></i>';
        leftBtn.className = "absolute left-2 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 text-white px-3 py-2 rounded-full z-30 hover:bg-opacity-80";

        const rightBtn = document.createElement("button");
        rightBtn.innerHTML = '<i class="fa-solid fa-arrow-right"></i>';
        rightBtn.className = "absolute right-2 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 text-white px-3 py-2 rounded-full z-30 hover:bg-opacity-80";

        container.parentElement.style.position = "relative";
        container.parentElement.appendChild(leftBtn);
        container.parentElement.appendChild(rightBtn);

        const scrollAmount = 300;
        leftBtn.addEventListener("click", () => container.scrollBy({ left: -scrollAmount, behavior: "smooth" }));
        rightBtn.addEventListener("click", () => container.scrollBy({ left: scrollAmount, behavior: "smooth" }));
    }

    addSliderArrows("product-slider");
    addSliderArrows("user-slider");
});


document.addEventListener("DOMContentLoaded", function () {
    const orderButton = document.getElementById('checkout-button');

    if (orderButton) {
        orderButton.addEventListener('click', function (event) {
            const isAuth = orderButton.getAttribute('data-auth') === 'true';

            if (!isAuth) {
                event.preventDefault(); 
                alert("To place an order, you must log in to your account.");
                window.location.href = "login.php"; 
            }
        });
    }
});

document.getElementById("checkout-button")?.addEventListener("click", function (e) {
    e.preventDefault();

    const isAuth = this.dataset.auth === "true";
    const productId = this.dataset.id;

    if (!isAuth) {
        alert("Please log in to your account before placing your order.");
        return;
    }

    fetch("add_to_cart.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "product_id=" + encodeURIComponent(productId)
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === "success") {
            window.location.href = "/progetto/cart/checkout.php";
        } else {
            alert(data.message || "Error adding to cart.");
        }
    })
    .catch(() => alert("Error connecting to the server."));
});


document.addEventListener("DOMContentLoaded", function () {
    const stars = document.querySelectorAll('[data-value]');
    const ratingInput = document.getElementById('ratingInput');

    stars.forEach(star => {
        star.addEventListener('click', () => {
            const rating = star.getAttribute('data-value');
            ratingInput.value = rating;

            
            stars.forEach(s => {
                s.classList.remove('fa-solid');
                s.classList.add('fa-regular');
            });
            for (let i = 0; i < rating; i++) {
                stars[i].classList.remove('fa-regular');
                stars[i].classList.add('fa-solid');
            }
        });
    });
});


const qtyInput = document.getElementById("qtyInput");
const increaseBtn = document.querySelector(".qty-increase");
const decreaseBtn = document.querySelector(".qty-decrease");

if (qtyInput && increaseBtn && decreaseBtn) {
    increaseBtn.addEventListener("click", function () {
        qtyInput.value = parseInt(qtyInput.value || "1") + 1;
    });

    decreaseBtn.addEventListener("click", function () {
        let current = parseInt(qtyInput.value || "1");
        if (current > 1) {
            qtyInput.value = current - 1;
        }
    });
}



