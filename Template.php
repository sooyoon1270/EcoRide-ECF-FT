<?php
class Template {
    protected $titre;
    public function __construct($titre) {
        $this->titre = $titre;
    }
    public function afficherHeader() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo htmlspecialchars($this->titre); ?></title>
            <style>
                /* 1. VARIABLES */
                :root {
                    --eco-green: #2E7D32;
                    --wood-brown: #6D4C41;
                    --soft-bg: #f0f4f0;
                    --dark-text: #263238;
                    --card-shadow: 0 10px 25px rgba(0,0,0,0.08);
                }

                /* 2. BASE */
                body {
                    font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
                    background-color: var(--soft-bg);
                    color: var(--dark-text);
                    margin: 0;
                    padding: 0;
                    line-height: 1.6;
                }
                .container {
                    max-width: 1100px;
                    margin: 0 auto;
                    padding: 20px;
                    width: 92%;
                }

                /* 3. NAVIGATION AMÉLIORÉE */
                nav {
                    background: #ffffff;
                    padding: 0.8rem 5%;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
                    position: sticky;
                    top: 0;
                    z-index: 1000;
                }
                nav .logo {
                    font-size: 1.5rem;
                    font-weight: 800;
                    color: var(--eco-green);
                    text-decoration: none;
                    letter-spacing: -0.5px;
                }
                nav .nav-menu {
                    display: flex;
                    align-items: center;
                }
                nav a {
                    text-decoration: none;
                    color: var(--dark-text);
                    font-weight: 600;
                    margin-left: 20px;
                    transition: all 0.2s ease;
                    font-size: 0.95rem;
                }
                nav a:hover {
                    color: var(--eco-green);
                }

                /* RESPONSIVE MOBILE AMÉLIORÉ */
                @media (max-width: 768px) {
                    nav {
                        flex-direction: column;
                        padding: 12px;
                    }
                    nav .nav-menu {
                        flex-wrap: wrap;
                        justify-content: center;
                        margin-top: 12px;
                        gap: 8px;
                    }
                    nav a {
                        margin-left: 0;
                        padding: 6px 12px;
                        background: #f5f5f5;
                        border-radius: 8px;
                        font-size: 0.85rem;
                    }
                    .admin-btn, .employe-btn {
                        width: 100%;
                        box-sizing: border-box;
                    }
                }

                /* 4. BOUTONS STYLE "APP" */
                .btn-eco {
                    background-color: var(--eco-green);
                    color: white !important;
                    padding: 12px 28px;
                    border-radius: 12px;
                    text-decoration: none;
                    display: inline-block;
                    border: none;
                    font-weight: 700;
                    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                    cursor: pointer;
                    box-shadow: 0 4px 10px rgba(46, 125, 50, 0.2);
                }
                .btn-eco:hover {
                    background-color: var(--wood-brown);
                    transform: translateY(-3px);
                    box-shadow: 0 8px 20px rgba(109, 76, 65, 0.3);
                }

                /* 5. SECTIONS STYLE "CARDS" */
                .section-profil, [style*="background: white"] {
                    background: #ffffff !important;
                    border-radius: 20px !important;
                    padding: 25px !important;
                    margin-bottom: 25px !important;
                    border: none !important;
                    border-left: 8px solid var(--eco-green) !important;
                    box-shadow: var(--card-shadow) !important;
                }
                
                h2, h3 {
                    color: var(--eco-green);
                    font-weight: 800;
                }

                /* STYLE BOUTON ADMIN / EMPLOYE */
                .admin-btn {
                    color: #d32f2f !important;
                    font-weight: 700;
                    border: 2px solid #d32f2f !important;
                    padding: 6px 14px !important;
                    border-radius: 10px !important;
                }
                .admin-btn:hover {
                    background: #d32f2f !important;
                    color: white !important;
                }
                .employe-btn {
                    color: var(--eco-green) !important;
                    font-weight: 700;
                    border: 2px solid var(--eco-green) !important;
                    padding: 6px 14px !important;
                    border-radius: 10px !important;
                }
                .employe-btn:hover {
                    background: var(--eco-green) !important;
                    color: white !important;
                }

