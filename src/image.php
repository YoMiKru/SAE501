<?php
session_start();

header('Content-Type: image/png');

$width = 120;
$height = 40;
$image = imagecreatetruecolor($width, $height);

// Couleurs
$bgColor = imagecolorallocate($image, 255, 255, 255);
$textColor = imagecolorallocate($image, 0, 0, 0);
$lineColor = imagecolorallocate($image, 64, 64, 64);

imagefilledrectangle($image, 0, 0, $width, $height, $bgColor);

// Générer un code captcha (exemple 5 lettres)
$chars = 'ABCDEFGHJKLMNPRSTUVWXYZ23456789';
$code = '';
for ($i = 0; $i < 5; $i++) {
    $code .= $chars[random_int(0, strlen($chars) - 1)];
}

// Stocker en session
$_SESSION['code'] = $code;

// Ajouter du bruit (lignes)
for ($i = 0; $i < 5; $i++) {
    imageline($image, random_int(0, $width), random_int(0, $height), random_int(0, $width), random_int(0, $height), $lineColor);
}

// Écrire le texte captcha
$font = __DIR__ . '/arial.ttf'; // mettre une vraie police .ttf dans ce fichier (ou utiliser imagestring pour police basique)
if (file_exists($font)) {
    // texte avec font TTF
    imagettftext($image, 20, random_int(-10, 10), 10, 30, $textColor, $font, $code);
} else {
    // fallback sans TTF
    imagestring($image, 5, 10, 10, $code, $textColor);
}

// Envoyer l'image
imagepng($image);
imagedestroy($image);
