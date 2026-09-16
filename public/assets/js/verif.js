function verifForm() {
    let pass = document.getElementById("password").value;
    let hasMaj = /[A-Z]/.test(pass);
    let hasSpec = /[!@#$%^&*(),.?":{}|<>]/.test(pass);
    if (!hasMaj || !hasSpec) {
        alert("Mot de passe invalide : au moins une majuscule et un caractère spécial requis.");
        return false;
    }
    return true;
}