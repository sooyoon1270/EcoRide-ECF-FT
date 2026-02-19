<?php
session_start();
require_once "Database.php";
require_once "Template.php";
require_once "Accueil.php"; // Pour réutiliser la barre de recherche

$db = (new Database())->getConnection();
$pageAccueil = new PageAccueil();

// 1. RÉCUPÉRATION DES FILTRES (GET)
$dep = $_GET['dep'] ?? '';
$arr = $_GET['arr'] ?? '';
$date_rep = $_GET['date_rep'] ?? '';
$eco = $_GET['eco'] ?? '';
$prix_max = $_GET['prix_max'] ?? '';
$note_min = (int)($_GET['note_min'] ?? 0);

// 2. CONSTRUCTION DE LA REQUÊTE SQL COMPLEXE
// On calcule la moyenne des notes (AVG) pour le conducteur dans une sous-requête
$sql = "SELECT t.*, u.prenom as conducteur_nom, v.modele as voiture_modele, v.energie,
        (SELECT AVG(note) FROM avis WHERE id_destinataire = u.id AND statut_id = 'valide') as moyenne_note
        FROM trajets t
        INNER JOIN utilisateurs u ON t.id_utilisateur = u.id
        LEFT JOIN voitures v ON t.id_voiture = v.id
        WHERE t.statut_id = 1"; // On ne prend que les trajets actifs

$params = [];

// Filtre Départ / Arrivée
if (!empty($dep)) { $sql .= " AND t.depart LIKE :dep"; $params[':dep'] = "%$dep%"; }
if (!empty($arr)) { $sql .= " AND t.arrivee LIKE :arr"; $params[':arr'] = "%$arr%"; }

// Filtre Date
if (!empty($date_rep)) { $sql .= " AND DATE(t.date_depart) = :date_rep"; $params[':date_rep'] = $date_rep; }

// Filtre Écologique (Voiture électrique)
if ($eco === '1') { $sql .= " AND v.energie = 'Electrique'"; }

// Filtre Prix Max
if (!empty($prix_max)) { $sql .= " AND t.prix <= :prix_max"; $params[':prix_max'] = $prix_max; }

// FILTRE US 10 : Note Minimale (on utilise HAVING car moyenne_note est calculée)
if ($note_min > 0) {
    $sql .= " HAVING (moyenne_note >= :note_min OR moyenne_note IS NULL)"; 
    $params[':note_min'] = $note_min;
}

$sql .= " ORDER BY t.date_depart ASC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$trajets = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. AFFICHAGE
$page = new Template("Recherche de trajets - EcoRide");
$page->afficherHeader();
?>

<main class="container">
    <h2 style="color: var(--eco-green); margin-top: 30px;">Trouver un trajet</h2>
    
    <?php $pageAccueil->afficherBarreRecherche($dep, $arr, $date_rep, $eco, $prix_max, $note_min); ?>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 30px;">
        <?php if (empty($trajets)): ?>
            <p style="text-align: center; grid-column: 1/-1; padding: 50px; background: white; border-radius: 10px;">
                Aucun trajet ne correspond à vos critères. Essayez d'élargir votre recherche !
            </p>
        <?php else: ?>
            <?php foreach ($trajets as $t): ?>
                <div style="background: white; padding: 20px; border-radius: 15px; box-shadow: var(--card-shadow); border-left: 5px solid <?php echo ($t['energie'] == 'Electrique') ? '#4caf50' : '#ddd'; ?>;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <span style="font-size: 0.8em; color: #888;"><?php echo date('d/m/Y à H:i', strtotime($t['date_depart'])); ?></span>
                            <h3 style="margin: 5px 0;"><?php echo htmlspecialchars($t['depart']); ?> ➔ <?php echo htmlspecialchars($t['arrivee']); ?></h3>
                            <p style="margin: 0; color: #555;">Conducteur : <strong><?php echo htmlspecialchars($t['conducteur_nom']); ?></strong></p>
                            
                            <div style="margin-top: 5px;">
                                <?php if ($t['moyenne_note']): ?>
                                    <span style="color: #f39c12; font-weight: bold;">⭐ <?php echo round($t['moyenne_note'], 1); ?>/5</span>
                                <?php else: ?>
                                    <span style="color: #bbb; font-size: 0.8em;">Nouveau chauffeur</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 1.5em; font-weight: bold; color: var(--eco-green);"><?php echo $t['prix']; ?> Eco-credits</div>
                        <span style="font-size: 0.7em; color: #999;">
                        <?php echo $t['places_disponibles'] ?? $t['places'] ?? 'X'; ?> places restantes
                        </span>
                        </div>
                    </div>

                    <hr style="border: none; border-top: 1px solid #eee; margin: 15px 0;">
                    
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 0.9em; color: #666;">🚗 <?php echo htmlspecialchars($t['voiture_modele'] ?? 'Véhicule'); ?></span>
                        <a href="confirmer_reservation.php?id=<?php echo $t['id']; ?>" class="btn-eco" style="padding: 8px 15px; font-size: 0.9em;">Réserver</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<?php $page->afficherFooter(); ?>