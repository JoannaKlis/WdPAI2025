function setSex(sex, element) {
    document.getElementById('sexInput').value = sex;
    document.querySelectorAll('.sex-button').forEach(btn => btn.classList.remove('active'));
    element.classList.add('active');
}