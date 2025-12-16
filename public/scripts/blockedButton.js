function validateForm() {
    const form = document.querySelector('form.wrapper');
    if (!form) return;

    const button = form.querySelector('button.button, button.button-save');
    if (!button) return;

    const inputs = form.querySelectorAll('input[type="text"], input[type="email"], input[type="password"]');

    function checkInputs() {
        let allFilled = true;

        inputs.forEach(input => {
            if (input.value.trim() === '') {
                allFilled = false;
            }
        });

        if (allFilled) {
            button.classList.remove('button-disabled');
            button.disabled = false;
        } else {
            button.classList.add('button-disabled');
            button.disabled = true;
        }
    }

    checkInputs();

    inputs.forEach(input => {
        input.addEventListener('input', checkInputs);
    });
}

document.addEventListener('DOMContentLoaded', validateForm);