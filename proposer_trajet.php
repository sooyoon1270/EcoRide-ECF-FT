<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Sécurité : on vérifie que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php"); 
    exit();
}

require_once "Database.php";
require_once "Template.php";

$database = new Database();
$pdo = $database->getConnection();

$page = new Template("EcoRide - Proposer un Trajet");
$page->afficherHeader();
?>

<main class="container">
    <div class="form-publication">
        <h2 style="text-align: center;">Proposer un nouveau trajet ⚡</h2>
        
        <form action="proposer_action.php" method="POST">
            
            <div class="field-row">
                <div>
                    <label>Ville de Départ :</label>
                    <input type="text" name="depart" placeholder="Ex: Paris" required>
                </div>
                <div>
                    <label>Ville d'Arrivée :</label>
                    <input type="text" name="arrivee" placeholder="Ex: Lyon" required>
                </div>
            </div>

            <div class="field-row">
                <div>
                    <label>Date et heure :</label>
                    <input type="datetime-local" name="date_depart" required>
                </div>
                <div>
                    <label>Nombre de places :</label>
                    <input type="number" name="places" placeholder="Ex: 3" required>
                </div>
            </div>

            <label>Choisir votre véhicule :</label>
            <select name="id_voiture" required>
                <option value="">-- Sélectionnez une voiture --</option>
                <?php
                $stmt = $pdo->prepare("SELECT id, modele, immatriculation FROM voitures WHERE id_utilisateur = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $voitures = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (count($voitures) > 0) {
                    foreach ($voitures as $v) {
                        echo "<option value='{$v['id']}'>" . htmlspecialchars($v['modele']) . " (" . htmlspecialchars($v['immatriculation']) . ")</option>";
                    }
                } else {
                    echo "<option value='' disabled>Aucune voiture enregistrée</option>";
                }
                ?>
            </select>

            <div class="options-group">
                <label class="option-item">
                    <input type="checkbox" name="animaux" value="1"> 
                    <span>Autoriser les animaux 🐶</span>
                </label>

                <label class="option-item">
                    <input type="checkbox" name="fumeur" value="1"> 
                    <span>Fumeur autorisé 🚬</span>
                </label>
                
                <label class="option-item">
                    <input type="checkbox" name="est_electrique" value="1"> 
                    <span>Covoiturage Éco (Électrique) 🌱</span>
                </label>
            </div>

            <button type="submit" class="btn-eco" style="width: 100%;">
                Publier le trajet ⚡
            </button>
            
            <?php if (count($voitures) === 0): ?>
                <p style="color: #d32f2f; font-size: 0.9em; text-align: center; font-weight: bold; margin-top: 15px;">
                    ⚠️ Vous devez <a href="ajouter_voiture.php" style="color: #d32f2f;">ajouter une voiture</a> avant de publier.
                </p>
            <?php endif; ?>
        </form>
    </div>
</main>

<?php $page->afficherFooter(); ?>