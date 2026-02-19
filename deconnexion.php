<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION = array(); // On vide le tableau de session
session_destroy(); // On détruit toutes les données du badge
header("Location: index.php"); // On renvoie à l'accueil
exit();