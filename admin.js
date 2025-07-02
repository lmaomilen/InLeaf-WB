let addSelectedFiles = []; 
let editSelectedFiles = []; 
let editExistingImages = [];

function showToast(message, type = "info") {
    const toast = document.getElementById('toast');
    toast.innerText = message;
    toast.className = "fixed bottom-6 right-6 px-5 py-3 rounded-lg shadow-lg text-white z-50 transition-opacity duration-300";

    toast.classList.remove('hidden', 'opacity-0');
    switch (type) {
        case "success": toast.classList.add("bg-green-600"); break;
        case "error": toast.classList.add("bg-red-600"); break;
        default: toast.classList.add("bg-blue-600"); break;
    }
    toast.classList.add('opacity-100');
    setTimeout(() => {
        toast.classList.add('opacity-0');
        setTimeout(() => toast.classList.add('hidden'), 300);
    }, 3000);
}

function loadProducts() {
    const container = document.getElementById("product-list");
    if (!container) return;

    fetch("admin_handler.php?action=get_products")
        .then(res => res.json())
        .then(data => {
            const container = document.getElementById("product-list");
            container.innerHTML = "";
            if (data.status === "success") {
                data.products.forEach(product => {
                    const encodedProduct = encodeURIComponent(JSON.stringify(product));
                    const imagesHTML = (Array.isArray(product.image) ? product.image : []).map(img =>
                        `<img src="uploads/${img}" class="w-24 h-24 object-contain rounded border flex-shrink-0" />`
                    ).join("");

                    let shortDescription = product.description.length > 100 
                        ? product.description.substring(0, 100) + "..." 
                        : product.description;

                    let fullDescription = product.description.replace(/"/g, '&quot;').replace(/'/g, "&#39;");

                    container.innerHTML += `
                        <div class="bg-white p-4 rounded shadow-md w-80 flex-shrink-0 snap-start">
                            <div class="overflow-x-auto flex space-x-2 pb-2 scrollbar-thin scrollbar-thumb-gray-400 scrollbar-track-gray-100">
                                ${imagesHTML}
                            </div>
                            <h3 class="text-lg font-bold mt-4">${product.name}</h3>
                            <p id="desc-${product.id}">${shortDescription}</p>
                            ${product.description.length > 100 ? `
                            <button data-full="${fullDescription}" data-id="${product.id}" data-state="short" class="toggle-desc text-blue-600 text-sm mt-1">
                              Show more
                            </button>` : ''}
                            <p class="text-blue-500 font-semibold">$${product.price}</p>
                            <div class="mt-2 space-x-2">
                                <button class="edit-btn bg-yellow-500 text-white px-2 py-1 rounded" data-product="${encodedProduct}">Edit</button>
                                <button onclick="deleteProduct(${product.id})" class="bg-red-500 text-white px-3 py-1 rounded">Delete</button>
                            </div>
                        </div>`;
                });
            }
        });
}

function deleteProduct(id) {
    if (!confirm("Удалить товар?")) return;
    fetch("admin_handler.php?action=delete_product", {
        method: "POST",
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${id}`
    })
    .then(res => res.json())
    .then(data => {
        showToast(data.message, data.status);
        loadProducts();
    });
}

document.addEventListener("DOMContentLoaded", () => loadProducts());

document.addEventListener("click", function (e) {
    if (e.target.classList.contains("edit-btn")) {
        const product = JSON.parse(decodeURIComponent(e.target.dataset.product));
        openModal(product);
    } else if (e.target.classList.contains("toggle-desc")) {
        const id = e.target.dataset.id;
        const full = e.target.dataset.full;
        const p = document.getElementById(`desc-${id}`);
        const state = e.target.dataset.state;
        if (state === "short") {
            p.innerText = full;
            e.target.innerText = "Hide";
            e.target.dataset.state = "full";
        } else {
            p.innerText = full.substring(0, 100) + "...";
            e.target.innerText = "Show more";
            e.target.dataset.state = "short";
        }
    } else if (e.target.matches("button[data-uid]")) {
        const uid = e.target.dataset.uid;
        addSelectedFiles = addSelectedFiles.filter(f => f.uid !== uid);
        renderAddPreview();
    } else if (e.target.classList.contains("remove-existing")) {
        const img = e.target.dataset.image;
        editExistingImages = editExistingImages.filter(i => i !== img);
        e.target.closest("[data-image]").remove();
    } else if (e.target.classList.contains("remove-new")) {
        const uid = e.target.dataset.uid;
        editSelectedFiles = editSelectedFiles.filter(f => f.uid !== uid);
        e.target.closest("[data-uid]").remove();
    
    } else if (e.target.classList.contains("delete-image-btn")) {
        const productId = e.target.dataset.productId;
        const image = e.target.dataset.image;

        if (!confirm("Удалить изображение?")) return;

        fetch("admin_handler.php?action=delete_image", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `product_id=${productId}&image=${encodeURIComponent(image)}`
        })
        .then(res => res.json())
        .then(data => {
            showToast(data.message, data.status);
            if (data.status === "success") {
                const el = document.querySelector(`[data-image-wrapper="${image}"]`);
                if (el) el.remove();
            }
        });
    }
});


const dropzone = document.getElementById("dropzone");
const imageInput = document.getElementById("imageInput");

function generateUid(file) {
    return `${file.name}-${file.size}`;
}

dropzone?.addEventListener("click", () => imageInput.click());
dropzone?.addEventListener("dragover", e => { e.preventDefault(); dropzone.classList.add("bg-gray-100"); });
dropzone?.addEventListener("dragleave", () => dropzone.classList.remove("bg-gray-100"));
dropzone?.addEventListener("drop", e => {
    e.preventDefault();
    dropzone.classList.remove("bg-gray-100");
    handleAddFiles(e.dataTransfer.files);
});

imageInput?.addEventListener("change", () => {
    handleAddFiles(imageInput.files);
    imageInput.value = "";
});

async function handleFiles(files) {
    for (const file of files) {
        const exists = selectedFiles.some(f => (
            f.file.name === file.name && f.file.size === file.size
        ));
        if (!exists) selectedFiles.push({ file });
    }
    renderPreview();
}

function handleAddFiles(files) {
    for (const file of files) {
        const uid = generateUid(file);
        const exists = addSelectedFiles.some(f => f.uid === uid);
        if (!exists) {
            addSelectedFiles.push({ uid, file });
        }
    }
    renderAddPreview();
}


function renderAddPreview() {
    const preview = document.getElementById("imagePreview");
    preview.innerHTML = "";
    addSelectedFiles.forEach(({ uid, file }) => {
        const reader = new FileReader();
        reader.onload = function (e) {
            const div = document.createElement("div");
            div.className = "relative w-24 h-24 rounded overflow-hidden";
            div.setAttribute("data-uid", uid);
            div.innerHTML = `
                <img src="${e.target.result}" class="object-cover w-full h-full" />
                <button type="button" class="absolute -top-2 -right-2 bg-red-600 text-white w-5 h-5 rounded-full text-xs" data-uid="${uid}">&times;</button>
            `;
            preview.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
}

function renderEditPreview() {
    const previewDiv = document.getElementById("currentImages");
    previewDiv.querySelectorAll("[data-uid]").forEach(el => el.remove());
    editSelectedFiles.forEach(({ uid, file }) => {
        const reader = new FileReader();
        reader.onload = function (e) {
            const wrapper = document.createElement("div");
            wrapper.className = "relative w-24 h-24 cursor-move";
            wrapper.setAttribute("data-uid", uid);
            wrapper.innerHTML = `
                <img src="${e.target.result}" class="object-cover w-full h-full rounded" />
                <button type="button" class="absolute top-0 right-0 bg-red-600 text-white w-5 h-5 rounded-full remove-new" data-uid="${uid}">&times;</button>
            `;
            previewDiv.appendChild(wrapper);
        };
        reader.readAsDataURL(file);
    });
}


document.getElementById("addProductForm")?.addEventListener("submit", function (e) {
    e.preventDefault();
    const formData = new FormData();
    formData.append("name", this.name.value);
    formData.append("description", this.description.value);
    formData.append("price", this.price.value);
    formData.append("category", this.category.value);
    addSelectedFiles.forEach(({ file }) => formData.append("image[]", file));

    fetch("admin_handler.php?action=add_product", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        showToast(data.message, data.status);
        if (data.status === "success") {
            this.reset();
            addSelectedFiles = [];
            renderAddPreview();
            loadProducts();
        }
    });
});

function openModal(product) {
    document.getElementById('editId').value = product.id;
    document.getElementById('editName').value = product.name;
    document.getElementById('editDescription').value = product.description
        .replace(/\\r\\n/g, "\n")
        .replace(/\\"/g, '"')
        .replace(/\\\\/g, "\\");
    document.getElementById('editPrice').value = product.price;

    editExistingImages = Array.isArray(product.image) ? [...product.image] : [];
    editSelectedFiles = [];

    const currentImagesDiv = document.getElementById("currentImages");
    currentImagesDiv.innerHTML = "";


    editExistingImages.forEach(img => {
        const wrapper = document.createElement("div");
        wrapper.className = "relative w-24 h-24 cursor-move";
        wrapper.setAttribute("data-image", img);
        wrapper.innerHTML = `
            <img src="uploads/${img}" class="object-cover w-full h-full rounded" />
            <button type="button" class="absolute top-0 right-0 bg-red-600 text-white w-5 h-5 rounded-full remove-existing" data-image="${img}">&times;</button>
        `;
        currentImagesDiv.appendChild(wrapper);
    });


    document.getElementById('editImages').value = "";


    document.getElementById('editModal').classList.remove('hidden');
}


document.getElementById("editImages")?.addEventListener("change", e => {
    const files = e.target.files;
    for (const file of files) {
        const uid = generateUid(file);
        if (!editSelectedFiles.some(f => f.uid === uid)) {
            editSelectedFiles.push({ uid, file });
        }
    }
    renderEditPreview(); 
    e.target.value = ""; 
});



function closeModal() {
    document.getElementById('editModal').classList.add('hidden');
    editSelectedFiles = [];
    editExistingImages = [];
    document.getElementById("currentImages").innerHTML = "";
}

function deleteOrder(orderId) {
    if (!confirm("Delete this order?")) return;

    fetch('/progetto/admin/admin_handler.php?action=delete_order', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'order_id=' + encodeURIComponent(orderId)
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === "success") {
            alert("Order deleted successfully");
            location.reload();
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(err => alert("Network error: " + err));
}

if (typeof flatpickr !== "undefined" && document.querySelector("#datePicker")) {
    flatpickr("#datePicker", {
        dateFormat: "Y-m-d",
        allowInput: true,
        animate: true,
        maxDate: "today",
        altInput: true,
        altFormat: "F j, Y",
        defaultDate: "today",
        locale: {
            firstDayOfWeek: 1
        }
    });
}

document.getElementById("editProductForm")?.addEventListener("submit", function (e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);

    fetch("admin_handler.php?action=edit_product", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        showToast(data.message, data.success ? "success" : "error");


        if (typeof loadProducts === "function") {
            loadProducts();
        }
    })
    .catch(error => {
        console.error("Error:", error);
        showToast("Error prodcut form", "error");
    });
});

function removeProduct(id) {
  if (!confirm("Are you sure you want to delete this product?")) return;
  fetch("admin_handler.php?action=delete_product", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `id=${id}`
  })
  .then(res => res.json())
  .then(data => {
    if (data.status === "success") {
      const el = document.getElementById(`product-${id}`);
      if (el) {
        el.classList.add("opacity-0", "scale-95");
        setTimeout(() => el.remove(), 300);
      }
    } else {
      alert(data.message || "Failed to delete product.");
    }
  })
  .catch(err => alert("Network error: " + err));
}
