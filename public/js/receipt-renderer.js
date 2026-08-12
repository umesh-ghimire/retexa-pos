/* ============================================
   SHARED RECEIPT RENDERING LOGIC
   Used by: /billing (live POS), /admin/bills
   (Bill History), and /admin/bill-templates
   (Bill Designer) — one single source of truth,
   so a receipt looks identical everywhere.
   ============================================ */

const DEFAULT_SECTION_ORDER = [
    'header', 'bill_info', 'customer_info', 'items', 'totals', 'payment', 'qr', 'footer',
];

function formatCurrency(amount) {
    const safeAmount = isNaN(amount) ? 0 : parseFloat(amount);
    return "Rs. " + Math.round(safeAmount).toLocaleString('en-IN');
}

function resolveLogoSrc(logoPath) {
    if (!logoPath) return '';
    return logoPath.startsWith('data:') ? logoPath : '/storage/' + logoPath;
}

function getItemName(item) {
    return item.item_name || item.name || '';
}

function getItemSku(item) {
    if (item.product && item.product.sku) return item.product.sku;
    return item.sku || null;
}

function getItemUnitCode(item) {
    if (!item.unit) return null;
    return typeof item.unit === 'string' ? item.unit : item.unit.short_code;
}

function getSectionOrder(tpl) {
    return (tpl && tpl.section_order && tpl.section_order.length > 0)
        ? tpl.section_order
        : DEFAULT_SECTION_ORDER;
}

// ---------- Per-section builders ----------

function sectionHeaderHtml(tpl) {
    let html = '';
    if (tpl.show_logo) {
        if (tpl.logo_path) {
            html += `<div class="receipt-logo-circle"><img src="${resolveLogoSrc(tpl.logo_path)}"></div>`;
        } else {
            html += `<div class="receipt-logo-circle"><svg viewBox="0 0 24 24"><path d="M6 8h12l-1 12H7L6 8z"/><path d="M8 8a4 4 0 0 1 8 0" fill="none" stroke="#fff" stroke-width="1.5"/></svg></div>`;
        }
    }
    html += `<div class="receipt-shop-name">${tpl.shop_name || 'Shop Name'}</div>`;
    if (tpl.address) html += `<div class="receipt-meta">${tpl.address}</div>`;
    if (tpl.phone) html += `<div class="receipt-meta">Phone: ${tpl.phone}</div>`;
    if (tpl.header_text) html += `<div class="receipt-meta">${tpl.header_text}</div>`;
    html += `<hr class="receipt-divider">`;
    return html;
}

function sectionBillInfoHtml(tpl, sale) {
    if (!tpl.show_bill_number && !tpl.show_date) return '';
    const parts = (sale.created_at || '').split('T');
    const datePart = parts[0] || sale.date || '';
    const timePart = parts[1] ? parts[1].substring(0, 5) : '';

    let html = '';
    if (tpl.show_bill_number) {
        html += `<div class="receipt-meta-row"><span class="meta-label">Bill No</span><span>: ${sale.bill_number}</span></div>`;
    }
    if (tpl.show_date) {
        html += `<div class="receipt-meta-row"><span class="meta-label">Date</span><span>: ${datePart}</span></div>`;
        if (timePart) {
            html += `<div class="receipt-meta-row"><span class="meta-label">Time</span><span>: ${timePart}</span></div>`;
        }
    }
    return html;
}

function sectionCustomerInfoHtml(tpl, sale) {
    if (!tpl.show_customer) return '';
    const name = sale.customer ? sale.customer.name : (sale.customer_name || null);
    if (!name) return '';
    return `<div class="receipt-meta-row"><span class="meta-label">Customer</span><span>: ${name}</span></div>`;
}

