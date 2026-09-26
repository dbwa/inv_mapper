<?php
//utilisez cela pour debeugger:
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include_once(__DIR__ . "/fonctions.inc.php");
connect();

// Action creant un compte : jeton CSRF obligatoire.
csrf_check();

if (isSet($_POST['username']) && isSet($_POST['password']) && isSet($_POST['pass'])) {

    $username = $_POST['username'];
    $password = $_POST['password'];
    $pass = $_POST['pass'];  //le code donné en ammont

    if (!preg_match('/^[A-Za-z0-9_][A-Za-z0-9_.-]{1,49}$/', $username)) {
        exit();
    }

    list($count, $row) = register_user($username, $password, $pass);

    if ($count == 1) {
        $_SESSION['login_user'] = '';
        $_SESSION['login_name'] = '';
        echo $row['login'];
    } else {
        $_SESSION['login_user'] = '';
        $_SESSION['login_name'] = '';
    }
}
?>