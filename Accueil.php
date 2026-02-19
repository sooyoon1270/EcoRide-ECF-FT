<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "Database.php";

class PageAccueil {
    private $regexVille = "/^[a-zA-Z\s\-éèàâêîôûäëïöüçÉÈÀÂÊÎÔÛÄËÏÖÜÇ]+$/";

    public function verifierVille($ville) {
        $ville = trim($ville);
        if (empty($ville)) {
            throw new Exception("Le champ ville est obligatoire.");
        }
        if (!preg_match($this->regexVille, $ville)) {
            throw new Exception("Le nom de la ville est invalide.");
        }
        return $ville;
    }

    // --- LA BARRE DE RECHERCHE UNIFIÉE (CORRIGÉE AVEC US 10) ---
    public function afficherBarreRecherche($depart = '', $arrivee = '', $date = '', $eco = '', $prix_max = '', $note_min = 0) {
        ?>
        <section style="background: white; padding: 30px; margin-bottom: 40px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-top: 4px solid #2e7d32;">
            <form method="GET" action="recherche.php" style="max-width: 1000px; margin: 0 auto;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; margin-bottom: 20px;">
                    
                    <div style="position: relative;">
                        <label style="font-size: 0.8em; color: #666; font-weight: bold;">DÉPART</label>
                        <input type="text" id="depart" name="dep" placeholder="Ex: Paris" value="<?php echo htmlspecialchars($depart); ?>" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ddd; outline-color: #2e7d32;">
                        <div id="suggestions-dep" class="suggestion-box"></div>
                    </div>

                    <div style="position: relative;">
                        <label style="font-size: 0.8em; color: #666; font-weight: bold;">ARRIVÉE</label>
                        <input type="text" id="arrivee" name="arr" placeholder="Ex: Lyon" value="<?php echo htmlspecialchars($arrivee); ?>" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ddd; outline-color: #2e7d32;">
                        <div id="suggestions-arr" class="suggestion-box"></div>
                    </div>

                    <div>
                        <label style="font-size: 0.8em; color: #666; font-weight: bold;">DATE</label>
                        <input type="date" name="date_rep" value="<?php echo htmlspecialchars($date); ?>" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ddd;">
                    </div>

                    <div>
                        <label style="font-size: 0.8em; color: #666; font-weight: bold;">NOTE MIN. (⭐)</label>
                        <select name="note_min" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ddd; background: white;">
                            <option value="0" <?php echo ($note_min == 0) ? 'selected' : ''; ?>>Toutes</option>
                            <option value="1" <?php echo ($note_min == 1) ? 'selected' : ''; ?>>1+</option>
                            <option value="2" <?php echo ($note_min == 2) ? 'selected' : ''; ?>>2+</option>
                            <option value="3" <?php echo ($note_min == 3) ? 'selected' : ''; ?>>3+</option>
                            <option value="4" <?php echo ($note_min == 4) ? 'selected' : ''; ?>>4+</option>
                        </select>
                    </div>
                </div>

                <div style="display: flex; gap: 20px; align-items: center; justify-content: space-between; flex-wrap: wrap;">
                    <div style="display: flex; gap: 20px; align-items: center;">
                        <label style="cursor: pointer; font-weight: bold; color: #2e7d32; display: flex; align-items: center; gap: 5px;">
                            <input type="checkbox" name="eco" value="1" <?php echo ($eco === '1') ? 'checked' : ''; ?>> 🌱 Électrique
                        </label>
                        <label style="font-weight: bold;">
                            Prix max : <input type="number" name="prix_max" value="<?php echo htmlspecialchars($prix_max); ?>" style="width: 70px; padding: 8px; border-radius: 5px; border: 1px solid #ddd;"> Eco-crédits
                        </label>
                    </div>
                    <button type="submit" style="background: #2e7d32; color: white; padding: 12px 35px; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; font-size: 1.1em; transition: 0.3s;">
                        🔍 Rechercher
                    </button>
                </div>
            </form>
        </section>
        <?php
    }

    public function afficherPresentation() {
        ?>
        <section style="max-width: 1000px; margin: 40px auto; padding: 20px; text-align: center; font-family: sans-serif;">
            <h1 style="color: #2e7d32; font-size: 2.5em; margin-bottom: 10px;">Voyagez vert, voyagez ensemble.</h1>
            <p style="color: #555; font-size: 1.1em; line-height: 1.6; max-width: 800px; margin: 0 auto 30px auto;">
                <strong>EcoRide</strong> est la plateforme de covoiturage engagée pour la planète. 
                Réduisez votre empreinte carbone tout en partageant vos frais de route avec une communauté responsable.
            </p>

            <div style="display: flex; gap: 20px; justify-content: center; flex-wrap: wrap; margin-top: 20px;">
                <div style="flex: 1; min-width: 250px; background: #f1f8e9; padding: 25px; border-radius: 15px; border-bottom: 4px solid #2e7d32; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                    <div style="font-size: 2.5em; margin-bottom: 10px;">🌿</div>
                    <h3 style="color: #1b5e20; margin: 0 0 10px 0;">Écologique</h3>
                    <p style="font-size: 0.9em; color: #666; margin: 0;">Favorisez les véhicules électriques et gagnez des éco-crédits pour chaque kilomètre.</p>
                </div>
                
                <div style="flex: 1; min-width: 250px; background: #e3f2fd; padding: 25px; border-radius: 15px; border-bottom: 4px solid #1976d2; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                    <div style="font-size: 2.5em; margin-bottom: 10px;">💰</div>
                    <h3 style="color: #0d47a1; margin: 0 0 10px 0;">Économique</h3>
                    <p style="font-size: 0.9em; color: #666; margin: 0;">Divisez vos frais de route. Le covoiturage est la solution la plus rentable.</p>
                </div>

                <div style="flex: 1; min-width: 250px; background: #fff3e0; padding: 25px; border-radius: 15px; border-bottom: 4px solid #ef6c00; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                    <div style="font-size: 2.5em; margin-bottom: 10px;">🛡️</div>
                    <h3 style="color: #e65100; margin: 0 0 10px 0;">Sécurité</h3>
                    <p style="font-size: 0.9em; color: #666; margin: 0;">Voyagez sereinement grâce à nos profils vérifiés et nos avis modérés.</p>
                </div>
            </div>
        </section>
        <hr style="border: 0; height: 1px; background: #eee; max-width: 800px; margin: 40px auto;">
        <?php
    }

    public function rechercherTrajets($pdo, $depart, $arrivee, $date = null) {
        $depCherchee = trim($depart);
        $arrCherchee = trim($arrivee);
        // Correction de la requête pour correspondre à tes noms de colonnes (minuscules/majuscules)
        $sql = "SELECT t.*, u.prenom as conducteur_nom, v.modele as voiture_modele 
                FROM trajets t
                INNER JOIN utilisateurs u ON t.id_utilisateur = u.id
                LEFT JOIN voitures v ON t.id_voiture = v.id
                WHERE t.depart LIKE ? AND t.arrivee LIKE ? AND t.statut_id = 1";
        $params = ["%$depCherchee%", "%$arrCherchee%"];
        
        if (!empty($date)) { 
            $sql .= " AND DATE(t.date_depart) = ?"; 
            $params[] = $date; 
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}