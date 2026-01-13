document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('form.wrapper');
    if (!form) return;

    const button = form.querySelector('button.button, button.button-save');
    
    // inputy tekstowe
    const inputs = form.querySelectorAll('input[type="text"], input[type="email"], input[type="password"], input[type="file"], input[type="date"], input[type="hidden"]');
    
    // pobranie przycisków wyboru płci
    const sexButtons = form.querySelectorAll('.sex-button');

    // pobranie checkboxa polityki prywatności
    const privacyCheckbox = form.querySelector('input[name="privacyPolicy"]');

    if (!button) return;

    // zapamiętanie wartości początkowych z bazy danych
    const initialValues = {};
    inputs.forEach(input => {
        if (input.type !== 'file') {
            initialValues[input.name] = input.value;
        }
    });

    function validateForm() {
        // setTimeout, aby inne skrypty zdążyły zaktualizować wartość hidden input przed sprawdzeniem
        setTimeout(() => {
            let allRequiredFilled = true;
            let hasChanges = false;

            inputs.forEach(input => {
                // ignorowanie inputów bez atrybutu name dla bezpieczeństwa
                if (!input.name) return;

                // sprawdzanie czy pola wymagane nie są puste 
                if (input.type !== 'password' && input.type !== 'file' && input.type !== 'hidden') {
                    if (input.value.trim() === '') {
                        allRequiredFilled = false;
                    }
                }

                // sprawdzanie czy nastąpiła zmiana względem bazy
                if (input.type === 'file') {
                    if (input.files.length > 0) hasChanges = true;
                } else {
                    if (input.value !== initialValues[input.name]) hasChanges = true;
                }
            });

            // sprawdzanie checkboxa polityki prywatności
            let privacyAccepted = true;
            if (privacyCheckbox) {
                // zaznaczenie checkboxa traktowane jest jako akcja użytkownika
                if (!privacyCheckbox.checked) {
                    privacyAccepted = false;
                } else {
                    hasChanges = true; 
                }
            }

            // pola wymagane muszą być pełne
            // musi być jakakolwiek zmiana (lub wpisane dane przy rejestracji)
            // checkbox musi być zaznaczony (jeśli jest na stronie)
            const isEnabled = allRequiredFilled && hasChanges && privacyAccepted;

            button.disabled = !isEnabled;
            if (isEnabled) {
                button.classList.remove('button-disabled');
            } else {
                button.classList.add('button-disabled');
            }
        }, 10);
    }

    // nasłuchiwanie zmian na inputach
    inputs.forEach(input => {
        input.addEventListener('input', validateForm);
        input.addEventListener('change', validateForm);
    });

    // nasłuchiwanie kliknięć na przyciski zmiany płci
    if (sexButtons) {
        sexButtons.forEach(btn => {
            btn.addEventListener('click', validateForm);
        });
    }

    // nasłuchiwanie zmian na checkboxie
    if (privacyCheckbox) {
        privacyCheckbox.addEventListener('change', validateForm);
    }

    validateForm();
});