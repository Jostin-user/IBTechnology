<?php
class Database {

    private $connexion;

    public function __construct() {
        $this->connexion = new mysqli(
            DB_HOST,
            DB_USER,
            DB_PASS,
            DB_NAME
        );

        if ($this->connexion->connect_error) {
            die('Erreur de connexion à la base de données');
        }

        // Encodage UTF-8 sécurisé
        $this->connexion->set_charset("utf8mb4");
    }

    public function getConnexion() {
        return $this->connexion;
    }
}
$db = (new Database())->getConnexion() ;
?>