class FormValidator {
    constructor(formElement) {
        this.form = formElement;
        if (!this.form) return;

        this.button = this.form.querySelector('button.button, button.button-save, button[type="submit"]');
        if (!this.button) return;

        this.inputs = this.form.querySelectorAll('input, select, textarea');
        this.sexButtons = this.form.querySelectorAll('.sex-button');
        
        // Obiekt przechowujący wartości początkowe (do wykrywania zmian)
        this.initialValues = {};

        this.init();
    }

    init() {
        this.captureInitialValues();
        this.attachListeners();
        
        // Nasłuchiwanie na zdarzenie
        this.form.addEventListener('form-data-updated', () => {
            this.captureInitialValues();
            this.validate();
        });

        // Walidacja na start
        this.validate(); 
    }

    // Zapamiętanie wartości z bazy danych
    captureInitialValues() {
        this.initialValues = {}; // Reset
        this.inputs.forEach(input => {
            if (['file', 'submit'].includes(input.type) || !input.name) return;
            this.initialValues[input.name] = input.type === 'checkbox' ? input.checked : input.value;
        });
    }

    attachListeners() {
        // Nasłuchiwanie zmian na polach
        this.inputs.forEach(input => {
            input.addEventListener('input', () => this.validateWithDelay());
            input.addEventListener('change', () => this.validateWithDelay());
        });

        // Nasłuchiwanie przycisków płci (div/button)
        this.sexButtons.forEach(btn => {
            btn.addEventListener('click', () => this.validateWithDelay());
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

            // Sprawdźenie czy pola wymagane są wypełnione
            if (input.hasAttribute('required')) {
                if (input.type === 'checkbox' && !input.checked) allRequiredFilled = false;
                else if (input.type !== 'file' && !input.value.trim()) allRequiredFilled = false;
            }

            if (input.type === 'file') {
                // Jeśli wybrano plik, to jest zmiana
                if (input.files.length > 0) hasChanges = true;
            } else {
                // Porównanie z wartością początkową
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
            
            if (this.messageContainer) this.messageContainer.innerHTML = '';

            try {
                const response = await fetch(this.endpoint, {
                    method: 'POST',
                    body: new FormData(this.form)
                });

                const result = await response.json();

                if (result.success) {
                    // Sukces -> przekierowanie
                    window.location.href = result.redirect;
                } else {
                    // Błąd -> wyświetlenie komunikatu w kontenerze
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

// Pojedyncza inicjalizacja po załadowaniu DOM
document.addEventListener('DOMContentLoaded', () => {
    // Obsługa Fetch dla Logowania i Rejestracji
    const authForms = [
        { id: 'loginForm', url: '/login' },
        { id: 'registrationForm', url: '/registration' }
    ];

    authForms.forEach(auth => {
        if (document.getElementById(auth.id)) {
            new AuthFormHandler(auth.id, auth.url);
        }
    });

    // Obsługa walidacji dla wszystkich formularzy na stronie
    document.querySelectorAll('form.wrapper, .modal-content form').forEach(form => {
        new FormValidator(form);
    });
});