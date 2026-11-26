<?php
session_start();

// Redirection si non connecté
if (!isset($_SESSION['email'])) {
    header("Location: /pages/login.php");
    exit();
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

<?php

# Liste par défaut
$ch = curl_init("http://api_product:8001/products");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
$products = $data["products"];

?>

<main>
    <h2>Liste des produits</h2>
        <form id="filtre-form" method="GET">
            <label for="ville">Filtrer par ville :</label>
            <select name="ville" id="ville" onchange="this.form.submit()">
                <option value="*">-- Toutes --</option>

                <?php foreach ($villes as $ville): ?>
                    <option value="<?= htmlspecialchars($ville) ?>"
                        <?= ($_GET['ville'] ?? '*') === $ville ? 'selected' : '' ?>>
                        <?= htmlspecialchars($ville) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="bio">Filtrer par agriculture biologique :</label>
            <select name="bio" id="bio" onchange="this.form.submit()">
                <option value="*" <?= ($_GET['bio'] ?? '*') === '*' ? 'selected' : '' ?>>Tous</option>
                <option value="1" <?= ($_GET['bio'] ?? '') === '1' ? 'selected' : '' ?>>Bio</option>
                <option value="0" <?= ($_GET['bio'] ?? '') === '0' ? 'selected' : '' ?>>Non bio</option>
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
</main>

<?php include '/var/www/src/includes/footer.php'; ?>

<script src="/assets/js/filter_products.js" defer></script>
</body>
</html>
