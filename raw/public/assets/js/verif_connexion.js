function verifForm() {
    const password = document.getElementById("password").value;
    const hasUppercase = /[A-Z]/.test(password);
    const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(password);

    if (!hasUppercase || !hasSpecialChar) {
        alert("Le mot de passe doit contenir une majuscule et un caractère spécial.");
        return false;
    }
    return true;
}
