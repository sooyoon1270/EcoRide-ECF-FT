<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "Database.php";
// Vérification de sécurité
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin' && isset($_GET['id'])) {
    $db = (new Database())->getConnection();
    $id_avis = intval($_GET['id']);

    try {
        $stmt = $db->prepare("DELETE FROM avis WHERE id = ?");
        $stmt->execute([$id_avis]);
         // On retourne sur la liste avec un message de succès
        header("Location: admin_avis.php?msg=supprime");
        exit();
    } catch (PDOException $e) {
        die("Erreur lors de la suppression : " . $e->getMessage());
    }
} else {
    // Si on n'est pas admin, on bloque
    header("Location: profil.php");
    exit();
} 