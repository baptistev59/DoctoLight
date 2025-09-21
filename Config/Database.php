<?php

class Database
{

    private $pdo;

    public function __construct()
    {
        // Charger la config
        $config = require 'config.php';

        $host = $config['host'];
        $db_name = $config['db_name'];
        $username = $config['username'];
        $password = $config['password'];


        try {
            $this->pdo = new PDO(
                "mysql:host=$host;dbname=$db_name;charset=utf8mb4",
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        } catch (PDOException $e) {
            die("Erreur de connexion : " . $e->getMessage());
        }
    }

    public function getConnection()
    {
        return $this->pdo;
    }
}
