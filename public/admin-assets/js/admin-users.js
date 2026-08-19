function openAddUserModal() {
    const form = document.getElementById('userForm');
    document.getElementById('userModalTitle').textContent = 'Add User';
    form.setAttribute('action', form.dataset.storeUrl);
    document.getElementById('userMethodField').innerHTML = '';

    document.getElementById('userNameInput').value = '';
    document.getElementById('userEmailInput').value = '';
    document.getElementById('userRoleInput').value = 'cashier';

    const passwordInput = document.getElementById('userPasswordInput');
    passwordInput.value = '';
    passwordInput.required = true;

    document.getElementById('userPasswordHint').style.display = 'none';
    document.getElementById('userPasswordLabel').textContent = 'Password';

    document.getElementById('userPinInput').value = '';

    $('#userModal').modal('show');
}

function openEditUserModal(updateUrl, name, email, role) {
    const form = document.getElementById('userForm');
    document.getElementById('userModalTitle').textContent = 'Edit User';
    form.setAttribute('action', updateUrl);
    document.getElementById('userMethodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';

    document.getElementById('userNameInput').value = name;
    document.getElementById('userEmailInput').value = email;
    document.getElementById('userRoleInput').value = role;

    const passwordInput = document.getElementById('userPasswordInput');
    passwordInput.value = '';
    passwordInput.required = false;

    document.getElementById('userPasswordHint').style.display = 'block';
    document.getElementById('userPasswordLabel').textContent = 'New Password (optional)';

    document.getElementById('userPinInput').value = '';

    $('#userModal').modal('show');
}

(function () {
    var searchInput = document.getElementById('userSearchInput');
    var roleFilter = document.getElementById('roleFilter');
    var statusFilter = document.getElementById('statusFilter');
    var clearBtn = document.getElementById('clearUserFiltersBtn');
    var rows = document.getElementById('userRowsBody');
    var noResults = document.getElementById('noUserResultsState');
    var countText = document.getElementById('usersCountText');
    var totalCount = rows ? rows.querySelectorAll('.user-row').length : 0;

    function applyFilters() {
        if (!rows) return;

        var search = (searchInput.value || '').toLowerCase().trim();
        var role = roleFilter.value;
        var status = statusFilter.value;
        var visibleCount = 0;

        var items = rows.querySelectorAll('.user-row');
        items.forEach(function (row) {
            var matchesSearch = !search ||
                row.dataset.name.indexOf(search) !== -1 ||
                row.dataset.email.indexOf(search) !== -1;
            var matchesRole = !role || row.dataset.role === role;
            var matchesStatus = !status || row.dataset.status === status;

            var visible = matchesSearch && matchesRole && matchesStatus;
            row.style.display = visible ? '' : 'none';
            if (visible) visibleCount++;
        });

        if (noResults) {
            noResults.style.display = (visibleCount === 0 && items.length > 0) ? 'block' : 'none';
        }
        if (countText) {
            countText.textContent = 'Showing ' + visibleCount + ' of ' + totalCount + ' users';
        }
    }

    if (searchInput) searchInput.addEventListener('input', applyFilters);
    if (roleFilter) roleFilter.addEventListener('change', applyFilters);
    if (statusFilter) statusFilter.addEventListener('change', applyFilters);
    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            searchInput.value = '';
            roleFilter.value = '';
            statusFilter.value = '';
            applyFilters();
        });
    }
})();