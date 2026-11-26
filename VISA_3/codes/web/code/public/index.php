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

// Récupération des produits depuis l'API
$products = [];

try {
    error_log("Initialisation des données des produits");

    $ch = curl_init("http://api_products:8001/products");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);

    if ($response === false) {
        throw new Exception(curl_error($ch));
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        throw new Exception("Code HTTP inattendu : " . $httpCode);
    }

    $data = json_decode($response, true);

    if (!is_array($data) || !isset($data['products']) || !is_array($data['products'])) {
        throw new Exception("Réponse JSON invalide");
    }

    $products = $data['products'];

} catch (Exception $e) {
    if (isset($ch) && is_resource($ch)) {
        curl_close($ch);
    }

    error_log("Appel de api_products échoué : " . $e->getMessage());
}

// Génération de la liste des villes disponibles
$villes = array_unique(array_column($products, 'Ville'));
sort($villes);

// Récupération des filtres sélectionnés
$ville = $_GET['ville'] ?? '*';
$bio = $_GET['bio'] ?? '*';

// Filtrage des produits déjà récupérés
$filteredProducts = array_filter($products, function ($product) use ($ville, $bio) {

    if ($ville !== '*' && ($product['Ville'] ?? '') !== $ville) {
        return false;
    }

    if ($bio !== '*') {
        $isBio = filter_var($product['Bio'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if (($bio === '1' && !$isBio) || ($bio === '0' && $isBio)) {
            return false;
        }
    }

    return true;
});
?>

<main>
    <h2>Liste des produits</h2>

    <form id="filtre-form" method="GET">

        <label for="ville">Filtrer par ville :</label>
        <select name="ville" id="ville" onchange="this.form.submit()">
            <option value="*">-- Toutes --</option>

            <?php foreach ($villes as $v): ?>
                <option value="<?= htmlspecialchars($v, ENT_QUOTES, 'UTF-8') ?>"
                    <?= $ville === $v ? 'selected' : '' ?>>
                    <?= htmlspecialchars($v, ENT_QUOTES, 'UTF-8') ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="bio">Filtrer par agriculture biologique :</label>
        <select name="bio" id="bio" onchange="this.form.submit()">
            <option value="*" <?= $bio === '*' ? 'selected' : '' ?>>Tous</option>
            <option value="1" <?= $bio === '1' ? 'selected' : '' ?>>Bio</option>
            <option value="0" <?= $bio === '0' ? 'selected' : '' ?>>Non bio</option>
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

            <?php foreach ($filteredProducts as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['Nom'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= number_format((float) ($row['PrixKilo'] ?? 0), 2, ',', ' ') ?> €</td>
                    <td><?= filter_var($row['Bio'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 'Oui' : 'Non' ?></td>
                    <td>
                        <?php $image = $row['ImagePath'] ?>
                        <img src="<?= htmlspecialchars($image, ENT_QUOTES, 'UTF-8') ?>"
                             alt="<?= htmlspecialchars($row['ProduitNom'] ?? 'Image du produit', ENT_QUOTES, 'UTF-8') ?>"
                             width="100">
                    </td>                    
                    <td><?= htmlspecialchars($row['Prenom'] ?? '', $row['Producteur.Nom'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($row['Ville'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
            <?php endforeach; ?>

            <?php if (empty($filteredProducts)): ?>
                <tr>
                    <td colspan="6">Aucun produit ne correspond aux filtres.</td>
                </tr>
            <?php endif; ?>

        </tbody>
    </table>
</main>

<?php include '/var/www/src/includes/footer.php'; ?>

</body>
</html>
