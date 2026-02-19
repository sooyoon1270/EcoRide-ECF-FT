<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "Database.php";

if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin' && isset($_GET['id'])) {
    $db = (new Database())->getConnection();
    // Grâce à "ON DELETE CASCADE" dans la base, supprimer l'utilisateur supprimera ses voitures/avis/trajets automatiquement.
    $stmt = $db->prepare("DELETE FROM utilisateurs WHERE id = ?");
    $stmt->execute([$_GET['id']]);
}
header("Location: admin_users.php");
exit();