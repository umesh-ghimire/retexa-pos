function openBillModal(sale, tpl) {
    const template = tpl || buildFallbackTemplate('Shop');
    const order = getSectionOrder(template);

    const container = document.getElementById('billModalContent');
    applyReceiptContainerClasses(container, template);

    container.innerHTML = buildReceiptHtml(template, sale, order);

    $('#billModal').modal('show');
}