<?php
session_start();

if (!isset($_SESSION['email']) || $_SESSION['statut'] !== 'admin') {
    header("Location: /pages/login.php");
    exit();
}

$db = new SQLite3('/var/www/data/produits.db');
?>

<!DOCTYPE html>

<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un produit</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="/assets/js/verif_insertion.js"></script>

<style>
    .drop-zone {
        width: 100%;
        max-width: 500px;
        padding: 30px;
        border: 2px dashed #aaa;
        border-radius: 10px;
        text-align: center;
        cursor: pointer;
        margin-bottom: 20px;
    }

    .drop-zone:hover,
    .drop-zone.dragover {
        border-color: #333;
        background-color: #f5f5f5;
    }

    .drop-zone input {
        display: none;
    }

    #image-preview {
        max-width: 200px;
        max-height: 200px;
        margin-top: 15px;
        display: none;
    }

    .producteur-nouveau {
        display: none;
        margin-top: 15px;
    }
</style>

</head>

<body>

<?php include("/var/www/src/includes/header.php"); ?>

<?php include("/var/www/src/includes/menu.php"); ?>

<main>
    <h2>Ajouter un nouveau produit</h2>

    <?php
    if (isset($_GET['ok'])) {
        echo "<p style='color:green;'>Produit ajouté avec succès.</p>";
    }

    if (isset($_GET['error'])) {
        echo "<p style='color:red;'>" . htmlspecialchars($_GET['error']) . "</p>";
    }
    ?>

    <form
        method="POST"
        action="/pages/add_action.php"
        enctype="multipart/form-data"
        onsubmit="return verifInsertion();"
    >

        <label for="nom">Nom du produit :</label><br>
        <input type="text" name="nom" id="nom" required>
        <br><br>


        <label for="prix">Prix au kilo (€) :</label><br>
        <input type="number" name="prix" id="prix" step="0.01" min="0" required>
        <br><br>


        <label for="bio">Biologique :</label><br>
        <select name="bio" id="bio" required>
            <option value="1">Oui</option>
            <option value="0">Non</option>
        </select>
        <br>
        <br>

        <label for="producteur">Producteur :</label><br>

        <select name="producteur" id="producteur" required>
            <option value="">-- Sélectionner un producteur --</option>

            <?php
            $res = $db->query(
                "SELECT NoProducteur, Nom, Prenom
                FROM Producteur
                ORDER BY Nom, Prenom"
            );

            while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
                echo '<option value="' . htmlspecialchars($row['NoProducteur']) . '">'
                    . htmlspecialchars($row['Nom'] . ' ' . $row['Prenom'])
                    . '</option>';
            }
            ?>

            <option value="nouveau">+ Ajouter un nouveau producteur</option>
        </select>


            <div id="nouveau-producteur" class="producteur-nouveau">

                <label for="producteur_nom">Nom du producteur :</label><br>
                <input type="text" name="producteur_nom" id="producteur_nom">
                <br><br>

                <label for="producteur_prenom">Prénom du producteur :</label><br>
                <input type="text" name="producteur_prenom" id="producteur_prenom">
                <br><br>

                <label for="producteur_ville">Ville :</label><br>
                <input type="text" name="producteur_ville" id="producteur_ville">
                <br><br>

            </div>


        <label>Image du produit :</label><br>

        <div class="drop-zone" id="drop-zone">
            <span id="drop-text">
                Glissez-déposez une image ici<br>
                ou cliquez pour sélectionner un fichier
            </span>

            <input
                type="file"
                name="image"
                id="image"
                accept="image/png,image/jpeg,image/webp,image/gif"
            >

            <img id="image-preview" alt="Aperçu de l'image">
        </div>


        <button type="submit">Ajouter</button>

    </form>
</main>

<?php include("/var/www/src/includes/footer.php"); ?>

<script>
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
</script>

</body>
</html>
