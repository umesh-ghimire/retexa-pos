function openAddProductModal() {
    const form = document.getElementById('productForm');

    document.getElementById('productModalTitle').textContent = 'Add Product';
    form.setAttribute('action', form.dataset.storeUrl);
    document.getElementById('productMethodField').innerHTML = '';

    form.reset();
    document.getElementById('productCurrentImageWrapper').style.display = 'none';

    $('#productModal').modal('show');
}

function openEditProductModal(product) {
    const form = document.getElementById('productForm');

    document.getElementById('productModalTitle').textContent = 'Edit Product';
    form.setAttribute('action', '/admin/products/' + product.id);
    document.getElementById('productMethodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';

    document.getElementById('productNameInput').value = product.name ?? '';
    document.getElementById('productSkuInput').value = product.sku ?? '';
    document.getElementById('productCategoryInput').value = product.category_id ?? '';
    document.getElementById('productUnitInput').value = product.unit_id ?? '';
    document.getElementById('productPriceInput').value = product.price ?? '';
    document.getElementById('productCostPriceInput').value = product.cost_price ?? '';
    document.getElementById('productBarcodeInput').value = product.barcode ?? '';
    document.getElementById('productStockInput').value = product.stock ?? '';
    document.getElementById('productMinStockInput').value = product.min_stock_level ?? '';
    document.getElementById('productStatusInput').value = product.status ?? 'active';

    document.getElementById('productImageInput').value = '';

    const imageWrapper = document.getElementById('productCurrentImageWrapper');
    const imageEl = document.getElementById('productCurrentImage');
    if (product.image) {
        imageEl.src = '/storage/' + product.image;
        imageWrapper.style.display = 'block';
    } else {
        imageWrapper.style.display = 'none';
    }

    $('#productModal').modal('show');
}