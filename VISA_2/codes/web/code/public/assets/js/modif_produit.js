function chargerProduit(id) {
    if (!id) {
        document.getElementById("formulaire").innerHTML = "";
        document.getElementById("resultat").innerHTML = "";
        return;
    }
    fetch("php/modification_ajax.php?id=" + id)
        .then(res => res.text())
        .then(html => {
            document.getElementById("formulaire").innerHTML = html;
            document.getElementById("resultat").innerHTML = "";
        });
}

function envoyerModification(event) {
    event.preventDefault();

    const form = document.getElementById("modifForm");
    if (!form) {
        alert("Veuillez sélectionner un produit d'abord.");
        return;
    }

    const captchaInput = document.getElementById("captcha");
    if (!captchaInput || !captchaInput.value.trim()) {
        alert("Veuillez remplir le code captcha.");
        return;
    }

    const data = new FormData(form);
    data.append('captcha', captchaInput.value.trim());

    fetch("php/traitement_modification.php", {
        method: "POST",
        body: data
    })
    .then(res => res.text())
    .then(result => {
        document.getElementById("resultat").innerHTML = result;
        // Optionnel : rafraîchir le captcha à chaque soumission
        document.getElementById("captchaImage").src = 'php/image.php?' + Math.random();
        captchaInput.value = "";
    })
    .catch(err => {
        document.getElementById("resultat").innerHTML = "<p style='color:red;'>Erreur : " + err + "</p>";
    });
}
