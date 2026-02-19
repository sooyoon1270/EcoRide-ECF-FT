<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "Database.php";
require_once "Template.php";

// SÉCURITÉ : Vérification du rôle admin
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] !== 'employe' && $_SESSION['user']['role'] !== 'admin')) {
    header("Location: profil.php");
    exit();
}

// CONNEXION À LA BASE DE DONNÉES
$database = new Database();
$db = $database->getConnection();

//  TRAITEMENT DES ACTIONS DE MODÉRATION 
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id_avis = (int)$_GET['id'];
    
    if ($_GET['action'] === 'valider') {
        // On passe le statut à 1 (Validé)
        $stmt = $db->prepare("UPDATE avis SET statut_id = 1 WHERE id = ?");
        $stmt->execute([$id_avis]);
        header("Location: employe_dashboard.php?status=valide");
        exit();
    }
}

//  RÉCUPÉRATION DES STATISTIQUES 
$total_users = $db->query("SELECT COUNT(*) FROM utilisateurs")->fetchColumn();
$total_trajets = $db->query("SELECT COUNT(*) FROM trajets")->fetchColumn();

//  RÉCUPÉRATION DES AVIS 

$sql_attente = "SELECT a.*, u.prenom as auteur 
                FROM avis a 
                JOIN utilisateurs u ON a.id_expediteur = u.id 
                WHERE a.statut_id = 2 
                ORDER BY a.date_avis ASC";
$avis_en_attente = $db->query($sql_attente)->fetchAll(PDO::FETCH_ASSOC);

// 2. Historique des derniers avis validés (statut_id = 1)
$sql_valides = "SELECT * FROM avis WHERE statut_id = 1 ORDER BY date_avis DESC LIMIT 5";
$avis_recents = $db->query($sql_valides)->fetchAll(PDO::FETCH_ASSOC);

$page = new Template("Employé - EcoRide");
$page->afficherHeader();
?>
    <h2 style="color: #d32f2f;">⏳ Avis en attente de modération</h2>
    <div style="background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); overflow: hidden; margin-bottom: 30px;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead style="background: #d32f2f; color: white;">
                <tr>
                    <th style="padding: 12px; text-align: left;">Auteur</th>
                    <th style="padding: 12px; text-align: left;">Commentaire</th>
                    <th style="padding: 12px; text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($avis_en_attente)): ?>
                    <tr><td colspan="3" style="padding: 20px; text-align: center; color: #888;">Aucun avis à modérer.</td></tr>
                <?php else: ?>
                    <?php foreach ($avis_en_attente as $aa): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 12px;"><?php echo htmlspecialchars($aa['auteur']); ?></td>
                        <td style="padding: 12px; font-style: italic;">"<?php echo htmlspecialchars($aa['commentaire']); ?>"</td>
                        <td style="padding: 12px; text-align: center;">
                            <a href="admin_dashboard.php?action=valider&id=<?php echo $aa['id']; ?>" 
                               style="background: #2e7d32; color: white; padding: 5px 10px; border-radius: 5px; text-decoration: none; font-size: 0.8em; font-weight: bold;">✅ Valider</a>
                            <a href="admin_delete_avis.php?id=<?php echo $aa['id']; ?>"
                               onclick="return confirm('Supprimer cet avis ?')"
                               style="background: #d32f2f; color: white; padding: 5px 10px; border-radius: 5px; text-decoration: none; font-size: 0.8em; font-weight: bold; margin-left: 5px;">🗑️ Refuser</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <h3 style="color: #2e7d32;">⭐ Derniers avis publiés</h3>
    <div style="background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead style="background: #2e7d32; color: white;">
                <tr>
                    <th style="padding: 12px; text-align: left;">Date</th>
                    <th style="padding: 12px; text-align: left;">Note</th>
                    <th style="padding: 12px; text-align: left;">Commentaire</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($avis_recents as $avis): ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 12px;"><?php echo date('d/m/y', strtotime($avis['date_avis'])); ?></td>
                    <td style="padding: 12px; color: #fbc02d;">★ <?php echo $avis['note']; ?>/5</td>
                    <td style="padding: 12px;">"<?php echo htmlspecialchars($avis['commentaire']); ?>"</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
<?php $page->afficherFooter(); ?>