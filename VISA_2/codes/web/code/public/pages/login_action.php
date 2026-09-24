<?php
session_start();

if (isset($_POST['email']) && isset($_POST['password'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    
    //$db = new SQLite3('/var/www/data/comptes.sqlite');
    //$stmt = $db->prepare("SELECT * FROM utilisateurs WHERE EMAIL = :email AND PASS = :pass");
    //$stmt->bindValue(':email', $email, SQLITE3_TEXT);
    //$stmt->bindValue(':pass', $password, SQLITE3_TEXT);
    //$result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

    $db = "db"
    $dbhost = "mariadb"
    $dbport = 3306
    $dbuser = "mariadb_user"
    $dbpassword = "05102006"

    $pdo = new PDO('mysql:host'.$dbhost';port='.$dbport';dbname='.$db'', $dbuser, $dbpassword)
    $pdo->prepare("SELECT * FROM utilisateurs WHERE EMAIL = :email AND PASS = :pass")
    $pdo->bindValue(':email', $email);
    $pdo->bindValue(':pass', $password);
    $result = $pdo->execute();

    $status = "Échec";
    $role = "N/A";

    if ($result) {
        $_SESSION['email'] = $email;
        $_SESSION['statut'] = $result['STATUT'];
        $status = "Succès";
        $role = $result['STATUT'];
        $redirect = "../index.php";
    } else {
        $_SESSION['login_error'] = "Email ou mot de passe incorrect.";
        $redirect = "../connexion.php";
    }

    //Écriture du log
    $log = fopen("/var/www/logs/connexion.log", "a");
    fwrite($log, date("Y-m-d H:i:s") . " - $email - " . $_SERVER['REMOTE_ADDR'] . " - $status - Rôle : $role\n");
    fclose($log);

    //Redirection après log
    header("Location: /index.php");
    exit();
}
?>
