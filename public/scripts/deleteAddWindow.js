function openDeleteModal(id) {
    // jeśli przekazano ID, znajdź ukryty input i ustaw jego wartość
    if (id) {
        const input = document.getElementById('modalWeightId') || document.getElementById('modalItemId');
        if (input) {
            input.value = id;
        }
    }
    // pokaż modal
    document.getElementById('deleteModal').style.display = 'flex';
}

// otwiera dowolny modal po jego ID
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
    }
}

// zamyka dowolny modal po jego ID
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
    }
}

// zamknij modal, jeśli kliknięto w tło
window.onclick = function(event) {
    if (event.target.classList.contains('modal-overlay')) {
        event.target.style.display = "none";
    }
}