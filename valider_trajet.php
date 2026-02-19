<?php
session_start();
require_once "Database.php";

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: profil.php");
    exit();
}

$db = (new Database())->getConnection();
$user_id = $_SESSION['user_id'];
$id_recu = (int)$_GET['id']; // C'est l'ID de la réservation envoyé par profil.php

try {
    $db->beginTransaction();

    // 1. On récupère la réservation ET les infos du chauffeur d'un coup
    $stmt = $db->prepare("
        SELECT r.id_trajet, t.id_utilisateur AS chauffeur_id 
        FROM reservations r
        JOIN trajets t ON r.id_trajet = t.id
        WHERE r.id = ? AND r.id_utilisateur = ? AND r.statut != 'termine'
    ");
    $stmt->execute([$id_recu, $user_id]);
    $res = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($res) {
        $trajet_id = $res['id_trajet'];
        $chauffeur_id = $res['chauffeur_id'];

        //Créditer le chauffeur de 2 coins
        $db->prepare("UPDATE utilisateurs SET credits = credits + 2 WHERE id = ?")
           ->execute([$chauffeur_id]);

        //Marquer la réservation comme "termine"
        $db->prepare("UPDATE reservations SET statut = 'termine' WHERE id = ?")
           ->execute([$id_recu]);

        $db->commit();
        // Redirection vers l'avis
        header("Location: laisser_avis.php?id_trajet=" . $trajet_id);
        exit();
    } else {
        // Si on ne trouve rien, on redirige au lieu de laisser une page blanche
        throw new Exception("Validation impossible ou déjà effectuée.");
    }

} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    // On renvoie l'erreur au profil pour l'afficher
    header("Location: profil.php?error=" . urlencode($e->getMessage()));
    exit();
}