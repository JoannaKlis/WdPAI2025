document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('form.wrapper');
    if (!form) return;

    const button = form.querySelector('button.button, button.button-save');
    const inputs = form.querySelectorAll('input[type="text"], input[type="email"], input[type="password"], input[type="file"]');
    
    if (!button) return;

    // zapamiętanie wartości początkowych z bazy danych
    const initialValues = {};
    inputs.forEach(input => {
        if (input.type !== 'file') {
            initialValues[input.name] = input.value;
        }
    });

    function validateForm() {
        let allRequiredFilled = true;
        let hasChanges = false;

        inputs.forEach(input => {
            // sprawdzanie czy pola nie są puste
            if (input.type !== 'password' && input.type !== 'file') {
                if (input.value.trim() === '') {
                    allRequiredFilled = false;
                }
            }

            // sprawdzanie czy nastąpiła zmiana względem bazy
            if (input.type === 'file') {
                if (input.files.length > 0) {
                    hasChanges = true;
                }
            } else {
                if (input.value !== initialValues[input.name]) {
                    hasChanges = true;
                }
            }
        });

        // logika przycisku: aktywny tylko gdy wszystkie wypełnione i jest zmiana
        const isEnabled = allRequiredFilled && hasChanges;

        button.disabled = !isEnabled;
        if (isEnabled) {
            button.classList.remove('button-disabled');
        } else {
            button.classList.add('button-disabled');
        }
    }

    // nasłuchiwanie zmian na wszystkich polach
    inputs.forEach(input => {
        input.addEventListener('input', validateForm);
        if (input.type === 'file') {
            input.addEventListener('change', validateForm);
        }
    });

    // uruchomienie na starcie, aby zablokować przycisk przed edycją
    validateForm();
});