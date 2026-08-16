function openBillModal(sale, tpl) {
    const template = resolveEffectiveTemplate(tpl, sale, 'Shop');
    const order = getSectionOrder(template);

    const container = document.getElementById('billModalContent');
    applyReceiptContainerClasses(container, template, printerPaperWidthMm);

    container.innerHTML = renderReceiptForTemplate(template, sale, order);

    $('#billModal').modal('show');
}

function printBillModal() {
    const container = document.getElementById('billModalContent');
    const copies = (typeof printerCopies !== "undefined" && printerCopies > 1) ? printerCopies : 1;

    if (copies > 1) {
        const original = container.innerHTML;
        const cutLine = '<div style="border-top:1px dashed #000; margin:6mm 0;"></div>';
        container.innerHTML = Array(copies).fill(original).join(cutLine);
        window.print();
        container.innerHTML = original;
    } else {
        window.print();
    }
}