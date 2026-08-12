const templateToggleFields = [
    'show_logo', 'show_customer', 'show_bill_number', 'show_date', 'show_sku',
    'show_quantity', 'show_unit', 'show_price', 'show_subtotal', 'show_discount',
    'show_cash_received', 'show_change', 'show_qr',
];

const SECTION_LABELS = {
    header: 'Header (Logo / Shop Info)',
    bill_info: 'Bill Information',
    customer_info: 'Customer Info',
    items: 'Items Table',
    totals: 'Totals',
    payment: 'Payment',
    qr: 'QR Code',
    footer: 'Footer',
};

let currentSectionOrder = [];
let pendingLogoPreviewUrl = null;
let draggedSectionKey = null;

function toPascalCase(snakeStr) {
    return snakeStr
        .split('_')
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join('');
}

// Fallback sample data, used only if no real sale exists yet
const sampleSaleForPreview = {
    bill_number: '000125',
    created_at: new Date().toISOString(),
    customer: { name: 'Walk-in Customer' },
    subtotal: 470,
    discount: 20,
    total: 450,
    cash_received: 500,
    change_amount: 50,
    items: [
        { item_name: 'Rice', quantity: 2, unit_price: 120, line_total: 240, product: { sku: 'RIC-001' }, unit: { short_code: 'kg' } },
        { item_name: 'Item 2', quantity: 1, unit_price: 130, line_total: 130, product: null, unit: null },
        { item_name: 'Coca Cola', quantity: 1, unit_price: 100, line_total: 100, product: { sku: 'BEV-014' }, unit: { short_code: 'pcs' } },
    ],
};

// ---------- Reading the live form into a template-shaped object ----------

function buildTemplateObjectFromForm() {
    const form = document.getElementById('templateForm');

    const tpl = {
        shop_name: document.getElementById('tplShopNameInput').value,
        address: document.getElementById('tplAddressInput').value,
        phone: document.getElementById('tplPhoneInput').value,
        header_text: document.getElementById('tplHeaderTextInput').value,
        footer_text: document.getElementById('tplFooterTextInput').value,
        paper_width: document.getElementById('tplPaperWidthInput').value,
        font_size: document.getElementById('tplFontSizeInput').value,
        alignment: document.getElementById('tplAlignmentInput').value,
        logo_path: pendingLogoPreviewUrl || form.dataset.currentLogoPath || null,
    };

    templateToggleFields.forEach((field) => {
        const el = document.getElementById('tpl' + toPascalCase(field) + 'Input');
        tpl[field] = el ? el.checked : false;
    });

    return tpl;
}

// ---------- Left column: section order list ----------

function renderSectionList(order) {
    const list = document.getElementById('designerSectionList');
    list.innerHTML = '';
    order.forEach((key, index) => {
        const li = document.createElement('li');
        li.className = 'designer-section-item';
        li.setAttribute('draggable', 'true');
        li.dataset.sectionKey = key;
        li.innerHTML = `
            <span class="drag-handle">⠿</span>
            <span class="section-number">${index + 1}</span>
            <span>${SECTION_LABELS[key] || key}</span>
        `;
        list.appendChild(li);
    });
}

function setupSectionDragAndDrop() {
    const list = document.getElementById('designerSectionList');

    list.addEventListener('dragstart', (e) => {
        const li = e.target.closest('.designer-section-item');
        if (!li) return;
        draggedSectionKey = li.dataset.sectionKey;
        li.classList.add('dragging');
    });

    list.addEventListener('dragend', (e) => {
        const li = e.target.closest('.designer-section-item');
        if (li) li.classList.remove('dragging');
    });

    list.addEventListener('dragover', (e) => {
        e.preventDefault();
        const li = e.target.closest('.designer-section-item');
        if (!li || li.dataset.sectionKey === draggedSectionKey) return;

        const rect = li.getBoundingClientRect();
        const isAfter = (e.clientY - rect.top) > (rect.height / 2);
        li.classList.toggle('drop-before', !isAfter);
        li.classList.toggle('drop-after', isAfter);
    });

    list.addEventListener('dragleave', (e) => {
        const li = e.target.closest('.designer-section-item');
        if (li) li.classList.remove('drop-before', 'drop-after');
    });

    list.addEventListener('drop', (e) => {
        e.preventDefault();
        const li = e.target.closest('.designer-section-item');
        if (!li || !draggedSectionKey) return;

        const targetKey = li.dataset.sectionKey;
        const isAfter = li.classList.contains('drop-after');
        li.classList.remove('drop-before', 'drop-after');

        if (targetKey === draggedSectionKey) return;

        currentSectionOrder = currentSectionOrder.filter((k) => k !== draggedSectionKey);
        const targetIndex = currentSectionOrder.indexOf(targetKey);
        const insertAt = isAfter ? targetIndex + 1 : targetIndex;
        currentSectionOrder.splice(insertAt, 0, draggedSectionKey);

        draggedSectionKey = null;
        renderSectionList(currentSectionOrder);
        renderDesignerPreview();
    });
}

