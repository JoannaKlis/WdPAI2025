document.addEventListener('DOMContentLoaded', function() {
    const passwordField = document.getElementById('password-field');
    const togglePassword = document.getElementById('toggle-password');
    
    const confirmPasswordField = document.getElementById('confirm-password-field');
    const toggleConfirmPassword = document.getElementById('toggle-confirm-password');

    // przełączanie widoczności hasła
    function toggleVisibility(field, toggle) {
        if (field && toggle) {
            toggle.addEventListener('click', function () {
                // przełączenie typu pola z 'password' na 'text' i odwrotnie
                const type = field.getAttribute('type') === 'password' ? 'text' : 'password';
                field.setAttribute('type', type);
                
                // zmiana ikony w zależności od stanu widoczności
                if (type === 'text') {
                    toggle.setAttribute('src', 'public/img/visible.png'); 
                } else {
                    toggle.setAttribute('src', 'public/img/invisible.png');
                }
            });
        }
    }

    // obsługa pierwszego pola hasła
    toggleVisibility(passwordField, togglePassword);
    
    // obsługa pola potwierdzenia hasła
    toggleVisibility(confirmPasswordField, toggleConfirmPassword);
});