<?php
require_once 'includes/fonctions.php';

$erreur = '';

// page où renvoyer après connexion (ex : la commande), on accepte seulement les chemins du site
$redirection = $_GET['redirection'] ?? ($_POST['redirection'] ?? '');
if (substr($redirection, 0, 1) != '/' || substr($redirection, 0, 2) == '//') {
    $redirection = '';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifierCsrf();

    $email = trim($_POST['email']);
    $mdp = $_POST['mot_de_passe'];

    $requete = $bdd->prepare('SELECT * FROM utilisateur WHERE email = ?');
    $requete->execute([$email]);
    $utilisateur = $requete->fetch();

    if ($utilisateur && password_verify($mdp, $utilisateur['mot_de_passe'])) {
        if ($utilisateur['actif'] == 0) {
            $erreur = 'Ce compte a été désactivé.';
        } else {
            // nouvel id de session pour éviter la fixation de session
            session_regenerate_id(true);
            $_SESSION['utilisateur'] = [
                'id' => $utilisateur['id'],
                'nom' => $utilisateur['nom'],
                'prenom' => $utilisateur['prenom'],
                'email' => $utilisateur['email'],
                'role' => $utilisateur['role'],
            ];

            if ($redirection != '') {
                header('Location: ' . $redirection);
            } elseif ($utilisateur['role'] == 'admin') {
                header('Location: /espace_admin/index.php');
            } elseif ($utilisateur['role'] == 'employe') {
                header('Location: /espace_employe/index.php');
            } else {
                header('Location: /espace_utilisateur/index.php');
            }
            exit;
        }
    } else {
        // message volontairement vague (on ne dit pas si c'est le mail ou le mdp)
        $erreur = 'Identifiants incorrects.';
    }
}

$titrePage = 'Connexion';
require_once 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <h1 class="text-bordeaux">Connexion</h1>

        <?php if ($erreur) : ?>
            <div class="alert alert-danger" role="alert"><?= e($erreur) ?></div>
        <?php endif; ?>

        <form method="post">
            <?= champCsrf() ?>
            <input type="hidden" name="redirection" value="<?= e($redirection) ?>">
            <div class="mb-3">
                <label for="email" class="form-label">Adresse mail</label>
                <input type="email" class="form-control" id="email" name="email" required autocomplete="username">
            </div>
            <div class="mb-3">
                <label for="mot_de_passe" class="form-label">Mot de passe</label>
                <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn btn-bordeaux">Se connecter</button>
            <a href="/mot_de_passe_oublie.php" class="ms-3">Mot de passe oublié ?</a>
        </form>

        <p class="mt-4">Pas encore de compte ? <a href="/inscription.php">Créer un compte</a></p>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
