<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "Database.php";
require_once "Template.php";

$id_trajet = $_GET['id'] ?? null;
if (!$id_trajet) { header("Location: recherche.php"); exit(); }
$db = (new Database())->getConnection();
// Requête complète pour récupérer le trajet, le chauffeur et la voiture
$sql = "SELECT t.*, u.prenom, u.nom, v.marque, v.modele, v.energie, v.immatriculation
        FROM trajets t
        JOIN utilisateurs u ON t.id_utilisateur = u.id
        LEFT JOIN voitures v ON t.id_voiture = v.id
        WHERE t.id = ?";
$stmt = $db->prepare($sql);
$stmt->execute([$id_trajet]);
$t = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$t) { echo "Trajet introuvable."; exit(); }

$page = new Template("Détails du trajet - EcoRide");
$page->afficherHeader();
?>
<main style="max-width: 800px; margin: 40px auto; padding: 20px; font-family: 'Segoe UI', sans-serif;">
    <div style="background: white; border-radius: 15px; shadow: 0 10px 30px rgba(0,0,0,0.1); overflow: hidden; border: 1px solid #eee;">
        <div style="background: #2e7d32; color: white; padding: 30px; text-align: center;">
            <h1 style="margin: 0;"><?php echo htmlspecialchars($t['depart']); ?> ➔ <?php echo htmlspecialchars($t['arrivee']); ?></h1>
            <p style="margin: 10px 0 0; opacity: 0.9;">Départ le <?php echo date('d/m/Y à H:i', strtotime($t['date_depart'])); ?></p>
        </div>
        <div style="padding: 30px; display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
            <div>
                <h3 style="color: #2e7d32; border-bottom: 2px solid #e8f5e9; padding-bottom: 10px;">📋 Détails</h3>
                <p><strong>💰 Prix :</strong> <?php echo $t['prix']; ?> €</p>
                <p><strong>👥 Places disponibles :</strong> <?php echo $t['places']; ?></p>
                <p><strong>👤 Chauffeur :</strong> <?php echo htmlspecialchars($t['prenom'] . " " . $t['nom']); ?></p>
            </div>

            <div style="background: #f9f9f9; padding: 20px; border-radius: 10px;">
                <h3 style="margin-top: 0; color: #1a73e8;">🚗 Le véhicule</h3>
                <?php if ($t['marque']): ?>
                    <p><strong>Modèle :</strong> <?php echo htmlspecialchars($t['marque'] . " " . $t['modele']); ?></p>
                    <p><strong>Énergie :</strong> <?php echo htmlspecialchars($t['energie']); ?></p>
                    <p style="font-size: 0.8em; color: #888;">Immat : <?php echo htmlspecialchars($t['immatriculation']); ?></p>
                <?php else: ?>
                    <p style="font-style: italic; color: #888;">Aucun véhicule spécifique enregistré pour ce trajet.</p>
                <?php endif; ?>
            </div>
        </div>

        <div style="padding: 0 30px 30px; text-align: center;">
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($_SESSION['user_id'] != $t['id_utilisateur']): ?>
                    <form action="reserver_action.php" method="POST">
                        <input type="hidden" name="id_trajet" value="<?php echo $t['id']; ?>">
                        <button type="submit" style="width: 100%; background: #2e7d32; color: white; padding: 15px; border: none; border-radius: 8px; font-size: 1.2em; font-weight: bold; cursor: pointer; transition: 0.3s;">
                            Confirmer la réservation
                        </button>
                    </form>
                <?php else: ?>
                    <p style="color: #666; font-style: italic;">C'est votre propre trajet.</p>
                <?php endif; ?>
            <?php else: ?>
                <a href="connexion.php" style="display: block; background: #666; color: white; padding: 15px; text-decoration: none; border-radius: 8px; font-weight: bold;">Connectez-vous pour réserver</a>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php $page->afficherFooter(); ?>