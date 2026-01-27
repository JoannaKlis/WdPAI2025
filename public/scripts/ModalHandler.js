class ModalHandler {
    constructor() {
        this.initGlobalListeners();
    }

    initGlobalListeners() {
        window.addEventListener('click', (event) => {
            if (event.target.classList.contains('modal-overlay')) {
                event.target.style.display = 'none';
            }
        });
    }

    open(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) {
            console.error(`Modal with ID '${modalId}' not found.`);
            return;
        }
        
        modal.style.display = 'flex';
        this._autoFillDefaults(modal);
    }

    close(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';
        }
    }

    openDelete(id, actionUrl, modalId = 'deleteModal') {
        const modal = document.getElementById(modalId);
        if (!modal) return;
        
        const idInput = modal.querySelector('input[name="id"]') || 
                        document.getElementById('modalItemId') || 
                        document.getElementById('modalWeightId');
        const form = modal.querySelector('form');
        
        if (idInput && id) {
            idInput.value = id;
            idInput.dispatchEvent(new Event('input'));
        }
        
        if (form && actionUrl) {
            form.action = actionUrl;
        }
        
        this.open(modalId);
    }

    fillAndOpen(modalId, dataObject) {
        const modal = document.getElementById(modalId);
        if (!modal) return;
        
        const form = modal.querySelector('form');
        
        for (const [key, value] of Object.entries(dataObject)) {
            const input = modal.querySelector(`[name="${key}"]`) || document.getElementById(key);
            if (input) {
                input.value = value;
            }
        }
        
        this.open(modalId);
        
        if (form) {
            form.dispatchEvent(new Event('form-data-updated'));
        }
    }

    openWithDate(modalId, dateStr) {
        const modal = document.getElementById(modalId);
        if (!modal) return;
        
        const dateInput = modal.querySelector('input[type="date"]');
        if (dateInput) dateInput.value = dateStr;
        
        this.open(modalId);
    }

    _autoFillDefaults(modal) {
        const timeInputs = modal.querySelectorAll('input[type="time"]');
        timeInputs.forEach(input => {
            if (!input.value) input.value = DateUtils.getLocalTime();
        });
        
        const dateInputs = modal.querySelectorAll('input[type="date"]');
        dateInputs.forEach(input => {
            if (!input.value) input.value = DateUtils.getLocalDate();
        });
    }
}

const modalHandler = new ModalHandler();

window.openModal = (id) => modalHandler.open(id);
window.closeModal = (id) => modalHandler.close(id);
window.openModalWithDate = (id, date) => modalHandler.openWithDate(id, date);
window.prepareDelete = (id, action) => modalHandler.openDelete(id, action);
window.openDeleteModal = (id = null) => {
    if (id) {
        modalHandler.openDelete(id, null, 'deleteModal');
    } else {
        modalHandler.open('deleteModal');
    }
};
window.openEditScheduleModal = (id, name, time) => {
    modalHandler.fillAndOpen('scheduleModal', {
        schedule_id: id,
        name: name,
        time: time
    });
};
window.openEditUserModal = (btn) => {
    const data = btn.dataset;
    modalHandler.fillAndOpen('editUserModal', {
        'editId': data.id,
        'editFirstname': data.firstname,
        'editLastname': data.lastname,
        'editEmail': data.email,
        'editRole': data.role
    });
};
window.openAddScheduleModal = () => {
    modalHandler.fillAndOpen('scheduleModal', {
        schedule_id: '',
        name: '',
        time: ''
    });
};