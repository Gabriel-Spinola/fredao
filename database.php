<?php

class Database
{
    private PDO $pdo;

    public function connect(): PDO
    {
        if ($this->pdo != null) {
            return $this->pdo;
        }

        try {
            // Connect to the database
            $this->pdo = new PDO('mysql:host=' . DATABASE_HOST . ';dbname=' . DATABASE_NAME, DATABASE_USER, DATABASE_PASSWORD, [
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
            ]);

            // Set error mode
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "<h2>Failed to connect to database</h2>" . $e->getMessage();
        }
    }
}