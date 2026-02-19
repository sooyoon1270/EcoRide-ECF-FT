<?php
session_start();
require_once "Template.php";

$page = new Template("Contact - EcoRide");
$page->afficherHeader();

// Simulation d'envoi de message
$message_success = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message_success = "Merci ! Votre message a bien été envoyé à l'équipe EcoRide. Nous vous répondrons sous 48h.";
}
?>

<main style="max-width: 800px; margin: 50px auto; font-family: sans-serif; padding: 0 20px;">
    <div style="text-align: center; margin-bottom: 40px;">
        <h2 style="color: #2e7d32;">Nous contacter</h2>
        <p style="color: #666;">Une question sur vos crédits ou un trajet ? Notre équipe est là pour vous.</p>
    </div>

    <?php if ($message_success): ?>
        <div style="background: #d4edda; color: #155724; padding: 20px; border-radius: 10px; margin-bottom: 30px; text-align: center; border: 1px solid #c3e6cb;">
            <strong><?php echo $message_success; ?></strong>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
        
        <form action="contact.php" method="POST" style="display: flex; flex-direction: column; gap: 15px;">
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Nom complet</label>
                <input type="text" name="nom" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Adresse Email</label>
                <input type="email" name="email" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Sujet</label>
                <input type="text" name="sujet" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Message</label>
                <textarea name="message" rows="5" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; resize: vertical;"></textarea>
            </div>
            <button type="submit" style="background: #2e7d32; color: white; border: none; padding: 12px; border-radius: 5px; font-weight: bold; cursor: pointer; transition: 0.3s;">
                Envoyer le message
            </button>
        </form>

        <div style="background: #f9f9f9; padding: 25px; border-radius: 10px;">
            <h4 style="margin-top: 0; color: #2e7d32;">Informations</h4>
            <p>📍 <strong>Adresse :</strong><br>62 rue je sais pas, Paris</p>
            <p>📞 <strong>Téléphone :</strong><br>01 23 45 67 89</p>
            <p>📧 <strong>Email :</strong><br>support@ecoride.fr</p>
            <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">
            <p style="font-size: 0.9em; color: #666;"><strong>Horaires du support :</strong><br>Lundi - Vendredi : 9h00 - 18h00</p>
        </div>
    </div>
</main>

<?php $page->afficherFooter(); ?>