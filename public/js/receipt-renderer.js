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

/**
 * Builds the template actually used to render one sale's receipt:
 * the given template (or fallback), with that sale's own show_qr
 * override applied if one was chosen at checkout time.
 */
function resolveEffectiveTemplate(tpl, sale, shopNameFallback) {
    const base = tpl || buildFallbackTemplate(shopNameFallback);
    const effective = Object.assign({}, base);

    if (sale && sale.show_qr !== null && sale.show_qr !== undefined) {
        effective.show_qr = Boolean(sale.show_qr);
    }

    return effective;
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
    if (tpl.vat_pan_number) html += `<div class="receipt-meta">VAT/PAN: ${tpl.vat_pan_number}</div>`;
    if (tpl.header_text) html += `<div class="receipt-meta">${tpl.header_text}</div>`;
    html += `<hr class="receipt-divider">`;
    return html;
}
function sectionBillInfoHtml(tpl, sale) {
    if (!tpl.show_bill_number && !tpl.show_date && !tpl.show_cashier) return '';
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
    if (tpl.show_cashier && sale.cashier_name) {
        html += `<div class="receipt-meta-row"><span class="meta-label">Cashier</span><span>: ${sale.cashier_name}</span></div>`;
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
    if (tpl.calculate_vat) {
        const vatPercent = parseFloat(tpl.vat_percentage) || 0;
        // Informational only: extracts the VAT portion already
        // included in the actual charged total. Never changes
        // the real total/cash/change figures.
        const vatAmount = (parseFloat(sale.total) * vatPercent) / (100 + vatPercent);
        html += `<div class="receipt-total-row"><span>VAT (${vatPercent}%)</span><span>${formatCurrency(vatAmount)}</span></div>`;
    }
    html += `<div class="receipt-total-row receipt-total-row--grand"><span>TOTAL</span><span>${formatCurrency(sale.total)}</span></div>`;
    return html;
}

function sectionPaymentHtml(tpl, sale) {
    let html = '';
    if (tpl.show_payment_method && sale.payment_method) {
        html += `<div class="receipt-total-row"><span>Payment Method</span><span>${sale.payment_method.toUpperCase()}</span></div>`;
    }
    if (tpl.show_cash_received) {
        html += `<div class="receipt-total-row"><span>Cash</span><span>${formatCurrency(sale.cash_received)}</span></div>`;
    }
    if (tpl.show_change) {
        html += `<div class="receipt-total-row"><span>Change</span><span>${formatCurrency(sale.change_amount)}</span></div>`;
    }
    if (sale.due_amount && parseFloat(sale.due_amount) > 0) {
        html += `<div class="receipt-total-row" style="font-weight:700; color:#b91c1c;"><span>Due</span><span>${formatCurrency(sale.due_amount)}</span></div>`;
    }
    return html;
}

function sectionQrHtml(tpl, sale) {
    if (!tpl.show_qr) return '';

    const hasRealQr = typeof paymentQrImageUrl !== 'undefined' && paymentQrImageUrl;
    const qrVisual = hasRealQr
        ? `<img src="${paymentQrImageUrl}" class="receipt-qr-image">`
        : `<div class="receipt-qr-placeholder"></div>`;

    return `<div class="receipt-qr-section">
        <div class="receipt-qr-label" style="font-weight:700; margin-bottom:4px;">Scan to Pay</div>
        ${qrVisual}
        <div class="receipt-qr-label">${formatCurrency(sale.total)}${hasRealQr ? '' : ' (demo)'}</div>
    </div>`;
}

function sectionFooterHtml(tpl) {
    if (!tpl.footer_text) return '';
    return `<div class="receipt-footer">${tpl.footer_text}<span class="footer-smiley">☺</span></div>`;
}

function sectionBarcodeHtml(tpl, sale) {
    if (!tpl.show_barcode) return '';
    return `<div class="receipt-barcode-section" style="text-align:center; margin:6px 0;">
        <svg class="canvas-barcode-svg" data-value="${sale.bill_number || ''}" style="width:90%; max-width:220px;"></svg>
    </div>`;
}

const SECTION_BUILDERS = {
    header: sectionHeaderHtml,
    bill_info: sectionBillInfoHtml,
    customer_info: sectionCustomerInfoHtml,
    items: sectionItemsHtml,
    totals: sectionTotalsHtml,
    payment: sectionPaymentHtml,
    qr: sectionQrHtml,
    barcode: sectionBarcodeHtml,
    footer: sectionFooterHtml,
};

function buildReceiptHtml(tpl, sale, order) {
    let html = '';
    let sections = order || DEFAULT_SECTION_ORDER;

    // "Show Barcode on Receipt" (tpl.show_barcode) is a template-level
    // toggle, independent of section_order. If the template wants a
    // barcode and the section list doesn't already place one, add it
    // just before the footer so it still renders without requiring
    // every existing saved template to be re-saved with a new order.
    if (tpl.show_barcode && sections.indexOf('barcode') === -1) {
        sections = sections.slice();
        const footerIndex = sections.indexOf('footer');
        if (footerIndex === -1) {
            sections.push('barcode');
        } else {
            sections.splice(footerIndex, 0, 'barcode');
        }
    }

    sections.forEach((key) => {
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
        show_barcode: false,
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
function applyReceiptContainerClasses(containerEl, tpl, paperWidthMm) {
    containerEl.className = 'receipt-content';
    containerEl.classList.add('font-' + (tpl.font_size || 'medium'));
    containerEl.classList.add('align-' + (tpl.alignment || 'left'));
    containerEl.classList.add('spacing-line-' + (tpl.line_spacing || 'normal'));
    containerEl.classList.add('spacing-section-' + (tpl.section_spacing || 'normal'));

    const width = paperWidthMm || (tpl.paper_width === '58mm' ? 58 : 80);
    containerEl.style.width = width + 'mm';
    containerEl.style.maxWidth = width + 'mm';
}


/* ============================================
   CANVAS-BASED BILL DESIGNS (freeform layout)
   ============================================ */

function resolveDynamicToken(token, tpl, sale) {
    const parts = (sale.created_at || '').split('T');
    const datePart = parts[0] || sale.date || '';
    const timePart = parts[1] ? parts[1].substring(0, 5) : '';
    const customerName = sale.customer ? sale.customer.name : (sale.customer_name || 'Walk-in');

    const map = {
        shop_name: tpl.shop_name || 'Shop Name',
        shop_address: tpl.address || '',
        shop_phone: tpl.phone || '',
        bill_number: sale.bill_number || '',
        date: datePart,
        time: timePart,
        customer: customerName,
        cashier: sale.cashier_name || 'Admin',
        subtotal: formatCurrency(sale.subtotal),
        discount: formatCurrency(sale.discount),
        total: formatCurrency(sale.total),
        payment_method: (sale.payment_method || 'cash').toUpperCase(),
        cash_received: formatCurrency(sale.cash_received),
        change: formatCurrency(sale.change_amount),
    };

    return map.hasOwnProperty(token) ? map[token] : '';
}

function replaceDynamicTokens(text, tpl, sale) {
    return (text || '').replace(/\{\{\s*([a-z_]+)\s*\}\}/gi, (match, token) => {
        return resolveDynamicToken(token.toLowerCase(), tpl, sale);
    });
}

function renderCanvasElementContent(el, tpl, sale) {
    switch (el.type) {
        case 'text':
        case 'dynamic_field': {
            const text = replaceDynamicTokens(el.content || '', tpl, sale);
            const align = el.align || 'left';
            const weight = el.bold ? '700' : '400';
            const fontSize = (el.font_size || 12) + 'px';
            return `<div style="width:100%; height:100%; text-align:${align}; font-weight:${weight}; font-size:${fontSize}; white-space:pre-wrap; line-height:1.3; overflow:hidden;">${text}</div>`;
        }
        case 'logo':
            return tpl.logo_path ? `<img src="${resolveLogoSrc(tpl.logo_path)}" style="width:100%; height:100%; object-fit:contain;">` : '';
        case 'image':
            return el.src ? `<img src="${el.src}" style="width:100%; height:100%; object-fit:contain;">` : '';
        case 'line': {
            const thickness = el.thickness || 1;
            return `<div style="width:100%; border-top:${thickness}px ${el.style || 'solid'} #000; margin-top:${(el.height || 2) / 2}mm;"></div>`;
        }
        case 'rectangle': {
            const border = el.border_width ? `${el.border_width}px solid ${el.border_color || '#000'}` : 'none';
            return `<div style="width:100%; height:100%; border:${border}; background:${el.fill || 'transparent'}; border-radius:${el.radius || 0}px; box-sizing:border-box;"></div>`;
        }
        case 'spacer':
            return '';
        case 'qr': {
            const hasRealQr = typeof paymentQrImageUrl !== 'undefined' && paymentQrImageUrl;
            return hasRealQr
                ? `<img src="${paymentQrImageUrl}" style="width:100%; height:100%; object-fit:contain;">`
                : `<div class="receipt-qr-placeholder" style="width:100%; height:100%; margin:0;"></div>`;
        }
        case 'barcode':
            return `<svg class="canvas-barcode-svg" data-value="${sale.bill_number || ''}" style="width:100%; height:100%;"></svg>`;
        case 'table_items': {
            let rows = '';
            (sale.items || []).forEach((item) => {
                const name = item.item_name || item.name;
                const qty = item.quantity !== undefined ? parseFloat(item.quantity) : '';
                rows += `<tr>
                    <td style="text-align:left; padding:2px 4px;">${name}</td>
                    <td style="text-align:right; padding:2px 4px;">${qty}</td>
                    <td style="text-align:right; padding:2px 4px;">${formatCurrency(item.unit_price).replace('Rs. ', '')}</td>
                    <td style="text-align:right; padding:2px 4px;">${formatCurrency(item.line_total).replace('Rs. ', '')}</td>
                </tr>`;
            });
            return `<table style="width:100%; border-collapse:collapse; font-size:${(el.font_size || 11)}px;">
                <thead><tr style="border-bottom:1.5px solid #000; font-weight:700;">
                    <td style="text-align:left; padding:2px 4px;">Item</td>
                    <td style="text-align:right; padding:2px 4px;">Qty</td>
                    <td style="text-align:right; padding:2px 4px;">Price</td>
                    <td style="text-align:right; padding:2px 4px;">Total</td>
                </tr></thead>
                <tbody>${rows}</tbody>
            </table>`;
        }
        default:
            return '';
    }
}

function renderCanvasElementHtml(el, tpl, sale) {
    if (el.visible === false) return '';
    const inner = renderCanvasElementContent(el, tpl, sale);
    const style = `position:absolute; left:${el.x}mm; top:${el.y}mm; width:${el.width}mm; ${el.height ? 'height:' + el.height + 'mm;' : ''}`;
    return `<div style="${style}">${inner}</div>`;
}

function buildReceiptFromCanvasLayout(tpl, sale) {
    const layout = tpl.canvas_layout;
    if (!layout || !layout.elements) return '';

    const visibleElements = layout.elements.filter((el) => el.visible !== false);
    const maxBottom = visibleElements.reduce((max, el) => Math.max(max, (el.y || 0) + (el.height || 8)), 0);

    const elementsHtml = visibleElements
        .map((el) => renderCanvasElementHtml(el, tpl, sale))
        .join('');

    return `<div class="canvas-receipt-wrapper" style="position:relative; width:100%; height:${maxBottom + 5}mm;">${elementsHtml}</div>`;
}

/**
 * Single entry point for rendering any receipt. Every consumer
 * (Designer preview, /billing, /admin/bills, Test Print) calls
 * this instead of buildReceiptHtml() directly, so the renderer
 * automatically picks canvas-based or toggle-based per template.
 */
function renderReceiptForTemplate(tpl, sale, order) {
    if (tpl && tpl.canvas_layout && tpl.canvas_layout.elements && tpl.canvas_layout.elements.length > 0) {
        const html = buildReceiptFromCanvasLayout(tpl, sale);
        setTimeout(() => {
            document.querySelectorAll('.canvas-barcode-svg').forEach((svg) => {
                const value = svg.getAttribute('data-value');
                if (value && typeof JsBarcode !== 'undefined') {
                    JsBarcode(svg, value, { format: 'CODE128', displayValue: true, fontSize: 10, height: 30, margin: 2 });
                }
            });
        }, 0);
        return html;
    }

    const html = buildReceiptHtml(tpl, sale, order || getSectionOrder(tpl));
    if (tpl && tpl.show_barcode) {
        setTimeout(() => {
            document.querySelectorAll('.canvas-barcode-svg').forEach((svg) => {
                const value = svg.getAttribute('data-value');
                if (value && typeof JsBarcode !== 'undefined') {
                    JsBarcode(svg, value, { format: 'CODE128', displayValue: true, fontSize: 10, height: 30, margin: 2 });
                }
            });
        }, 0);
    }
    return html;
}