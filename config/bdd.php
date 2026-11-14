<?php
require_once __DIR__ . '/config.php';

// en ligne (Aiven) la base refuse les connexions non chiffrées :
// si la variable DB_SSL est définie, on se connecte en SSL
$optionsBdd = [];
if (getenv('DB_SSL')) {
    $optionsBdd[PDO::MYSQL_ATTR_SSL_CA] = getenv('DB_SSL_CA') ?: '/etc/ssl/certs/ca-certificates.crt';
    // Aiven utilise son propre certificat : la connexion est chiffrée mais on ne vérifie pas le certificat
    // (pour le vérifier il faudrait télécharger le ca.pem d'Aiven et mettre son chemin dans DB_SSL_CA)
    $optionsBdd[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = getenv('DB_SSL_CA') ? true : false;
}

// connexion à la base MySQL avec PDO
try {
    $bdd = new PDO(
        'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        $optionsBdd
    );
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $bdd->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // on n'affiche pas le vrai message d'erreur à l'utilisateur (sécurité)
    error_log($e->getMessage());
    die('Erreur de connexion à la base de données.');
}
