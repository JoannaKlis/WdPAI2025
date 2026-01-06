function openDeleteModal(id) {
    // jeśli przekazano ID, znajdź ukryty input i ustaw jego wartość
    if (id) {
        const input = document.getElementById('modalWeightId');
        if (input) {
            input.value = id;
        }
    }
    // pokaż modal
    document.getElementById('deleteModal').style.display = 'flex';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
}

// ekran dodawania
function openAddModal() {
    document.getElementById('addModal').style.display = 'flex';
}

function closeAddModal() {
    document.getElementById('addModal').style.display = 'none';
}

// zamknij modal, jeśli kliknięto w tło
window.onclick = function(event) {
    const deleteModal = document.getElementById('deleteModal');
    const addModal = document.getElementById('addModal');
    
    if (event.target == deleteModal) {
        deleteModal.style.display = "none";
    }
    if (event.target == addModal) {
        addModal.style.display = "none";
    }
}