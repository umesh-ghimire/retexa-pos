function setAdjustModalProduct(productId, productName, unitCode, actionUrl) {
    const form = document.getElementById('adjustStockForm');
    form.setAttribute('action', actionUrl || '#');

    const nameEl = document.getElementById('adjustProductName');
    const nameWrap = document.getElementById('adjustProductNameWrap');
    if (productName) {
        nameEl.textContent = productName;
        nameWrap.style.display = 'inline';
    } else {
        nameWrap.style.display = 'none';
    }

    document.getElementById('adjustUnitLabel').textContent = unitCode ? '(' + unitCode + ')' : '';
}

// Opened from a row's "Adjust Stock" button — product is already known.
function openAdjustModal(productId, productName, unitCode) {
    const form = document.getElementById('adjustStockForm');
    form.reset();

    const select = document.getElementById('adjustProductSelect');
    select.value = String(productId);

    const selectWrap = document.getElementById('adjustProductSelectWrap');
    selectWrap.style.display = 'none';

    setAdjustModalProduct(productId, productName, unitCode, '/admin/inventory/' + productId + '/adjust');

    $('#adjustStockModal').modal('show');
}

// Opened from the page-level "Adjust Stock" button — product must be chosen.
function openAddAdjustModal() {
    const form = document.getElementById('adjustStockForm');
    form.reset();

    const select = document.getElementById('adjustProductSelect');
    select.value = '';

    const selectWrap = document.getElementById('adjustProductSelectWrap');
    selectWrap.style.display = 'block';

    setAdjustModalProduct(null, null, '', '#');

    $('#adjustStockModal').modal('show');
}

document.addEventListener('DOMContentLoaded', function () {
    const select = document.getElementById('adjustProductSelect');
    if (select) {
        select.addEventListener('change', function () {
            const option = select.options[select.selectedIndex];
            if (!option || !option.value) {
                setAdjustModalProduct(null, null, '', '#');
                return;
            }
            setAdjustModalProduct(
                option.value,
                option.dataset.name,
                option.dataset.unit,
                option.dataset.url
            );
        });
    }

    // ---- Stock Levels: client-side search / category / status filter ----
    const searchInput = document.getElementById('stockSearchInput');
    const categoryFilter = document.getElementById('stockCategoryFilter');
    const statusFilter = document.getElementById('stockStatusFilter');
    const rowsBody = document.getElementById('stockRowsBody');
    const noResults = document.getElementById('noStockResultsState');

    function applyStockFilters() {
        if (!rowsBody) return;

        const search = (searchInput.value || '').toLowerCase().trim();
        const category = categoryFilter.value;
        const status = statusFilter.value;
        let visibleCount = 0;

        rowsBody.querySelectorAll('.stock-row').forEach(function (row) {
            const matchesSearch = !search || row.dataset.name.indexOf(search) !== -1;
            const matchesCategory = !category || row.dataset.category === category;
            const matchesStatus = !status || row.dataset.status === status;

            const visible = matchesSearch && matchesCategory && matchesStatus;
            row.style.display = visible ? '' : 'none';
            if (visible) visibleCount++;
        });

        if (noResults) {
            const total = rowsBody.querySelectorAll('.stock-row').length;
            noResults.style.display = (visibleCount === 0 && total > 0) ? 'block' : 'none';
        }
    }

    if (searchInput) searchInput.addEventListener('input', applyStockFilters);
    if (categoryFilter) categoryFilter.addEventListener('change', applyStockFilters);
    if (statusFilter) statusFilter.addEventListener('change', applyStockFilters);
});

// ---- Recent Stock Movements: server-side type filter ----
function filterMovements(type) {
    const url = new URL(window.location.href);
    if (type) {
        url.searchParams.set('movement_type', type);
    } else {
        url.searchParams.delete('movement_type');
    }
    url.searchParams.delete('page');
    window.location.href = url.toString();
}