<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "Database.php";

if (!isset($_SESSION['user_id']) || !isset($_GET['id']) || !isset($_GET['new_statut'])) {
    header("Location: profil.php");
    exit();
}


$database = new Database();
$pdo = $database->getConnection();

$trajet_id = (int)$_GET['id'];
$nouveau_statut = (int)$_GET['new_statut'];
try {
    // Mise à jour du statut dans la table 'trajets'
    $stmt = $pdo->prepare("UPDATE trajets SET statut_id = ? WHERE id = ? AND id_utilisateur = ?");
    $stmt->execute([$nouveau_statut, $trajet_id, $_SESSION['user_id']]);
    header("Location: profil.php?status=Statut mis à jour");
} catch (PDOException $e) {
    die("Erreur lors de la mise à jour : " . $e->getMessage());
}
exit();