<?php
class Database {
    // Config locale - A modifier si on passe sur un hébergement en ligne
    private $host = "localhost";
    private $db_name = "ecoride";
    private $username = "root";
    private $password = ""; 
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            // Utilisation de PDO pour la sécurité (requêtes préparées) et la souplesse
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            
            // On force l'affichage des erreurs SQL pour faciliter le débug pendant le dev
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Encodage UTF8 pour éviter les soucis d'accents en BDD
            $this->conn->exec("set names utf8");
            
        } catch(PDOException $e) {
            // En cas de crash, on affiche l'erreur (à masquer en production pour la sécurité)
            die("Problème de connexion BDD : " . $e->getMessage());
        }
        return $this->conn;
    }
}