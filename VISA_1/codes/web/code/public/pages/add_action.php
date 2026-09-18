<?php

session_start();

if (!isset($_SESSION['email']) || $_SESSION['statut'] !== 'admin') {
    header("Location: /pages/login.php");
    exit();
}


/*
 * Vérification des données du produit
 */

if (
    !isset($_POST['nom']) ||
    !isset($_POST['prix']) ||
    !isset($_POST['bio']) ||
    !isset($_POST['producteur'])
) {
    header("Location: /pages/add.php?error=Données manquantes");
    exit();
}


$nom = trim($_POST['nom']);
$prix = floatval($_POST['prix']);
$bio = intval($_POST['bio']);
$producteur = $_POST['producteur'];


/*
 * Vérification du nom du produit
 */

if ($nom === '') {
    header("Location: /pages/add.php?error=Nom du produit invalide");
    exit();
}


/*
 * Connexion à la BDD
 */

$db = new SQLite3('/var/www/data/produits.db');


/*
 * Gestion du producteur
 */

if ($producteur === 'nouveau') {

    if (
        !isset($_POST['producteur_nom']) ||
        !isset($_POST['producteur_prenom'])
    ) {
        header("Location: /pages/add.php?error=Informations du producteur manquantes");
        exit();
    }

    $nomProducteur = trim($_POST['producteur_nom']);
    $prenomProducteur = trim($_POST['producteur_prenom']);

    if ($nomProducteur === '' || $prenomProducteur === '') {
        header("Location: /pages/add.php?error=Nom ou prénom du producteur invalide");
        exit();
    }


    /*
     * Ajout du producteur
     */

    $stmt = $db->prepare(
        "INSERT INTO Producteur (Nom, Prenom)
         VALUES (:nom, :prenom)"
    );

    $stmt->bindValue(':nom', $nomProducteur, SQLITE3_TEXT);
    $stmt->bindValue(':prenom', $prenomProducteur, SQLITE3_TEXT);

    $result = $stmt->execute();

    if (!$result) {
        header("Location: /pages/add.php?error=Erreur lors de l'ajout du producteur");
        exit();
    }


    /*
     * Récupération du numéro du producteur
     */

    $producteur = $db->lastInsertRowID();
}


/*
 * Gestion de l'image
 */

$imagePath = null;

if (
    isset($_FILES['image']) &&
    $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE
) {

    if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        header("Location: /pages/add.php?error=Erreur lors de l'envoi de l'image");
        exit();
    }


    /*
     * Vérification du type réel de l'image
     */

    $imageInfo = getimagesize($_FILES['image']['tmp_name']);

    if ($imageInfo === false) {
        header("Location: /pages/add.php?error=Le fichier envoyé n'est pas une image");
        exit();
    }


    /*
     * Extensions autorisées
     */

    $extensionsAutorisees = [
        'jpg',
        'jpeg',
        'png',
        'gif',
        'webp'
    ];

    $extension = strtolower(
        pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION)
    );

    if (!in_array($extension, $extensionsAutorisees, true)) {
        header("Location: /pages/add.php?error=Format d'image non autorisé");
        exit();
    }


    /*
     * Nettoyage du nom du produit
     *
     * Exemple :
     * "Pomme rouge" -> "pomme_rouge"
     */

    $nomImage = iconv(
        'UTF-8',
        'ASCII//TRANSLIT//IGNORE',
        $nom
    );

    $nomImage = strtolower($nomImage);
    $nomImage = preg_replace('/[^a-z0-9]+/', '_', $nomImage);
    $nomImage = trim($nomImage, '_');


    if ($nomImage === '') {
        header("Location: /pages/add.php?error=Nom de produit invalide pour le nom de l'image");
        exit();
    }


    /*
     * Chemin réel sur le serveur
     */

    $dossierImage = '/var/www/html/assets/images/';


    /*
     * Création du dossier s'il n'existe pas
     */

    if (!is_dir($dossierImage)) {
        mkdir($dossierImage, 0755, true);
    }


    /*
     * Nom final de l'image
     */

    $nomFichier = $nomImage . '.' . $extension;

    $cheminReel = $dossierImage . $nomFichier;


    /*
     * Déplacement de l'image
     */

    if (!move_uploaded_file(
        $_FILES['image']['tmp_name'],
        $cheminReel
    )) {
        header("Location: /pages/add.php?error=Impossible de sauvegarder l'image");
        exit();
    }


    /*
     * Chemin enregistré dans la BDD
     */

    $imagePath = '/assets/images/' . $nomFichier;
}


/*
 * Ajout du produit
 */

$stmt = $db->prepare(
    "INSERT INTO Produit
        (Nom, PrixKilo, Bio, NoProducteur, ImagePath)
     VALUES
        (:nom, :prix, :bio, :producteur, :image)"
);

$stmt->bindValue(':nom', $nom, SQLITE3_TEXT);
$stmt->bindValue(':prix', $prix, SQLITE3_FLOAT);
$stmt->bindValue(':bio', $bio, SQLITE3_INTEGER);
$stmt->bindValue(':producteur', intval($producteur), SQLITE3_INTEGER);

if ($imagePath !== null) {
    $stmt->bindValue(':image', $imagePath, SQLITE3_TEXT);
} else {
    $stmt->bindValue(':image', null, SQLITE3_NULL);
}


$result = $stmt->execute();


if (!$result) {
    header("Location: /pages/add.php?error=Erreur lors de l'ajout du produit");
    exit();
}


$db->close();


header("Location: /pages/add.php?ok=1");
exit();

?>