                /*OPTIMISATION PC */
                @media (min-width: 769px) {
                    /* On cible uniquement la barre de recherche */
                    .search-form {
                        display: grid !important;
                        grid-template-columns: repeat(4, 1fr) auto;
                        gap: 15px;
                        align-items: flex-end;
                    }
                    
                    .search-form div {
                        width: 100%;
                    }

                    /* Le bouton Rechercher se cale à droite sur la même ligne */
                    .search-form .btn-eco, .search-form button[type="submit"] {
                        width: auto !important;
                        min-width: 150px;
                        padding: 11px 20px;
                        height: 42px;
                    }

                    /* Les filtres (options) prennent toute la largeur sous les champs */
                    .search-options {
                        grid-column: 1 / span 5;
                        display: flex;
                        gap: 20px;
                        margin-top: 10px;
                    }
                }

                /* Inputs et Selects harmonisés */
                input, select {
                    width: 100%;
                    padding: 10px;
                    border-radius: 8px;
                    border: 1px solid #ddd;
                    margin-top: 5px;
                    box-sizing: border-box;
                    font-size: 0.9rem;
                }

                /* FIX FORMULAIRE PUBLICATION (PROPOSER TRAJET) */
                @media (min-width: 769px) {
                    .form-publication {
                        max-width: 700px;
                        margin: 0 auto;
                        display: block !important; 
                    }
                    .form-publication .field-row {
                        display: grid;
                        grid-template-columns: 1fr 1fr;
                        gap: 20px;
                        margin-bottom: 15px;
                    }
                    .options-group {
                        display: grid;
                        grid-template-columns: 1fr 1fr;
                        gap: 15px;
                        margin: 20px 0;
                        padding: 15px;
                        background: #f9f9f9;
                        border-radius: 12px;
                        border: 1px inset #eee;
                    }
                    .option-item {
                        display: flex;
                        align-items: center;
                        gap: 10px;
                        font-weight: 600;
                        color: #444;
                        cursor: pointer;
                    }
                    .option-item input[type="checkbox"] {
                        width: 18px;
                        height: 18px;
                        margin: 0;
                        cursor: pointer;
                    }
                }
            </style>
        </head>
        <body>
            <nav>
                <a href="index.php" class="logo">🌿 EcoRide</a>
                <div class="nav-menu">
                    <a href="recherche.php">Recherche</a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="profil.php">Mon Profil</a>
                        
                        <?php 
                        $user_role = isset($_SESSION['role']) ? strtolower($_SESSION['role']) : ''; 
                        ?>

                        <?php if ($user_role === 'employe'): ?>
                            <a href="employe.php" class="employe-btn">🛠️ Employé</a>
                        <?php endif; ?>

                        <?php if ($user_role === 'admin'): ?>
                            <a href="admin_dashboard.php" class="admin-btn">🛠️ Admin</a>
                        <?php endif; ?>
                        
                        <a href="deconnexion.php" style="color: #777;">Déconnexion</a>
                    <?php else: ?>
                        <a href="connexion.php">Connexion</a>
                        <a href="inscription.php">Inscription</a>
                        <a href="contact.php">Contact</a>
                    <?php endif; ?>
                </div>
            </nav>
            <div class="container">
        <?php
    }
    public function afficherFooter() {
        ?>
            </div>
            <footer style="text-align:center; padding:40px 20px; background:#ffffff; border-top: 1px solid #eee; margin-top: 50px;">
                <p style="color: var(--eco-green); font-weight: 800; font-size: 1.2rem; margin-bottom: 5px;">🌿 EcoRide</p>
                <p style="color: #888; font-size: 0.9rem;">2026 | La mobilité verte pour tous</p>
            </footer>
        </body>
        </html>
        <?php
    }
}