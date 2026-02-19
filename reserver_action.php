<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "Database.php";

// Sécurité : il faut être connecté pour réserver
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
}

$id_trajet = $_POST['id_trajet'] ?? null;
$id_passager = $_SESSION['user_id'];

if ($id_trajet) {
    $db = (new Database())->getConnection();

    // 1. On récupère les infos du trajet ET le solde du passager
    // US 10 : Gestion des crédits
    $stmt_trajet = $db->prepare("SELECT places, id_utilisateur, prix FROM trajets WHERE id = ?");
    $stmt_trajet->execute([$id_trajet]);
    $trajet = $stmt_trajet->fetch(PDO::FETCH_ASSOC);

    $stmt_user = $db->prepare("SELECT credits FROM utilisateurs WHERE id = ?");
    $stmt_user->execute([$id_passager]);
    $user_credits = $stmt_user->fetchColumn();

    // 2. Vérifications de sécurité
    if (!$trajet) {
        header("Location: recherche.php?error=trajet_inexistant");
        exit();
    }

    if ($trajet['id_utilisateur'] == $id_passager) {
        header("Location: recherche.php?error=votre_propre_trajet");
        exit();
    }

    if ($trajet['places'] <= 0) {
        header("Location: recherche.php?error=plus_de_places");
        exit();
    }

    if ($user_credits < $trajet['prix']) {
        header("Location: recherche.php?error=pas_assez_de_credits");
        exit();
    }

    try {
        // On utilise une TRANSACTION pour garantir que tout se passe bien ou rien du tout
        $db->beginTransaction();

        // 3. ON DÉDUIT LES CRÉDITS DU PASSAGER
        $upd_credits = $db->prepare("UPDATE utilisateurs SET credits = credits - ? WHERE id = ?");
        $upd_credits->execute([$trajet['prix'], $id_passager]);

        // 4. ON INSÈRE LA RÉSERVATION
        $ins = $db->prepare("INSERT INTO reservations (id_trajet, id_utilisateur, date_reservation, statut) VALUES (?, ?, NOW(), 'en_attente')");
        $ins->execute([$id_trajet, $id_passager]);

        // 5. ON DÉCRÉMENTE LES PLACES
        $upd_places = $db->prepare("UPDATE trajets SET places = places - 1 WHERE id = ?");
        $upd_places->execute([$id_trajet]);

        $db->commit();
        
        // Redirection avec un message clair
        header("Location: profil.php?status=reservation_ok");

    } catch (Exception $e) {
        $db->rollBack();
        header("Location: recherche.php?error=erreur_serveur");
    }
} else {
    header("Location: recherche.php");
}
exit();