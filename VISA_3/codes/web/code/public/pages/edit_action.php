<?php
session_start();
if (!isset($_SESSION['email']) || $_SESSION['statut'] !== 'admin') {
    header("Location: /pages/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $captchaSaisi = $_POST['captcha'] ?? '';
    if (!isset($_SESSION['code']) || strtoupper($captchaSaisi) !== strtoupper($_SESSION['code'])) {
        exit("<p class=\"message-error\">Code captcha incorrect. Veuillez réessayer.</p>");
    }

    $id = intval($_POST['id']);
    $nom = htmlspecialchars($_POST['nom']);
    $prix = floatval($_POST['prix']);
    $bio = ($_POST['bio'] === "1") ? 1 : 0;

    require_once '/var/www/src/includes/database.php';
    $db = getDatabase();
    $stmt = $db->prepare("UPDATE Produit SET Nom = :nom, PrixKilo = :prix, Bio = :bio WHERE NoProduit = :id");
    $stmt->bindValue(':nom', $nom, PDO::PARAM_STR);
    $stmt->bindValue(':prix', $prix, PDO::PARAM_STR);
    $stmt->bindValue(':bio', $bio, PDO::PARAM_INT);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo "<p class=\"message-success\">Produit modifié avec succès.</p>";
    } else {
        echo "<p class=\"message-error\">Erreur lors de la modification.</p>";
    }
} else {
    echo "<p class=\"message-error\">Données invalides.</p>";
}
