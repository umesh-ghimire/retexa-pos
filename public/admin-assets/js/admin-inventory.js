function openAdjustModal(productId, productName, unitCode) {
    const form = document.getElementById('adjustStockForm');
    form.setAttribute('action', '/admin/inventory/' + productId + '/adjust');

    document.getElementById('adjustProductName').textContent = productName;
    document.getElementById('adjustUnitLabel').textContent = unitCode ? '(' + unitCode + ')' : '';

    form.reset();
    $('#adjustStockModal').modal('show');
}