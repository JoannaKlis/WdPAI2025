document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('form.wrapper');
    if (!form) return;

    const button = form.querySelector('button.button, button.button-save');
    
    // dodano input[type="date"] oraz input[type="hidden"] do selektora
    const inputs = form.querySelectorAll('input[type="text"], input[type="email"], input[type="password"], input[type="file"], input[type="date"], input[type="hidden"]');
    
    // pobranie przycisków wyboru płci
    const sexButtons = form.querySelectorAll('.sex-button');

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
                    if (input.files.length > 0) {
                        hasChanges = true;
                    }
                } else {
                    // porównanie aktualnej wartości z zapamiętaną początkową
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
        }, 10);
    }

    // nasłuchiwanie zmian na wszystkich polach (w tym Data)
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

    // uruchomienie na starcie
    validateForm();
});