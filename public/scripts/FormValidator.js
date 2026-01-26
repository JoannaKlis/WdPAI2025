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
            // Ignorowanie inputy typu file, submit, itp.
            if (input.type !== 'file' && input.type !== 'submit' && input.name) {
                if (input.type === 'checkbox') {
                    this.initialValues[input.name] = input.checked;
                } else {
                    this.initialValues[input.name] = input.value;
                }
            }
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
            btn.addEventListener('click', () => {
                // setTimeout pozwala najpierw wykonać się skryptowi UIHandler
                this.validateWithDelay();
            });
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
                if (input.type === 'checkbox') {
                    if (!input.checked) allRequiredFilled = false;
                } else if (input.type !== 'file') {
                    if (!input.value.trim()) allRequiredFilled = false;
                }
            }

            // Sprawdźenie czy zaszły zmiany względem stanu początkowego
            if (input.type === 'file') {
                // Jeśli wybrano plik, to jest zmiana
                if (input.files.length > 0) hasChanges = true;
            } else if (input.type === 'checkbox') {
                if (input.checked !== this.initialValues[input.name]) hasChanges = true;
            } else {
                // Porównanie z wartością początkową
                const initVal = this.initialValues[input.name];
                
                if (initVal !== undefined) {
                    if (input.value !== initVal) {
                        hasChanges = true;
                    }
                } 
                else if (input.value !== '') {
                    hasChanges = true;
                }
            }
        });

        const isEditMode = Object.values(this.initialValues).some(val => val !== '' && val !== false);

        let isEnabled = false;

        if (isEditMode) {
            // Tryb edycji: Wszystko wymagane musi być wypełnione ORAZ musi być zmiana
            isEnabled = allRequiredFilled && hasChanges;
        } else {
            // Tryb dodawania/rejestracji: Wszystko wymagane musi być wypełnione
            isEnabled = allRequiredFilled && hasChanges;
        }

        this.toggleButton(isEnabled);
    }

    toggleButton(isEnabled) {
        this.button.disabled = !isEnabled;
        if (isEnabled) {
            this.button.classList.remove('button-disabled');
        } else {
            this.button.classList.add('button-disabled');
        }
    }
}

class AuthFormHandler {
    constructor(formId, endpoint) {
        this.form = document.getElementById(formId);
        this.endpoint = endpoint;
        // Lokalizacja kontenera na wiadomości
        this.messageContainer = document.querySelector('.status-messages-container'); 
        
        if (this.form) {
            this.init();
        }
    }

    init() {
        this.form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            // Czyszczenie poprzednich błędów przed nową próbą
            if (this.messageContainer) {
                this.messageContainer.innerHTML = '';
            }

            const formData = new FormData(this.form);

            try {
                const response = await fetch(this.endpoint, {
                    method: 'POST',
                    body: formData
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

// Inicjalizacja przy ładowaniu DOM
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('loginForm')) {
        new AuthFormHandler('loginForm', '/login');
    }
    if (document.getElementById('registrationForm')) {
        new AuthFormHandler('registrationForm', '/registration');
    }
});

// Inicjalizacja dla WSZYSTKICH formularzy na stronie
document.addEventListener('DOMContentLoaded', () => {
    const forms = document.querySelectorAll('form.wrapper, .modal-content form');
    forms.forEach(form => new FormValidator(form));
});