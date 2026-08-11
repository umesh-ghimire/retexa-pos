/* ============================================
   INVENTORY SCREEN LOGIC
   Handles: listing, searching, add, edit, delete
   of products. Uses in-memory dummy data for now.
   ============================================ */

// ---------- STATE ----------
// We copy dummyProducts into our own array so we can freely
// add/edit/delete without touching the original data file.
let inventoryState = dummyProducts.map((product) => ({
    ...product,
    category: product.category || "General",
    stock: product.stock ?? 50,
    status: product.status || "active",
}));

let nextProductId = Math.max(...inventoryState.map((p) => p.id)) + 1;

// ---------- DOM ELEMENTS ----------
const inventoryTableBodyEl = document.getElementById("inventoryTableBody");
const emptyInventoryMessageEl = document.getElementById("emptyInventoryMessage");
const inventorySearchInputEl = document.getElementById("inventorySearchInput");

const addProductBtnEl = document.getElementById("addProductBtn");
const productModalOverlayEl = document.getElementById("productModalOverlay");
const productModalTitleEl = document.getElementById("productModalTitle");
const productFormEl = document.getElementById("productForm");
const cancelProductBtnEl = document.getElementById("cancelProductBtn");

const productIdInputEl = document.getElementById("productId");
const productNameInputEl = document.getElementById("productNameInput");
const productCategoryInputEl = document.getElementById("productCategoryInput");
const productPriceInputEl = document.getElementById("productPriceInput");
const productStockInputEl = document.getElementById("productStockInput");
const productStatusInputEl = document.getElementById("productStatusInput");

// ---------- INITIAL RENDER ----------
renderInventoryTable(inventoryState);

// ---------- SEARCH ----------
inventorySearchInputEl.addEventListener("input", () => {
    const query = inventorySearchInputEl.value.trim().toLowerCase();
    const filtered = inventoryState.filter((product) =>
        product.name.toLowerCase().includes(query)
    );
    renderInventoryTable(filtered);
});

// ---------- RENDER TABLE ----------

function renderInventoryTable(products) {
    inventoryTableBodyEl.innerHTML = "";

    if (products.length === 0) {
        emptyInventoryMessageEl.style.display = "block";
        return;
    }
    emptyInventoryMessageEl.style.display = "none";

    products.forEach((product) => {
        const row = document.createElement("tr");
        row.innerHTML = `
            <td>${product.name}</td>
            <td>${product.category}</td>
            <td>${formatCurrency(product.price)}</td>
            <td>${renderStockBadge(product.stock)}</td>
            <td>${renderStatusBadge(product.status)}</td>
            <td>
                <div class="table-actions">
                    <button class="btn-edit" data-id="${product.id}">Edit</button>
                    <button class="btn-delete" data-id="${product.id}">Delete</button>
                </div>
            </td>
        `;
        inventoryTableBodyEl.appendChild(row);
    });

    attachRowActionListeners();
}

function renderStockBadge(stock) {
    if (stock <= 0) {
        return `<span class="stock-badge stock-badge--out">Out of stock</span>`;
    }
    if (stock <= 10) {
        return `<span class="stock-badge stock-badge--low">${stock} left</span>`;
    }
    return `<span class="stock-badge stock-badge--ok">${stock} in stock</span>`;
}

function renderStatusBadge(status) {
    return status === "active"
        ? `<span class="status-badge status-badge--active">Active</span>`
        : `<span class="status-badge status-badge--inactive">Inactive</span>`;
}

function attachRowActionListeners() {
    document.querySelectorAll(".btn-edit").forEach((btn) => {
        btn.addEventListener("click", () => openEditModal(parseInt(btn.dataset.id)));
    });
    document.querySelectorAll(".btn-delete").forEach((btn) => {
        btn.addEventListener("click", () => deleteProduct(parseInt(btn.dataset.id)));
    });
}

// ---------- ADD PRODUCT ----------

addProductBtnEl.addEventListener("click", () => {
    productModalTitleEl.textContent = "Add Product";
    productFormEl.reset();
    productIdInputEl.value = "";
    productModalOverlayEl.style.display = "flex";
});

// ---------- EDIT PRODUCT ----------

function openEditModal(productId) {
    const product = inventoryState.find((p) => p.id === productId);
    if (!product) return;

    productModalTitleEl.textContent = "Edit Product";
    productIdInputEl.value = product.id;
    productNameInputEl.value = product.name;
    productCategoryInputEl.value = product.category;
    productPriceInputEl.value = product.price;
    productStockInputEl.value = product.stock;
    productStatusInputEl.value = product.status;

    productModalOverlayEl.style.display = "flex";
}

// ---------- DELETE PRODUCT ----------

function deleteProduct(productId) {
    const product = inventoryState.find((p) => p.id === productId);
    if (!product) return;

    const confirmed = confirm(`Delete "${product.name}"? This cannot be undone.`);
    if (!confirmed) return;

    inventoryState = inventoryState.filter((p) => p.id !== productId);
    renderInventoryTable(inventoryState);
}

// ---------- SAVE (ADD OR EDIT) ----------

productFormEl.addEventListener("submit", (event) => {
    event.preventDefault();

    const id = productIdInputEl.value;
    const name = productNameInputEl.value.trim();
    const category = productCategoryInputEl.value.trim() || "General";
    const price = parseFloat(productPriceInputEl.value);
    const stock = parseInt(productStockInputEl.value);
    const status = productStatusInputEl.value;

    // Basic validation
    if (!name) {
        alert("Product name is required.");
        return;
    }
    if (isNaN(price) || price < 0) {
        alert("Please enter a valid selling price.");
        return;
    }
    if (isNaN(stock) || stock < 0) {
        alert("Please enter a valid stock quantity.");
        return;
    }

    if (id) {
        // Editing existing product
        const product = inventoryState.find((p) => p.id === parseInt(id));
        product.name = name;
        product.category = category;
        product.price = price;
        product.stock = stock;
        product.status = status;
    } else {
        // Adding new product
        inventoryState.push({
            id: nextProductId++,
            name,
            category,
            price,
            stock,
            status,
        });
    }

    closeProductModal();
    renderInventoryTable(inventoryState);
});

// ---------- CANCEL / CLOSE MODAL ----------

cancelProductBtnEl.addEventListener("click", closeProductModal);

function closeProductModal() {
    productModalOverlayEl.style.display = "none";
    productFormEl.reset();
}