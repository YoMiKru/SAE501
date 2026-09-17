<?php
if (!isset($_GET['ville'])) {
    http_response_code(400);
    echo "Ville non spécifiée";
    exit();
}

$ville = $_GET['ville'];

$db = new SQLite3('/var/html/data/produits.db');

if ($ville === '') {
    // Pas de filtre, on affiche tous les produits
    $query = "
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
    ";
    $stmt = $db->prepare($query);
} else {
    // Filtrage par ville
    $query = "
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
        WHERE Ville = :ville
    ";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':ville', $ville, SQLITE3_TEXT);
}

$results = $stmt->execute();

while ($row = $results->fetchArray(SQLITE3_ASSOC)) {

    $extension = pathinfo($row['ImagePath'], PATHINFO_EXTENSION);

    $nomProduit = strtolower(
        str_replace(' ', '_', $row['ProduitNom'])
    );

    $image = !empty($extension)
        ? '/assets/images/' . $nomProduit . '.' . $extension
        : '/assets/images/default.png';

    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['ProduitNom']) . "</td>";
    echo "<td>" . number_format($row['PrixKilo'], 2) . " €</td>";
    echo "<td>" . ($row['Bio'] ? 'Oui' : 'Non') . "</td>";
    echo "<td><img src='" . htmlspecialchars($image) . "' width='100' alt='Produit'></td>";
    echo "<td>" . htmlspecialchars($row['ProdNom'] . ' ' . $row['Prenom']) . "</td>";
    echo "<td>" . htmlspecialchars($row['Ville']) . "</td>";
    echo "</tr>";
}
?>
