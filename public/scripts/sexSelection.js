document.addEventListener('DOMContentLoaded', function() {
    const sexButtons = document.querySelectorAll('.sex-button');

    sexButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            sexButtons.forEach(btn => btn.classList.remove('active'));
            
            this.classList.add('active');
        });
    });
});