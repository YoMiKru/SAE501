<?php
session_start();

if (!isset($_SESSION['email']) || $_SESSION['statut'] !== 'admin') {
    header("Location: /pages/login.php");
    exit();
}

require_once '/var/www/src/includes/database.php';
$db = getDatabase();
?>

<!DOCTYPE html>

<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un produit</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/add_product.css">
    <script src="/assets/js/verif_insertion.js" defer></script>
    <script src="/assets/js/add_product.js" defer></script>



</head>

<body>

<?php include("/var/www/src/includes/header.php"); ?>

<?php include("/var/www/src/includes/menu.php"); ?>

<main>
    <h2>Ajouter un nouveau produit</h2>

    <?php
    if (isset($_GET['ok'])) {
        echo "<p class=\"message-success\">Produit ajouté avec succès.</p>";
    }

    if (isset($_GET['error'])) {
        echo "<p class=\"message-error\">" . htmlspecialchars($_GET['error']) . "</p>";
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

            while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
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



</body>
</html>
