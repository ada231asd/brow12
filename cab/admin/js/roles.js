document.addEventListener('DOMContentLoaded', () => {
    loadTableData('Role');
});

function openPermissionsModal(roleId) {
    const modal = document.getElementById('permissions-modal');
    const title = document.getElementById('permissions-modal-title');
    title.textContent = `Управление правами для роли ${roleId}`;
    modal.dataset.roleId = roleId;

    fetch(`./api/index.php?action=analytics&table=Role_Permissions&role_id=${roleId}`, { credentials: 'include' })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
                return;
            }
            const permissions = data.map(row => row.permission);
            document.querySelectorAll('#permissions-form input[name="permissions[]"]').forEach(checkbox => {
                checkbox.checked = permissions.includes(checkbox.value);
            });
            modal.style.display = 'block';
        })
        .catch(error => alert('Ошибка загрузки прав: ' + error));
}

function closePermissionsModal() {
    document.getElementById('permissions-modal').style.display = 'none';
}

function savePermissions() {
    const roleId = document.getElementById('permissions-modal').dataset.roleId;
    const form = document.getElementById('permissions-form');
    const permissions = Array.from(form.querySelectorAll('input[name="permissions[]"]:checked')).map(input => input.value);

    fetch(`./api/index.php?action=update_permissions&role_id=${roleId}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ permissions }),
        credentials: 'include'
    })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                closePermissionsModal();
                alert('Права обновлены');
            } else {
                alert(result.error);
            }
        })
        .catch(error => alert('Ошибка сохранения прав: ' + error));
}