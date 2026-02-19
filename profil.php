<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
}
require_once "Database.php";
require_once "Template.php";

$database = new Database();
$pdo = $database->getConnection();
$user_id = $_SESSION['user_id'];

// --- RÉCUPÉRATION DES DONNÉES UTILISATEUR ---
$stmt_u = $pdo->prepare("SELECT credits, type_utilisateur FROM utilisateurs WHERE id = ?");
$stmt_u->execute([$user_id]);
$user_data = $stmt_u->fetch(PDO::FETCH_ASSOC);
$solde_actuel = $user_data['credits'] ?? 0;
$role_actuel = $user_data['type_utilisateur'] ?? 'passager';

// 1. RÉSERVATIONS (En tant que PASSAGER)
$sql_res = "SELECT t.*, r.id as reservation_id, r.statut as resa_statut, t.statut_id as voyage_statut
            FROM reservations r
            JOIN trajets t ON r.id_trajet = t.id
            WHERE r.id_utilisateur = :user_id";
$stmt_res = $pdo->prepare($sql_res);
$stmt_res->execute([':user_id' => $user_id]);
$mes_reservations = $stmt_res->fetchAll(PDO::FETCH_ASSOC);

// 2. TRAJETS PROPOSÉS (En tant que CHAUFFEUR)
$sql_mes_trajets = "SELECT * FROM trajets WHERE id_utilisateur = :user_id AND statut_id != 3 AND statut_id != 4 ORDER BY date_depart ASC";
$stmt_mes_trajets = $pdo->prepare($sql_mes_trajets);
$stmt_mes_trajets->execute([':user_id' => $user_id]);
$mes_offres = $stmt_mes_trajets->fetchAll(PDO::FETCH_ASSOC);

// 3. VÉHICULES
$stmt_voiture = $pdo->prepare("SELECT * FROM voitures WHERE id_utilisateur = ?");
$stmt_voiture->execute([$user_id]);
$mes_voitures = $stmt_voiture->fetchAll(PDO::FETCH_ASSOC);

$page = new Template("Mon Profil - EcoRide");
$page->afficherHeader();
?>

