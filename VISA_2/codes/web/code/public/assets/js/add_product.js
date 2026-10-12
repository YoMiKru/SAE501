const producteurSelect = document.getElementById("producteur");
const nouveauProducteur = document.getElementById("nouveau-producteur");
const nomProducteur = document.getElementById("producteur_nom");
const prenomProducteur = document.getElementById("producteur_prenom");
const villeProducteur = document.getElementById("producteur_ville");

producteurSelect.addEventListener("change", function () {

    if (this.value === "nouveau") {
        nouveauProducteur.style.display = "block";

        nomProducteur.required = true;
        prenomProducteur.required = true;
        villeProducteur.required = true;

    } else {
        nouveauProducteur.style.display = "none";

        nomProducteur.required = false;
        prenomProducteur.required = false;
        villeProducteur.required = false;
    }
});

const dropZone = document.getElementById("drop-zone");
const imageInput = document.getElementById("image");
const imagePreview = document.getElementById("image-preview");
const dropText = document.getElementById("drop-text");


dropZone.addEventListener("click", function () {
    imageInput.click();
});


imageInput.addEventListener("change", function () {
    afficherImage(this.files[0]);
});


dropZone.addEventListener("dragover", function (event) {
    event.preventDefault();
    dropZone.classList.add("dragover");
});


dropZone.addEventListener("dragleave", function () {
    dropZone.classList.remove("dragover");
});


dropZone.addEventListener("drop", function (event) {

    event.preventDefault();
    dropZone.classList.remove("dragover");

    const files = event.dataTransfer.files;

    if (files.length > 0) {
        imageInput.files = files;
        afficherImage(files[0]);
    }
});


function afficherImage(file) {

    if (!file) {
        return;
    }

    if (!file.type.startsWith("image/")) {
        alert("Veuillez sélectionner une image.");
        imageInput.value = "";
        return;
    }

    const reader = new FileReader();

    reader.onload = function (event) {
        imagePreview.src = event.target.result;
        imagePreview.style.display = "block";
        dropText.style.display = "none";
    };

    reader.readAsDataURL(file);
}
