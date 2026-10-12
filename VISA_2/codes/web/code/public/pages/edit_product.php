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
    <meta charset="UTF-8" />
    <title>Modification produit</title>
    <link rel="stylesheet" href="/assets/css/style.css" />
    <script src="/assets/js/modif_produit.js" defer></script>
    <script src="/assets/js/edit_product.js" defer></script>
</head>
<body>
<?php include("/var/www/src/includes/header.php"); ?>
<?php include("/var/www/src/includes/menu.php"); ?>

    <main>
    <h2>Modifier un produit</h2>

    <form>
        <label>Choisir un produit :</label>
        <select onchange="chargerProduit(this.value)">
            <option value="">-- Sélectionner --</option>
            <?php
            $res = $db->query("SELECT NoProduit, Nom FROM Produit");
            while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
                echo "<option value='{$row['NoProduit']}'>" . htmlspecialchars($row['Nom']) . "</option>";
            }
            ?>
        </select>
        <!-- Captcha toujours visible ici -->
        <div class="captcha-container">
            <label for="captcha">Recopiez le code :</label><br />
            <img id="captchaImage" src="/pages/captcha_generator.php" alt="Captcha" class="captcha-image" title="Cliquez pour rafraîchir le code" /><br />
            <input type="text" id="captcha" name="captcha" required>
        </div>
        <!-- Bouton Modification en dehors du formulaire AJAX -->
        <button id="btnModifier" onclick="envoyerModification(event)">Modifier</button>
    </form>

    <!-- Formulaire AJAX chargé ici (sans captcha) -->
    <div id="formulaire"></div>

    <div id="resultat" class="captcha-container"></div>
</main>




<?php include("/var/www/src/includes/footer.php"); ?>
</body>
</html>
