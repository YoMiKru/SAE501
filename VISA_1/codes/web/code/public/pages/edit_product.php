<?php
session_start();
if (!isset($_SESSION['email']) || $_SESSION['statut'] !== 'admin') {
    header("Location: /var/www/html/pages/login.php");
    exit();
}
$db = new SQLite3('/var/www/data/produits.db');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Modification produit</title>
    <link rel="stylesheet" href="css/style.css" />
    <script src="/var/www/html/assets/js/modif_produit.js" defer></script>
</head>
<body>
<?php include("/var/www/src/includes/header.php"); ?>
<?php include("/var/www/src/includes/menu.php"); ?>

<h2>Modifier un produit</h2>

<form>
    <label>Choisir un produit :</label>
    <select onchange="chargerProduit(this.value)">
        <option value="">-- Sélectionner --</option>
        <?php
        $res = $db->query("SELECT NoProduit, Nom FROM Produit");
        while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
            echo "<option value='{$row['NoProduit']}'>" . htmlspecialchars($row['Nom']) . "</option>";
        }
        ?>
    </select>
    <!-- Captcha toujours visible ici -->
    <div style="margin-top:15px;">
        <label for="captcha">Recopiez le code :</label><br />
        <img id="captchaImage" src="/pages/captcha_generator.php" alt="Captcha" style="cursor:pointer;" title="Cliquez pour rafraîchir le code" /><br />
        <input type="text" id="captcha" name="captcha" required>
    </div>
    <!-- Bouton Modification en dehors du formulaire AJAX -->
    <button id="btnModifier" onclick="envoyerModification(event)">Modifier</button>
</form>

<!-- Formulaire AJAX chargé ici (sans captcha) -->
<div id="formulaire"></div>

<div id="resultat" style="margin-top:15px;"></div>

<script>
document.getElementById("captchaImage").onclick = function() {
    this.src = '/var/www/src/controllers/captcha_generator.php?' + Math.random();
};
</script>

<?php include("/var/www/src/includes/footer.php"); ?>
</body>
</html>
