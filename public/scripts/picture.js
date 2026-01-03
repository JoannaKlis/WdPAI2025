function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('profile-preview');
            preview.src = e.target.result;
            preview.classList.add('user-photo');
        }
        reader.readAsDataURL(input.files[0]);
    }
}