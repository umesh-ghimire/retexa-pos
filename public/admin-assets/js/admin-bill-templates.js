const templateToggleFields = [
    'show_logo', 'show_customer', 'show_cashier', 'show_bill_number', 'show_date', 'show_sku',
    'show_quantity', 'show_unit', 'show_price', 'show_subtotal', 'show_discount',
    'show_payment_method', 'show_cash_received', 'show_change', 'show_qr', 'calculate_vat',
];

const sampleSaleForPreview = {
    bill_number: '000125',
    created_at: new Date().toISOString(),
    customer: { name: 'Walk-in Customer' },
    cashier_name: 'Admin',
    payment_method: 'cash',
    subtotal: 470,
    discount: 20,
    total: 450,
    cash_received: 500,
    change_amount: 50,
    due_amount: 0,
    items: [
        { item_name: 'Rice', quantity: 2, unit_price: 120, line_total: 240, product: { sku: 'RIC-001' }, unit: { short_code: 'kg' } },
        { item_name: 'Item 2', quantity: 1, unit_price: 130, line_total: 130, product: null, unit: null },
        { item_name: 'Coca Cola', quantity: 1, unit_price: 100, line_total: 100, product: { sku: 'BEV-014' }, unit: { short_code: 'pcs' } },
    ],
};

function toPascalCase(snakeStr) {
    return snakeStr
        .split('_')
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join('');
}

function openAddTemplateModal() {
    const form = document.getElementById('templateForm');

    document.getElementById('templateModalTitle').textContent = 'Add Bill Design';
    form.setAttribute('action', form.dataset.storeUrl);
    document.getElementById('templateMethodField').innerHTML = '';
    form.reset();

    document.getElementById('tplCurrentLogoWrapper').style.display = 'none';

    $('#templateModal').modal('show');
}

function openEditTemplateModal(template) {
    const form = document.getElementById('templateForm');

    document.getElementById('templateModalTitle').textContent = 'Edit Bill Design';
    form.setAttribute('action', '/admin/bill-templates/' + template.id);
    document.getElementById('templateMethodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';

    document.getElementById('tplNameInput').value = template.name ?? '';
    document.getElementById('tplShopNameInput').value = template.shop_name ?? '';
    document.getElementById('tplPhoneInput').value = template.phone ?? '';
    document.getElementById('tplAddressInput').value = template.address ?? '';
    document.getElementById('tplVatPanInput').value = template.vat_pan_number ?? '';
    document.getElementById('tplHeaderTextInput').value = template.header_text ?? '';
    document.getElementById('tplFooterTextInput').value = template.footer_text ?? '';
    document.getElementById('tplVatPercentageInput').value = template.vat_percentage ?? 13;
    document.getElementById('tplFontSizeInput').value = template.font_size ?? 'medium';
    document.getElementById('tplAlignmentInput').value = template.alignment ?? 'left';
    document.getElementById('tplLineSpacingInput').value = template.line_spacing ?? 'normal';
    document.getElementById('tplSectionSpacingInput').value = template.section_spacing ?? 'normal';

    templateToggleFields.forEach((field) => {
        const el = document.getElementById('tpl' + toPascalCase(field) + 'Input');
        if (el) el.checked = Boolean(template[field]);
    });

    document.getElementById('tplLogoInput').value = '';

    const logoWrapper = document.getElementById('tplCurrentLogoWrapper');
    const logoImg = document.getElementById('tplCurrentLogo');
    if (template.logo_path) {
        logoImg.src = '/storage/' + template.logo_path;
        logoWrapper.style.display = 'block';
    } else {
        logoWrapper.style.display = 'none';
    }

    $('#templateModal').modal('show');
}

function openPreviewModal(template) {
    document.getElementById('previewTemplateName').textContent = template.name;

    const sale = latestSaleForPreview || sampleSaleForPreview;
    const isSampleData = !latestSaleForPreview;
    const order = getSectionOrder(template);

    const container = document.getElementById('previewReceiptContent');
    applyReceiptContainerClasses(container, template, printerPaperWidthMm);
    container.classList.add('designer-paper');

    let html = '';
    if (isSampleData) {
        html += `<div class="receipt-sample-data-note">No real bills yet — showing sample data</div>`;
    }
    html += renderReceiptForTemplate(template, sale, order);

    container.innerHTML = html + '<div class="receipt-torn-edge"></div>';

    $('#previewModal').modal('show');
}