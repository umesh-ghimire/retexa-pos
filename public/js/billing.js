/* ============================================
   BILLING SCREEN LOGIC (database-connected)
   ============================================ */

const billState = {
    items: [],          // { name, unit_price, quantity, line_total, product_id, unit_id, unit_label }
    mode: "normal",
    currentInput: "",
    finalized: false,
};

const displayValueEl = document.getElementById("displayValue");
const displayModeEl = document.getElementById("displayMode");
const billItemsEl = document.getElementById("billItems");
const emptyBillMessageEl = document.getElementById("emptyBillMessage");
const subtotalValueEl = document.getElementById("subtotalValue");
const grandTotalValueEl = document.getElementById("grandTotalValue");
const changeValueEl = document.getElementById("changeValue");
const discountInputEl = document.getElementById("discountInput");
const cashInputEl = document.getElementById("cashInput");
const billDateEl = document.getElementById("billDate");
const billNumberEl = document.getElementById("billNumber");
const shopNameEl = document.getElementById("shopName");

const productSearchBoxEl = document.getElementById("productSearchBox");
const productSearchInputEl = document.getElementById("productSearchInput");
const productSuggestionsEl = document.getElementById("productSuggestions");

const receiptOverlayEl = document.getElementById("receiptOverlay");
const receiptContentEl = document.getElementById("receiptContent");
const showBillBtnEl = document.getElementById("showBillBtn");
const newBillBtnEl = document.getElementById("newBillBtn");
const closeReceiptBtnEl = document.getElementById("closeReceiptBtn");
const printReceiptBtnEl = document.getElementById("printReceiptBtn");

const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

billDateEl.textContent = getTodayDateString();
renderDisplay();
renderBill();

// ---------- CALCULATOR BUTTONS ----------

document.querySelectorAll(".calc-btn").forEach((button) => {
    button.addEventListener("click", () => {
        const key = button.getAttribute("data-key");
        const action = button.getAttribute("data-action");

        if (key !== null) {
            handleNumberKey(key);
        } else if (action === "clear") {
            handleClear();
        } else if (action === "backspace") {
            handleBackspace();
        } else if (action === "enter") {
            handleEnter();
        } else if (action === "product") {
            handleProductMode();
        }
    });
});

function handleNumberKey(key) {
    if (key === "." && billState.currentInput.includes(".")) return;
    billState.currentInput += key;
    renderDisplay();
}

function handleClear() {
    billState.currentInput = "";
    exitProductMode();
    renderDisplay();
}

function handleBackspace() {
    billState.currentInput = billState.currentInput.slice(0, -1);
    renderDisplay();
}

function handleEnter() {
    const amount = parseFloat(billState.currentInput);
    if (!billState.currentInput || isNaN(amount) || amount <= 0) return;

    const itemNumber = billState.items.length + 1;
    billState.items.push({
        name: `Item ${itemNumber}`,
        unit_price: amount,
        quantity: 1,
        line_total: amount,
        product_id: null,
        unit_id: null,
        unit_label: null,
    });

    billState.currentInput = "";
    renderDisplay();
    renderBill();
}

function handleProductMode() {
    billState.mode = "product";
    billState.currentInput = "";
    renderDisplay();

    productSearchBoxEl.style.display = "block";
    productSearchInputEl.value = "";
    productSearchInputEl.focus();
    renderProductSuggestions("");
}

function exitProductMode() {
    billState.mode = "normal";
    productSearchBoxEl.style.display = "none";
    productSuggestionsEl.innerHTML = "";
}

// ---------- PRODUCT SEARCH (real database products) ----------

productSearchInputEl.addEventListener("input", () => {
    renderProductSuggestions(productSearchInputEl.value);
});

function renderProductSuggestions(searchText) {
    const query = searchText.trim().toLowerCase();

    const matches = query === ""
        ? realProducts
        : realProducts.filter((p) => p.name.toLowerCase().startsWith(query));

    productSuggestionsEl.innerHTML = "";

    if (matches.length === 0) {
        productSuggestionsEl.innerHTML = `<p style="padding:10px; color:#6b7280;">No products found.</p>`;
        return;
    }

    matches.forEach((product) => {
        const item = document.createElement("div");
        item.className = "product-suggestion-item";
        item.innerHTML = `
            <span class="suggestion-info">${product.name} — ${formatCurrency(product.price)}${product.unit ? " / " + product.unit : ""}</span>
            <input type="number" class="suggestion-qty" value="1" min="0.001" step="0.001">
            <button type="button" class="suggestion-add-btn">Add</button>
        `;
        item.querySelector(".suggestion-add-btn").addEventListener("click", () => {
            const qtyInput = item.querySelector(".suggestion-qty");
            const quantity = parseFloat(qtyInput.value);

            if (isNaN(quantity) || quantity <= 0) {
                alert("Please enter a valid quantity.");
                return;
            }
            if (quantity > product.stock) {
                alert(`Only ${product.stock} ${product.unit || ""} of "${product.name}" available in stock.`);
                return;
            }
            addProductToBill(product, quantity);
        });
        productSuggestionsEl.appendChild(item);
    });
}

function addProductToBill(product, quantity) {
    billState.items.push({
        name: product.name,
        unit_price: product.price,
        quantity: quantity,
        line_total: Math.round(product.price * quantity * 100) / 100,
        product_id: product.id,
        unit_id: product.unit_id,
        unit_label: product.unit,
    });

    exitProductMode();
    renderBill();
}

