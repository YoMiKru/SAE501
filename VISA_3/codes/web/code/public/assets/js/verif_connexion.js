function verifForm() {
    const password = document.getElementById("password").value;
    const hasUppercase = /[A-Z]/.test(password);
    const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(password);

    if (!hasUppercase || !hasSpecialChar) {
        alert("Mot de passe invalide.");
        return false;
    }
    return true;
}
