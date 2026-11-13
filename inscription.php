<?php
require_once 'includes/fonctions.php';

$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifierCsrf();

    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $telephone = trim($_POST['telephone']);
    $email = trim($_POST['email']);
    $adresse = trim($_POST['adresse']);
    $codePostal = trim($_POST['code_postal']);
    $ville = trim($_POST['ville']);
    $mdp = $_POST['mot_de_passe'];
    $mdp2 = $_POST['mot_de_passe2'];

    // vérifications
    if ($nom == '' || $prenom == '' || $telephone == '' || $adresse == '' || $ville == '') {
        $erreurs[] = 'Tous les champs sont obligatoires.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreurs[] = 'L\'adresse mail n\'est pas valide.';
    }
    if (!preg_match('/^0[1-9][0-9]{8}$/', str_replace(' ', '', $telephone))) {
        $erreurs[] = 'Le numéro de téléphone n\'est pas valide.';
    }
    if (!motDePasseValide($mdp)) {
        $erreurs[] = 'Le mot de passe doit faire au moins 10 caractères avec une majuscule, une minuscule, un chiffre et un caractère spécial.';
    }
    if ($mdp != $mdp2) {
        $erreurs[] = 'Les deux mots de passe ne sont pas identiques.';
    }
    if (!isset($_POST['rgpd'])) {
        $erreurs[] = 'Vous devez accepter la politique de confidentialité.';
    }

    // email déjà utilisé ?
    $requete = $bdd->prepare('SELECT id FROM utilisateur WHERE email = ?');
    $requete->execute([$email]);
    if ($requete->fetch()) {
        $erreurs[] = 'Un compte existe déjà avec cette adresse mail.';
    }

    if (count($erreurs) == 0) {
        // le role est forcé à "utilisateur", on ne le prend pas du formulaire
        $requete = $bdd->prepare("INSERT INTO utilisateur (nom, prenom, email, telephone, adresse, code_postal, ville, mot_de_passe, role)
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'utilisateur')");
        $requete->execute([
            $nom, $prenom, $email, str_replace(' ', '', $telephone), $adresse, $codePostal, $ville,
            password_hash($mdp, PASSWORD_DEFAULT)
        ]);

        // mail de bienvenue
        $contenu = '<h1>Bienvenue ' . e($prenom) . ' !</h1>'
            . '<p>Votre compte Vite & Gourmand a bien été créé.</p>'
            . '<p>Vous pouvez dès maintenant commander nos menus sur <a href="' . URL_SITE . '">notre site</a>.</p>';
        envoyerMail($email, 'Bienvenue chez Vite & Gourmand', $contenu);

        setMessage('success', 'Votre compte a été créé, vous pouvez vous connecter.');
        header('Location: /connexion.php');
        exit;
    }
}

$titrePage = 'Créer un compte';
require_once 'includes/header.php';
?>

<h1 class="text-bordeaux">Créer un compte</h1>

<?php if (count($erreurs) > 0) : ?>
    <div class="alert alert-danger" role="alert">
        <ul class="mb-0">
            <?php foreach ($erreurs as $err) : ?>
                <li><?= e($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" class="row g-3" novalidate>
    <?= champCsrf() ?>
    <div class="col-md-6">
        <label for="nom" class="form-label">Nom *</label>
        <input type="text" class="form-control" id="nom" name="nom" required autocomplete="family-name" value="<?= e($_POST['nom'] ?? '') ?>">
    </div>
    <div class="col-md-6">
        <label for="prenom" class="form-label">Prénom *</label>
        <input type="text" class="form-control" id="prenom" name="prenom" required autocomplete="given-name" value="<?= e($_POST['prenom'] ?? '') ?>">
    </div>
    <div class="col-md-6">
        <label for="email" class="form-label">Adresse mail *</label>
        <input type="email" class="form-control" id="email" name="email" required autocomplete="email" value="<?= e($_POST['email'] ?? '') ?>">
    </div>
    <div class="col-md-6">
        <label for="telephone" class="form-label">Numéro de GSM *</label>
        <input type="tel" class="form-control" id="telephone" name="telephone" required autocomplete="tel" value="<?= e($_POST['telephone'] ?? '') ?>">
    </div>
    <div class="col-md-6">
        <label for="adresse" class="form-label">Adresse postale *</label>
        <input type="text" class="form-control" id="adresse" name="adresse" required autocomplete="street-address" value="<?= e($_POST['adresse'] ?? '') ?>">
    </div>
    <div class="col-md-2">
        <label for="code_postal" class="form-label">Code postal *</label>
        <input type="text" class="form-control" id="code_postal" name="code_postal" required autocomplete="postal-code" value="<?= e($_POST['code_postal'] ?? '') ?>">
    </div>
    <div class="col-md-4">
        <label for="ville" class="form-label">Ville *</label>
        <input type="text" class="form-control" id="ville" name="ville" required autocomplete="address-level2" value="<?= e($_POST['ville'] ?? '') ?>">
    </div>
    <div class="col-md-6">
        <label for="mot_de_passe" class="form-label">Mot de passe *</label>
        <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe" required autocomplete="new-password" aria-describedby="aideMdp">
        <small id="aideMdp" class="text-muted">10 caractères minimum dont une majuscule, une minuscule, un chiffre et un caractère spécial.</small>
    </div>
    <div class="col-md-6">
        <label for="mot_de_passe2" class="form-label">Confirmer le mot de passe *</label>
        <input type="password" class="form-control" id="mot_de_passe2" name="mot_de_passe2" required autocomplete="new-password">
    </div>
    <div class="col-12">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="rgpd" name="rgpd" required>
            <label class="form-check-label" for="rgpd">
                J'accepte que mes données soient utilisées pour la gestion de mes commandes
                (voir les <a href="/mentions_legales.php">mentions légales</a>) *
            </label>
        </div>
    </div>
    <div class="col-12">
        <button type="submit" class="btn btn-bordeaux">Créer mon compte</button>
    </div>
</form>

<?php require_once 'includes/footer.php'; ?>