// ---------- RENDERING ----------

function renderDisplay() {
    displayModeEl.textContent = billState.mode === "product" ? "PRODUCT MODE" : "NORMAL MODE";
    displayValueEl.textContent = billState.currentInput === "" ? "0" : billState.currentInput;
}

function renderBill() {
    billItemsEl.innerHTML = "";

    if (billState.items.length === 0) {
        billItemsEl.appendChild(emptyBillMessageEl);
        updateTotals();
        return;
    }

    billState.items.forEach((item, index) => {
        const quantityLabel = item.unit_label
            ? `${trimZeros(item.quantity)} ${item.unit_label} × ${formatCurrency(item.unit_price)}`
            : "";

        const row = document.createElement("div");
        row.className = "bill-item-row";
        row.innerHTML = `
            <span class="item-name">
                ${item.name}
                ${quantityLabel ? `<br><small style="color:#6b7280;">${quantityLabel}</small>` : ""}
            </span>
            <div class="item-actions">
                <span class="item-price">${formatCurrency(item.line_total)}</span>
                <button class="remove-btn" data-index="${index}">✕</button>
            </div>
        `;
        billItemsEl.appendChild(row);
    });

    document.querySelectorAll(".remove-btn").forEach((btn) => {
        btn.addEventListener("click", () => {
            const index = parseInt(btn.getAttribute("data-index"));
            billState.items.splice(index, 1);
            renderBill();
        });
    });

    updateTotals();
}

function trimZeros(num) {
    return parseFloat(num).toString();
}

function updateTotals() {
    const subtotal = billState.items.reduce((sum, item) => sum + item.line_total, 0);
    const discount = parseFloat(discountInputEl.value) || 0;
    const grandTotal = Math.max(subtotal - discount, 0);
    const cash = parseFloat(cashInputEl.value) || 0;
    const change = cash - grandTotal;

    subtotalValueEl.textContent = formatCurrency(subtotal);
    grandTotalValueEl.textContent = formatCurrency(grandTotal);
    changeValueEl.textContent = formatCurrency(change > 0 ? change : 0);
}

discountInputEl.addEventListener("input", updateTotals);
cashInputEl.addEventListener("input", updateTotals);

// ---------- SHOW BILL (saves to database) ----------

showBillBtnEl.addEventListener("click", async () => {
    if (billState.finalized) {
        receiptOverlayEl.style.display = "flex";
        return;
    }

    if (billState.items.length === 0) {
        alert("Please add at least one item before showing the bill.");
        return;
    }

    const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked').value;

    const payload = {
        customer_name: document.getElementById("customerName").value || null,
        customer_phone: document.getElementById("customerPhone").value || null,
        discount: parseFloat(discountInputEl.value) || 0,
        cash_received: parseFloat(cashInputEl.value) || 0,
        payment_method: paymentMethod,
        items: billState.items.map((item) => ({
            name: item.name,
            price: item.unit_price,
            quantity: item.quantity,
            product_id: item.product_id,
            unit_id: item.unit_id,
        })),
    };

    showBillBtnEl.disabled = true;
    showBillBtnEl.textContent = "Saving...";

    try {
        const response = await fetch("/billing/checkout", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-CSRF-TOKEN": csrfToken,
            },
            body: JSON.stringify(payload),
        });

        const data = await response.json();

        if (!response.ok) {
            alert(data.message || "Something went wrong while saving the bill.");
            showBillBtnEl.disabled = false;
            showBillBtnEl.textContent = "SHOW BILL";
            return;
        }

        billState.finalized = true;
        billNumberEl.textContent = data.bill_number;
        billDateEl.textContent = data.date;

        renderReceipt(data);
        receiptOverlayEl.style.display = "flex";
        showBillBtnEl.textContent = "VIEW BILL";
        showBillBtnEl.disabled = false;

    } catch (error) {
        alert("Could not connect to the server. Please check your connection and try again.");
        showBillBtnEl.disabled = false;
        showBillBtnEl.textContent = "SHOW BILL";
    }
});

closeReceiptBtnEl.addEventListener("click", () => {
    receiptOverlayEl.style.display = "none";
});

printReceiptBtnEl.addEventListener("click", () => {
    window.print();
});

function renderReceipt(sale) {
    const tpl = activeTemplate || buildFallbackTemplate(shopNameEl.textContent);
    const order = getSectionOrder(tpl);

    applyReceiptContainerClasses(receiptContentEl, tpl);
    receiptContentEl.innerHTML = buildReceiptHtml(tpl, sale, order);
}

// ---------- NEW BILL (reset everything) ----------

newBillBtnEl.addEventListener("click", () => {
    billState.items = [];
    billState.currentInput = "";
    billState.mode = "normal";
    billState.finalized = false;

    document.getElementById("customerName").value = "";
    document.getElementById("customerPhone").value = "";
    discountInputEl.value = 0;
    cashInputEl.value = 0;
    document.querySelector('input[name="paymentMethod"][value="cash"]').checked = true;

    billNumberEl.textContent = "New";
    billDateEl.textContent = getTodayDateString();

    showBillBtnEl.textContent = "SHOW BILL";
    showBillBtnEl.disabled = false;

    exitProductMode();
    renderDisplay();
    renderBill();
});