/* ============================================
   BILLING SCREEN LOGIC
   Handles: calculator input, normal mode item
   entry, product search mode, bill totals,
   and change calculation.
   ============================================ */

// ---------- STATE ----------
// This object holds everything about the current bill in memory.
const billState = {
    items: [],          // array of { name, price }
    mode: "normal",      // "normal" or "product"
    currentInput: "",    // what's currently typed on the calculator display
};

// ---------- DOM ELEMENTS ----------
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

const productSearchBoxEl = document.getElementById("productSearchBox");
const productSearchInputEl = document.getElementById("productSearchInput");
const productSuggestionsEl = document.getElementById("productSuggestions");

const receiptOverlayEl = document.getElementById("receiptOverlay");
const receiptContentEl = document.getElementById("receiptContent");
const showBillBtnEl = document.getElementById("showBillBtn");
const closeReceiptBtnEl = document.getElementById("closeReceiptBtn");
const printReceiptBtnEl = document.getElementById("printReceiptBtn");
const billNumberEl = document.getElementById("billNumber");
const shopNameEl = document.getElementById("shopName");

// ---------- INITIAL SETUP ----------
billDateEl.textContent = getTodayDateString();
renderDisplay();
renderBill();

// ---------- CALCULATOR BUTTON HANDLING ----------

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

/**
 * Handles number/decimal key presses (0-9, .)
 */
function handleNumberKey(key) {
    // Prevent multiple decimal points
    if (key === "." && billState.currentInput.includes(".")) {
        return;
    }
    billState.currentInput += key;
    renderDisplay();
}

/**
 * Clears the current input completely (does not remove bill items).
 */
function handleClear() {
    billState.currentInput = "";
    exitProductMode();
    renderDisplay();
}

/**
 * Removes the last typed character.
 */
function handleBackspace() {
    billState.currentInput = billState.currentInput.slice(0, -1);
    renderDisplay();
}

/**
 * ENTER button: adds the typed amount as a new default item
 * (Item 1, Item 2, Item 3, etc.)
 */
function handleEnter() {
    const amount = parseFloat(billState.currentInput);

    // Validation: ignore empty or invalid input
    if (!billState.currentInput || isNaN(amount) || amount <= 0) {
        return;
    }

    const itemNumber = billState.items.length + 1;
    billState.items.push({
        name: `Item ${itemNumber}`,
        price: amount,
    });

    billState.currentInput = "";
    renderDisplay();
    renderBill();
}

/**
 * Switches the calculator into PRODUCT search mode.
 */
function handleProductMode() {
    billState.mode = "product";
    billState.currentInput = "";
    renderDisplay();

    productSearchBoxEl.style.display = "block";
    productSearchInputEl.value = "";
    productSearchInputEl.focus();
    renderProductSuggestions("");
}

/**
 * Exits PRODUCT mode and returns to normal calculator mode.
 */
function exitProductMode() {
    billState.mode = "normal";
    productSearchBoxEl.style.display = "none";
    productSuggestionsEl.innerHTML = "";
}

// ---------- PRODUCT SEARCH ----------

productSearchInputEl.addEventListener("input", () => {
    renderProductSuggestions(productSearchInputEl.value);
});

/**
 * Filters dummyProducts by the typed search text and displays matches.
 */
function renderProductSuggestions(searchText) {
    const query = searchText.trim().toLowerCase();

    const matches = query === ""
        ? dummyProducts
        : dummyProducts.filter((product) =>
            product.name.toLowerCase().startsWith(query)
          );

    productSuggestionsEl.innerHTML = "";

    if (matches.length === 0) {
        productSuggestionsEl.innerHTML = `<p style="padding:10px; color:#6b7280;">No products found.</p>`;
        return;
    }

    matches.forEach((product) => {
        const item = document.createElement("div");
        item.className = "product-suggestion-item";
        item.innerHTML = `
            <span>${product.name}</span>
            <span class="price">${formatCurrency(product.price)}</span>
        `;
        item.addEventListener("click", () => addProductToBill(product));
        productSuggestionsEl.appendChild(item);
    });
}

/**
 * Adds a selected inventory product to the bill using its stored price.
 */
function addProductToBill(product) {
    billState.items.push({
        name: product.name,
        price: product.price,
    });

    exitProductMode();
    renderBill();
}

// ---------- RENDERING ----------

/**
 * Updates the calculator display (mode label + current typed value).
 */
