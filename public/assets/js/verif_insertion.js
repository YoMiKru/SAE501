function verifInsertion() {
    const nom = document.getElementById("nom").value.trim();
    const prix = parseFloat(document.getElementById("prix").value);

    if (nom === "") {
        alert("Le nom du produit est obligatoire.");
        return false;
    }
    if (isNaN(prix) || prix <= 0) {
        alert("Le prix doit être un nombre positif.");
        return false;
    }
    return true;
}
