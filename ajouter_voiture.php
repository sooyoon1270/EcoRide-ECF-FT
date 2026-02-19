<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "Template.php";
if (!isset($_SESSION['user_id'])) { header("Location: connexion.php"); exit(); }

$page = new Template("Ajouter un véhicule - EcoRide");
$page->afficherHeader();
?>


<main style="max-width: 600px; margin: 40px auto; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); font-family: sans-serif;">
    <h2 style="color: #2e7d32; border-bottom: 2px solid #2e7d32; padding-bottom: 10px;">🚗 Mon Véhicule</h2>
    <form action="voiture_action.php" method="POST" style="margin-top: 20px;">
        <div style="margin-bottom: 15px;">
            <label>Marque :</label>
            <input type="text" name="marque" required placeholder="Ex: Tesla, Renault..." style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label>Modèle :</label>
            <input type="text" name="modele" required placeholder="Ex: Model 3, Zoe..." style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
        </div>


        <div style="display: flex; gap: 10px; margin-bottom: 15px;">
            <div style="flex: 1;">
                <label>Immatriculation :</label>
                <input type="text" name="immatriculation" required placeholder="AA-123-BB" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
            </div>
            <div style="flex: 1;">
                <label>Énergie :</label>
                <select name="energie" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                    <option value="Electrique">Électrique 🌿</option>
                    <option value="Hybride">Hybride</option>
                    <option value="Essence">Essence</option>
                    <option value="Diesel">Diesel</option>
                </select>
            </div>
        </div>

        <button type="submit" style="width: 100%; background: #2e7d32; color: white; padding: 12px; border: none; border-radius: 5px; font-weight: bold; cursor: pointer;">
            Enregistrer mon véhicule
        </button>
    </form>
</main>
<?php $page->afficherFooter(); ?>