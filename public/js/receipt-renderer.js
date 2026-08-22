/* ============================================
   SHARED RECEIPT RENDERING LOGIC
   SINGLE SOURCE OF TRUTH

   Used by:
   - /billing
   - /admin/bills
   - Bill Designer preview
   - Test print

   IMPORTANT:
   TEMPLATE controls:
   - alignment
   - font size
   - font weight
   - line spacing
   - section spacing
   - visible fields
   - section order

   PRINTER controls:
   - physical receipt width

   ============================================ */

const DEFAULT_SECTION_ORDER = [
    'header',
    'bill_info',
    'customer_info',
    'items',
    'totals',
    'payment',
    'qr',
    'barcode',
    'footer'
];


/* =========================================================
   BASIC HELPERS
   ========================================================= */

function formatCurrency(amount) {
    const safeAmount = isNaN(amount) ? 0 : parseFloat(amount);

    return "Rs. " + Math.round(safeAmount).toLocaleString('en-IN');
}


function resolveLogoSrc(logoPath) {
    if (!logoPath) return '';

    return logoPath.startsWith('data:')
        ? logoPath
        : '/storage/' + logoPath;
}


function escapeReceiptHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, function (char) {
        return {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;'
        }[char];
    });
}


function getItemName(item) {
    return item.item_name || item.name || '';
}


function getItemSku(item) {
    if (item.product && item.product.sku) {
        return item.product.sku;
    }

    return item.sku || null;
}


function getItemUnitCode(item) {
    if (!item.unit) {
        return null;
    }

    return typeof item.unit === 'string'
        ? item.unit
        : item.unit.short_code;
}


/* =========================================================
   SECTION ORDER
   ========================================================= */

function getSectionOrder(tpl) {

    if (
        tpl &&
        Array.isArray(tpl.section_order) &&
        tpl.section_order.length > 0
    ) {
        return [...tpl.section_order];
    }

    return [...DEFAULT_SECTION_ORDER];
}


/* =========================================================
   EFFECTIVE TEMPLATE
   ========================================================= */

function resolveEffectiveTemplate(tpl, sale, shopNameFallback) {

    const base = tpl || buildFallbackTemplate(shopNameFallback);

    const effective = Object.assign({}, base);

    /*
     * The sale's QR choice is allowed to override
     * the template QR setting.
     */
    if (
        sale &&
        sale.show_qr !== null &&
        sale.show_qr !== undefined
    ) {
        effective.show_qr = Boolean(sale.show_qr);
    }

    return effective;
}


/* =========================================================
   HEADER
   ========================================================= */

function sectionHeaderHtml(tpl) {

    let html = '';

    if (tpl.show_logo) {

        if (tpl.logo_path) {

            html += `
                <div class="receipt-logo-circle">
                    <img
                        src="${escapeReceiptHtml(resolveLogoSrc(tpl.logo_path))}"
                        alt="Logo"
                    >
                </div>
            `;

        } else {

            html += `
                <div class="receipt-logo-circle">
                    <svg viewBox="0 0 24 24">
                        <path d="M6 8h12l-1 12H7L6 8z"/>
                        <path
                            d="M8 8a4 4 0 0 1 8 0"
                            fill="none"
                            stroke="#fff"
                            stroke-width="1.5"
                        />
                    </svg>
                </div>
            `;
        }
    }


    if (tpl.shop_name) {

        html += `
            <div class="receipt-shop-name">
                ${escapeReceiptHtml(tpl.shop_name)}
            </div>
        `;
    }


    if (tpl.address) {

        html += `
            <div class="receipt-meta">
                ${escapeReceiptHtml(tpl.address)}
            </div>
        `;
    }


    if (tpl.phone) {

        html += `
            <div class="receipt-meta">
                Phone: ${escapeReceiptHtml(tpl.phone)}
            </div>
        `;
    }


    if (tpl.vat_pan_number) {

        html += `
            <div class="receipt-meta">
                VAT/PAN: ${escapeReceiptHtml(tpl.vat_pan_number)}
            </div>
        `;
    }


    if (tpl.header_text) {

        html += `
            <div class="receipt-meta">
                ${escapeReceiptHtml(tpl.header_text)}
            </div>
        `;
    }


    html += `
        <hr class="receipt-divider">
    `;

    return html;
}


