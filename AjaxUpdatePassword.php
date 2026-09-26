<?php
//utilisez cela pour debeugger:
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include_once(__DIR__ . "/fonctions.inc.php");
connect();

// Action modifiant des donnees : jeton CSRF obligatoire.
csrf_check();

if (isSet($_POST['currentpass']) && isSet($_POST['newpassword'])) {

    // Le login vient de la session, jamais de la requête (cf. besoin_securite.md
    // point 22) : on ne peut changer que le mot de passe de sa propre session.
    if (empty($_SESSION['login_user'])) {
        $reponse = array(
            'success' => false,
            'raison' => 'session expiree',
            'login' => ''
        );
        echo json_encode($reponse);
        return 1;
    }
    $username = $_SESSION['login_user'];
    $currentpass = $_POST['currentpass'];
    $newpassword = $_POST['newpassword'];  //le code donné en ammont

    $result = update_password($username, $currentpass, $newpassword);
    if ($result == 'password non changé')
    {
        $reponse = array(
            'success' => false, 
            'raison'=> 'erreur password',
            'login' => $username
        );
        echo json_encode($reponse);
        return 1;  
    }

    /*test du nouveau mot de passe*/
    list($count, $row) = authentificate($username, $newpassword);
    if ($count == 1) {
        $_SESSION['login_user'] = $row['login'];
        $_SESSION['login_name'] = $row['name'];
        $_SESSION['user_type'] = $row['user_type'];
        #pour maj des layers
        $reponse = array(
            'success' => true, 
            'login' => $row['login']
        );
        echo json_encode($reponse);

    } else {
        $_SESSION['login_user'] = '';
        $_SESSION['login_name'] = '';
        $_SESSION['user_type'] = '';
        $reponse = array(
            'success' => false, 
            'raison'=> 'erreur d\'autentification',
            'login' => ''
        );
        echo json_encode($reponse);

    }
}
?>