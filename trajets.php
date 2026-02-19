<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "Database.php"; //gère la connexion à la base de données
if (!isset($_SESSION['est connecte']) || $_SESSION['est connecte'] !== true) {
    header("Location:connexion.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $datebase = new Database();
    $pdo = $datebase->getConnection();
    // traiter les données du formulaire de création de trajet
    // récupérer les champs du formulaire et les stocker dans une base de données
    $depart = htmlspecialchars($_POST['depart']);
    $arrivee = htmlspecialchars($_POST['arrivee']);
    $date = htmlspecialchars($_POST['date']);
    $prix = htmlspecialchars($_POST['prix']);
    $places = htmlspecialchars($_POST['places']);
    try{
        //on prepare la commande SQL pour inserer le trajet dans la base de données
        $sql = "INSERT INTO trajets (depart, arrivee, date_depart, prix, places) VALUES (:depart, :arrivee, :date, :prix, :places)";
        $stmt = $pdo->prepare($sql);
        //on execute la commande SQL en passant les valeurs du formulaire
        $stmt->execute([
            ':depart' => $depart,
            ':arrivee' => $arrivee,
            ':date' => $date,
            ':prix' => $prix,
            ':places' => $places
        ]);
        echo "<p style='color: green; font-weight: bold;'>Trajet créé avec succès !</p>";
    }catch (PDOException $e) {
        echo "<p style='color: red; font-weight: bold;'>Erreur lors de la création du trajet : " . $e->getMessage() . "</p>";
    }
}