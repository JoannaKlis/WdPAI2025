class UIHandler {
    static init() {
        this.initPasswordToggles();
    }

    static previewImage(input) {
        if (!input.files || !input.files[0]) return;
        
        const reader = new FileReader();
        reader.onload = function(e) {
            const previewIds = ['petImagePreview', 'profile-preview'];
            previewIds.forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    el.src = e.target.result;
                    el.classList.add('user-photo');
                }
            });
        };
        reader.readAsDataURL(input.files[0]);
    }

    static setSex(sex, element) {
        const input = document.getElementById('sexInput');
        if (!input) return;
        
        input.value = sex;
        document.querySelectorAll('.sex-button').forEach(btn => btn.classList.remove('active'));
        element.classList.add('active');
    }

    static initPasswordToggles() {
        const toggles = document.querySelectorAll('.icon-switch');
        
        toggles.forEach(toggle => {
            const container = toggle.parentElement;
            const input = container.querySelector('input');
            if (!input) return;
            
            toggle.addEventListener('click', () => {
                const isPassword = input.getAttribute('type') === 'password';
                const newType = isPassword ? 'text' : 'password';
                input.setAttribute('type', newType);
                
                const iconPath = isPassword 
                    ? 'public/img/profile/visible.png' 
                    : 'public/img/profile/invisible.png';
                toggle.setAttribute('src', iconPath);
            });
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    UIHandler.init();
});

window.previewImage = UIHandler.previewImage;
window.setSex = UIHandler.setSex;