/* =========================================================
   BILL INFORMATION
   ========================================================= */

function sectionBillInfoHtml(tpl, sale) {

    if (
        !tpl.show_bill_number &&
        !tpl.show_date &&
        !tpl.show_cashier &&
        !tpl.show_payment_method
    ) {
        return '';
    }


    const createdAt = sale.created_at || '';

    let datePart = sale.date || '';
    let timePart = '';


    if (createdAt) {

        const parts = createdAt.split('T');

        datePart = parts[0] || datePart;

        if (parts[1]) {
            timePart = parts[1].substring(0, 5);
        }
    }


    let html = '';


    if (tpl.show_bill_number) {

        html += `
            <div class="receipt-meta-row">
                <span class="meta-label">Bill No</span>
                <span>: ${escapeReceiptHtml(sale.bill_number || '')}</span>
            </div>
        `;
    }


    if (tpl.show_date) {

        html += `
            <div class="receipt-meta-row">
                <span class="meta-label">Date</span>
                <span>: ${escapeReceiptHtml(datePart)}</span>
            </div>
        `;


        if (timePart) {

            html += `
                <div class="receipt-meta-row">
                    <span class="meta-label">Time</span>
                    <span>: ${escapeReceiptHtml(timePart)}</span>
                </div>
            `;
        }
    }


    if (tpl.show_cashier && sale.cashier_name) {

        html += `
            <div class="receipt-meta-row">
                <span class="meta-label">Cashier</span>
                <span>: ${escapeReceiptHtml(sale.cashier_name)}</span>
            </div>
        `;
    }


    if (tpl.show_payment_method && sale.payment_method) {

        html += `
            <div class="receipt-meta-row">
                <span class="meta-label">Payment</span>
                <span>: ${escapeReceiptHtml(
                    String(sale.payment_method).toUpperCase()
                )}</span>
            </div>
        `;
    }


    return html;
}


/* =========================================================
   CUSTOMER
   ========================================================= */

function sectionCustomerInfoHtml(tpl, sale) {

    if (!tpl.show_customer) {
        return '';
    }


    const name = sale.customer
        ? sale.customer.name
        : (sale.customer_name || null);


    if (!name) {
        return '';
    }


    return `
        <div class="receipt-meta-row">
            <span class="meta-label">Customer</span>
            <span>: ${escapeReceiptHtml(name)}</span>
        </div>
    `;
}


/* =========================================================
   ITEMS
   ========================================================= */

function sectionItemsHtml(tpl, sale) {

    let html = `
        <hr class="receipt-divider">

        <table class="receipt-items-table">

            <thead>
                <tr>

                    <th>Item</th>
    `;


    if (tpl.show_quantity) {
        html += `<th>Qty</th>`;
    }


    if (tpl.show_unit) {
        html += `<th>Unit</th>`;
    }


    if (tpl.show_price) {

        html += `
            <th>Price</th>
            <th>Total</th>
        `;
    }


    html += `
                </tr>
            </thead>

            <tbody>
    `;


    (sale.items || []).forEach(function (item) {

        let name = getItemName(item);

        const sku = getItemSku(item);


        if (tpl.show_sku && sku) {

            name += ` (${sku})`;
        }


        html += `
            <tr>

                <td>
                    ${escapeReceiptHtml(name)}
                </td>
        `;


        if (tpl.show_quantity) {

            html += `
                <td>
                    ${parseFloat(item.quantity)}
                </td>
            `;
        }


        if (tpl.show_unit) {

            html += `
                <td>
                    ${escapeReceiptHtml(
                        getItemUnitCode(item) || ''
                    )}
                </td>
            `;
        }


        if (tpl.show_price) {

            html += `
                <td>
                    ${formatCurrency(item.unit_price)
                        .replace('Rs. ', '')}
                </td>

                <td>
                    ${formatCurrency(item.line_total)
                        .replace('Rs. ', '')}
                </td>
            `;
        }


        html += `
            </tr>
        `;
    });


    html += `
            </tbody>

        </table>

        <hr class="receipt-divider">
    `;


    return html;
}


