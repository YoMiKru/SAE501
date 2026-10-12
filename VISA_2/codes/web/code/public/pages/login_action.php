<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' ||
    !isset($_POST['email'], $_POST['password'])) {
    header('Location: /pages/login.php');
    exit();
}

require_once '/var/www/src/includes/database.php';

$email = trim($_POST['email']);
$password = $_POST['password'];

try {
    $db = getDatabase();
    $stmt = $db->prepare(
        'SELECT Email, Pass, Statut FROM Utilisateur WHERE Email = :email AND Pass = :password'
    );
    $stmt->execute([
        ':email' => $email,
        ':password' => $password,
    ]);
    $result = $stmt->fetch();

    $status = 'Échec';
    $role = 'N/A';

    if ($result) {
        $_SESSION['email'] = $result['Email'];
        $_SESSION['statut'] = $result['Statut'];
        $status = 'Succès';
        $role = $result['Statut'];
        $redirect = '/index.php';
    } else {
        $_SESSION['login_error'] = 'Email ou mot de passe incorrect.';
        $redirect = '/pages/login.php';
    }
} catch (PDOException $e) {
    error_log('Erreur de connexion MariaDB : ' . $e->getMessage());
    $_SESSION['login_error'] = 'Service temporairement indisponible. Réessayez plus tard.';
    $status = 'Erreur BDD';
    $role = 'N/A';
    $redirect = '/pages/login.php';
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
