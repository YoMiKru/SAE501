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
        'username' => $email,
        'password' => $password
    );

    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL => 'http://api_auth:8001/login',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($donnees),
        CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_TIMEOUT => 5
    ));

    $reponse = curl_exec($ch);

    // curl_exec ne lève pas d'exception : il renvoie false si l'API est injoignable
    if ($reponse === false) {
        throw new Exception('Erreur cURL : ' . curl_error($ch));
    }

    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($code === 200) {
        $data = json_decode($reponse, true);
        if (!is_array($data) || !isset($data['user'], $data['admin'])) {
            throw new Exception('Réponse inattendue de l’API');
        }
        $role = filter_var($data['admin'], FILTER_VALIDATE_BOOLEAN) ? 'admin' : 'user';
        $_SESSION['email'] = $data['user'];
        $_SESSION['statut'] = $role;
        $status = 'Succès';
        $redirect = '/index.php';
    } elseif ($code === 401) {
        $_SESSION['login_error'] = 'Email ou mot de passe incorrect.';
        $status = 'Échec';
        $role = 'N/A';
        $redirect = '/pages/login.php';
    } else {
        throw new Exception('Code HTTP inattendu : ' . $code);
    }
}
catch (Exception $e) {
    error_log('Erreur de connexion à l’API : ' . $e->getMessage());
    $_SESSION['login_error'] = 'Service temporairement indisponible. Réessayez plus tard.';
    $status = 'Erreur API';
    $role = 'N/A';
    $redirect = '/pages/login.php';
}
finally{
    curl_close($ch);
}

header('Location: ' . $redirect);
exit();


?>