/* ============================================
   BILLING SCREEN LOGIC (database-connected)
   Smart calculator + payment popup + hold bill
   ============================================ */

const billState = {
    items: [],
    finalized: false,
    lastAddedIndex: -1,      // index of the last item added via search/barcode/qty-parse
    awaitingQty: false,      // true right after adding a product, until the next non-numeric input
    expressionConfirmed: false, // true once an arithmetic expression has been evaluated and is awaiting a 2nd ENTER
};

let currentSuggestions = [];
let highlightedIndex = -1;
let pendingImpliedQty = null;
let searchDebounceTimer = null;
let searchRequestToken = 0;
let selectedPaymentMethod = null;

/* ---------- DOM references ---------- */

const smartInputEl = document.getElementById("smartInput");
const smartInputAmountEl = document.getElementById("smartInputAmount");
const smartSuggestionsEl = document.getElementById("smartSuggestions");
const smartInputBoxEl = document.getElementById("smartInputBox");

const billItemsEl = document.getElementById("billItems");
const emptyBillMessageEl = document.getElementById("emptyBillMessage");
const subtotalValueEl = document.getElementById("subtotalValue");
const discountInputEl = document.getElementById("discountInput");
const vatRowEl = document.getElementById("vatRow");
const vatLabelEl = document.getElementById("vatLabel");
const vatValueEl = document.getElementById("vatValue");
const grandTotalValueEl = document.getElementById("grandTotalValue");

const billDateEl = document.getElementById("billDate");
const billTimeEl = document.getElementById("billTime");
const billNumberEl = document.getElementById("billNumber");
const shopNameEl = document.getElementById("shopName");

const customerNameEl = document.getElementById("customerName");
const customerPhoneEl = document.getElementById("customerPhone");
const showQrCheckboxEl = document.getElementById("showQrCheckbox");

const newBillBtnEl = document.getElementById("newBillBtn");
const newBillBtnBottomEl = document.getElementById("newBillBtnBottom");
const holdBillBtnEl = document.getElementById("holdBillBtn");
const heldBillsBadgeEl = document.getElementById("heldBillsBadge");
const billHistoryLinkEl = document.getElementById("billHistoryLink");
const settingsLinkEl = document.getElementById("settingsLink");

const showBillBtnEl = document.getElementById("showBillBtn");
const showBillBtnDefaultHTML = showBillBtnEl.innerHTML;

const receiptOverlayEl = document.getElementById("receiptOverlay");
const receiptContentEl = document.getElementById("receiptContent");
const closeReceiptBtnEl = document.getElementById("closeReceiptBtn");
const printReceiptBtnEl = document.getElementById("printReceiptBtn");

const barcodeNotFoundOverlayEl = document.getElementById("barcodeNotFoundOverlay");
const barcodeNotFoundCodeEl = document.getElementById("barcodeNotFoundCode");
const barcodeSearchInsteadBtnEl = document.getElementById("barcodeSearchInsteadBtn");
const barcodeCancelBtnEl = document.getElementById("barcodeCancelBtn");

const paymentModalOverlayEl = document.getElementById("paymentModalOverlay");
const paymentModalCloseBtnEl = document.getElementById("paymentModalCloseBtn");
const paymentModalTotalEl = document.getElementById("paymentModalTotal");
const paymentStepMethodEl = document.getElementById("paymentStepMethod");
const paymentStepCashEl = document.getElementById("paymentStepCash");
const paymentStepQrEl = document.getElementById("paymentStepQr");
const paymentStepCreditEl = document.getElementById("paymentStepCredit");
const paymentMethodCashBtnEl = document.getElementById("paymentMethodCashBtn");
const paymentMethodQrBtnEl = document.getElementById("paymentMethodQrBtn");
const paymentMethodCreditBtnEl = document.getElementById("paymentMethodCreditBtn");
const paymentModalCancelBtnEl = document.getElementById("paymentModalCancelBtn");
const cashReceivedInputEl = document.getElementById("cashReceivedInput");
const cashChangeLabelEl = document.getElementById("cashChangeLabel");
const cashChangeValueEl = document.getElementById("cashChangeValue");
const completeCashBtnEl = document.getElementById("completeCashBtn");
const backFromCashBtnEl = document.getElementById("backFromCashBtn");
const completeQrBtnEl = document.getElementById("completeQrBtn");
const backFromQrBtnEl = document.getElementById("backFromQrBtn");
const paymentCreditCustomerNameEl = document.getElementById("paymentCreditCustomerName");
const completeCreditBtnEl = document.getElementById("completeCreditBtn");
const backFromCreditBtnEl = document.getElementById("backFromCreditBtn");

