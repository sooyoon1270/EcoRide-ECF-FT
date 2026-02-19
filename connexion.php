<?php
// 1. DÉMARRAGE DE LA SESSION EN TOUT PREMIER
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "Database.php";
require_once "Template.php";

$erreur = "";

// 2. TRAITEMENT DU FORMULAIRE 
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $database = new Database();
    $pdo = $database->getConnection();

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // On récupère l'utilisateur
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['mot_de_passe'])) {
        
        // RÉGÉNÉRATION DE SESSION (Sécurité)
        session_regenerate_id(true);

        // Assignation des variables de session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['prenom']  = $user['Prenom']; // Majuscule comme dans ta BDD
        $_SESSION['nom']     = $user['Nom'];    // Majuscule comme dans ta BDD
        $_SESSION['role']    = strtolower($user['Role']);
        $_SESSION['email']   = $user['email'];
        $_SESSION['credits'] = isset($user['credits']) ? $user['credits'] : 0;

        // REDIRECTION PROPRE
        if ($_SESSION['role'] === 'admin') {
            header("Location: admin_dashboard.php");
        } else {
           
            header("Location: recherche.php");
        }
        exit(); 
    } else {
        $erreur = "Identifiants ou Email incorrects.";
    }
}

// 3. AFFICHAGE DU HEADER
$connexion = new Template("EcoRide - Connexion");
$connexion->afficherHeader();
?>

<section style="text-align: center; padding: 40px; min-height: 60vh;">
    <div style="max-width: 400px; margin: 0 auto; background: #f9f9f9; padding: 30px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
        <h2 style="color: #2e7d32; margin-bottom: 20px;">Connexion</h2>
        
        <?php if ($erreur): ?>
            <p style="color: #d32f2f; background: #ffebee; padding: 10px; border-radius: 5px; font-weight: bold;">
                <?php echo $erreur; ?>
            </p>
        <?php endif; ?>

        <form method="POST" style="text-align: left;">
            <div style="margin-bottom: 15px;">
                <label for="email" style="font-weight: bold;">Email :</label><br>
                <input type="email" name="email" id="email" required 
                       style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; margin-top: 5px;">
            </div>
            
            <div style="margin-bottom: 20px;">
                <label for="password" style="font-weight: bold;">Mot de passe :</label><br>
                <input type="password" name="password" id="password" required 
                       style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; margin-top: 5px;">
            </div>
            
            <button type="submit" class="btn-eco" 
                    style="width: 100%; background: #2e7d32; color: white; border: none; padding: 12px; border-radius: 5px; font-weight: bold; cursor: pointer;">
                Se connecter
            </button>
        </form>
    </div>
</section>

<?php
$connexion->afficherFooter();
?>