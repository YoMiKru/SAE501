<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' ||
    !isset($_POST['email'], $_POST['password'])) {
    header('Location: /pages/login.php');
    exit();
}

$email = trim($_POST['email']);
$password = $_POST['password'];

try {
    $donnees = array(
        'email' => $email,
        'password' => $password
    );

    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL => 'http://api_auth:8000/login',                    # api_auth est le nom du service dans le docker-compose, docker résout automatiquement les noms des services
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($donnees),
        CURLOPT_HTTPHEADER => array('Content-Type: application/json')
    ));

    $reponse = curl_exec($ch);

    if ($reponse && $reponse["status"] === "Success") {
        $_SESSION['email'] = $reponse["user"];
        $_SESSION['statut'] = $reponse["admin"] ? 'admin' : 'user';
        $status = 'Succès';
        $redirect = '/index.php';
    } else {
        $_SESSION['login_error'] = 'Email ou mot de passe incorrect.';
        $status = 'Échec';
        $role = 'N/A';
        $redirect = '/pages/login.php';
    }
}
else{
    throw new Exception('Erreur lors de la requête cURL : ' . curl_error($ch));
}
finally{
    curl_close($ch);
}

// Journal de connexion : ne jamais y écrire le mot de passe.
$log = fopen('/var/www/logs/connexion.log', 'a');
if ($log !== false) {
    fwrite(
        $log,
        date('Y-m-d H:i:s') . ' - ' .
        str_replace(["\r", "\n"], '', $email) . ' - ' .
        ($_SERVER['REMOTE_ADDR'] ?? 'inconnue') . ' - ' .
        $status . ' - Rôle : ' . $role . PHP_EOL
    );
    fclose($log);
}

header('Location: ' . $redirect);
exit();


?>