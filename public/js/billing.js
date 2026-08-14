/* ============================================
   BILLING SCREEN LOGIC (database-connected)
   ============================================ */

const billState = {
    items: [],
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

const productScanInputEl = document.getElementById("productScanInput");
const productSuggestionsEl = document.getElementById("productSuggestions");
const productSearchBoxEl = document.getElementById("productSearchBox");
const productSearchCloseBtnEl = document.getElementById("productSearchCloseBtn");

const receiptOverlayEl = document.getElementById("receiptOverlay");
const receiptContentEl = document.getElementById("receiptContent");
const showBillBtnEl = document.getElementById("showBillBtn");
const newBillBtnEl = document.getElementById("newBillBtn");
const closeReceiptBtnEl = document.getElementById("closeReceiptBtn");
const printReceiptBtnEl = document.getElementById("printReceiptBtn");

const barcodeNotFoundOverlayEl = document.getElementById("barcodeNotFoundOverlay");
const barcodeNotFoundCodeEl = document.getElementById("barcodeNotFoundCode");
const barcodeSearchInsteadBtnEl = document.getElementById("barcodeSearchInsteadBtn");
const barcodeCancelBtnEl = document.getElementById("barcodeCancelBtn");

const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

let isSearchMode = false;
let currentSuggestions = [];
let highlightedIndex = -1;
let searchDebounceTimer = null;

billDateEl.textContent = getTodayDateString();
renderDisplay();
renderBill();

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
    showSearchUI();
}

function showSearchUI() {
    isSearchMode = true;
    productSearchBoxEl.classList.add("search-visible");
    productScanInputEl.value = "";
    clearSuggestions();
    productScanInputEl.focus();
}

function hideSearchUI() {
    isSearchMode = false;
    productSearchBoxEl.classList.remove("search-visible");
    productScanInputEl.value = "";
    clearSuggestions();
}

function clearSuggestions() {
    currentSuggestions = [];
    highlightedIndex = -1;
    productSuggestionsEl.innerHTML = "";
}

productSearchCloseBtnEl.addEventListener("click", hideSearchUI);

productScanInputEl.addEventListener("input", () => {
    if (!isSearchMode) return;

    const query = productScanInputEl.value.trim();
    clearTimeout(searchDebounceTimer);

    if (query.length < 2) {
        clearSuggestions();
        return;
    }

    searchDebounceTimer = setTimeout(() => fetchProductSuggestions(query), 250);
});

async function fetchProductSuggestions(query) {
    try {
        const response = await fetch("/billing/search-products?q=" + encodeURIComponent(query));
        const products = await response.json();
        currentSuggestions = products;
        highlightedIndex = products.length > 0 ? 0 : -1;
        renderProductSuggestions();
    } catch (error) {
        productSuggestionsEl.innerHTML = `<p style="padding:10px; color:#6b7280;">Search failed. Please try again.</p>`;
    }
}

function renderProductSuggestions() {
    productSuggestionsEl.innerHTML = "";

    if (currentSuggestions.length === 0) {
        productSuggestionsEl.innerHTML = `<p style="padding:10px; color:#6b7280;">No products found.</p>`;
        return;
    }

    currentSuggestions.forEach((product, index) => {
        const isOut = product.stock <= 0;
        const stockLabel = isOut
            ? "Out of stock"
            : `${trimZeros(product.stock)} ${product.unit || ""} in stock`;

        const item = document.createElement("div");
        item.className = "product-suggestion-item";
        if (index === highlightedIndex) {
            item.classList.add("product-suggestion-item--highlighted");
        }
        item.style.cursor = isOut ? "not-allowed" : "pointer";
        item.style.opacity = isOut ? "0.6" : "1";
        item.innerHTML = `
            <span class="suggestion-info">
                ${product.name} — ${formatCurrency(product.price)}${product.unit ? " / " + product.unit : ""}
                <span class="suggestion-stock ${isOut ? 'suggestion-stock--out' : 'suggestion-stock--ok'}">${stockLabel}</span>
            </span>
        `;

        if (!isOut) {
            item.addEventListener("click", () => selectSuggestion(index));
        }

        productSuggestionsEl.appendChild(item);
    });
}

function selectSuggestion(index) {
    const product = currentSuggestions[index];
    if (!product || product.stock <= 0) return;

    addProductToBill(product, 1);

    productScanInputEl.value = "";
    clearSuggestions();
    productScanInputEl.focus();
}