// ---------- Center column: live preview ----------

function renderDesignerPreview() {
    const tpl = buildTemplateObjectFromForm();
    const sale = latestSaleForPreview || sampleSaleForPreview;
    const container = document.getElementById('designerPreviewContent');

    applyReceiptContainerClasses(container, tpl);
    container.classList.add('designer-paper');

    let html = '';
    if (!latestSaleForPreview) {
        html += `<div class="receipt-sample-data-note">No real bills yet — showing sample data</div>`;
    }
    html += buildReceiptHtml(tpl, sale, currentSectionOrder);

    container.innerHTML = html + '<div class="receipt-torn-edge"></div>';
}

// ---------- Modal open handlers ----------

function openAddTemplateModal() {
    const form = document.getElementById('templateForm');

    form.setAttribute('action', form.dataset.storeUrl);
    document.getElementById('templateMethodField').innerHTML = '';
    form.reset();

    document.getElementById('tplCurrentLogoWrapper').style.display = 'none';
    form.dataset.currentLogoPath = '';
    pendingLogoPreviewUrl = null;

    currentSectionOrder = DEFAULT_SECTION_ORDER.slice();
    renderSectionList(currentSectionOrder);
    renderDesignerPreview();

    $('#templateModal').modal('show');
}

function openEditTemplateModal(template) {
    const form = document.getElementById('templateForm');

    form.setAttribute('action', '/admin/bill-templates/' + template.id);
    document.getElementById('templateMethodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';

    document.getElementById('tplNameInput').value = template.name ?? '';
    document.getElementById('tplPaperWidthInput').value = template.paper_width ?? '80mm';
    document.getElementById('tplFontSizeInput').value = template.font_size ?? 'medium';
    document.getElementById('tplAlignmentInput').value = template.alignment ?? 'left';
    document.getElementById('tplShopNameInput').value = template.shop_name ?? '';
    document.getElementById('tplPhoneInput').value = template.phone ?? '';
    document.getElementById('tplAddressInput').value = template.address ?? '';
    document.getElementById('tplHeaderTextInput').value = template.header_text ?? '';
    document.getElementById('tplFooterTextInput').value = template.footer_text ?? '';

    templateToggleFields.forEach((field) => {
        const el = document.getElementById('tpl' + toPascalCase(field) + 'Input');
        if (el) el.checked = Boolean(template[field]);
    });

    document.getElementById('tplLogoInput').value = '';
    pendingLogoPreviewUrl = null;
    form.dataset.currentLogoPath = template.logo_path || '';

    const logoWrapper = document.getElementById('tplCurrentLogoWrapper');
    const logoImg = document.getElementById('tplCurrentLogo');
    if (template.logo_path) {
        logoImg.src = '/storage/' + template.logo_path;
        logoWrapper.style.display = 'block';
    } else {
        logoWrapper.style.display = 'none';
    }

    currentSectionOrder = getSectionOrder(template);
    renderSectionList(currentSectionOrder);
    renderDesignerPreview();

    $('#templateModal').modal('show');
}

// ---------- List page's standalone "Preview" button/modal ----------

function openPreviewModal(template) {
    document.getElementById('previewTemplateName').textContent = template.name;

    const sale = latestSaleForPreview || sampleSaleForPreview;
    const isSampleData = !latestSaleForPreview;
    const order = getSectionOrder(template);

    const container = document.getElementById('previewReceiptContent');
    applyReceiptContainerClasses(container, template);
    container.classList.add('designer-paper');

    let html = '';
    if (isSampleData) {
        html += `<div class="receipt-sample-data-note">No real bills yet — showing sample data</div>`;
    }
    html += buildReceiptHtml(template, sale, order);

    container.innerHTML = html + '<div class="receipt-torn-edge"></div>';

    $('#previewModal').modal('show');
}

// ---------- Live-update wiring ----------

setupSectionDragAndDrop();

const templateFormEl = document.getElementById('templateForm');
if (templateFormEl) {
    templateFormEl.addEventListener('submit', () => {
        document.getElementById('tplSectionOrderInput').value = JSON.stringify(currentSectionOrder);
    });

    templateFormEl.addEventListener('input', (e) => {
        if (e.target.id === 'tplLogoInput') return;
        renderDesignerPreview();
    });
    templateFormEl.addEventListener('change', (e) => {
        if (e.target.id === 'tplLogoInput') return;
        renderDesignerPreview();
    });

    document.getElementById('tplLogoInput').addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (!file) {
            pendingLogoPreviewUrl = null;
            renderDesignerPreview();
            return;
        }
        const reader = new FileReader();
        reader.onload = () => {
            pendingLogoPreviewUrl = reader.result;
            renderDesignerPreview();
        };
        reader.readAsDataURL(file);
    });
}