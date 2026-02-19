🌿 EcoRide - Plateforme de Covoiturage Éco-responsable 📖 À propos du projet EcoRide est né d'une idée simple : rendre le covoiturage plus transparent et plus vert. Contrairement aux plateformes classiques, EcoRide met en avant les véhicules électriques et intègre un système de crédits pour encourager le partage communautaire.

🚀 Fonctionnalités clés Recherche dynamique : Filtres par ville (avec autocomplétion via l'API Adresse), date, prix et type d'énergie.

Espace Conducteur : Gestion de son parc automobile, publication de trajets et suivi des statuts (À venir / En cours / Terminé).

Système Économique : Publication de trajets soumise à un coût en crédits pour réguler l'offre.

Sécurité des données : * Mots de passe hachés (password_hash).

Protection totale contre les injections SQL (Requêtes préparées PDO).

Gestion de l'atomicité des réservations (Transactions SQL FOR UPDATE).

🛠️ Stack Technique Backend : PHP 8.x (Programmation Orientée Objet).

Frontend : HTML5, CSS3 (Variables CSS pour la charte graphique), JavaScript Vanilla.

Base de données : MySQL.

API externe : API Adresse (data.gouv.fr) pour une saisie simplifiée des villes.

⚙️ Installation (Pour tester le projet) Cloner le dépôt :

Bash git clone https://github.com/sooyoon1270/EcoRide-ECF-FT.git Base de données : * Importer le fichier ecoride.sql (inclus à la racine) dans votre phpMyAdmin.

Configurer vos accès dans Database.php.

Lancer le serveur : * Utilisez XAMPP, WAMP ou le serveur interne de PHP : php -S localhost:8000.

👤 Identifiants de test voici deux comptes pré-configurés :

Compte Admin : admin@ecoride.fr / 123456 (Accès au dashboard).

Compte Utilisateur : user@test.fr / User123! (Pour tester la réservation).

📈 Évolutions prévues (Roadmap) Si je devais continuer le développement demain, voici mes priorités :

Messagerie : Créer un chat temps réel entre conducteur et passager avant le départ.

Avis & Notes : Finaliser le module de notation après l'arrivée du trajet (Statut 3).

Notifications : Envoyer un mail automatique lors d'une nouvelle réservation.

✍️ Note de l'auteur Ce projet a été réalisé avec une attention particulière portée à la propreté du code et à l'expérience utilisateur (UX). J'ai pris beaucoup de plaisir à résoudre les problématiques de "race conditions" sur les réservations de places.
