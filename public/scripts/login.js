const toggle = document.getElementById("toggle-password");
const password = document.getElementById("password-field");

toggle.addEventListener("click", () => {
    const isHidden = password.type === "password";

    password.type = isHidden ? "text" : "password";
});
