<?php
session_start();
require_once "Database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
}

$db = (new Database())->getConnection();
$user_id = $_SESSION['user_id'];

// On récupère l'action (GET pour annuler, POST pour publier)
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// --- CAS 1 : ANNULATION ---
// AJOUT : On accepte aussi 'annuler_reservation' envoyé par le profil
if ($action === 'annuler' || $action === 'annuler_reservation') {
    $id_cible = (int)($_GET['id'] ?? 0);

    if ($id_cible <= 0) {
        header("Location: profil.php?error=Action impossible");
        exit();
    }

    try {
        $db->beginTransaction();

        $stmt = $db->prepare("SELECT id_utilisateur FROM trajets WHERE id = ?");
        $stmt->execute([$id_cible]);
        $trajet = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$trajet) {
            throw new Exception("Trajet introuvable.");
        }

        $is_chauffeur = ($trajet['id_utilisateur'] == $user_id);

        if ($is_chauffeur) {
            // --- CAS CHAUFFEUR ---
            $stmtRes = $db->prepare("SELECT id_utilisateur FROM reservations WHERE id_trajet = ?");
            $stmtRes->execute([$id_cible]);
            $passagers = $stmtRes->fetchAll();

            foreach ($passagers as $p) {
                // Remboursement fixe de 2 crédits par passager
                $db->prepare("UPDATE utilisateurs SET credits = credits + 2 WHERE id = ?")
                   ->execute([$p['id_utilisateur']]);
            }
            $db->prepare("UPDATE trajets SET statut_id = 3 WHERE id = ?")->execute([$id_cible]);
            $msg = "annule_ok";
        } else {
            // --- CAS PASSAGER ---
            $stmtCheck = $db->prepare("SELECT id FROM reservations WHERE id_trajet = ? AND id_utilisateur = ?");
            $stmtCheck->execute([$id_cible, $user_id]);
            
            if ($stmtCheck->rowCount() > 0) {
                $db->prepare("UPDATE utilisateurs SET credits = credits + 2 WHERE id = ?")
                   ->execute([$user_id]);

                $db->prepare("DELETE FROM reservations WHERE id_trajet = ? AND id_utilisateur = ?")
                   ->execute([$id_cible, $user_id]);
                $db->prepare("UPDATE trajets SET places = places + 1 WHERE id = ?")
                   ->execute([$id_cible]);
                
                $msg = "annule_ok";
            } else {
                throw new Exception("Aucune réservation trouvée.");
            }
        }

        $db->commit();
        header("Location: profil.php?success=" . $msg);
        exit();

    } catch (Exception $e) {
        if ($db->inTransaction()) $db->rollBack();
        header("Location: profil.php?error=" . urlencode($e->getMessage()));
        exit();
    }
} 

// --- CAS 2 : PUBLICATION D'UN TRAJET ---
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Vérification des données du formulaire
        if (empty($_POST['date_depart']) || empty($_POST['depart']) || empty($_POST['arrivee'])) {
            throw new Exception("Veuillez remplir tous les champs obligatoires.");
        }

        $depart = $_POST['depart'];
        $arrivee = $_POST['arrivee'];
        $date_depart = $_POST['date_depart'];
        $places = (int)$_POST['places'];
        $prix = (float)($_POST['prix'] ?? 2.00); // Par défaut 2 crédits
        $voiture_id = (int)$_POST['id_voiture'];

        $sql = "INSERT INTO trajets (id_utilisateur, id_voiture, depart, arrivee, date_depart, places, prix, statut_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 1)";
        
        $stmt = $db->prepare($sql);
        $result = $stmt->execute([$user_id, $voiture_id, $depart, $arrivee, $date_depart, $places, $prix]);

        if ($result) {
            header("Location: profil.php?success=trajet_ok");
            exit();
        } else {
            throw new Exception("Erreur lors de la création du trajet.");
        }

    } catch (Exception $e) {
        header("Location: profil.php?error=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    header("Location: profil.php");
    exit();
}