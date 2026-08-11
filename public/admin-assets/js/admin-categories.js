function openAddCategoryModal() {
    const form = document.getElementById('categoryForm');
    document.getElementById('categoryModalTitle').textContent = 'Add Category';
    form.setAttribute('action', form.dataset.storeUrl);
    document.getElementById('categoryMethodField').innerHTML = '';
    document.getElementById('categoryNameInput').value = '';
    document.getElementById('categoryStatusInput').value = 'active';
    $('#categoryModal').modal('show');
}

function openEditCategoryModal(updateUrl, name, status) {
    const form = document.getElementById('categoryForm');
    document.getElementById('categoryModalTitle').textContent = 'Edit Category';
    form.setAttribute('action', updateUrl);
    document.getElementById('categoryMethodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    document.getElementById('categoryNameInput').value = name;
    document.getElementById('categoryStatusInput').value = status;
    $('#categoryModal').modal('show');
}