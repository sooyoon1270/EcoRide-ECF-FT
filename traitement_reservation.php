<?php
session_start();
require_once "Database.php";
$database = new Database();
$db = $database->getConnection();

// On vérifie que l'utilisateur est bien connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_trajet'])) {
    $id_trajet = (int)$_POST['id_trajet'];
    $user_id = (int)$_SESSION['user_id'];

    try {
        $db->beginTransaction();

        // 1. Débiter les 2 crédits (Table: utilisateurs, Colonne: credits)
        $updateU = $db->prepare("UPDATE utilisateurs SET credits = credits - 2 WHERE id = ? AND credits >= 2");
        $updateU->execute([$user_id]);
        
        if ($updateU->rowCount() === 0) {
            throw new Exception("Nombre de crédits insuffisant pour participer.");
        }

        // 2. Retirer une place au trajet (Table: trajets, Colonne: places)
        $updateT = $db->prepare("UPDATE trajets SET places = places - 1 WHERE id = ? AND places > 0");
        $updateT->execute([$id_trajet]);

        if ($updateT->rowCount() === 0) {
            throw new Exception("Désolé, il n'y a plus de places disponibles.");
        }

        // 3. Enregistrer la réservation (Table: reservations)
        // On ne précise pas date_reservation car elle est en current_timestamp()
        $insertR = $db->prepare("INSERT INTO reservations (id_trajet, id_utilisateur, statut) VALUES (?, ?, 'confirmé')");
        $insertR->execute([$id_trajet, $user_id]);

        $db->commit();
        header("Location: profil.php?success=Réservation confirmée !");
        exit();

    } catch (Exception $e) {
        $db->rollBack();
        header("Location: profil.php?error=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    header("Location: recherche.php");
    exit();
}