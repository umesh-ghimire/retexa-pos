function openAddCategoryModal() {
    const form = document.getElementById('categoryForm');
    document.getElementById('categoryModalTitle').textContent = 'Add Category';
    form.setAttribute('action', form.dataset.storeUrl);
    document.getElementById('categoryMethodField').innerHTML = '';
    document.getElementById('categoryNameInput').value = '';
    document.getElementById('categoryStatusInput').value = 'active';

    const imageInput = document.getElementById('categoryImageInput');
    if (imageInput) imageInput.value = '';

    const currentImageWrapper = document.getElementById('categoryCurrentImageWrapper');
    if (currentImageWrapper) currentImageWrapper.style.display = 'none';

    $('#categoryModal').modal('show');
}

function openEditCategoryModal(updateUrl, name, status, imageUrl) {
    const form = document.getElementById('categoryForm');
    document.getElementById('categoryModalTitle').textContent = 'Edit Category';
    form.setAttribute('action', updateUrl);
    document.getElementById('categoryMethodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    document.getElementById('categoryNameInput').value = name;
    document.getElementById('categoryStatusInput').value = status;

    const imageInput = document.getElementById('categoryImageInput');
    if (imageInput) imageInput.value = '';

    const currentImageWrapper = document.getElementById('categoryCurrentImageWrapper');
    const currentImage = document.getElementById('categoryCurrentImage');
    if (currentImageWrapper && currentImage) {
        if (imageUrl) {
            currentImage.src = imageUrl;
            currentImageWrapper.style.display = 'block';
        } else {
            currentImageWrapper.style.display = 'none';
        }
    }

    $('#categoryModal').modal('show');
}