<?php
session_start();
if (!isset($_SESSION['email']) || $_SESSION['statut'] !== 'admin') {
    exit("Accès refusé");
}

$db = new SQLite3('../BDD/produits.db');
$id = intval($_GET['id']);
$res = $db->query("SELECT * FROM Produit WHERE NoProduit = $id");
$prod = $res->fetchArray(SQLITE3_ASSOC);

if (!$prod) {
    exit("<p>Produit introuvable</p>");
}
?>

<form id="modifForm">
    <input type="hidden" name="id" value="<?= $prod['NoProduit'] ?>">
    
    <label>Nom :</label><br>
    <input type="text" name="nom" value="<?= htmlspecialchars($prod['Nom']) ?>" required><br><br>

    <label>Prix (€) :</label><br>
    <input type="number" name="prix" value="<?= htmlspecialchars($prod['PrixKilo']) ?>" step="0.01" required><br><br>

    <label>Bio :</label><br>
    <select name="bio">
        <option value="1" <?= $prod['Bio'] ? "selected" : "" ?>>Oui</option>
        <option value="0" <?= !$prod['Bio'] ? "selected" : "" ?>>Non</option>
    </select><br><br>
</form>
