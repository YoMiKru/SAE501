<?php
session_start();

if (!isset($_SESSION['email']) || $_SESSION['statut'] !== 'admin') {
    header("Location: ../connexion.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['produit'] ?? 0);
    $captchaSaisi = $_POST['captcha'] ?? '';
    $captchaAttendu = $_SESSION['code'] ?? '';

    // Vérification captcha
    if (strtoupper($captchaSaisi) !== strtoupper($captchaAttendu)) {
        header("Location: ../suppression.php?err=Code+captcha+incorrect.+Veuillez+r%C3%A9essayer.");
        exit();
    }

    // Supprimer le code captcha après vérif
    unset($_SESSION['code']);

    if ($id === 0) {
        header("Location: ../suppression.php?err=Produit+non+valide.");
        exit();
    }

    $db = new SQLite3('../BDD/produits.db');
    $stmt = $db->prepare("DELETE FROM Produit WHERE NoProduit = :id");
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);

    if ($stmt->execute()) {
        header("Location: ../suppression.php?msg=Produit+supprim%C3%A9+avec+succ%C3%A8s.");
    } else {
        header("Location: ../suppression.php?err=Erreur+lors+de+la+suppression.");
    }
    exit();
}
?>
