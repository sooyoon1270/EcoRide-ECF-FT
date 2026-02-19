<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "Database.php";
require_once "Template.php";
// SÉCURITÉ : Uniquement pour les admins
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {

    header("Location: profil.php");

    exit();

}
$db = (new Database())->getConnection();
// On récupère tous les avis avec les noms des personnes concernées
$sql = "SELECT a.*,

               exp.prenom as exp_prenom,

               dest.prenom as dest_prenom

        FROM avis a

        JOIN utilisateurs exp ON a.id_expediteur = exp.id

        JOIN utilisateurs dest ON a.id_destinataire = dest.id

        ORDER BY a.date_avis DESC";

$avis_liste = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
$page = new Template("Modération des avis - EcoRide");
$page->afficherHeader();

?>
<main style="max-width: 1000px; margin: 40px auto; padding: 20px; font-family: sans-serif;">

    <h2 style="color: #2e7d32;">🛡️ Modération des avis</h2>
    <table style="width: 100%; border-collapse: collapse; background: white; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
        <thead style="background: #2e7d32; color: white;">
            <tr>
                <th style="padding: 12px; text-align: left;">De</th>
                <th style="padding: 12px; text-align: left;">Pour</th>
                <th style="padding: 12px; text-align: center;">Note</th>
                <th style="padding: 12px; text-align: left;">Commentaire</th>
                <th style="padding: 12px; text-align: center;">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($avis_liste as $a): ?>
            <tr style="border-bottom: 1px solid #eee;">
                <td style="padding: 12px;"><?php echo htmlspecialchars($a['exp_prenom']); ?></td>
                <td style="padding: 12px;"><?php echo htmlspecialchars($a['dest_prenom']); ?></td>
                <td style="padding: 12px; text-align: center; color: #fbc02d;">★ <?php echo $a['note']; ?></td>
                <td style="padding: 12px; font-size: 0.9em; max-width: 300px;"><?php echo htmlspecialchars($a['commentaire']); ?></td>
                <td style="padding: 12px; text-align: center;">
                    <a href="admin_delete_avis_action.php?id=<?php echo $a['id']; ?>"
                       onclick="return confirm('Voulez-vous vraiment supprimer cet avis ?')"
                       style="color: #d32f2f; text-decoration: none; font-weight: bold; padding: 5px 10px; border: 1px solid #d32f2f; border-radius: 4px;">
                       🗑️ Supprimer
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

<?php $page->afficherFooter(); ?> 