const heldBillsOverlayEl = document.getElementById("heldBillsOverlay");
const heldBillsListEl = document.getElementById("heldBillsList");
const heldBillsEmptyMessageEl = document.getElementById("heldBillsEmptyMessage");
const closeHeldBillsBtnEl = document.getElementById("closeHeldBillsBtn");

const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

/* ---------- small helpers ---------- */

function trimZeros(num) {
    return parseFloat(num).toString();
}

function round2(num) {
    return Math.round(num * 100) / 100;
}

function escapeHtml(str) {
    return String(str ?? "").replace(/[&<>"']/g, (ch) => ({
        "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;",
    }[ch]));
}

function getCurrentTimeString() {
    const now = new Date();
    let hours = now.getHours();
    const minutes = String(now.getMinutes()).padStart(2, "0");
    const ampm = hours >= 12 ? "PM" : "AM";
    hours = hours % 12 || 12;
    return `${hours}:${minutes} ${ampm}`;
}

/**
 * Does this string look like a pure arithmetic expression
 * (numbers separated by + - × ÷ x *), and nothing else?
 */
function looksLikeExpression(str) {
    const compact = str.replace(/\s+/g, "");
    return /^[0-9]+(\.[0-9]+)?([+\-\u2212×÷x*/][0-9]+(\.[0-9]+)?)+$/i.test(compact);
}

function isPureNumber(str) {
    return /^[0-9]+(\.[0-9]+)?$/.test(str.trim());
}

/**
 * Small, safe left-to-right expression evaluator (no eval()).
 * Handles + - × ÷ with standard * / precedence over + -.
 */
function evaluateExpression(str) {
    const normalized = str
        .replace(/\s+/g, "")
        .replace(/×/g, "*")
        .replace(/÷/g, "/")
        .replace(/\u2212/g, "-")
        .replace(/x/gi, "*");

    const tokens = normalized.match(/(\d+(?:\.\d+)?|[+\-*/])/g);
    if (!tokens || tokens.length === 0) return null;

    // Pass 1: resolve * and / left-to-right
    const pass1 = [tokens[0]];
    for (let i = 1; i < tokens.length; i += 2) {
        const op = tokens[i];
        const num = tokens[i + 1];
        if (num === undefined) break;

        if (op === "*" || op === "/") {
            const prev = parseFloat(pass1.pop());
            const next = parseFloat(num);
            if (op === "/" && next === 0) return null;
            pass1.push(String(op === "*" ? prev * next : prev / next));
        } else {
            pass1.push(op, num);
        }
    }

    // Pass 2: resolve + and - left-to-right
    let result = parseFloat(pass1[0]);
    for (let i = 1; i < pass1.length; i += 2) {
        const op = pass1[i];
        const num = parseFloat(pass1[i + 1]);
        if (op === "+") result += num;
        else if (op === "-") result -= num;
    }

    return isNaN(result) ? null : round2(result);
}

/* ---------- smart input: live preview + classification ---------- */

function updateAmountPreview(raw) {
    const trimmed = raw.trim();

    if (!trimmed) {
        smartInputAmountEl.textContent = "";
        return;
    }
    if (looksLikeExpression(trimmed)) {
        const result = evaluateExpression(trimmed);
        smartInputAmountEl.textContent = result === null ? "" : "= " + formatCurrency(result);
        return;
    }
    if (isPureNumber(trimmed)) {
        smartInputAmountEl.textContent = formatCurrency(parseFloat(trimmed));
        return;
    }
    smartInputAmountEl.textContent = "";
}

async function fetchProducts(query) {
    if (query.trim().length < 2) return [];
    try {
        const response = await fetch("/billing/search-products?q=" + encodeURIComponent(query));
        if (!response.ok) return [];
        return await response.json();
    } catch (error) {
        return [];
    }
}

/**
 * Figures out what a free-text query means:
 *  1) try the full string as typed (covers "apple", "apple juice",
 *     "7up", "5 star", "Coke 500ml", "Nescafe 3 in 1", "AJ250"...)
 *  2) if that finds nothing, try stripping a trailing "x2" / "x 2" / " 2"
 *     quantity suffix and searching what's left ("apple 2", "apple x2")
 *  3) if that also finds nothing, try stripping a leading "2 " quantity
 *     prefix and searching what's left ("2 apple")
 */
