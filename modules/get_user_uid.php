<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include_once(__DIR__ . "/../fonctions.inc.php");
connect();

header('Content-Type: application/json');

if (isset($_SESSION['login_user'])) {
    $login = $_SESSION['login_user'];
    $uid = get_user_uid($login);
    echo json_encode(['uid_flashinvader' => $uid]);
} else {
    echo json_encode(['uid_flashinvader' => null]);
}
?>