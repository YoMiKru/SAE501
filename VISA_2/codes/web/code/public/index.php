<?php
session_start();

// Redirection si non connecté
if (!isset($_SESSION['email'])) {
    header("Location: pages/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Accueil - Produit Gourmands</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
	<header>
		<h1>Bienvenue</h1>
	</header>

	<main>
		<p>Bienvenue sur la page d'accueil.</p>
	</main>
</body>
</html>
