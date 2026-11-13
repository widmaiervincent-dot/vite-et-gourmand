<?php
require_once __DIR__ . '/config.php';

// connexion à la base MySQL avec PDO
try {
    $bdd = new PDO(
        'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS
    );
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $bdd->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // on n'affiche pas le vrai message d'erreur à l'utilisateur (sécurité)
    error_log($e->getMessage());
    die('Erreur de connexion à la base de données.');
}