async function resolveSearchIntent(trimmed) {
    let products = await fetchProducts(trimmed);
    if (products.length > 0) {
        return { products, impliedQty: null };
    }

    let match = trimmed.match(/^(.+?)\s*[x×]\s*(\d+(?:\.\d+)?)$/i);
    if (!match) match = trimmed.match(/^(.+?)\s+(\d+(?:\.\d+)?)$/);
    if (match) {
        const words = match[1].trim();
        const qty = parseFloat(match[2]);
        if (words.length >= 2 && qty > 0) {
            const secondary = await fetchProducts(words);
            if (secondary.length > 0) {
                return { products: secondary, impliedQty: qty };
            }
        }
    }

    const leadingMatch = trimmed.match(/^(\d+(?:\.\d+)?)\s+(.+)$/);
    if (leadingMatch) {
        const qty = parseFloat(leadingMatch[1]);
        const words = leadingMatch[2].trim();
        if (words.length >= 2 && qty > 0) {
            const secondary = await fetchProducts(words);
            if (secondary.length > 0) {
                return { products: secondary, impliedQty: qty };
            }
        }
    }

    return { products: [], impliedQty: null };
}

async function runSmartSearch(trimmed) {
    const token = ++searchRequestToken;
    const result = await resolveSearchIntent(trimmed);
    if (token !== searchRequestToken) return; // a newer keystroke has already started a new search

    pendingImpliedQty = result.impliedQty;
    currentSuggestions = result.products;
    highlightedIndex = currentSuggestions.length > 0 ? 0 : -1;

    if (currentSuggestions.length === 0) {
        renderNoResultsMessage();
    } else {
        renderSuggestions();
    }
}

smartInputEl.addEventListener("input", () => {
    const raw = smartInputEl.value;
    const trimmed = raw.trim();

    if (/[a-zA-Z]/.test(trimmed)) {
        billState.awaitingQty = false;
    }

    updateAmountPreview(raw);
    clearTimeout(searchDebounceTimer);

    if (!trimmed || looksLikeExpression(trimmed) || isPureNumber(trimmed)) {
        closeSuggestions();
        return;
    }

    searchDebounceTimer = setTimeout(() => runSmartSearch(trimmed), 250);
});

/* ---------- suggestions dropdown ---------- */

function closeSuggestions() {
    currentSuggestions = [];
    pendingImpliedQty = null;
    highlightedIndex = -1;
    smartSuggestionsEl.classList.remove("is-open");
    smartSuggestionsEl.innerHTML = "";
}

function renderNoResultsMessage() {
    smartSuggestionsEl.innerHTML = '<div class="suggestion-empty">No products found.</div>';
    smartSuggestionsEl.classList.add("is-open");
}

function renderSuggestions() {
    smartSuggestionsEl.innerHTML = "";

    if (currentSuggestions.length === 0) {
        smartSuggestionsEl.classList.remove("is-open");
        return;
    }

    smartSuggestionsEl.classList.add("is-open");

    currentSuggestions.forEach((product, index) => {
        const isOut = product.stock <= 0;
        const stockLabel = isOut
            ? "Out of stock"
            : `${trimZeros(product.stock)} ${product.unit || ""} in stock`;
        const qtyNote = pendingImpliedQty ? ` &middot; qty ${trimZeros(pendingImpliedQty)}` : "";

        const item = document.createElement("div");
        item.className = "product-suggestion-item";
        if (index === highlightedIndex) {
            item.classList.add("product-suggestion-item--highlighted");
        }
        item.style.cursor = isOut ? "not-allowed" : "pointer";
        item.style.opacity = isOut ? "0.6" : "1";
        item.innerHTML = `
            <span class="suggestion-info">
                ${escapeHtml(product.name)} &mdash; ${formatCurrency(product.price)}${product.unit ? " / " + escapeHtml(product.unit) : ""}${qtyNote}
                <span class="suggestion-stock ${isOut ? "suggestion-stock--out" : "suggestion-stock--ok"}">${stockLabel}</span>
            </span>
        `;

        if (!isOut) {
            item.addEventListener("click", () => selectSuggestion(index));
        }

        smartSuggestionsEl.appendChild(item);
    });
}

function selectSuggestion(index) {
    const product = currentSuggestions[index];
    if (!product || product.stock <= 0) return;

    const qty = pendingImpliedQty && pendingImpliedQty > 0 ? pendingImpliedQty : 1;
    addProductToBill(product, qty);
    resetSmartInput();
}

document.addEventListener("click", (event) => {
    if (!smartInputBoxEl.contains(event.target) && !smartSuggestionsEl.contains(event.target)) {
        closeSuggestions();
    }
});

/* ---------- committing the smart input (ENTER) ---------- */

smartInputEl.addEventListener("keydown", async (event) => {
    if (event.key === "ArrowDown") {
        if (currentSuggestions.length === 0) return;
        event.preventDefault();
        highlightedIndex = (highlightedIndex + 1) % currentSuggestions.length;
        renderSuggestions();
        return;
    }

    if (event.key === "ArrowUp") {
        if (currentSuggestions.length === 0) return;
        event.preventDefault();
        highlightedIndex = (highlightedIndex - 1 + currentSuggestions.length) % currentSuggestions.length;
        renderSuggestions();
        return;
    }

    if (event.key === "Escape") {
        closeSuggestions();
        return;
    }

    if (event.key !== "Enter") return;
    event.preventDefault();
    await commitSmartInput();
});

