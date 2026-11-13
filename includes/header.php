<?php
// en-tête commun à toutes les pages
// il faut définir $titrePage avant d'inclure ce fichier
require_once __DIR__ . '/fonctions.php';
if (!isset($titrePage)) {
    $titrePage = 'Vite & Gourmand';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Vite & Gourmand, traiteur à Bordeaux depuis 25 ans. Menus pour Noël, Pâques et tous vos évènements.">
    <title><?= e($titrePage) ?> - Vite & Gourmand</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
<a href="#contenu" class="visually-hidden-focusable">Aller au contenu</a>

<header>
    <nav class="navbar navbar-expand-lg navbar-dark bg-bordeaux" aria-label="Menu principal">
        <div class="container">
            <a class="navbar-brand titre-logo" href="/index.php">Vite &amp; Gourmand</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal"
                    aria-controls="menuPrincipal" aria-expanded="false" aria-label="Ouvrir le menu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="menuPrincipal">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="/index.php">Accueil</a></li>
                    <li class="nav-item"><a class="nav-link" href="/menus.php">Nos menus</a></li>
                    <li class="nav-item"><a class="nav-link" href="/contact.php">Contact</a></li>

                    <?php if (!estConnecte()) : ?>
                        <li class="nav-item"><a class="nav-link" href="/connexion.php">Connexion</a></li>
                        <li class="nav-item"><a class="nav-link" href="/inscription.php">Créer un compte</a></li>
                    <?php else : ?>
                        <?php if (roleActuel() == 'utilisateur') : ?>
                            <li class="nav-item"><a class="nav-link" href="/espace_utilisateur/index.php">Mon espace</a></li>
                        <?php endif; ?>
                        <?php if (roleActuel() == 'employe' || roleActuel() == 'admin') : ?>
                            <li class="nav-item"><a class="nav-link" href="/espace_employe/index.php">Espace employé</a></li>
                        <?php endif; ?>
                        <?php if (roleActuel() == 'admin') : ?>
                            <li class="nav-item"><a class="nav-link" href="/espace_admin/index.php">Administration</a></li>
                        <?php endif; ?>
                        <li class="nav-item"><a class="nav-link" href="/deconnexion.php">Déconnexion</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>

<main id="contenu" class="container my-4">
<?php afficherMessage(); ?>
