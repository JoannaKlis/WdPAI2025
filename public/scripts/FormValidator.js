class FormValidator {
    constructor(formElement) {
        this.form = formElement;
        if (!this.form) return;
        
        this.button = this.form.querySelector('button.button, button.button-save, button[type="submit"]');
        if (!this.button) return;
        
        this.inputs = this.form.querySelectorAll('input, select, textarea');
        this.sexButtons = this.form.querySelectorAll('.sex-button');
        this.initialValues = {};
        
        this.init();
    }

    init() {
        this.captureInitialValues();
        this.attachListeners();
        
        this.form.addEventListener('form-data-updated', () => {
            this.captureInitialValues();
            this.validate();
        });
        
        this.validate();
    }

    captureInitialValues() {
        this.initialValues = {};
        this.inputs.forEach(input => {
            if (['file', 'submit'].includes(input.type) || !input.name) return;
            this.initialValues[input.name] = input.type === 'checkbox' ? input.checked : input.value;
        });
    }

    attachListeners() {
        const validateHandler = () => this.validateWithDelay();
        
        this.inputs.forEach(input => {
            input.addEventListener('input', validateHandler);
            input.addEventListener('change', validateHandler);
        });
        
        this.sexButtons.forEach(btn => {
            btn.addEventListener('click', validateHandler);
        });
    }

    validateWithDelay() {
        setTimeout(() => this.validate(), 20);
    }

    validate() {
        let allRequiredFilled = true;
        let hasChanges = false;
        
        this.inputs.forEach(input => {
            if (!input.name) return;
            
            // Sprawdzenie wymaganych pól
            if (input.hasAttribute('required')) {
                if (input.type === 'checkbox' && !input.checked) {
                    allRequiredFilled = false;
                } else if (input.type !== 'file' && !input.value.trim()) {
                    allRequiredFilled = false;
                }
            }
            
            // Sprawdzenie zmian
            if (input.type === 'file') {
                if (input.files.length > 0) hasChanges = true;
            } else {
                const initVal = this.initialValues[input.name];
                const currentVal = input.type === 'checkbox' ? input.checked : input.value;
                if (currentVal !== initVal) hasChanges = true;
            }
        });
        
        const isEnabled = allRequiredFilled && hasChanges;
        this.button.disabled = !isEnabled;
        this.button.classList.toggle('button-disabled', !isEnabled);
    }
}

class AuthFormHandler {
    constructor(formId, endpoint) {
        this.form = document.getElementById(formId);
        this.endpoint = endpoint;
        this.messageContainer = document.querySelector('.status-messages-container');
        
        if (this.form) this.init();
    }

    init() {
        this.form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            if (this.messageContainer) {
                this.messageContainer.innerHTML = '';
            }
            
            try {
                const response = await fetch(this.endpoint, {
                    method: 'POST',
                    body: new FormData(this.form)
                });
                const result = await response.json();
                
                if (result.success) {
                    window.location.href = result.redirect;
                } else {
                    this.displayError(result.message);
                }
            } catch (error) {
                this.displayError("An unexpected server error occurred.");
            }
        });
    }

    displayError(message) {
        if (this.messageContainer) {
            this.messageContainer.innerHTML = `<p class="error-messages">${message}</p>`;
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const authForms = [
        { id: 'loginForm', url: '/login' },
        { id: 'registrationForm', url: '/registration' }
    ];
    
    authForms.forEach(auth => {
        if (document.getElementById(auth.id)) {
            new AuthFormHandler(auth.id, auth.url);
        }
    });
    
    document.querySelectorAll('form.wrapper, .modal-content form').forEach(form => {
        new FormValidator(form);
    });
});