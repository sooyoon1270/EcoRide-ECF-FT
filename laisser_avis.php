<?php
session_start();
require_once "Database.php";
require_once "Template.php";

// 1. VERIFICATION DE LA SESSION
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
}

$db = (new Database())->getConnection();

// --- BLOC DE TRAITEMENT PHP ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id']; // L'expéditeur
    $trajet_id = (int)($_POST['id_trajet'] ?? 0);
    $note = (int)$_POST['note'];
    $commentaire = htmlspecialchars($_POST['commentaire']);

    try {
        // A. ON RÉCUPÈRE L'ID DU CHAUFFEUR (le destinataire) depuis la table trajets
        $stmtChauffeur = $db->prepare("SELECT id_utilisateur FROM trajets WHERE id = ?");
        $stmtChauffeur->execute([$trajet_id]);
        $destinataire_id = $stmtChauffeur->fetchColumn();

        if (!$destinataire_id) {
            throw new Exception("Impossible de trouver le chauffeur pour ce trajet.");
        }

        // B. INSERTION DANS LA TABLE AVIS
        // On utilise tes colonnes : id_expediteur, id_destinataire, id_trajet, note, commentaire, statut_id
        $sql = "INSERT INTO avis (id_expediteur, id_destinataire, id_trajet, note, commentaire, statut_id) 
                VALUES (?, ?, ?, ?, ?, 2)"; 
        
        $stmt = $db->prepare($sql);
        $result = $stmt->execute([
            $user_id, 
            $destinataire_id, 
            $trajet_id, 
            $note, 
            $commentaire
        ]);

        if ($result) {
            // Redirection vers le profil avec succès
            header("Location: profil.php?success=avis_ok");
            exit();
        }
    } catch (Exception $e) {
        // En cas d'erreur, on renvoie l'erreur au profil pour affichage
        header("Location: profil.php?error=" . urlencode("Erreur SQL : " . $e->getMessage()));
        exit();
    }
}

// --- AFFICHAGE DE LA PAGE ---
$page = new Template("Laisser un avis - EcoRide");
$page->afficherHeader();

// On récupère l'id_trajet depuis l'URL (GET) envoyé par valider_trajet.php
$trajet_id = (int)($_GET['id_trajet'] ?? 0);
?>

<main style="max-width: 600px; margin: 60px auto; padding: 20px; font-family: sans-serif;">
    <div style="background: white; padding: 30px; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); border-top: 5px solid #2e7d32;">
        
        <h2 style="color: #2e7d32; margin-top: 0; text-align: center;">⭐ Votre avis compte</h2>
        <p style="text-align: center; color: #666; margin-bottom: 30px;">
            Comment s'est déroulé votre covoiturage ? Notez votre chauffeur pour aider la communauté EcoRide.
        </p>

        <form action="laisser_avis.php" method="POST">
            <input type="hidden" name="id_trajet" value="<?php echo $trajet_id; ?>">
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: bold; margin-bottom: 8px;">Note globale :</label>
                <select name="note" required style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid #ddd; background: #f9f9f9; font-size: 1em;">
                    <option value="5">⭐⭐⭐⭐⭐ Excellent</option>
                    <option value="4">⭐⭐⭐⭐ Très bien</option>
                    <option value="3">⭐⭐⭐ Moyen</option>
                    <option value="2">⭐⭐ Décevant</option>
                    <option value="1">⭐ Mauvais</option>
                </select>
            </div>

            <div style="margin-bottom: 25px;">
                <label style="display: block; font-weight: bold; margin-bottom: 8px;">Votre commentaire :</label>
                <textarea name="commentaire" rows="5" required 
                    style="width: 100%; padding: 15px; border-radius: 10px; border: 1px solid #ddd; background: #f9f9f9; font-family: inherit; resize: none;" 
                    placeholder="Partagez votre expérience avec ce chauffeur..."></textarea>
            </div>

            <button type="submit" style="width: 100%; background: #2e7d32; color: white; border: none; padding: 15px; border-radius: 10px; font-size: 1.1em; font-weight: bold; cursor: pointer; transition: background 0.3s;">
                Envoyer mon avis
            </button>
        </form>

        <div style="text-align: center; margin-top: 20px;">
            <a href="profil.php" style="color: #888; text-decoration: none; font-size: 0.9em;">Plus tard / Annuler</a>
        </div>
    </div>
</main>

<?php $page->afficherFooter(); ?>