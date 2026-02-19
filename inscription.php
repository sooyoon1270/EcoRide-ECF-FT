<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "Database.php";
require_once "Template.php";

$page = new Template("EcoRide - Inscription");
$page->afficherHeader();
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $database = new Database();
    $pdo = $database->getConnection();

    // On récupère les infos
    $email = $_POST['email'];
    $mdp = password_hash($_POST['password'], PASSWORD_DEFAULT); // Sécurité max
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
// On vérifie si l'email existe déjà
    $check = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
    $check->execute([$email]);

    if ($check->rowCount() > 0) {
        $message = "❌ Cet email est déjà utilisé.";
    } else {
        // Insertion
        $sql = "INSERT INTO utilisateurs (email, mot_de_passe, nom, prenom) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$email, $mdp, $nom, $prenom])) {
            $message = "✅ Inscription réussie ! <a href='connexion.php'>Connectez-vous ici</a>";
        }
    }
}
?>

<main style="max-width: 400px; margin: 50px auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px; background: #fff;">
    <h2 style="text-align: center; color: #2e7d32;">Créer un compte</h2>
    <?php echo $message; ?>
    <form method="POST" style="display: flex; flex-direction: column; gap: 15px; margin-top: 20px;">
        <input type="text" name="nom" placeholder="Nom" required style="padding: 10px;">
        <input type="text" name="prenom" placeholder="Prénom" required style="padding: 10px;">
        <input type="email" name="email" placeholder="Email" required style="padding: 10px;">
        <input type="password" name="password" placeholder="Mot de passe" required style="padding: 10px;">
        <button type="submit" style="background: #2e7d32; color: white; padding: 12px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">
            S'inscrire
        </button>
    </form>
</main>
<?php $page->afficherFooter(); ?>