productScanInputEl.addEventListener("keydown", async (event) => {
    if (event.key === "ArrowDown") {
        if (currentSuggestions.length === 0) return;
        event.preventDefault();
        highlightedIndex = (highlightedIndex + 1) % currentSuggestions.length;
        renderProductSuggestions();
        return;
    }

    if (event.key === "ArrowUp") {
        if (currentSuggestions.length === 0) return;
        event.preventDefault();
        highlightedIndex = (highlightedIndex - 1 + currentSuggestions.length) % currentSuggestions.length;
        renderProductSuggestions();
        return;
    }

    if (event.key !== "Enter") return;
    event.preventDefault();

    const code = productScanInputEl.value.trim();
    if (!code) return;

    clearTimeout(searchDebounceTimer);

    if (currentSuggestions.length > 0 && highlightedIndex >= 0) {
        selectSuggestion(highlightedIndex);
        return;
    }

    const looksLikeBarcode = /^[0-9]+$/.test(code);

    if (!looksLikeBarcode) {
        await fetchProductSuggestions(code);
        if (currentSuggestions.length > 0) {
            selectSuggestion(0);
        }
        return;
    }

    try {
        const response = await fetch("/billing/lookup-barcode", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-CSRF-TOKEN": csrfToken,
            },
            body: JSON.stringify({ barcode: code }),
        });

        const data = await response.json();

        if (!response.ok) {
            showBarcodeNotFound(code);
            return;
        }

        if (data.stock <= 0) {
            alert(`"${data.name}" is out of stock.`);
            productScanInputEl.value = "";
            productScanInputEl.focus();
            return;
        }

        addProductToBill(data, 1);
        productScanInputEl.value = "";
        clearSuggestions();
        productScanInputEl.focus();

    } catch (error) {
        alert("Could not reach the server. Please try again.");
        productScanInputEl.focus();
    }
});

document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && isSearchMode) {
        hideSearchUI();
    }
});

document.addEventListener("click", (event) => {
    if (!isSearchMode) return;

    const isInsideBox = productSearchBoxEl.contains(event.target);
    const isProductButton = event.target.closest('[data-action="product"]');

    if (!isInsideBox && !isProductButton) {
        hideSearchUI();
    }
});

function addProductToBill(product, quantity) {
    const existingItem = billState.items.find((item) => item.product_id === product.id);

    if (existingItem) {
        existingItem.quantity += quantity;
        existingItem.line_total = Math.round(existingItem.unit_price * existingItem.quantity * 100) / 100;
    } else {
        billState.items.push({
            name: product.name,
            unit_price: product.price,
            quantity: quantity,
            line_total: Math.round(product.price * quantity * 100) / 100,
            product_id: product.id,
            unit_id: product.unit_id,
            unit_label: product.unit,
        });
    }

    renderBill();
}

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

showBillBtnEl.addEventListener("click", async () => {
    if (billState.finalized) {
        receiptOverlayEl.style.display = "flex";
        return;
    }

    if (billState.items.length === 0) {
        alert("Please add at least one item before showing the bill.");
        return;
    }

    const customerNameValue = document.getElementById("customerName").value.trim();
    if (!customerNameValue) {
        alert("Please enter the customer's name before showing the bill.");
        document.getElementById("customerName").focus();
        return;
    }

    const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked').value;

    const payload = {
        customer_name: customerNameValue,
        customer_phone: document.getElementById("customerPhone").value || null,
        discount: parseFloat(discountInputEl.value) || 0,
        cash_received: parseFloat(cashInputEl.value) || 0,
        payment_method: paymentMethod,
        show_qr: document.getElementById("showQrCheckbox").checked,
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
    const tpl = resolveEffectiveTemplate(activeTemplate, sale, shopNameEl.textContent);
    const order = getSectionOrder(tpl);

    applyReceiptContainerClasses(receiptContentEl, tpl);
    receiptContentEl.innerHTML = buildReceiptHtml(tpl, sale, order);
}

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
    document.getElementById("showQrCheckbox").checked = defaultShowQr;

    billNumberEl.textContent = "New";
    billDateEl.textContent = getTodayDateString();

    showBillBtnEl.textContent = "SHOW BILL";
    showBillBtnEl.disabled = false;

    hideSearchUI();
    renderDisplay();
    renderBill();
});

function showBarcodeNotFound(barcode) {
    barcodeNotFoundCodeEl.textContent = barcode;
    barcodeNotFoundOverlayEl.style.display = "flex";
}

function hideBarcodeNotFound() {
    barcodeNotFoundOverlayEl.style.display = "none";
    productScanInputEl.focus();
}

barcodeCancelBtnEl.addEventListener("click", hideBarcodeNotFound);
barcodeSearchInsteadBtnEl.addEventListener("click", hideBarcodeNotFound);

productScanInputEl.focus();