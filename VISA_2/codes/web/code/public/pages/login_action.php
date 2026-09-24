<?php
session_start();

if (isset($_POST['email']) && isset($_POST['password'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Configuration de MariaDB (a séparer pour la suite dans src ?)
    $db = "db";
    $dbhost = "mariadb";
    $dbport = 3306;
    $dbuser = "mariadb_user";
    $dbpassword = "05102006";

    $pdo = new PDO(
        "mysql:host=$dbhost;port=$dbport;dbname=$db;charset=utf8mb4",
        $dbuser,
        $dbpassword
    );


    $stmt = $pdo->prepare("SELECT * FROM Utilisateurs WHERE Email = :email AND Pass = :pass");
    $stmt->bindValue(':email', $email);
    $stmt->bindValue(':pass', $password);

    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

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

    fwrite($log, 
            date("Y-m-d H:i:s") . 
            " - $email - " . 
            $_SERVER['REMOTE_ADDR'] . 
            " - $status - Rôle : $role\n"
            );
            
    fclose($log);

    //Redirection après log
    header("Location: /index.php");
    exit();
}
?>
