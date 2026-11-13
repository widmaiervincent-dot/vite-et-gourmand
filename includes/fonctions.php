<?php
// fonctions utilisées un peu partout sur le site

if (session_status() === PHP_SESSION_NONE) {
    // cookie de session un peu plus sécurisé
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => isset($_SERVER['HTTPS']),
    ]);
    session_start();
}

require_once __DIR__ . '/../config/bdd.php';
require_once __DIR__ . '/mail.php';

// pour éviter les failles XSS quand on affiche des données
function e($texte)
{
    return htmlspecialchars((string)$texte, ENT_QUOTES, 'UTF-8');
}

// ----- Token CSRF -----
function getCsrfToken()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function champCsrf()
{
    return '<input type="hidden" name="csrf_token" value="' . getCsrfToken() . '">';
}

function verifierCsrf()
{
    if (!isset($_POST['csrf_token']) || !hash_equals(getCsrfToken(), $_POST['csrf_token'])) {
        die('Erreur : formulaire invalide (token CSRF).');
    }
}

// ----- Connexion / rôles -----
function estConnecte()
{
    return isset($_SESSION['utilisateur']);
}

function roleActuel()
{
    return $_SESSION['utilisateur']['role'] ?? null;
}

// vérifie que la personne a le bon rôle sinon on la renvoie
// $roles = tableau des roles autorisés
function verifierRole($roles)
{
    if (!estConnecte()) {
        header('Location: /connexion.php');
        exit;
    }

    // si le compte a été désactivé pendant que la personne était connectée, on la déconnecte
    global $bdd;
    $requete = $bdd->prepare('SELECT actif FROM utilisateur WHERE id = ?');
    $requete->execute([$_SESSION['utilisateur']['id']]);
    if ($requete->fetchColumn() != 1) {
        unset($_SESSION['utilisateur']);
        setMessage('danger', 'Ce compte a été désactivé.');
        header('Location: /connexion.php');
        exit;
    }

    if (!in_array(roleActuel(), $roles)) {
        http_response_code(403);
        die('Accès refusé.');
    }
}

// ----- Messages flash -----
function setMessage($type, $message)
{
    $_SESSION['message'] = ['type' => $type, 'texte' => $message];
}

function afficherMessage()
{
    if (isset($_SESSION['message'])) {
        $m = $_SESSION['message'];
        echo '<div class="alert alert-' . e($m['type']) . '" role="alert">' . e($m['texte']) . '</div>';
        unset($_SESSION['message']);
    }
}

// mot de passe : 10 caractères mini, 1 majuscule, 1 minuscule, 1 chiffre, 1 caractère spécial
function motDePasseValide($mdp)
{
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).{10,}$/', $mdp);
}

// ----- Calcul du prix d'une commande -----
// retourne un tableau avec le détail du prix
function calculerPrix($menu, $nbPersonnes, $ville, $distanceKm)
{
    // prix par personne = prix du menu / nombre de personnes mini
    $prixParPersonne = $menu['prix'] / $menu['nb_personnes_min'];
    $prixMenu = $prixParPersonne * $nbPersonnes;

    // réduction de 10% si 5 personnes de plus que le minimum
    $reduction = 0;
    if ($nbPersonnes >= $menu['nb_personnes_min'] + 5) {
        $reduction = $prixMenu * 0.10;
    }

    // livraison : gratuite à Bordeaux sinon 5€ + 0.59€/km
    $livraison = 0;
    if (strtolower(trim($ville)) != 'bordeaux') {
        $livraison = PRIX_LIVRAISON_BASE + (PRIX_PAR_KM * $distanceKm);
    }

    $total = $prixMenu - $reduction + $livraison;

    return [
        'prix_menu' => round($prixMenu, 2),
        'reduction' => round($reduction, 2),
        'livraison' => round($livraison, 2),
        'total' => round($total, 2),
    ];
}

function formatPrix($prix)
{
    return number_format($prix, 2, ',', ' ') . ' €';
}

// liste des statuts possibles d'une commande
function listeStatuts()
{
    return [
        'en attente',
        'accepté',
        'en préparation',
        'en cours de livraison',
        'livré',
        'en attente du retour de matériel',
        'terminée',
        'annulée',
    ];
}

// récupère les horaires pour le footer
function getHoraires()
{
    global $bdd;
    return $bdd->query('SELECT * FROM horaire ORDER BY id')->fetchAll();
}