<main style="max-width: 900px; margin: 40px auto; font-family: sans-serif; padding: 0 20px;">

    <?php if (isset($_GET['success']) || isset($_GET['status']) || isset($_GET['error'])): ?>
        <?php 
            $isError = isset($_GET['error']);
            $msgCode = $_GET['success'] ?? $_GET['status'] ?? $_GET['error'];
            $bgColor = $isError ? "#f8d7da" : "#d4edda";
            $textColor = $isError ? "#721c24" : "#155724";
            $borderColor = $isError ? "#f5c6cb" : "#c3e6cb";
        ?>
        <div style="background: <?= $bgColor ?>; color: <?= $textColor ?>; padding: 15px; border-radius: 12px; margin-bottom: 25px; border: 1px solid <?= $borderColor ?>; text-align: center; font-weight: bold;">
            <?php 
                if($msgCode === 'reservation_ok') echo "✅ Réservation confirmée !";
                elseif($msgCode === 'annule_ok') echo "🗑️ Annulation confirmée et remboursement effectué.";
                elseif($msgCode === 'role_updated') echo "👤 Statut mis à jour avec succès !";
                elseif($msgCode === 'avis_ok') echo "⭐ Merci ! Votre avis est en attente de modération. 🕒";
                elseif($msgCode === 'trajet_ok') echo "🚗 Votre trajet a été publié !";
                elseif($msgCode === 'statut_updated') echo "🏁 Trajet terminé avec succès !";
                elseif($msgCode === 'pas_assez_de_credits') echo "⚠️ Solde insuffisant pour réserver.";
                else echo htmlspecialchars($msgCode);
            ?>
        </div>
    <?php endif; ?>

    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; background: #fff; padding: 20px; border-radius: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); margin-bottom: 30px;">
        <div>
            <h2 style="color: #2e7d32; margin: 0 0 10px 0;">👤 Mon Profil</h2>
            <p style="margin: 5px 0;"><strong><?php echo htmlspecialchars($_SESSION['prenom'] . " " . $_SESSION['nom']); ?></strong></p>
            <p style="margin: 5px 0; color: #666;"><?php echo htmlspecialchars($_SESSION['email']); ?></p>
        </div>
        <div style="background: #f1f8e9; padding: 15px 25px; border-radius: 12px; border: 2px solid #2e7d32; text-align: center;">
            <span style="font-size: 0.8em; color: #555; font-weight: bold;">MON SOLDE</span><br>
            <strong style="font-size: 2em; color: #2e7d32;"><?php echo number_format($solde_actuel, 0, ',', ' '); ?> coins 🪙</strong>
        </div>
    </div>

    <div style="background: white; padding: 20px; border-radius: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); margin-bottom: 30px; border-left: 5px solid #2e7d32;">
        <h3 style="margin-top: 0;">🛠️ Mon statut EcoRide</h3>
        <form action="update_role.php" method="POST" style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
            <select name="nouveau_role" style="padding: 10px; border-radius: 8px; border: 1px solid #ddd;">
                <option value="passager" <?php echo ($role_actuel == 'passager') ? 'selected' : ''; ?>>Passager uniquement</option>
                <option value="chauffeur" <?php echo ($role_actuel == 'chauffeur') ? 'selected' : ''; ?>>Chauffeur uniquement</option>
                <option value="les_deux" <?php echo ($role_actuel == 'les_deux') ? 'selected' : ''; ?>>Passager & Chauffeur</option>
            </select>
            <button type="submit" style="background: #2e7d32; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: bold;">Mettre à jour</button>
        </form>
    </div>

    <?php if ($role_actuel !== 'passager'): ?>
        <div style="margin-bottom: 30px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h3 style="margin: 0;">🚗 Mes Véhicules</h3>
                <a href="ajouter_voiture.php" style="background: #eee; padding: 5px 12px; border-radius: 5px; text-decoration: none; font-size: 0.9em; color: #333;">+ Ajouter</a>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px;">
                <?php if (empty($mes_voitures)): ?>
                    <p style="color: #888;">Aucun véhicule enregistré.</p>
                <?php else: ?>
                    <?php foreach ($mes_voitures as $v): ?>
                        <div style="background: white; padding: 15px; border-radius: 10px; border: 1px solid #eee;">
                            <strong><?php echo htmlspecialchars($v['marque'] . " " . $v['modele']); ?></strong><br>
                            <small style="color: #666;">Immat: <?php echo htmlspecialchars($v['immatriculation']); ?></small>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div style="margin-bottom: 30px; background: white; padding: 20px; border-radius: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="margin: 0;">📅 Trajets que je propose</h3>
                <a href="proposer_trajet.php" style="background: #2e7d32; color: white; padding: 10px 15px; border-radius: 8px; text-decoration: none; font-weight: bold;">+ Proposer</a>
            </div>
            <?php foreach ($mes_offres as $offre): ?>
                <div style="padding: 15px; border: 1px solid #f0f0f0; border-radius: 10px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <strong><?php echo htmlspecialchars($offre['depart']); ?> ➔ <?php echo htmlspecialchars($offre['arrivee']); ?></strong><br>
                        <small><?php echo date('d/m/Y à H:i', strtotime($offre['date_depart'])); ?></small>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <a href="proposer_action.php?action=annuler&id=<?php echo $offre['id']; ?>"
                           onclick="return confirm('Annuler ce trajet remboursera tous les passagers. Confirmer ?')"
                           style="background: #fff; color: #d32f2f; padding: 8px 12px; border-radius: 5px; text-decoration: none; font-size: 0.85em; border: 1px solid #d32f2f;">Annuler</a>
                       
                        <?php if ($offre['statut_id'] == 1): ?>
                            <a href="update_statut.php?id=<?php echo $offre['id']; ?>&new_statut=2" style="background: #2e7d32; color: white; padding: 8px 12px; border-radius: 5px; text-decoration: none; font-size: 0.85em;">Démarrer</a>
                        <?php elseif ($offre['statut_id'] == 2): ?>
                            <a href="update_statut.php?id=<?php echo $offre['id']; ?>&new_statut=4" 
                               onclick="return confirm('Voulez-vous marquer ce trajet comme terminé ?')"
                               style="background: #1976d2; color: white; padding: 8px 12px; border-radius: 5px; text-decoration: none; font-size: 0.85em;">🏁 Terminer</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div style="background: white; padding: 20px; border-radius: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
        <h3>🎟️ Mes réservations (Passager)</h3>
        <?php if (empty($mes_reservations)): ?>
            <p style="color: #888;">Aucune réservation pour le moment.</p>
        <?php else: ?>
            <?php foreach ($mes_reservations as $res): ?>
                <div style="padding: 15px; border-bottom: 1px solid #f0f0f0; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <strong><?php echo htmlspecialchars($res['depart']); ?> ➔ <?php echo htmlspecialchars($res['arrivee']); ?></strong><br>
                        <small>Départ : <?php echo date('d/m/Y', strtotime($res['date_depart'])); ?></small>
                    </div>
                    <div style="text-align: right;">
                        <?php if ($res['voyage_statut'] == 1): ?>
                                <a href="proposer_action.php?action=annuler_reservation&id=<?php echo $res['id']; ?>"
                               onclick="return confirm('Voulez-vous annuler votre place et être remboursé ?')"
                               style="color: #d32f2f; text-decoration: none; font-weight: bold; border: 1px solid #d32f2f; padding: 5px 10px; border-radius: 5px; font-size: 0.8em;">Annuler</a>
                        
                        <?php elseif ($res['voyage_statut'] == 2 && $res['resa_statut'] !== 'termine'): ?>
                            <a href="valider_trajet.php?id=<?php echo $res['reservation_id']; ?>"
                               style="background: #2e7d32; color: white; text-decoration: none; padding: 8px 12px; border-radius: 5px; font-weight: bold; font-size: 0.85em;">✅ Arrivé ?</a>
                            <br><a href="signaler_probleme.php?id=<?php echo $res['reservation_id']; ?>" style="color: #d32f2f; font-size: 0.7em; text-decoration: underline;">Signaler un problème</a>
                        
                        <?php elseif ($res['resa_statut'] === 'termine' || $res['voyage_statut'] == 4): ?>
                            <a href="laisser_avis.php?id_trajet=<?php echo $res['id']; ?>"
                               style="background: #fbc02d; color: #333; text-decoration: none; padding: 8px 12px; border-radius: 5px; font-weight: bold; font-size: 0.85em;">⭐ Noter le voyage</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div style="text-align: center; margin-top: 40px;">
        <a href="deconnexion.php" style="color: #d32f2f; text-decoration: none; font-weight: bold; border: 1px solid #d32f2f; padding: 10px 30px; border-radius: 50px;">Se déconnecter</a>
    </div>
</main>

<?php $page->afficherFooter(); ?>