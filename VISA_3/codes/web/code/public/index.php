<?php
session_start();

// Redirection si non connecté
if (!isset($_SESSION['email'])) {
    header("Location: /pages/login.php");
    exit();
}

// Connexion centralisée à MariaDB
require_once '/var/www/src/includes/database.php';

try {
    $db = getDatabase();

    $query = "
        SELECT
            Produit.Nom AS ProduitNom,
            Produit.PrixKilo,
            Produit.Bio,
            Produit.ImagePath,
            Producteur.Nom AS ProdNom,
            Producteur.Prenom,
            Producteur.Ville
        FROM Produit
        JOIN Producteur ON Produit.NoProducteur = Producteur.NoProducteur
    ";

    $stmt = $db->query($query);
    $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Erreur de récupération des produits : ' . $e->getMessage());
    http_response_code(500);
    $erreurBaseDeDonnees = true;
    $produits = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - Produits Gourmands</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<?php
include '/var/www/src/includes/header.php';

if (($_SESSION['statut'] ?? '') === 'admin') {
    include '/var/www/src/includes/menu_admin.php';
} else {
    include '/var/www/src/includes/menu_user.php';
}
?>

<main>
    <h2>Liste des produits</h2>

    <?php if (!empty($erreurBaseDeDonnees)): ?>
        <p role="alert">Impossible de charger les produits pour le moment. Veuillez réessayer plus tard.</p>
    <?php else: ?>
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
                <?php foreach ($produits as $row): ?>
                    <?php
                    $image = !empty($row['ImagePath'])
                        ? $row['ImagePath']
                        : '/assets/images/default.png';
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($row['ProduitNom'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= number_format((float) $row['PrixKilo'], 2, ',', ' ') ?> €</td>
                        <td><?= !empty($row['Bio']) ? 'Oui' : 'Non' ?></td>
                        <td>
                            <img src="<?= htmlspecialchars($image, ENT_QUOTES, 'UTF-8') ?>"
                                 alt="<?= htmlspecialchars($row['ProduitNom'] ?? 'Image du produit', ENT_QUOTES, 'UTF-8') ?>"
                                 width="100">
                        </td>
                        <td><?= htmlspecialchars(($row['ProdNom'] ?? '') . ' ' . ($row['Prenom'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($row['Ville'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>

<?php include '/var/www/src/includes/footer.php'; ?>

<script src="/assets/js/filter_products.js" defer></script>
</body>
</html>
