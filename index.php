<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Affichage des erreurs pour le développement
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "Template.php";
require_once "Accueil.php";
require_once "Database.php";

// 1. Initialisation de la connexion à la base de données
$database = new Database();
$pdo = $database->getConnection();

// 2. Initialisation de la classe Accueil
$pa = new PageAccueil();

// 3. Création du template et affichage du header
$monAccueil = new Template("EcoRide - Accueil");
$monAccueil->afficherHeader();

// --- ÉTAPE 1 : AFFICHAGE DU MESSAGE DE PRÉSENTATION ---
$pa->afficherPresentation();

// --- ÉTAPE 2 : AFFICHAGE DE LA BARRE DE RECHERCHE UNIFIÉE ---
// On l'affiche en haut pour qu'elle soit visible tout de suite
$pa->afficherBarreRecherche();

// --- ÉTAPE 3 : TRAITEMENT DE LA RECHERCHE (SI FORMULAIRE ENVOYÉ) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // On récupère les données envoyées par le formulaire de la classe Accueil
        // Note : dans la classe, les noms sont 'dep' et 'arr' car c'est une méthode GET vers recherche.php
        // Mais si tu restes en POST sur l'index, on vérifie les noms correspondants :
        $depSaisi = $_POST["dep"] ?? '';
        $arrSaisi = $_POST["arr"] ?? '';

        $depPropre = $pa->verifierVille($depSaisi);
        $arrPropre = $pa->verifierVille($arrSaisi);
        
        // On effectue la recherche
        $trajetsTrouves = $pa->rechercherTrajets($pdo, $depPropre, $arrPropre);
        
        echo "<section style='max-width: 800px; margin: 20px auto;'>";
        if (empty($trajetsTrouves)) {
            echo "<p style='color: orange; font-weight: bold; text-align: center;'>Aucun trajet disponible pour ce parcours.</p>";
        } else {
            echo "<h3 style='text-align: center;'>Résultats de votre recherche :</h3>";
            foreach ($trajetsTrouves as $t) {
                ?>
                <div style="border: 2px solid #2e7d32; padding: 15px; margin: 10px auto; max-width: 450px; border-radius: 10px; background-color: #f9f9f9; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                    <p><strong>📍 De :</strong> <?php echo htmlspecialchars($t['depart']); ?> <strong> vers </strong> <?php echo htmlspecialchars($t['arrivee']); ?></p>
                    <p><strong>👤 Conducteur :</strong> <?php echo htmlspecialchars($t['conducteur_nom']); ?></p>
                    <p><strong>🚗 Voiture :</strong> <?php echo htmlspecialchars($t['voiture_modele'] ?? 'Non précisé'); ?></p>
                    <p style="color: #2e7d32; font-weight: bold; font-size: 1.2em;">Prix : <?php echo $t['prix']; ?> €</p>
                    <a href="recherche.php?dep=<?php echo urlencode($t['depart']); ?>&arr=<?php echo urlencode($t['arrivee']); ?>" style="display: inline-block; margin-top: 10px; color: #1a73e8; text-decoration: none; font-weight: bold;">Voir plus de détails →</a>
                </div>
                <?php
            }
        }
        echo "</section>";
    } catch (Exception $e) {
        echo "<p style='color: red; font-weight: bold; text-align: center;'> Erreur : " . $e->getMessage() . "</p>";
    }
}

// --- ÉTAPE 4 : MESSAGE DE BIENVENUE (SI CONNECTÉ) ---
if (isset($_SESSION['est_connecte']) && $_SESSION['est_connecte'] === true): ?>
    <div style="max-width: 800px; margin: 20px auto; background-color: #d1edda; padding: 15px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; border: 1px solid #c3e6cb;">
        <span style="color: #155724;">
            ✨ <strong>Bienvenue <?php echo htmlspecialchars($_SESSION['user']); ?> !</strong> Heureux de vous revoir.
        </span>
        <a href="deconnexion.php" style="background-color: #dc3545; color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 14px;">
            Se déconnecter
        </a>
    </div>
<?php endif; 

// On affiche le footer
$monAccueil->afficherFooter();
?>