/* =========================================================
   TOTALS
   ========================================================= */

function sectionTotalsHtml(tpl, sale) {

    let html = '';


    if (tpl.show_subtotal) {

        html += `
            <div class="receipt-total-row">
                <span>Subtotal</span>
                <span>${formatCurrency(sale.subtotal)}</span>
            </div>
        `;
    }


    if (tpl.show_discount) {

        html += `
            <div class="receipt-total-row">
                <span>Discount</span>
                <span>${formatCurrency(sale.discount)}</span>
            </div>
        `;
    }


    if (tpl.calculate_vat) {

        const vatPercent =
            parseFloat(tpl.vat_percentage) || 0;


        const vatAmount =
            (
                parseFloat(sale.total) *
                vatPercent
            ) /
            (100 + vatPercent);


        html += `
            <div class="receipt-total-row">
                <span>VAT (${vatPercent}%)</span>
                <span>${formatCurrency(vatAmount)}</span>
            </div>
        `;
    }


    html += `
        <div class="receipt-total-row receipt-total-row--grand">
            <span>TOTAL</span>
            <span>${formatCurrency(sale.total)}</span>
        </div>
    `;


    return html;
}


/* =========================================================
   PAYMENT
   ========================================================= */

function sectionPaymentHtml(tpl, sale) {

    let html = '';


    if (tpl.show_payment_method && sale.payment_method) {

        html += `
            <div class="receipt-total-row">
                <span>Payment Method</span>
                <span>
                    ${escapeReceiptHtml(
                        String(sale.payment_method).toUpperCase()
                    )}
                </span>
            </div>
        `;
    }


    if (tpl.show_cash_received) {

        html += `
            <div class="receipt-total-row">
                <span>Cash</span>
                <span>${formatCurrency(sale.cash_received)}</span>
            </div>
        `;
    }


    if (tpl.show_change) {

        html += `
            <div class="receipt-total-row">
                <span>Change</span>
                <span>${formatCurrency(sale.change_amount)}</span>
            </div>
        `;
    }


    if (
        sale.due_amount &&
        parseFloat(sale.due_amount) > 0
    ) {

        html += `
            <div
                class="receipt-total-row"
                style="font-weight:700;"
            >
                <span>Due</span>
                <span>${formatCurrency(sale.due_amount)}</span>
            </div>
        `;
    }


    return html;
}


/* =========================================================
   QR
   ========================================================= */

function sectionQrHtml(tpl, sale) {

    if (!tpl.show_qr) {
        return '';
    }


    const hasRealQr =
        typeof paymentQrImageUrl !== 'undefined' &&
        paymentQrImageUrl;


    const qrVisual = hasRealQr

        ? `
            <img
                src="${escapeReceiptHtml(paymentQrImageUrl)}"
                class="receipt-qr-image"
            >
        `

        : `
            <div class="receipt-qr-placeholder"></div>
        `;


    return `
        <div class="receipt-qr-section">

            <div
                class="receipt-qr-label"
                style="font-weight:700; margin-bottom:4px;"
            >
                Scan to Pay
            </div>

            ${qrVisual}

            <div class="receipt-qr-label">
                ${formatCurrency(sale.total)}
                ${hasRealQr ? '' : ' (demo)'}
            </div>

        </div>
    `;
}


/* =========================================================
   BARCODE
   ========================================================= */

function sectionBarcodeHtml(tpl, sale) {

    if (!tpl.show_barcode) {
        return '';
    }


    return `
        <div class="receipt-barcode-section">

            <svg
                class="canvas-barcode-svg"
                data-value="${escapeReceiptHtml(
                    sale.bill_number || ''
                )}"
            ></svg>

        </div>
    `;
}


/* =========================================================
   FOOTER
   ========================================================= */

function sectionFooterHtml(tpl) {

    if (!tpl.footer_text) {
        return '';
    }


    return `
        <div class="receipt-footer">
            ${escapeReceiptHtml(tpl.footer_text)}
            <span class="footer-smiley">☺</span>
        </div>
    `;
}


/* =========================================================
   SECTION MAP
   ========================================================= */

