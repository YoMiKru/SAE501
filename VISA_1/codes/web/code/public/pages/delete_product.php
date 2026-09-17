<?php
session_start();

if (!isset($_SESSION['email']) || $_SESSION['statut'] !== 'admin') {
    header("Location: /pages/login.php");
    exit();
}

$db = new SQLite3('/var/www/data/produits.db');
$msg = $_GET['msg'] ?? '';
$err = $_GET['err'] ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Suppression d'un produit</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<?php include("/var/www/src/includes/header.php"); ?>
<?php include("/var/www/src/includes/menu.php"); ?>

<h2>Suppression d'un produit</h2>

<?php
if ($msg) echo "<p style='color:green;'>".htmlspecialchars($msg)."</p>";
if ($err) echo "<p style='color:red;'>".htmlspecialchars($err)."</p>";
?>

<form method="POST" action="/var/wwww/src/controllers/delete_action.php">
    <label>Produit à supprimer :</label><br>
    <select name="produit" required>
        <option value="">-- Sélectionner --</option>
        <?php
        $res = $db->query("SELECT NoProduit, Nom FROM Produit");
        while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
            echo "<option value='" . htmlspecialchars($row['NoProduit']) . "'>" . htmlspecialchars($row['Nom']) . "</option>";
        }
        ?>
    </select><br><br>

    <!-- Captcha -->
    <div style="margin-top:15px;">
        <label for="captcha">Recopiez le code :</label><br />
        <img id="captchaImage" src="/var/www/src/controllers/captcha_generator.php" alt="Captcha"
             style="cursor:pointer;" title="Cliquez pour rafraîchir"
             onclick="this.src='/var/www/src/controllers/captcha_generator.php?' + Date.now();" /><br />
        <input type="text" id="captcha" name="captcha" required>
    </div><br>

    <button type="submit">Supprimer</button>
</form>

<?php include("/var/www/src/includes/footer.php"); ?>
</body>
</html>
