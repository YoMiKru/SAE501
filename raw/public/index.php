<?php
session_start();

// Redirection si non connecté
if (!isset($_SESSION['email'])) {
    header("Location: connexion.php");
    exit();
}

$db = new SQLite3('BDD/produits.db');

$results = $db->query("
    SELECT 
        Produit.Nom AS ProduitNom, 
        PrixKilo, 
        Bio, 
        ImagePath, 
        Producteur.Nom AS ProdNom, 
        Prenom, 
        Ville 
    FROM Produit 
    JOIN Producteur ON Produit.NoProducteur = Producteur.NoProducteur
");

if (!$results) {
    die("Erreur SQL : " . $db->lastErrorMsg());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accueil - Produits Gourmands</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include("includes/header.php"); ?>

<?php
if ($_SESSION['statut'] === 'admin') {
    include("includes/menu_admin.php");
} else {
    include("includes/menu_user.php");
}
?>

<main>
    <h2>Liste des produits</h2>

    <form id="filtre-form">
        <label for="ville">Filtrer par ville :</label>
        <select name="ville" id="ville">
            <option value="">-- Toutes --</option>
            <option value="Lannion">Lannion</option>
            <option value="Brest">Brest</option>
            <option value="Rennes">Rennes</option>
        </select>
    </form>

    <table class="table-style">
        <thead>
            <tr>
                <th>Produit</th>
                <th>Prix</th>
                <th>Bio</th>
                <th>Image</th>
                <th>Producteur</th>
                <th>Ville</th>
            </tr>
        </thead>
        <tbody id="tableau-produits">
            <?php while ($row = $results->fetchArray(SQLITE3_ASSOC)): ?>
                <?php $image = !empty($row['ImagePath']) ? $row['ImagePath'] : 'images/default.png'; ?>
                <tr>
                    <td><?= htmlspecialchars($row['ProduitNom']) ?></td>
                    <td><?= number_format($row['PrixKilo'], 2) ?> €</td>
                    <td><?= $row['Bio'] ? 'Oui' : 'Non' ?></td>
                    <td><img src="<?= htmlspecialchars($image) ?>" alt="Image du produit" width="100"></td>
                    <td><?= htmlspecialchars($row['ProdNom'] . ' ' . $row['Prenom']) ?></td>
                    <td><?= htmlspecialchars($row['Ville']) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</main>

<?php include("includes/footer.php"); ?>

<script>
document.getElementById("ville").addEventListener("change", function () {
    var ville = this.value;
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "filtrer.php?ville=" + encodeURIComponent(ville), true);
    xhr.onload = function () {
        if (xhr.status === 200) {
            document.getElementById("tableau-produits").innerHTML = xhr.responseText;
        } else {
            console.error("Erreur AJAX : " + xhr.statusText);
        }
    };
    xhr.send();
});
</script>

</body>
</html>
