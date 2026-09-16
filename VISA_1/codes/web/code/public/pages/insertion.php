<?php
session_start();
if (!isset($_SESSION['email']) || $_SESSION['statut'] !== 'admin') {
    header("Location: connexion.php");
    exit();
}
$db = new SQLite3('BDD/produits.db');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un produit</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/verif_insertion.js"></script>
</head>
<body>
<?php include("includes/header.php"); ?>
<?php include("includes/menu.php"); ?>

<h2>Ajouter un nouveau produit</h2>

<?php
if (isset($_GET['ok'])) {
    echo "<p style='color:green;'>Produit ajouté avec succès.</p>";
}
?>

<form method="POST" action="php/traitement_insertion.php" onsubmit="return verifInsertion();">
    <label>Nom du produit :</label><br>
    <input type="text" name="nom" id="nom" required><br><br>

    <label>Prix au kilo (€) :</label><br>
    <input type="number" name="prix" id="prix" step="0.01" min="0" required><br><br>

    <label>Biologique :</label><br>
    <select name="bio" required>
        <option value="1">Oui</option>
        <option value="0">Non</option>
    </select><br><br>

    <label>Producteur :</label><br>
    <select name="producteur" required>
        <?php
        $res = $db->query("SELECT NoProducteur, Nom, Prenom FROM Producteur");
        while ($row = $res->fetchArray()) {
            echo "<option value='{$row['NoProducteur']}'>{$row['Nom']} {$row['Prenom']}</option>";
        }
        ?>
    </select><br><br>

    <button type="submit">Ajouter</button>
</form>

<?php include("includes/footer.php"); ?>
</body>
</html>