async function commitSmartInput() {
    const raw = smartInputEl.value;
    const trimmed = raw.trim();
    if (!trimmed) return;

    clearTimeout(searchDebounceTimer);

    // A suggestion is already open and highlighted — Enter selects it.
    if (currentSuggestions.length > 0 && highlightedIndex >= 0 && !looksLikeExpression(trimmed) && !isPureNumber(trimmed)) {
        selectSuggestion(highlightedIndex);
        return;
    }

    // Arithmetic expression: first ENTER evaluates, second ENTER adds it as a priced line.
    if (looksLikeExpression(trimmed)) {
        const result = evaluateExpression(trimmed);
        if (result === null) return;

        if (!billState.expressionConfirmed) {
            smartInputEl.value = trimZeros(result);
            billState.expressionConfirmed = true;
            smartInputAmountEl.textContent = "= " + formatCurrency(result);
            return;
        }

        addManualAmount(result);
        resetSmartInput();
        return;
    }

    billState.expressionConfirmed = false;

    // Pure number: barcode, quantity update, or a manual priced line.
    if (isPureNumber(trimmed)) {
        const digitsOnly = trimmed.replace(".", "");
        const isBarcodeLike = !trimmed.includes(".") && digitsOnly.length >= 7;

        if (isBarcodeLike) {
            await commitBarcodeLookup(trimmed);
            return;
        }

        const amount = parseFloat(trimmed);
        if (!amount || amount <= 0) return;

        if (billState.awaitingQty && billState.lastAddedIndex >= 0 && billState.lastAddedIndex < billState.items.length) {
            const item = billState.items[billState.lastAddedIndex];
            item.quantity = amount;
            item.line_total = round2(item.unit_price * item.quantity);
            billState.awaitingQty = false;
            renderBill();
            resetSmartInput();
            return;
        }

        addManualAmount(amount);
        resetSmartInput();
        return;
    }

    // Text: product name, SKU, or "product qty" combos.
    billState.awaitingQty = false;

    const result = await resolveSearchIntent(trimmed);
    if (result.products.length === 0) {
        currentSuggestions = [];
        renderNoResultsMessage();
        return;
    }

    pendingImpliedQty = result.impliedQty;
    currentSuggestions = result.products;

    if (result.products.length === 1) {
        selectSuggestion(0);
    } else {
        highlightedIndex = 0;
        renderSuggestions();
    }
}

async function commitBarcodeLookup(code) {
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
            resetSmartInput();
            return;
        }

        addProductToBill(data, 1);
        resetSmartInput();
    } catch (error) {
        alert("Could not reach the server. Please try again.");
        resetSmartInput();
    }
}

function showBarcodeNotFound(barcode) {
    barcodeNotFoundCodeEl.textContent = barcode;
    barcodeNotFoundOverlayEl.style.display = "flex";
}

function hideBarcodeNotFound() {
    barcodeNotFoundOverlayEl.style.display = "none";
    resetSmartInput();
}

barcodeCancelBtnEl.addEventListener("click", hideBarcodeNotFound);
barcodeSearchInsteadBtnEl.addEventListener("click", hideBarcodeNotFound);

function resetSmartInput() {
    smartInputEl.value = "";
    smartInputAmountEl.textContent = "";
    billState.expressionConfirmed = false;
    closeSuggestions();
    smartInputEl.focus();
}

function addManualAmount(amount) {
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
    billState.awaitingQty = false;
    billState.lastAddedIndex = billState.items.length - 1;
    renderBill();
}

function addProductToBill(product, quantity) {
    const existingIndex = billState.items.findIndex((item) => item.product_id === product.id);

    if (existingIndex >= 0) {
        const item = billState.items[existingIndex];
        item.quantity += quantity;
        item.line_total = round2(item.unit_price * item.quantity);
        billState.lastAddedIndex = existingIndex;
    } else {
        billState.items.push({
            name: product.name,
            unit_price: product.price,
            quantity: quantity,
            line_total: round2(product.price * quantity),
            product_id: product.id,
            unit_id: product.unit_id,
            unit_label: product.unit,
        });
        billState.lastAddedIndex = billState.items.length - 1;
    }

    billState.awaitingQty = true;
    renderBill();
}

/* ---------- calculator buttons ---------- */

