<?php
class Connexion {
    private $connextion;

    public function __construct() {
        $host = 'localhost';
        $dbname = 'localisation';
        $login = 'root';
        $password = '';

        try {
            $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8";
            $this->connextion = new PDO($dsn, $login, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    function getConnextion() {
        return $this->connextion;
    }
}