function sectionItemsHtml(tpl, sale) {
    let html = '<hr class="receipt-divider">';
    html += '<table class="receipt-items-table"><thead><tr><th>Item</th>';
    if (tpl.show_quantity) html += '<th>Qty</th>';
    if (tpl.show_unit) html += '<th>Unit</th>';
    if (tpl.show_price) html += '<th>Price</th><th>Total</th>';
    html += '</tr></thead><tbody>';

    (sale.items || []).forEach((item) => {
        let name = getItemName(item);
        const sku = getItemSku(item);
        if (tpl.show_sku && sku) name += ` (${sku})`;

        html += `<tr><td>${name}</td>`;
        if (tpl.show_quantity) html += `<td>${parseFloat(item.quantity)}</td>`;
        if (tpl.show_unit) html += `<td>${getItemUnitCode(item) || ''}</td>`;
        if (tpl.show_price) {
            html += `<td>${formatCurrency(item.unit_price).replace('Rs. ', '')}</td>`;
            html += `<td>${formatCurrency(item.line_total).replace('Rs. ', '')}</td>`;
        }
        html += `</tr>`;
    });

    html += '</tbody></table><hr class="receipt-divider">';
    return html;
}

function sectionTotalsHtml(tpl, sale) {
    let html = '';
    if (tpl.show_subtotal) {
        html += `<div class="receipt-total-row"><span>Subtotal</span><span>${formatCurrency(sale.subtotal)}</span></div>`;
    }
    if (tpl.show_discount) {
        html += `<div class="receipt-total-row"><span>Discount</span><span>${formatCurrency(sale.discount)}</span></div>`;
    }
    html += `<div class="receipt-total-row receipt-total-row--grand"><span>TOTAL</span><span>${formatCurrency(sale.total)}</span></div>`;
    return html;
}

function sectionPaymentHtml(tpl, sale) {
    let html = '';
    if (tpl.show_cash_received) {
        html += `<div class="receipt-total-row"><span>Cash</span><span>${formatCurrency(sale.cash_received)}</span></div>`;
    }
    if (tpl.show_change) {
        html += `<div class="receipt-total-row"><span>Change</span><span>${formatCurrency(sale.change_amount)}</span></div>`;
    }
    return html;
}

function sectionQrHtml(tpl, sale) {
    if (!tpl.show_qr) return '';
    return `<div class="receipt-qr-section">
        <div class="receipt-qr-label" style="font-weight:700; margin-bottom:4px;">Scan to Pay</div>
        <div class="receipt-qr-placeholder"></div>
        <div class="receipt-qr-label">${formatCurrency(sale.total)} (demo)</div>
    </div>`;
}

function sectionFooterHtml(tpl) {
    if (!tpl.footer_text) return '';
    return `<div class="receipt-footer">${tpl.footer_text}<span class="footer-smiley">☺</span></div>`;
}

const SECTION_BUILDERS = {
    header: sectionHeaderHtml,
    bill_info: sectionBillInfoHtml,
    customer_info: sectionCustomerInfoHtml,
    items: sectionItemsHtml,
    totals: sectionTotalsHtml,
    payment: sectionPaymentHtml,
    qr: sectionQrHtml,
    footer: sectionFooterHtml,
};

function buildReceiptHtml(tpl, sale, order) {
    let html = '';
    (order || DEFAULT_SECTION_ORDER).forEach((key) => {
        const builder = SECTION_BUILDERS[key];
        if (builder) html += builder(tpl, sale);
    });
    return html;
}

/**
 * Safe fallback design, used only when a sale has no linked bill
 * design (e.g. a bill created before this feature existed, or the
 * rare case no active design exists at all). Shows everything.
 */
function buildFallbackTemplate(shopName) {
    return {
        shop_name: shopName || 'Shop',
        address: null,
        phone: null,
        header_text: null,
        footer_text: 'THANK YOU / VISIT AGAIN',
        paper_width: '80mm',
        font_size: 'medium',
        alignment: 'left',
        logo_path: null,
        show_logo: false,
        show_customer: true,
        show_bill_number: true,
        show_date: true,
        show_sku: false,
        show_quantity: true,
        show_unit: true,
        show_price: true,
        show_subtotal: true,
        show_discount: true,
        show_cash_received: true,
        show_change: true,
        show_qr: true,
        section_order: DEFAULT_SECTION_ORDER,
    };
}

/**
 * Applies paper width / font size / alignment classes to a
 * receipt container, matching the given design's settings.
 */
function applyReceiptContainerClasses(containerEl, tpl) {
    containerEl.className = 'receipt-content';
    containerEl.classList.add('paper-' + tpl.paper_width);
    containerEl.classList.add('font-' + tpl.font_size);
    containerEl.classList.add('align-' + tpl.alignment);
}