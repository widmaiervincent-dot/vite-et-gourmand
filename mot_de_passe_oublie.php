<?php
require_once 'includes/fonctions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifierCsrf();
    $email = trim($_POST['email']);

    $requete = $bdd->prepare('SELECT id, prenom FROM utilisateur WHERE email = ? AND actif = 1');
    $requete->execute([$email]);
    $utilisateur = $requete->fetch();

    if ($utilisateur) {
        // token aléatoire valable 1 heure
        $token = bin2hex(random_bytes(32));
        $requete = $bdd->prepare('UPDATE utilisateur SET token_reset = ?, token_expiration = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE id = ?');
        $requete->execute([$token, $utilisateur['id']]);

        $lien = URL_SITE . '/reinitialiser_mdp.php?token=' . $token;
        $contenu = '<p>Bonjour ' . e($utilisateur['prenom']) . ',</p>'
            . '<p>Pour réinitialiser votre mot de passe, cliquez sur ce lien (valable 1 heure) :</p>'
            . '<p><a href="' . $lien . '">' . $lien . '</a></p>'
            . '<p>Si vous n\'avez rien demandé, ignorez ce mail.</p>';
        envoyerMail($email, 'Réinitialisation de votre mot de passe', $contenu);
    }

    // même message dans tous les cas pour ne pas dire si le mail existe
    setMessage('info', 'Si un compte existe avec cette adresse, un mail vous a été envoyé.');
    header('Location: /connexion.php');
    exit;
}

$titrePage = 'Mot de passe oublié';
require_once 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <h1 class="text-bordeaux">Mot de passe oublié</h1>
        <p>Indiquez votre adresse mail, nous vous enverrons un lien pour choisir un nouveau mot de passe.</p>
        <form method="post">
            <?= champCsrf() ?>
            <div class="mb-3">
                <label for="email" class="form-label">Adresse mail</label>
                <input type="email" class="form-control" id="email" name="email" required autocomplete="email">
            </div>
            <button type="submit" class="btn btn-bordeaux">Envoyer le lien</button>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
