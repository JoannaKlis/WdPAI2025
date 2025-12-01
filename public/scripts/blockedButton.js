function validateForm() {
    const form = document.querySelector('form.wrapper');
    
    const button = form.querySelector('button.button');
    const inputs = form.querySelectorAll('input[type="text"], input[type="email"], input[type="password"]');

    function checkInputs() {
        let allFilled = true;

        inputs.forEach(input => {
            if (input.value.trim() === '') {
                allFilled = false;
            }
        });

        if (allFilled) {
            button.classList.remove('disabled');
        } else {
            button.classList.add('disabled');
        }
    }

    // sprawdzenie stanu przy ładowaniu strony
    checkInputs();

    // nasłuchiwanie na każdy input
    inputs.forEach(input => {
        input.addEventListener('input', checkInputs);
        input.addEventListener('change', checkInputs);
    });

    // blokowanie wysłania formularza gdy przycisk jest disabled
    form.addEventListener('submit', function(e) {
        if (button.classList.contains('disabled')) {
            e.preventDefault();
            return false;
        }
    });
}

// uruchomienie walidacji po załadowaniu przycisku
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', validateForm);
} else {
    validateForm();
}