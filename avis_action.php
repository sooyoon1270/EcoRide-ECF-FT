<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "Database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {
    $database = new Database();
    $pdo = $database->getConnection();

    // On récupère les données du formulaire
    $id_trajet = (int)$_POST['id_trajet'];
    $note = (int)$_POST['note'];
    $commentaire = htmlspecialchars($_POST['commentaire']);
    $id_expediteur = $_SESSION['user_id']; // L'auteur (Passager)

    // Il nous faut l'id_destinataire (le chauffeur).
    // On va le chercher en base via l'id_trajet.
    $stmt_chauffeur = $pdo->prepare("SELECT id_utilisateur FROM trajets WHERE id = ?");
    $stmt_chauffeur->execute([$id_trajet]);
    $id_destinataire = $stmt_chauffeur->fetchColumn();
try {
               
        $sql = "INSERT INTO avis (id_trajet, id_expediteur, id_destinataire, note, commentaire)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$id_trajet, $id_expediteur, $id_destinataire, $note, $commentaire])) {
            header("Location: profil.php?status=avis_sent");
        } else {
            header("Location: profil.php?error=avis_failed");
        }
    } catch (PDOException $e) {
        die("Erreur SQL : " . $e->getMessage());
    }
} else {
    header("Location: profil.php");
}
exit();