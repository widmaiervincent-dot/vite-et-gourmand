<?php
require_once 'includes/fonctions.php';

$token = $_GET['token'] ?? ($_POST['token'] ?? '');

$requete = $bdd->prepare('SELECT id FROM utilisateur WHERE token_reset = ? AND token_expiration > NOW()');
$requete->execute([$token]);
$utilisateur = $requete->fetch();

if (!$utilisateur || $token == '') {
    setMessage('danger', 'Le lien n\'est pas valide ou a expiré.');
    header('Location: /mot_de_passe_oublie.php');
    exit;
}

$erreur = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifierCsrf();
    $mdp = $_POST['mot_de_passe'];

    if (!motDePasseValide($mdp)) {
        $erreur = 'Le mot de passe doit faire au moins 10 caractères avec une majuscule, une minuscule, un chiffre et un caractère spécial.';
    } elseif ($mdp != $_POST['mot_de_passe2']) {
        $erreur = 'Les mots de passe ne correspondent pas.';
    } else {
        $requete = $bdd->prepare('UPDATE utilisateur SET mot_de_passe = ?, token_reset = NULL, token_expiration = NULL WHERE id = ?');
        $requete->execute([password_hash($mdp, PASSWORD_DEFAULT), $utilisateur['id']]);
        setMessage('success', 'Votre mot de passe a été modifié.');
        header('Location: /connexion.php');
        exit;
    }
}

$titrePage = 'Nouveau mot de passe';
require_once 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <h1 class="text-bordeaux">Nouveau mot de passe</h1>
        <?php if ($erreur) : ?>
            <div class="alert alert-danger" role="alert"><?= e($erreur) ?></div>
        <?php endif; ?>
        <form method="post">
            <?= champCsrf() ?>
            <input type="hidden" name="token" value="<?= e($token) ?>">
            <div class="mb-3">
                <label for="mot_de_passe" class="form-label">Nouveau mot de passe</label>
                <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe" required autocomplete="new-password">
            </div>
            <div class="mb-3">
                <label for="mot_de_passe2" class="form-label">Confirmer</label>
                <input type="password" class="form-control" id="mot_de_passe2" name="mot_de_passe2" required autocomplete="new-password">
            </div>
            <button type="submit" class="btn btn-bordeaux">Valider</button>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
