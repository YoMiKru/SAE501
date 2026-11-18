const formFiltre = document.getElementById("filtre-form");
const selectVille = document.getElementById("ville");
const tableauProduits = document.getElementById("tableau-produits");

// Ces éléments n'existent pas si index.php a affiché l'erreur de base de données
if (formFiltre && selectVille && tableauProduits) {

    // Évite l'envoi classique du formulaire (par exemple avec la touche Entrée)
    formFiltre.addEventListener("submit", function (event) {
        event.preventDefault();
    });

    selectVille.addEventListener("change", function () {
        const url = "/pages/filter_action.php?ville=" + encodeURIComponent(this.value);

        fetch(url)
            .then(function (reponse) {
                if (!reponse.ok) {
                    throw new Error("Erreur HTTP " + reponse.status);
                }
                return reponse.text();
            })
            .then(function (html) {
                // filter_action.php renvoie directement les lignes <tr> du tableau
                tableauProduits.innerHTML = html;
            })
            .catch(function (erreur) {
                console.error("Filtre impossible :", erreur);
                afficherErreur();
            });
    });
}

function afficherErreur() {
    tableauProduits.innerHTML = "";

    const ligne = document.createElement("tr");
    const cellule = document.createElement("td");
    cellule.colSpan = 6;
    cellule.textContent = "Impossible de charger les produits pour le moment.";

    ligne.appendChild(cellule);
    tableauProduits.appendChild(ligne);
}