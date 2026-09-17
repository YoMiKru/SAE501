<?php
session_start();
if (!isset($_SESSION['email']) || $_SESSION['statut'] !== 'admin') {
    header("Location: /var/www/html/pages/login.php");
    exit();
}

if (isset($_POST['nom'], $_POST['prix'], $_POST['bio'], $_POST['producteur'])) {
    $db = new SQLite3('/var/www/data/produits.db');

    $nom = htmlspecialchars($_POST['nom']);
    $prix = floatval($_POST['prix']);
    $bio = $_POST['bio'] === "1" ? 1 : 0;
    $producteur = intval($_POST['producteur']);

    $stmt = $db->prepare("INSERT INTO Produit (Nom, NoProducteur, PrixKilo, Bio) VALUES (:nom, :prod, :prix, :bio)");
    $stmt->bindValue(':nom', $nom, SQLITE3_TEXT);
    $stmt->bindValue(':prod', $producteur, SQLITE3_INTEGER);
    $stmt->bindValue(':prix', $prix, SQLITE3_FLOAT);
    $stmt->bindValue(':bio', $bio, SQLITE3_INTEGER);
    $stmt->execute();

    header("Location: /var/www/html/pages/add_product.php?ok=1");
    exit();
}
?>
