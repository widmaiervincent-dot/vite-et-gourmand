<?php
require_once __DIR__ . '/../includes/fonctions.php';
verifierRole(['utilisateur']);

$id = $_SESSION['utilisateur']['id'];
$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifierCsrf();

    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $telephone = str_replace(' ', '', trim($_POST['telephone']));
    $adresse = trim($_POST['adresse']);
    $codePostal = trim($_POST['code_postal']);
    $ville = trim($_POST['ville']);

    if ($nom == '' || $prenom == '' || $adresse == '' || $ville == '') {
        $erreurs[] = 'Tous les champs sont obligatoires.';
    }
    if (!preg_match('/^0[1-9][0-9]{8}$/', $telephone)) {
        $erreurs[] = 'Le numéro de téléphone n\'est pas valide.';
    }

    // changement de mot de passe (optionnel)
    $nouveauMdp = $_POST['nouveau_mdp'];
    if ($nouveauMdp != '' && !motDePasseValide($nouveauMdp)) {
        $erreurs[] = 'Le nouveau mot de passe ne respecte pas les règles de sécurité.';
    }

    if (count($erreurs) == 0) {
        $requete = $bdd->prepare('UPDATE utilisateur SET nom = ?, prenom = ?, telephone = ?, adresse = ?, code_postal = ?, ville = ? WHERE id = ?');
        $requete->execute([$nom, $prenom, $telephone, $adresse, $codePostal, $ville, $id]);

        if ($nouveauMdp != '') {
            $requete = $bdd->prepare('UPDATE utilisateur SET mot_de_passe = ? WHERE id = ?');
            $requete->execute([password_hash($nouveauMdp, PASSWORD_DEFAULT), $id]);
        }

        $_SESSION['utilisateur']['nom'] = $nom;
        $_SESSION['utilisateur']['prenom'] = $prenom;
        setMessage('success', 'Vos informations ont été mises à jour.');
        header('Location: /espace_utilisateur/profil.php');
        exit;
    }
}

$requete = $bdd->prepare('SELECT * FROM utilisateur WHERE id = ?');
$requete->execute([$id]);
$u = $requete->fetch();

$titrePage = 'Mes informations';
require_once __DIR__ . '/../includes/header.php';
?>

<h1 class="text-bordeaux">Mes informations</h1>

<?php foreach ($erreurs as $err) : ?>
    <div class="alert alert-danger" role="alert"><?= e($err) ?></div>
<?php endforeach; ?>

<form method="post" class="row g-3">
    <?= champCsrf() ?>
    <div class="col-md-6">
        <label for="nom" class="form-label">Nom</label>
        <input type="text" id="nom" name="nom" class="form-control" value="<?= e($u['nom']) ?>" required>
    </div>
    <div class="col-md-6">
        <label for="prenom" class="form-label">Prénom</label>
        <input type="text" id="prenom" name="prenom" class="form-control" value="<?= e($u['prenom']) ?>" required>
    </div>
    <div class="col-md-6">
        <label for="email" class="form-label">Mail (non modifiable)</label>
        <input type="email" id="email" class="form-control" value="<?= e($u['email']) ?>" readonly>
    </div>
    <div class="col-md-6">
        <label for="telephone" class="form-label">GSM</label>
        <input type="tel" id="telephone" name="telephone" class="form-control" value="<?= e($u['telephone']) ?>" required>
    </div>
    <div class="col-md-6">
        <label for="adresse" class="form-label">Adresse</label>
        <input type="text" id="adresse" name="adresse" class="form-control" value="<?= e($u['adresse']) ?>" required>
    </div>
    <div class="col-md-2">
        <label for="code_postal" class="form-label">Code postal</label>
        <input type="text" id="code_postal" name="code_postal" class="form-control" value="<?= e($u['code_postal']) ?>">
    </div>
    <div class="col-md-4">
        <label for="ville" class="form-label">Ville</label>
        <input type="text" id="ville" name="ville" class="form-control" value="<?= e($u['ville']) ?>" required>
    </div>
    <div class="col-md-6">
        <label for="nouveau_mdp" class="form-label">Nouveau mot de passe (laisser vide pour ne pas changer)</label>
        <input type="password" id="nouveau_mdp" name="nouveau_mdp" class="form-control" autocomplete="new-password">
    </div>
    <div class="col-12">
        <button type="submit" class="btn btn-bordeaux">Enregistrer</button>
        <a href="/espace_utilisateur/index.php" class="btn btn-outline-secondary">Retour</a>
    </div>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