function renderDisplay() {
    displayModeEl.textContent = billState.mode === "product"
        ? "PRODUCT MODE"
        : "NORMAL MODE";

    displayValueEl.textContent = billState.currentInput === ""
        ? "0"
        : billState.currentInput;
}

/**
 * Redraws the full list of bill items and recalculates totals.
 */
function renderBill() {
    billItemsEl.innerHTML = "";

    if (billState.items.length === 0) {
        billItemsEl.appendChild(emptyBillMessageEl);
        updateTotals();
        return;
    }

    billState.items.forEach((item, index) => {
        const row = document.createElement("div");
        row.className = "bill-item-row";
        row.innerHTML = `
            <span class="item-name">${item.name}</span>
            <div class="item-actions">
                <span class="item-price">${formatCurrency(item.price)}</span>
                <button class="remove-btn" data-index="${index}">✕</button>
            </div>
        `;
        billItemsEl.appendChild(row);
    });

    // Attach remove handlers
    document.querySelectorAll(".remove-btn").forEach((btn) => {
        btn.addEventListener("click", () => {
            const index = parseInt(btn.getAttribute("data-index"));
            billState.items.splice(index, 1);
            renderBill();
        });
    });

    updateTotals();
}

/**
 * Recalculates subtotal, grand total, and change based on
 * current bill items, discount input, and cash input.
 */
function updateTotals() {
    const subtotal = billState.items.reduce((sum, item) => sum + item.price, 0);
    const discount = parseFloat(discountInputEl.value) || 0;
    const grandTotal = Math.max(subtotal - discount, 0);
    const cash = parseFloat(cashInputEl.value) || 0;
    const change = cash - grandTotal;

    subtotalValueEl.textContent = formatCurrency(subtotal);
    grandTotalValueEl.textContent = formatCurrency(grandTotal);
    changeValueEl.textContent = formatCurrency(change > 0 ? change : 0);
}

// Recalculate totals live when discount or cash values change
discountInputEl.addEventListener("input", updateTotals);
cashInputEl.addEventListener("input", updateTotals);

// ---------- SHOW BILL / RECEIPT ----------

showBillBtnEl.addEventListener("click", () => {
    // Basic validation: don't show a bill with no items
    if (billState.items.length === 0) {
        alert("Please add at least one item before showing the bill.");
        return;
    }
    renderReceipt();
    receiptOverlayEl.style.display = "flex";
});

closeReceiptBtnEl.addEventListener("click", () => {
    receiptOverlayEl.style.display = "none";
});

printReceiptBtnEl.addEventListener("click", () => {
    window.print();
});

/**
 * Builds the printable receipt HTML from the current bill state.
 */
function renderReceipt() {
    const subtotal = billState.items.reduce((sum, item) => sum + item.price, 0);
    const discount = parseFloat(discountInputEl.value) || 0;
    const grandTotal = Math.max(subtotal - discount, 0);
    const cash = parseFloat(cashInputEl.value) || 0;
    const change = Math.max(cash - grandTotal, 0);

    const itemsHtml = billState.items.map((item) => `
        <div class="receipt-item-row">
            <span>${item.name}</span>
            <span>${formatCurrency(item.price)}</span>
        </div>
    `).join("");

    receiptContentEl.innerHTML = `
        <div class="receipt-shop-name">${shopNameEl.textContent}</div>
        <div class="receipt-meta">
            Bill No: ${billNumberEl.textContent}<br>
            Date: ${getTodayDateString()}
        </div>

        <hr class="receipt-divider">

        ${itemsHtml}

        <hr class="receipt-divider">

        <div class="receipt-total-row">
            <span>Subtotal</span>
            <span>${formatCurrency(subtotal)}</span>
        </div>
        <div class="receipt-total-row">
            <span>Discount</span>
            <span>${formatCurrency(discount)}</span>
        </div>
        <div class="receipt-total-row receipt-total-row--grand">
            <span>TOTAL</span>
            <span>${formatCurrency(grandTotal)}</span>
        </div>
        <div class="receipt-total-row">
            <span>Cash</span>
            <span>${formatCurrency(cash)}</span>
        </div>
        <div class="receipt-total-row">
            <span>Change</span>
            <span>${formatCurrency(change)}</span>
        </div>

        <div class="receipt-qr-section">
            <div class="receipt-qr-placeholder"></div>
            <div class="receipt-qr-label">Scan to pay ${formatCurrency(grandTotal)} (demo)</div>
        </div>

        <div class="receipt-footer">
            THANK YOU<br>VISIT AGAIN
        </div>
    `;
}