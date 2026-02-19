<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "Database.php";
require_once "Template.php";

// 1. SÉCURITÉ
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'employe' && $_SESSION['role'] !== 'admin')) {
    header("Location: profil.php"); 
    exit();
}

$db = (new Database())->getConnection();

// 2. LOGIQUE DE MODÉRATION (Valider / Supprimer)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($_GET['action'] === 'valider') {
        $stmt = $db->prepare("UPDATE avis SET statut_id = 1 WHERE id = ?");
        $stmt->execute([$id]);
        $msg = "Avis validé.";
    } elseif ($_GET['action'] === 'supprimer') {
        $stmt = $db->prepare("DELETE FROM avis WHERE id = ?");
        $stmt->execute([$id]);
        $msg = "Avis supprimé.";
    }
    header("Location: employe.php?msg=" . urlencode($msg));
    exit();
}

// 3. RÉCUPÉRATION DES AVIS POUR LA MODÉRATION
$queryAvis = "SELECT a.id, a.commentaire, a.note, a.statut_id, u.pseudo 
              FROM avis a 
              JOIN utilisateurs u ON a.id_expediteur = u.id 
              ORDER BY a.statut_id DESC, a.date_avis DESC";
$avis = $db->query($queryAvis)->fetchAll(PDO::FETCH_ASSOC);

// 4. RÉCUPÉRATION DES INCIDENTS (US 12) - ADAPTÉE À TA TABLE
// On utilise 'depart', 'arrivee' et 'date_depart' comme vu sur ta capture
$queryIncidents = "
    SELECT 
        a.id_trajet, a.commentaire, a.note,
        u_exp.pseudo as exp_pseudo, u_exp.email as exp_email,
        u_dest.pseudo as dest_pseudo, u_dest.email as dest_email,
        t.depart, t.arrivee, t.date_depart 
    FROM avis a
    JOIN utilisateurs u_exp ON a.id_expediteur = u_exp.id
    JOIN utilisateurs u_dest ON a.id_destinataire = u_dest.id
    JOIN trajets t ON a.id_trajet = t.id
    WHERE a.note <= 2
    ORDER BY t.date_depart DESC";

$incidents = $db->query($queryIncidents)->fetchAll(PDO::FETCH_ASSOC);

$page = new Template("Espace Employé - EcoRide");
$page->afficherHeader();
?>

<main style="max-width: 1100px; margin: 40px auto; padding: 20px; font-family: sans-serif;">
    
    <h2 style="color: #2e7d32;">🛠 Modération des avis</h2>

    <?php if (isset($_GET['msg'])): ?>
        <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
            ✅ <?php echo htmlspecialchars($_GET['msg']); ?>
        </div>
    <?php endif; ?>

    <table style="width: 100%; border-collapse: collapse; background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 50px;">
        <thead style="background: #2e7d32; color: white;">
            <tr>
                <th style="padding: 12px; text-align: left;">Utilisateur</th>
                <th style="padding: 12px; text-align: left;">Commentaire</th>
                <th style="padding: 12px; text-align: center;">Note</th>
                <th style="padding: 12px; text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($avis as $a): ?>
            <tr style="border-bottom: 1px solid #eee;">
                <td style="padding: 12px;"><strong>@<?php echo htmlspecialchars($a['pseudo']); ?></strong></td>
                <td style="padding: 12px;"><?php echo htmlspecialchars($a['commentaire']); ?></td>
                <td style="padding: 12px; text-align: center; font-weight: bold; color: #fbc02d;">
                    <?php echo $a['note']; ?>/5
                </td>
                <td style="padding: 12px; text-align: center;">
                    <?php if ($a['statut_id'] != 1): ?>
                        <a href="?action=valider&id=<?php echo $a['id']; ?>" style="background: #2e7d32; color: white; padding: 5px 10px; border-radius: 3px; text-decoration: none; font-size: 0.8em;">Valider</a>
                    <?php endif; ?>
                    <a href="?action=supprimer&id=<?php echo $a['id']; ?>" style="background: #d32f2f; color: white; padding: 5px 10px; border-radius: 3px; text-decoration: none; font-size: 0.8em; margin-left: 5px;" onclick="return confirm('Supprimer ?')">Supprimer</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <hr style="border: 0; height: 1px; background: #eee; margin: 40px 0;">

    <h2 style="color: #d32f2f;">⚠️ Historique des Incidents (Notes ≤ 2)</h2>
    <table style="width: 100%; border-collapse: collapse; background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <thead style="background: #d32f2f; color: white;">
            <tr>
                <th style="padding: 12px; text-align: left;">N° Trajet</th>
                <th style="padding: 12px; text-align: left;">Itinéraire</th>
                <th style="padding: 12px; text-align: left;">Passager (Mail)</th>
                <th style="padding: 12px; text-align: left;">Conducteur (Mail)</th>
                <th style="padding: 12px; text-align: left;">Litige</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($incidents as $i): ?>
            <tr style="border-bottom: 1px solid #eee;">
                <td style="padding: 12px; font-weight: bold;">#<?php echo $i['id_trajet']; ?></td>
                <td style="padding: 12px; font-size: 0.9em;">
                    <strong><?php echo htmlspecialchars($i['depart']); ?> ➔ <?php echo htmlspecialchars($i['arrivee']); ?></strong><br>
                    Le <?php echo date('d/m/Y', strtotime($i['date_depart'])); ?>
                </td>
                <td style="padding: 12px; font-size: 0.85em;">
                    <strong>@<?php echo htmlspecialchars($i['exp_pseudo']); ?></strong><br>
                    <?php echo htmlspecialchars($i['exp_email']); ?>
                </td>
                <td style="padding: 12px; font-size: 0.85em;">
                    <strong>@<?php echo htmlspecialchars($i['dest_pseudo']); ?></strong><br>
                    <?php echo htmlspecialchars($i['dest_email']); ?>
                </td>
                <td style="padding: 12px;">
                    <span style="color: #d32f2f; font-weight: bold;">Note : <?php echo $i['note']; ?>/5</span><br>
                    <span style="font-size: 0.85em; font-style: italic;">"<?php echo htmlspecialchars($i['commentaire']); ?>"</span>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

<?php $page->afficherFooter(); ?>