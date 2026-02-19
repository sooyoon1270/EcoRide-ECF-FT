<?php
session_start();
require_once "Database.php";
require_once "Template.php";

if (!isset($_SESSION['user_id'])) { header("Location: connexion.php"); exit(); }

$db = (new Database())->getConnection();
$id_trajet = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Récupération des infos du trajet et de l'utilisateur avec tes vrais noms de colonnes
$t = $db->query("SELECT * FROM trajets WHERE id = $id_trajet")->fetch();
$u = $db->query("SELECT credits FROM utilisateurs WHERE id = $user_id")->fetch();

$page = new Template("Confirmation");
$page->afficherHeader();
?>

<main style="max-width: 600px; margin: 50px auto; text-align: center; font-family: sans-serif;">
    <div style="border: 1px solid #ddd; padding: 30px; border-radius: 10px; background: #fff;">
        <h2 style="color: #2e7d32;">Double Confirmation</h2>
        <p>Voulez-vous valider votre participation pour le trajet :<br>
        <strong><?php echo $t['depart']; ?> ➔ <?php echo $t['arrivee']; ?></strong> ?</p>

        <div style="background: #f1f8e9; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p>Places disponibles : <strong><?php echo $t['places']; ?></strong></p>
            <p>Votre solde : <strong><?php echo $u['credits']; ?> crédits</strong></p>
            <p style="color: #d32f2f;"><strong>Coût : 2 crédits</strong></p>
        </div>

        <?php if ($t['places'] > 0 && $u['credits'] >= 2): ?>
            <form action="traitement_reservation.php" method="POST">
                <input type="hidden" name="id_trajet" value="<?php echo $id_trajet; ?>">
                <button type="submit" style="background: #2e7d32; color: white; border: none; padding: 12px 25px; border-radius: 5px; cursor: pointer;">
                    OUI, j'utilise 2 crédits
                </button>
            </form>
        <?php else: ?>
            <p style="color: red;">⚠️ Crédits insuffisants ou plus de places disponibles.</p>
        <?php endif; ?>
        <a href="recherche.php" style="display: block; margin-top: 15px; color: #666;">Annuler</a>
    </div>
</main>
<?php $page->afficherFooter(); ?>