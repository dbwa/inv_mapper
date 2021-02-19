<?php
include_once(__DIR__ . "/fonctions.inc.php");
connect();
session_start();
if (isSet($_POST['username']) && isSet($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    list($count, $row) = authentificate($username, $password);
    if ($count == 1) {
        $_SESSION['login_user'] = $row['login'];
        $_SESSION['login_name'] = $row['name'];
        echo $row['login'];
    } else {
        $_SESSION['login_user'] = '';
        $_SESSION['login_name'] = '';
    }
}
?>