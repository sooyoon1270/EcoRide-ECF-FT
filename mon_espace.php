<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
//si l'utilisateur n'est pas connecté, rediriger vers la page de connexion
if (!isset($_SESSION['est connecte']) || $_SESSION['est connecte'] !== true) {
    header("Location: connexion.php");
    exit();//on arrete tout pourqu'on ne puisse pas accéder à la page sans être connecté
}
//si on arrive ici c'est que l'utilisateur est connecté.
require_once "Template.php";
$page = new Template("EcoRide - Mon Espace");
$page->afficherHeader();
?>

<main style="padding: 50px; text-align: center;">
    <h1>Bienvenue dans votre espace privé</h1>
    <p>Vous etes connecté en tant que <strong><?php echo htmlspecialchars($_SESSION['user']);?></strong></p>

    <div style="margin-top: 30px; border: 1px solid #ddd; padding: 20px; border-radius: 10px;">
        <h3>Ou voudriez vous aller aujourdhui ? </h3>
        <ul style="list-style: none; padding: 0;">
            <li><a href="creer_trajet.php">Proposer un nouveau trajet</a></li>
            <li><a href="detail_trajet.php">Voir mes trajets</a></li>
        </ul>
</div>
</main>
<?php $page->afficherFooter(); ?>