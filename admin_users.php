<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "Database.php";
require_once "Template.php";

// Vérification de sécurité immédiate
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: profil.php");
    exit();
}

$db = (new Database())->getConnection();

// On récupère les infos essentielles. 
// on trie par nom pour que ce soit plus simple à lire pour l'admin.
$sql = "SELECT id, nom, prenom, email, role FROM utilisateurs ORDER BY nom ASC";
$users = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$page = new Template("Gestion Utilisateurs - EcoRide");
$page->afficherHeader();
?>

<main style="max-width: 1000px; margin: 30px auto; padding: 20px; font-family: 'Segoe UI', sans-serif;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="margin: 0;">👥 Annuaire des membres</h2>
        <a href="admin_dashboard.php" style="color: #2e7d32; text-decoration: none; font-weight: bold;">← Dashboard</a>
    </div>
    
    <div style="background: white; border: 1px solid #ddd; border-radius: 4px; overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f8f9fa; border-bottom: 2px solid #eee;">
                    <th style="padding: 15px; text-align: left; width: 50px;">ID</th>
                    <th style="padding: 15px; text-align: left;">Identité</th>
                    <th style="padding: 15px; text-align: left;">Contact</th>
                    <th style="padding: 15px; text-align: center;">Statut</th>
                    <th style="padding: 15px; text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 15px; color: #999;">#<?= $u['id'] ?></td>
                    <td style="padding: 15px;">
                        <strong><?= htmlspecialchars(strtoupper($u['nom'])) ?></strong> 
                        <?= htmlspecialchars($u['prenom']) ?>
                    </td>
                    <td style="padding: 15px; font-size: 0.9em;"><?= htmlspecialchars($u['email']) ?></td>
                    <td style="padding: 15px; text-align: center;">
                        <?php if ($u['role'] === 'admin'): ?>
                            <small style="background: #333; color: white; padding: 2px 8px; border-radius: 3px;">ADMIN</small>
                        <?php else: ?>
                            <small style="background: #eee; color: #666; padding: 2px 8px; border-radius: 3px;">USER</small>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 15px; text-align: right;">
                        <?php if ($u['id'] != $_SESSION['user_id']): ?>
                            <a href="admin_delete_user.php?id=<?= $u['id'] ?>" 
                               onclick="return confirm('Confirmer la suppression définitive de ce compte ?')"
                               style="color: #d32f2f; text-decoration: none; font-size: 0.85em;">
                               ❌ Supprimer
                            </a>
                        <?php else: ?>
                            <span style="color: #bbb; font-style: italic; font-size: 0.85em;">Mon compte</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<?php $page->afficherFooter(); ?>