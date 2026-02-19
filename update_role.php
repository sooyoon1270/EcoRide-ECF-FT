<?php
session_start();
require_once "Database.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $db = (new Database())->getConnection();
    $role = $_POST['nouveau_role'];
    $user_id = $_SESSION['user_id'];

    $stmt = $db->prepare("UPDATE utilisateurs SET type_utilisateur = ? WHERE id = ?");
    if ($stmt->execute([$role, $user_id])) {
        header("Location: profil.php?status=role_updated");
    } else {
        header("Location: profil.php?error=Erreur lors de la mise à jour");
    }
    exit();
}