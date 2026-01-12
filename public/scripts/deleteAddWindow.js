function openDeleteModal(id) {
    if (id) {
        const input = document.getElementById('modalWeightId') || document.getElementById('modalItemId');
        if (input) {
            input.value = id;
        }
    }
    document.getElementById('deleteModal').style.display = 'flex';
}

// funkcja do pobierania czasu lokalnego w formacie HH:MM
function getLocalTime() {
    const now = new Date();
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    return `${hours}:${minutes}`;
}

// funkcja do pobierania daty lokalnej w formacie YYYY-MM-DD
function getLocalDate() {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';

        // automatyczne ustawianie czasu i daty użytkownika lokalnego
        const timeInputs = modal.querySelectorAll('input[type="time"]');
        timeInputs.forEach(input => {
            if (!input.value) { // Tylko jeśli pole jest puste (żeby nie nadpisać edycji)
                input.value = getLocalTime();
            }
        });

        const dateInputs = modal.querySelectorAll('input[type="date"]');
        dateInputs.forEach(input => {
            if (!input.value) {
                input.value = getLocalDate();
            }
        });
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
    }
}

window.onclick = function(event) {
    if (event.target.classList.contains('modal-overlay')) {
        event.target.style.display = "none";
    }
}