const SECTION_BUILDERS = {

    header: sectionHeaderHtml,

    bill_info: sectionBillInfoHtml,

    customer_info: sectionCustomerInfoHtml,

    items: sectionItemsHtml,

    totals: sectionTotalsHtml,

    payment: sectionPaymentHtml,

    qr: sectionQrHtml,

    barcode: sectionBarcodeHtml,

    footer: sectionFooterHtml

};


/* =========================================================
   BUILD RECEIPT
   ========================================================= */

function buildReceiptHtml(tpl, sale, order) {

    let html = '';

    let sections = order && order.length
        ? [...order]
        : getSectionOrder(tpl);


    /*
     * Barcode is template-controlled.
     * If enabled but missing from an old section_order,
     * insert it before footer.
     */

    if (
        tpl.show_barcode &&
        !sections.includes('barcode')
    ) {

        const footerIndex =
            sections.indexOf('footer');


        if (footerIndex === -1) {

            sections.push('barcode');

        } else {

            sections.splice(
                footerIndex,
                0,
                'barcode'
            );
        }
    }


    sections.forEach(function (key) {

        const builder = SECTION_BUILDERS[key];

        if (!builder) {
            return;
        }


        html += builder(tpl, sale);
    });


    return html;
}


/* =========================================================
   FALLBACK TEMPLATE
   ========================================================= */

function buildFallbackTemplate(shopName) {

    return {

        shop_name: shopName || 'Shop',

        address: null,

        phone: null,

        vat_pan_number: null,

        header_text: null,

        footer_text: 'THANK YOU / VISIT AGAIN',

        paper_width: '80mm',

        font_size: 'medium',

        font_weight: 'normal',

        alignment: 'left',

        line_spacing: 'normal',

        section_spacing: 'normal',

        logo_path: null,

        show_logo: false,

        show_customer: true,

        show_bill_number: true,

        show_date: true,

        show_cashier: false,

        show_payment_method: true,

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

        calculate_vat: false,

        vat_percentage: 0,

        section_order: DEFAULT_SECTION_ORDER

    };
}


/* =========================================================
   TEMPLATE ALIGNMENT
   ========================================================= */

function normalizeReceiptAlignment(value) {

    const alignment =
        String(value || 'left').toLowerCase().trim();


    if (
        alignment === 'center' ||
        alignment === 'right' ||
        alignment === 'left'
    ) {
        return alignment;
    }


    return 'left';
}


/* =========================================================
   TEMPLATE FONT SIZE
   ========================================================= */

function resolveTemplateFontSize(tpl) {

    /*
     * If Bill Designer stores a numeric value,
     * use it directly.
     */

    if (
        tpl &&
        tpl.font_size !== null &&
        tpl.font_size !== undefined &&
        tpl.font_size !== ''
    ) {

        const raw = tpl.font_size;


        if (
            typeof raw === 'number' ||
            /^[0-9]+(\.[0-9]+)?px$/i.test(String(raw))
        ) {

            return String(raw).includes('px')
                ? String(raw)
                : String(raw) + 'px';
        }


        switch (String(raw).toLowerCase()) {

            case 'small':
                return '13px';

            case 'medium':
                return '16px';

            case 'large':
                return '20px';

            case 'xlarge':
            case 'extra-large':
                return '24px';

            default:
                return String(raw);
        }
    }


    return '16px';
}


/* =========================================================
   TEMPLATE FONT WEIGHT
   ========================================================= */

function resolveTemplateFontWeight(tpl) {

    if (
        tpl &&
        tpl.font_weight !== undefined &&
        tpl.font_weight !== null &&
        tpl.font_weight !== ''
    ) {

        return String(tpl.font_weight);
    }


    return 'normal';
}


/* =========================================================
   TEMPLATE LINE SPACING
   ========================================================= */

function resolveTemplateLineSpacing(tpl) {

    const value =
        String(
            tpl && tpl.line_spacing
                ? tpl.line_spacing
                : 'normal'
        ).toLowerCase();


    return value;
}


/* =========================================================
   TEMPLATE SECTION SPACING
   ========================================================= */

function resolveTemplateSectionSpacing(tpl) {

    const value =
        String(
            tpl && tpl.section_spacing
                ? tpl.section_spacing
                : 'normal'
        ).toLowerCase();


    return value;
}


