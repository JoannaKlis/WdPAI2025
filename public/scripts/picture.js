function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            let preview = document.getElementById('petImagePreview');
            if (!preview) {
                preview = document.getElementById('profile-preview');
            }
            if (preview) {
                preview.src = e.target.result;
                preview.classList.add('user-photo');
            }
        }
        reader.readAsDataURL(input.files[0]);
    }
}