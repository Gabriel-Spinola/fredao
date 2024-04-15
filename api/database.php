<?php

// gabriel.fredaugusto.com.br
const DATABASE_NAME = "fred_bd";
const DATABASE_HOST = "localhost:3306";
const DATABASE_USER = "root";
const DATABASE_PASSWORD = "";

class Database
{
    private ?PDO $pdo;

    public function connect(): PDO
    {
        if (isset($this->pdo)) {
            return $this->pdo;
        }

        try {
            // Connect to the database
            $this->pdo = new PDO(
                "mysql:host=" . DATABASE_HOST . ";dbname=" . DATABASE_NAME,
                DATABASE_USER,
                DATABASE_PASSWORD,
                [PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"]
            );

            // Set error mode
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "<h2>Failed to connect to database</h2>" . $e->getMessage();
            header("HTTP/1.0 500 Internal Server Error");

            die;
        }

        return $this->pdo;
    }
}