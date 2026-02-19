<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "Database.php";
require_once "Template.php";

// SÉCURITÉ : Admin uniquement
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: profil.php");
    exit();
}

$db = (new Database())->getConnection();

// 1. Récupération des statistiques globales
$stats_users = $db->query("SELECT COUNT(*) FROM utilisateurs")->fetchColumn();
$stats_trajets = $db->query("SELECT COUNT(*) FROM trajets")->fetchColumn();
$stats_avis = $db->query("SELECT COUNT(*) FROM avis")->fetchColumn();
$total_credits = $db->query("SELECT SUM(credits) FROM utilisateurs")->fetchColumn();

// 2. US 13 : Calcul des crédits "gagnés" par jour (7 derniers jours)
$sql_stats_credits = "
    SELECT DATE(date_reservation) as jour, COUNT(*) * 2 as total_credits_jour 
    FROM reservations 
    WHERE date_reservation >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(date_reservation)
    ORDER BY jour ASC
";
$res_credits = $db->query($sql_stats_credits)->fetchAll(PDO::FETCH_ASSOC);

// 3. NOUVEAU : Calcul du nombre de trajets par jour (7 derniers jours)
$sql_stats_trajets = "
    SELECT DATE(date_depart) as jour, COUNT(*) as nb_trajets 
    FROM trajets 
    WHERE date_depart >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(date_depart)
    ORDER BY jour ASC
";
$res_trajets_jour = $db->query($sql_stats_trajets)->fetchAll(PDO::FETCH_ASSOC);

// Préparation des données pour les graphiques
$labels_c = []; $values_c = [];
foreach ($res_credits as $row) {
    $labels_c[] = date('d/m', strtotime($row['jour']));
    $values_c[] = $row['total_credits_jour'];
}

$labels_t = []; $values_t = [];
foreach ($res_trajets_jour as $row) {
    $labels_t[] = date('d/m', strtotime($row['jour']));
    $values_t[] = $row['nb_trajets'];
}

$page = new Template("Statistiques Admin - EcoRide");
$page->afficherHeader();
?>

<main style="max-width: 1000px; margin: 40px auto; font-family: sans-serif; padding: 0 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h2 style="color: #2e7d32; margin: 0;">📊 Statistiques de la plateforme</h2>
        <a href="admin_dashboard.php" style="text-decoration: none; color: #666; font-weight: bold; padding: 8px 15px; border: 1px solid #ddd; border-radius: 8px;">← Retour Dashboard</a>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px;">
        <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); border-left: 5px solid #2e7d32; text-align: center;">
            <small style="color: #888; text-transform: uppercase; font-weight: bold;">Utilisateurs</small><br>
            <strong style="font-size: 2.2em; color: #333;"><?php echo $stats_users; ?></strong>
        </div>
        <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); border-left: 5px solid #1976d2; text-align: center;">
            <small style="color: #888; text-transform: uppercase; font-weight: bold;">Covoiturages</small><br>
            <strong style="font-size: 2.2em; color: #333;"><?php echo $stats_trajets; ?></strong>
        </div>
        <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); border-left: 5px solid #fbc02d; text-align: center;">
            <small style="color: #888; text-transform: uppercase; font-weight: bold;">Avis Clients</small><br>
            <strong style="font-size: 2.2em; color: #333;"><?php echo $stats_avis; ?></strong>
        </div>
        <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); border-left: 5px solid #d32f2f; text-align: center;">
            <small style="color: #888; text-transform: uppercase; font-weight: bold;">Crédits globaux</small><br>
            <strong style="font-size: 2.2em; color: #333;"><?php echo number_format($total_credits, 0, '.', ' '); ?></strong>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
        
        <div style="background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
            <h3 style="margin-top: 0; font-size: 1.1em; color: #333;">🪙 Crédits générés</h3>
            <div style="display: flex; align-items: flex-end; gap: 10px; height: 180px; margin-top: 20px; border-bottom: 2px solid #eee; padding-bottom: 5px;">
                <?php 
                $max_c = (!empty($values_c)) ? max($values_c) : 10;
                foreach($values_c as $index => $val): 
                    $h = ($val / $max_c) * 100;
                ?>
                    <div style="flex: 1; display: flex; flex-direction: column; align-items: center; height: 100%; justify-content: flex-end;">
                        <span style="font-size: 0.7em; font-weight: bold; color: #2e7d32;"><?php echo $val; ?></span>
                        <div style="width: 80%; background: #81c784; height: <?php echo $h; ?>%; border-radius: 4px 4px 0 0;" title="<?php echo $labels_c[$index]; ?>"></div>
                        <span style="font-size: 0.75em; color: #999; margin-top: 5px;"><?php echo $labels_c[$index]; ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div style="background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
            <h3 style="margin-top: 0; font-size: 1.1em; color: #333;">🚗 Nombre de trajets</h3>
            <div style="display: flex; align-items: flex-end; gap: 10px; height: 180px; margin-top: 20px; border-bottom: 2px solid #eee; padding-bottom: 5px;">
                <?php 
                $max_t = (!empty($values_t)) ? max($values_t) : 5;
                foreach($values_t as $index => $val): 
                    $h = ($val / $max_t) * 100;
                ?>
                    <div style="flex: 1; display: flex; flex-direction: column; align-items: center; height: 100%; justify-content: flex-end;">
                        <span style="font-size: 0.7em; font-weight: bold; color: #1976d2;"><?php echo $val; ?></span>
                        <div style="width: 80%; background: #64b5f6; height: <?php echo $h; ?>%; border-radius: 4px 4px 0 0;" title="<?php echo $labels_t[$index]; ?>"></div>
                        <span style="font-size: 0.75em; color: #999; margin-top: 5px;"><?php echo $labels_t[$index]; ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>
</main>

<?php $page->afficherFooter(); ?>