document.querySelectorAll(".calc-btn").forEach((button) => {
    button.addEventListener("click", () => {
        const key = button.getAttribute("data-key");
        const action = button.getAttribute("data-action");

        if (key !== null) {
            if (key === "." && smartInputEl.value.includes(".")) {
                smartInputEl.focus();
                return;
            }
            smartInputEl.value += key;
            smartInputEl.dispatchEvent(new Event("input"));
        } else if (action === "clear") {
            smartInputEl.value = "";
            smartInputEl.dispatchEvent(new Event("input"));
        } else if (action === "backspace") {
            smartInputEl.value = smartInputEl.value.slice(0, -1);
            smartInputEl.dispatchEvent(new Event("input"));
        } else if (action === "enter") {
            commitSmartInput();
        } else if (action === "focus-input") {
            smartInputEl.value = "";
            smartInputEl.dispatchEvent(new Event("input"));
        }

        smartInputEl.focus();
    });
});

/* ---------- bill rendering & totals ---------- */

function computeTotals() {
    const subtotal = billState.items.reduce((sum, item) => sum + item.line_total, 0);
    const discount = Math.min(parseFloat(discountInputEl.value) || 0, subtotal);
    const grandTotal = Math.max(subtotal - discount, 0);

    let vatAmount = 0;
    let vatPercent = 0;
    const vatEnabled = !!(activeTemplate && activeTemplate.calculate_vat);

    if (vatEnabled) {
        vatPercent = parseFloat(activeTemplate.vat_percentage) || 0;
        // Same "VAT already included in the total" formula used by receipt-renderer.js —
        // informational only, it does not add anything on top of the grand total.
        vatAmount = (grandTotal * vatPercent) / (100 + vatPercent);
    }

    return { subtotal, discount, grandTotal, vatAmount, vatPercent, vatEnabled };
}

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
            <span class="item-num">${index + 1}</span>
            <span class="item-name">
                ${escapeHtml(item.name)}
                ${item.unit_label ? `<br><small style="color:var(--color-text-muted);">${escapeHtml(item.unit_label)}</small>` : ""}
            </span>
            <input type="number" class="item-qty-input" min="0.001" step="0.001" value="${trimZeros(item.quantity)}" data-index="${index}">
            <span class="item-price">${formatCurrency(item.unit_price)}</span>
            <span class="item-total">${formatCurrency(item.line_total)}</span>
            <button type="button" class="remove-btn" data-index="${index}">&#10005;</button>
        `;
        billItemsEl.appendChild(row);
    });

    billItemsEl.querySelectorAll(".remove-btn").forEach((btn) => {
        btn.addEventListener("click", () => {
            const index = parseInt(btn.getAttribute("data-index"), 10);
            if (billState.lastAddedIndex === index) {
                billState.lastAddedIndex = -1;
                billState.awaitingQty = false;
            }
            billState.items.splice(index, 1);
            renderBill();
        });
    });

    billItemsEl.querySelectorAll(".item-qty-input").forEach((input) => {
        input.addEventListener("change", () => {
            const index = parseInt(input.getAttribute("data-index"), 10);
            const item = billState.items[index];
            const newQty = parseFloat(input.value);

            if (!newQty || newQty <= 0) {
                input.value = trimZeros(item.quantity);
                return;
            }

            item.quantity = newQty;
            item.line_total = round2(item.unit_price * item.quantity);
            renderBill();
        });
    });

    updateTotals();
}

function updateTotals() {
    const totals = computeTotals();

    subtotalValueEl.textContent = formatCurrency(totals.subtotal);

    if (totals.vatEnabled && totals.vatAmount > 0) {
        vatRowEl.style.display = "flex";
        vatLabelEl.textContent = `VAT (${trimZeros(totals.vatPercent)}%)`;
        vatValueEl.textContent = formatCurrency(totals.vatAmount);
    } else {
        vatRowEl.style.display = "none";
    }

    grandTotalValueEl.textContent = formatCurrency(totals.grandTotal);
}

discountInputEl.addEventListener("input", updateTotals);

/* ---------- New Bill ---------- */

function triggerNewBill() {
    if (!billState.finalized && billState.items.length > 0) {
        const confirmed = confirm(
            "Start a new bill? Any unsaved items will be lost.\n\nTip: use Hold Bill instead if you want to keep them for later."
        );
        if (!confirmed) return;
    }
    resetBillState();
}

function resetBillState() {
    billState.items = [];
    billState.finalized = false;
    billState.lastAddedIndex = -1;
    billState.awaitingQty = false;
    billState.expressionConfirmed = false;

    customerNameEl.value = "";
    customerPhoneEl.value = "";
    discountInputEl.value = 0;
    showQrCheckboxEl.checked = defaultShowQr;

    billNumberEl.textContent = "NEW";
    billDateEl.textContent = getTodayDateString();
    billTimeEl.textContent = getCurrentTimeString();

    showBillBtnEl.innerHTML = showBillBtnDefaultHTML;
    showBillBtnEl.disabled = false;

    resetSmartInput();
    renderBill();
}

newBillBtnEl.addEventListener("click", triggerNewBill);
newBillBtnBottomEl.addEventListener("click", triggerNewBill);

/* ---------- Hold Bill ---------- */

async function refreshHeldBillsBadge() {
    try {
        const response = await fetch("/billing/held-bills");
        if (!response.ok) return;
        const heldBills = await response.json();

        if (heldBills.length > 0) {
            heldBillsBadgeEl.textContent = heldBills.length;
            heldBillsBadgeEl.style.display = "flex";
        } else {
            heldBillsBadgeEl.style.display = "none";
        }
    } catch (error) {
        // silent — the badge just won't update this time
    }
}

async function holdCurrentBill() {
    if (billState.items.length === 0) {
        openHeldBillsModal();
        return;
    }

    const payload = {
        customer_name: customerNameEl.value.trim() || null,
        customer_phone: customerPhoneEl.value.trim() || null,
        discount: parseFloat(discountInputEl.value) || 0,
        items: billState.items.map((item) => ({
            name: item.name,
            unit_price: item.unit_price,
            quantity: item.quantity,
            line_total: item.line_total,
            product_id: item.product_id,
            unit_id: item.unit_id,
            unit_label: item.unit_label,
        })),
    };

    holdBillBtnEl.disabled = true;

    try {
        const response = await fetch("/billing/hold", {
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
            alert(data.message || "Could not hold this bill. Please try again.");
            return;
        }

        resetBillState();
        refreshHeldBillsBadge();
    } catch (error) {
        alert("Could not reach the server. Please try again.");
    } finally {
        holdBillBtnEl.disabled = false;
    }
}

holdBillBtnEl.addEventListener("click", holdCurrentBill);

heldBillsBadgeEl.addEventListener("click", (event) => {
    event.stopPropagation();
    openHeldBillsModal();
});

async function openHeldBillsModal() {
    heldBillsOverlayEl.style.display = "flex";
    heldBillsListEl.innerHTML = "";
    heldBillsEmptyMessageEl.style.display = "none";

    try {
        const response = await fetch("/billing/held-bills");
        const heldBills = await response.json();
        renderHeldBillsList(heldBills);
    } catch (error) {
        heldBillsEmptyMessageEl.textContent = "Could not load held bills. Please try again.";
        heldBillsEmptyMessageEl.style.display = "block";
    }
}

function hideHeldBillsModal() {
    heldBillsOverlayEl.style.display = "none";
}

closeHeldBillsBtnEl.addEventListener("click", hideHeldBillsModal);

function renderHeldBillsList(heldBills) {
    heldBillsListEl.innerHTML = "";

    if (heldBills.length === 0) {
        heldBillsEmptyMessageEl.textContent = "No bills are on hold right now.";
        heldBillsEmptyMessageEl.style.display = "block";
        return;
    }

    heldBillsEmptyMessageEl.style.display = "none";

    heldBills.forEach((bill) => {
        const heldTime = new Date(bill.held_at).toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });

        const card = document.createElement("div");
        card.className = "held-bill-card";
        card.innerHTML = `
            <div class="held-bill-card-top">
                <span>${escapeHtml(bill.label || "Held Bill")}</span>
                <span>${formatCurrency(bill.total)}</span>
            </div>
            <div class="held-bill-card-meta">
                ${escapeHtml(bill.customer_name || "Walk-in")} &middot; ${bill.item_count} item(s) &middot; ${heldTime}
            </div>
            <div class="held-bill-card-actions">
                <button type="button" class="held-bill-restore-btn" data-id="${bill.id}">Restore</button>
                <button type="button" class="held-bill-discard-btn" data-id="${bill.id}">Discard</button>
            </div>
        `;
        heldBillsListEl.appendChild(card);
    });

    heldBillsListEl.querySelectorAll(".held-bill-restore-btn").forEach((btn) => {
        btn.addEventListener("click", () => restoreHeldBill(btn.getAttribute("data-id")));
    });

    heldBillsListEl.querySelectorAll(".held-bill-discard-btn").forEach((btn) => {
        btn.addEventListener("click", () => discardHeldBill(btn.getAttribute("data-id")));
    });
}

async function restoreHeldBill(id) {
    if (billState.items.length > 0) {
        const confirmed = confirm("Restoring will replace your current unsaved bill. Continue?");
        if (!confirmed) return;
    }

    try {
        const response = await fetch(`/billing/held-bills/${id}/restore`, {
            method: "POST",
            headers: {
                "Accept": "application/json",
                "X-CSRF-TOKEN": csrfToken,
            },
        });

        const data = await response.json();

        if (!response.ok) {
            alert(data.message || "Could not restore this bill.");
            return;
        }

        billState.items = data.items.map((item) => ({
            name: item.name,
            unit_price: parseFloat(item.unit_price),
            quantity: parseFloat(item.quantity),
            line_total: parseFloat(item.line_total),
            product_id: item.product_id,
            unit_id: item.unit_id,
            unit_label: item.unit_label,
        }));

        customerNameEl.value = data.customer_name || "";
        customerPhoneEl.value = data.customer_phone || "";
        discountInputEl.value = data.discount || 0;

        billState.finalized = false;
        billState.lastAddedIndex = -1;
        billState.awaitingQty = false;

        billNumberEl.textContent = "NEW";
        showBillBtnEl.innerHTML = showBillBtnDefaultHTML;
        showBillBtnEl.disabled = false;

        renderBill();
        hideHeldBillsModal();
        refreshHeldBillsBadge();
        smartInputEl.focus();
    } catch (error) {
        alert("Could not reach the server. Please try again.");
    }
}

async function discardHeldBill(id) {
    const confirmed = confirm("Discard this held bill? This cannot be undone.");
    if (!confirmed) return;

    try {
        await fetch(`/billing/held-bills/${id}`, {
            method: "DELETE",
            headers: {
                "Accept": "application/json",
                "X-CSRF-TOKEN": csrfToken,
            },
        });
        await openHeldBillsModal();
        refreshHeldBillsBadge();
    } catch (error) {
        alert("Could not reach the server. Please try again.");
    }
}

/* ---------- Show Bill -> Payment popup ---------- */

showBillBtnEl.addEventListener("click", () => {
    if (billState.finalized) {
        receiptOverlayEl.style.display = "flex";
        return;
    }

    if (billState.items.length === 0) {
        alert("Please add at least one item before showing the bill.");
        return;
    }

    const customerNameValue = customerNameEl.value.trim();
    if (!customerNameValue) {
        alert("Please enter the customer's name before showing the bill.");
        customerNameEl.focus();
        return;
    }

    openPaymentModal();
});

function openPaymentModal() {
    selectedPaymentMethod = null;
    const totals = computeTotals();
    paymentModalTotalEl.textContent = formatCurrency(totals.grandTotal);
    showPaymentStep("method");
    paymentModalOverlayEl.style.display = "flex";
}

function closePaymentModal() {
    paymentModalOverlayEl.style.display = "none";
    showPaymentStep("method");
}

function showPaymentStep(step) {
    paymentStepMethodEl.style.display = step === "method" ? "flex" : "none";
    paymentStepCashEl.style.display = step === "cash" ? "flex" : "none";
    paymentStepQrEl.style.display = step === "qr" ? "flex" : "none";
    paymentStepCreditEl.style.display = step === "credit" ? "flex" : "none";
}

paymentModalCloseBtnEl.addEventListener("click", closePaymentModal);
paymentModalCancelBtnEl.addEventListener("click", closePaymentModal);

paymentMethodCashBtnEl.addEventListener("click", () => {
    selectedPaymentMethod = "cash";
    cashReceivedInputEl.value = "";
    updateCashChange();
    showPaymentStep("cash");
    setTimeout(() => cashReceivedInputEl.focus(), 50);
});

paymentMethodQrBtnEl.addEventListener("click", () => {
    selectedPaymentMethod = "qr";
    showPaymentStep("qr");
});

paymentMethodCreditBtnEl.addEventListener("click", () => {
    selectedPaymentMethod = "credit";
    paymentCreditCustomerNameEl.textContent = customerNameEl.value.trim() || "the customer";
    showPaymentStep("credit");
});

backFromCashBtnEl.addEventListener("click", () => showPaymentStep("method"));
backFromQrBtnEl.addEventListener("click", () => showPaymentStep("method"));
backFromCreditBtnEl.addEventListener("click", () => showPaymentStep("method"));

cashReceivedInputEl.addEventListener("input", () => {
    // Numeric-only: strip anything that isn't a digit or a single decimal point.
    let value = cashReceivedInputEl.value.replace(/[^0-9.]/g, "");
    const firstDot = value.indexOf(".");
    if (firstDot !== -1) {
        value = value.slice(0, firstDot + 1) + value.slice(firstDot + 1).replace(/\./g, "");
    }
    cashReceivedInputEl.value = value;
    updateCashChange();
});

function updateCashChange() {
    const totals = computeTotals();
    const cash = parseFloat(cashReceivedInputEl.value) || 0;
    const diff = round2(cash - totals.grandTotal);
    const rowEl = cashChangeValueEl.closest(".payment-change-row");

    if (diff >= 0) {
        cashChangeLabelEl.textContent = "Change";
        cashChangeValueEl.textContent = formatCurrency(diff);
        rowEl.classList.remove("is-remaining");
    } else {
        cashChangeLabelEl.textContent = "Remaining";
        cashChangeValueEl.textContent = formatCurrency(Math.abs(diff));
        rowEl.classList.add("is-remaining");
    }
}

async function finalizeSale(paymentMethod, cashReceived) {
    const payload = {
        customer_name: customerNameEl.value.trim(),
        customer_phone: customerPhoneEl.value.trim() || null,
        discount: parseFloat(discountInputEl.value) || 0,
        cash_received: cashReceived,
        payment_method: paymentMethod,
        show_qr: showQrCheckboxEl.checked,
        items: billState.items.map((item) => ({
            name: item.name,
            price: item.unit_price,
            quantity: item.quantity,
            product_id: item.product_id,
            unit_id: item.unit_id,
        })),
    };

    const completeButtons = [completeCashBtnEl, completeQrBtnEl, completeCreditBtnEl];
    completeButtons.forEach((btn) => { btn.disabled = true; });

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
            return;
        }

        billState.finalized = true;
        billNumberEl.textContent = data.bill_number;
        billDateEl.textContent = data.date;

        closePaymentModal();
        renderReceipt(data);
        receiptOverlayEl.style.display = "flex";
        showBillBtnEl.textContent = "VIEW BILL";
        showBillBtnEl.disabled = false;
    } catch (error) {
        alert("Could not connect to the server. Please check your connection and try again.");
    } finally {
        completeButtons.forEach((btn) => { btn.disabled = false; });
    }
}

completeCashBtnEl.addEventListener("click", () => {
    const cash = parseFloat(cashReceivedInputEl.value) || 0;
    finalizeSale("cash", cash);
});

completeQrBtnEl.addEventListener("click", () => {
    const totals = computeTotals();
    finalizeSale("qr", totals.grandTotal);
});

completeCreditBtnEl.addEventListener("click", () => {
    finalizeSale("credit", 0);
});

/* ---------- receipt ---------- */

closeReceiptBtnEl.addEventListener("click", () => {
    receiptOverlayEl.style.display = "none";
});

printReceiptBtnEl.addEventListener("click", () => {
    const copies = (typeof printerCopies !== "undefined" && printerCopies > 1) ? printerCopies : 1;

    if (copies > 1) {
        const original = receiptContentEl.innerHTML;
        const cutLine = '<div style="border-top:1px dashed #000; margin:6mm 0;"></div>';
        receiptContentEl.innerHTML = Array(copies).fill(original).join(cutLine);
        window.print();
        receiptContentEl.innerHTML = original;
    } else {
        window.print();
    }
});

function renderReceipt(sale) {
    const tpl = resolveEffectiveTemplate(activeTemplate, sale, shopNameEl.textContent);
    const order = getSectionOrder(tpl);

    applyReceiptContainerClasses(receiptContentEl, tpl, printerPaperWidthMm);
    receiptContentEl.innerHTML = renderReceiptForTemplate(tpl, sale, order);
}

/* ---------- keyboard shortcuts ---------- */

function anyOverlayOpen() {
    return (
        paymentModalOverlayEl.style.display !== "none" ||
        heldBillsOverlayEl.style.display !== "none" ||
        receiptOverlayEl.style.display !== "none" ||
        barcodeNotFoundOverlayEl.style.display !== "none"
    );
}

document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") {
        if (paymentModalOverlayEl.style.display !== "none") { closePaymentModal(); return; }
        if (heldBillsOverlayEl.style.display !== "none") { hideHeldBillsModal(); return; }
        if (barcodeNotFoundOverlayEl.style.display !== "none") { hideBarcodeNotFound(); return; }
        closeSuggestions();
        return;
    }

    if (event.key === "F1") {
        event.preventDefault();
        if (!anyOverlayOpen()) smartInputEl.focus();
        return;
    }

    if (event.key === "F2") {
        event.preventDefault();
        if (!anyOverlayOpen()) triggerNewBill();
        return;
    }

    if (event.key === "F3") {
        event.preventDefault();
        if (!anyOverlayOpen()) holdCurrentBill();
        return;
    }

    if (event.key === "F4") {
        event.preventDefault();
        if (!anyOverlayOpen() && billHistoryLinkEl) window.location.href = billHistoryLinkEl.href;
        return;
    }

    if (event.key === "F5") {
        event.preventDefault();
        if (!anyOverlayOpen() && settingsLinkEl) window.open(settingsLinkEl.href, "_blank");
        return;
    }

    if (event.key === "F8") {
        event.preventDefault();
        if (!anyOverlayOpen()) showBillBtnEl.click();
        return;
    }
});

/* ---------- initial load ---------- */

billDateEl.textContent = getTodayDateString();
billTimeEl.textContent = getCurrentTimeString();
renderBill();
refreshHeldBillsBadge();
smartInputEl.focus();