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

// zamknij modal, jeśli kliknięto w tło
window.onclick = function(event) {
    const modal = document.getElementById('deleteModal');
    if (event.target == modal) {
        modal.style.display = "none";
    }
}