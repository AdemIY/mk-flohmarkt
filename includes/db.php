<?php

function connect(): PDO
{
    $host = 'db';         // bei DDEV: "db"
    $dbname = 'db';       // Standard-Datenbankname in DDEV
    $user = 'db';         // Standard-User
    $pass = 'db';         // Standard-Passwort
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

    try {
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,     // Fehler als Exception
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Ergebnisse als Array
        ]);
        return $pdo;
    } catch (PDOException $e) {
        die('Verbindung fehlgeschlagen: ' . $e->getMessage());
    }
}