function prepareDelete(id, actionUrl) {
    document.getElementById('modalItemId').value = id;
    document.getElementById('deleteForm').action = actionUrl;
    openModal('deleteModal');
}

function openAddScheduleModal() {
    document.getElementById('scheduleModalId').value = ''; 
    document.getElementById('scheduleModalName').value = '';
    document.getElementById('scheduleModalTime').value = '<?= date("H:i") ?>';
    openModal('scheduleModal');
}

function openEditScheduleModal(id, name, time) {
    document.getElementById('scheduleModalId').value = id; 
    document.getElementById('scheduleModalName').value = name;
    document.getElementById('scheduleModalTime').value = time;
    openModal('scheduleModal');
}

function openEditUserModal(btn) {
    const data = btn.dataset;

    document.getElementById('editId').value = data.id;
    document.getElementById('editFirstname').value = data.firstname;
    document.getElementById('editLastname').value = data.lastname;
    document.getElementById('editEmail').value = data.email;
    document.getElementById('editRole').value = data.role;
            
    openModal('editUserModal');
}