<?php session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="/assets/js/verif_connexion.js"></script>
</head>
<body>
    <h2>Connexion à l'application</h2>
    <?php
    // Affichage message d’erreur éventuel
    if (isset($_SESSION['login_error'])) {
        echo "<p style='color:red;'>{$_SESSION['login_error']}</p>";
        unset($_SESSION['login_error']);
    }
    ?>
    <form method="POST" action="/pages/login_action.php" onsubmit="return verifForm();">
        <label>Email :</label><br>
        <input type="email" name="email" required><br><br>

        <label>Mot de passe :</label><br>
        <input type="password" name="password" id="password" required><br><br>

        <button type="submit">Connexion</button>
    </form>
</body>
</html>
