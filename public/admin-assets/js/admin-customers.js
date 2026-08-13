function openEditCustomerModal(updateUrl, name, phone) {
    const form = document.getElementById('customerForm');
    form.setAttribute('action', updateUrl);

    document.getElementById('customerNameInput').value = name === 'null' ? '' : name;
    document.getElementById('customerPhoneInput').value = phone === 'null' ? '' : phone;

    $('#customerModal').modal('show');
}