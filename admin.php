<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "Database.php";
require_once "Template.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: profil.php"); exit();
}

$db = (new Database())->getConnection();
$users = $db->query("SELECT id, nom, prenom, email, role FROM utilisateurs ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
$page = new Template("Gestion Utilisateurs - EcoRide");
$page->afficherHeader();
?>

<main style="max-width: 1000px; margin: 40px auto; padding: 20px; font-family: sans-serif;">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h2 style="color: #2e7d32;">👥 Liste des membres</h2>
        <a href="admin.php" style="text-decoration: none; color: #666;">⬅ Retour Dashboard</a>
    </div>

   
    <table style="width: 100%; border-collapse: collapse; background: white; box-shadow: 0 4px 10px rgba(0,0,0,0.1); margin-top: 20px;">
        <thead style="background: #2e7d32; color: white;">
            <tr>
                <th style="padding: 12px; text-align: left;">ID</th>
                <th style="padding: 12px; text-align: left;">Utilisateur</th>
                <th style="padding: 12px; text-align: left;">Email</th>
                <th style="padding: 12px; text-align: center;">Rôle</th>
                <th style="padding: 12px; text-align: center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
            <tr style="border-bottom: 1px solid #eee;">
                <td style="padding: 12px; color: #888;">#<?php echo $u['id']; ?></td>
                <td style="padding: 12px;"><strong><?php echo htmlspecialchars($u['prenom'] . " " . $u['nom']); ?></strong></td>
                <td style="padding: 12px;"><?php echo htmlspecialchars($u['email']); ?></td>
                <td style="padding: 12px; text-align: center;">
                    <span style="padding: 3px 8px; border-radius: 10px; font-size: 0.8em; background: <?php echo ($u['role'] === 'admin' ? '#fff3e0' : '#e8f5e9'); ?>; color: <?php echo ($u['role'] === 'admin' ? '#ef6c00' : '#2e7d32'); ?>;">
                        <?php echo strtoupper($u['role']); ?>
                    </span>
                </td>
                <td style="padding: 12px; text-align: center;">
                    <?php if ($u['id'] != $_SESSION['user_id']): ?>
                        <a href="admin_delete_user.php?id=<?php echo $u['id']; ?>"
                           onclick="return confirm('Attention : cela supprimera aussi ses trajets et avis ! Continuer ?')"
                           style="color: #d32f2f; text-decoration: none; font-size: 0.8em; border: 1px solid #d32f2f; padding: 5px 8px; border-radius: 4px;">Exclure</a>
                    <?php else: ?>
                        <span style="color: #ccc; font-size: 0.8em;">(Moi)</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>
<?php $page->afficherFooter(); ?>