<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "Database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {
    $db = (new Database())->getConnection();
    // Récupération et sécurisation des données
    $id_user = intval($_SESSION['user_id']);
    $marque = htmlspecialchars($_POST['marque']);
    $modele = htmlspecialchars($_POST['modele']);
    $immat = htmlspecialchars($_POST['immatriculation']);
    $energie = $_POST['energie']; // On récupère la valeur du select

    try {
        $sql = "INSERT INTO voitures (id_utilisateur, marque, modele, immatriculation, energie)
                VALUES (:id_u, :marque, :modele, :immat, :energie)";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':id_u'    => $id_user,
            ':marque'  => $marque,
            ':modele'  => $modele,
            ':immat'   => $immat,
            ':energie' => $energie
        ]);
        // Redirection vers le profil avec un message de succès
        header("Location: profil.php?statuts= Voiture ajoutée avec succès !");
        exit();
    } catch (PDOException $e) {
        die("Erreur lors de l'ajout du véhicule : " . $e->getMessage());
    }
} else {
    header("Location: profil.php");
    exit();
}