/* =========================================================
   RECEIPT CONTAINER
   ========================================================= */

/*
 * VERY IMPORTANT:
 *
 * DO NOT use printerVars.alignment here.
 *
 * DO NOT use printerVars.font_size here.
 *
 * DO NOT use printerVars.font_weight here.
 *
 * These were causing /billing to look different from
 * Bill Designer/Admin Bills.
 *
 * Template controls visual design.
 *
 * Printer settings only determine physical width.
 */

function applyReceiptContainerClasses(
    containerEl,
    tpl,
    printerVars
) {

    const vars = printerVars || {};


    /*
     * -----------------------------------------------------
     * PHYSICAL WIDTH
     * -----------------------------------------------------
     *
     * Printer setting is allowed to control ONLY width.
     */

    const physicalWidth =
        vars.width ||
        tpl.paper_width ||
        '72mm';


    /*
     * -----------------------------------------------------
     * RESET
     * -----------------------------------------------------
     */

    containerEl.className = 'receipt-content';


    /*
     * -----------------------------------------------------
     * SECTION / LINE SPACING
     * -----------------------------------------------------
     */

    containerEl.classList.add(
        'spacing-line-' +
        resolveTemplateLineSpacing(tpl)
    );


    containerEl.classList.add(
        'spacing-section-' +
        resolveTemplateSectionSpacing(tpl)
    );


    /*
     * -----------------------------------------------------
     * PHYSICAL WIDTH
     * -----------------------------------------------------
 */

    containerEl.style.width = physicalWidth;

    containerEl.style.maxWidth = physicalWidth;

    containerEl.style.boxSizing = 'border-box';


    /*
     * -----------------------------------------------------
     * FONT
     * -----------------------------------------------------
     */

    containerEl.style.fontFamily =
        'Arial, Helvetica, sans-serif';


    containerEl.style.fontSize =
        resolveTemplateFontSize(tpl);


    containerEl.style.fontWeight =
        resolveTemplateFontWeight(tpl);


    /*
     * -----------------------------------------------------
     * ALIGNMENT
     * -----------------------------------------------------
     *
     * THIS IS THE IMPORTANT FIX.
     *
     * Use Bill Template alignment.
     */

    containerEl.style.textAlign =
        normalizeReceiptAlignment(
            tpl.alignment
        );


    /*
     * -----------------------------------------------------
     * MARGINS
     * -----------------------------------------------------
     *
     * Printer margins can affect physical printable area,
     * but they must NOT change the template's alignment.
     */

    containerEl.style.paddingLeft =
        vars.margin_left || '3mm';


    containerEl.style.paddingRight =
        vars.margin_right || '3mm';


    containerEl.style.paddingTop = '2mm';

    containerEl.style.paddingBottom = '2mm';


    /*
     * -----------------------------------------------------
     * IMPORTANT
     * -----------------------------------------------------
     *
     * Prevent inherited text alignment from printer CSS.
     */

    containerEl.style.setProperty(
        'text-align',
        normalizeReceiptAlignment(tpl.alignment),
        'important'
    );
}


/* =========================================================
   MAIN RENDER FUNCTION
   ========================================================= */

function renderReceiptForTemplate(
    tpl,
    sale,
    order
) {

    const effectiveTemplate =
        tpl || buildFallbackTemplate('Shop');


    const finalOrder =
        order || getSectionOrder(effectiveTemplate);


    const html =
        buildReceiptHtml(
            effectiveTemplate,
            sale,
            finalOrder
        );


    /*
     * Barcode generation happens AFTER the HTML
     * has been inserted into the DOM.
     */

    if (effectiveTemplate.show_barcode) {

        setTimeout(function () {

            document
                .querySelectorAll('.canvas-barcode-svg')
                .forEach(function (svg) {

                    const value =
                        svg.getAttribute('data-value');


                    if (
                        value &&
                        typeof JsBarcode !== 'undefined'
                    ) {

                        JsBarcode(
                            svg,
                            value,
                            {
                                format: 'CODE128',
                                displayValue: true,
                                fontSize: 10,
                                height: 30,
                                margin: 2
                            }
                        );
                    }
                });

        }, 0);
    }


    return html;
}