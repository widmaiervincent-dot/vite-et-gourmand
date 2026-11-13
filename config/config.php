<?php
// Fichier de configuration
// les valeurs sont prises dans les variables d'environnement (pour le déploiement)
// sinon on met des valeurs par défaut pour le local

// les identifiants perso (Mailtrap...) sont dans config.local.php qui n'est pas envoyé sur GitHub
// (copier config.local.exemple.php pour le créer)
if (file_exists(__DIR__ . '/config.local.php')) {
    require_once __DIR__ . '/config.local.php';
}

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'vite_gourmand');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

define('MONGO_URI', getenv('MONGO_URI') ?: 'mongodb://localhost:27017');
define('MONGO_DB', getenv('MONGO_DB') ?: 'vite_gourmand');

// config mail (SMTP) -> j'utilise Mailtrap en local
define('SMTP_HOST', getenv('SMTP_HOST') ?: 'sandbox.smtp.mailtrap.io');
define('SMTP_PORT', getenv('SMTP_PORT') ?: 2525);
define('SMTP_USER', getenv('SMTP_USER') ?: '');
define('SMTP_PASS', getenv('SMTP_PASS') ?: '');
define('MAIL_ENTREPRISE', getenv('MAIL_ENTREPRISE') ?: 'contact@vite-gourmand.fr');

// url du site (pour les liens dans les mails)
define('URL_SITE', getenv('URL_SITE') ?: 'http://localhost:8000');

// tarifs livraison
define('PRIX_LIVRAISON_BASE', 5);
define('PRIX_PAR_KM', 0.59);
