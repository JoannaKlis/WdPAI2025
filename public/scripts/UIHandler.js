class UIHandler {
    static init() {
        this.initPasswordToggles();
    }

    // Obsługa podglądu zdjęć
    static previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                // Obsługa wielu ID podglądu dla elastyczności
                const ids = ['petImagePreview', 'profile-preview'];
                ids.forEach(id => {
                    const el = document.getElementById(id);
                    if (el) {
                        el.src = e.target.result;
                        el.classList.add('user-photo');
                    }
                });
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Obsługa wyboru płci
    static setSex(sex, element) {
        const input = document.getElementById('sexInput');
        if (input) {
            input.value = sex;
            document.querySelectorAll('.sex-button').forEach(btn => btn.classList.remove('active'));
            element.classList.add('active');
        }
    }

    // Obsługa pokazywania hasła
    static initPasswordToggles() {
        const toggles = document.querySelectorAll('.icon-switch');
        
        toggles.forEach(toggle => {
            const container = toggle.parentElement;
            const input = container.querySelector('input');

            if (input) {
                toggle.addEventListener('click', () => {
                    const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                    input.setAttribute('type', type);
                    
                    const iconPath = type === 'text' ? 'public/img/profile/visible.png' : 'public/img/profile/invisible.png';
                    toggle.setAttribute('src', iconPath);
                });
            }
        });
    }
}

// Inicjalizacja przy załadowaniu strony
document.addEventListener('DOMContentLoaded', () => {
    UIHandler.init();
});

// Eksport globalny dla onclick w HTML
window.previewImage = UIHandler.previewImage;
window.setSex = UIHandler.setSex;