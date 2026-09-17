<?php
session_start();
if (!isset($_SESSION['email']) || $_SESSION['statut'] !== 'admin') {
    header("Location: /var/www/html/pages/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $captchaSaisi = $_POST['captcha'] ?? '';
    if (!isset($_SESSION['code']) || strtoupper($captchaSaisi) !== strtoupper($_SESSION['code'])) {
        exit("<p style='color:red;'>Code captcha incorrect. Veuillez réessayer.</p>");
    }

    $id = intval($_POST['id']);
    $nom = htmlspecialchars($_POST['nom']);
    $prix = floatval($_POST['prix']);
    $bio = ($_POST['bio'] === "1") ? 1 : 0;

    $db = new SQLite3('/var/html/data/produits.db');
    $stmt = $db->prepare("UPDATE Produit SET Nom = :nom, PrixKilo = :prix, Bio = :bio WHERE NoProduit = :id");
    $stmt->bindValue(':nom', $nom, SQLITE3_TEXT);
    $stmt->bindValue(':prix', $prix, SQLITE3_FLOAT);
    $stmt->bindValue(':bio', $bio, SQLITE3_INTEGER);
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);

    if ($stmt->execute()) {
        echo "<p style='color:green;'>Produit modifié avec succès.</p>";
    } else {
        echo "<p style='color:red;'>Erreur lors de la modification.</p>";
    }
} else {
    echo "<p style='color:red;'>Données invalides.</p>";
}
