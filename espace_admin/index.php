<?php
require_once __DIR__ . '/../includes/fonctions.php';
verifierRole(['admin']);

$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifierCsrf();

    // création d'un compte employé
    if ($_POST['action'] == 'creer') {
        $email = trim($_POST['email']);
        $mdp = $_POST['mot_de_passe'];
        $nom = trim($_POST['nom']);
        $prenom = trim($_POST['prenom']);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erreurs[] = 'Adresse mail invalide.';
        }
        if (!motDePasseValide($mdp)) {
            $erreurs[] = 'Le mot de passe ne respecte pas les règles de sécurité.';
        }
        $requete = $bdd->prepare('SELECT id FROM utilisateur WHERE email = ?');
        $requete->execute([$email]);
        if ($requete->fetch()) {
            $erreurs[] = 'Cette adresse mail est déjà utilisée.';
        }

        if (count($erreurs) == 0) {
            // le role est toujours "employe" : on ne peut pas créer d'admin depuis l'appli
            $requete = $bdd->prepare("INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, role) VALUES (?, ?, ?, ?, 'employe')");
            $requete->execute([$nom, $prenom, $email, password_hash($mdp, PASSWORD_DEFAULT)]);

            // mail SANS le mot de passe
            $contenu = '<p>Bonjour,</p><p>Un compte employé Vite & Gourmand a été créé pour vous avec l\'identifiant : '
                . e($email) . '</p><p>Pour obtenir votre mot de passe, rapprochez-vous de l\'administrateur.</p>';
            envoyerMail($email, 'Votre compte employé Vite & Gourmand', $contenu);

            setMessage('success', 'Compte employé créé.');
            header('Location: /espace_admin/index.php');
            exit;
        }
    }

    // activer / désactiver un compte employé
    if ($_POST['action'] == 'activer' || $_POST['action'] == 'desactiver') {
        $actif = $_POST['action'] == 'activer' ? 1 : 0;
        $bdd->prepare("UPDATE utilisateur SET actif = ? WHERE id = ? AND role = 'employe'")->execute([$actif, (int)$_POST['id']]);
        setMessage('success', $actif ? 'Compte réactivé.' : 'Compte désactivé.');
        header('Location: /espace_admin/index.php');
        exit;
    }
}

$employes = $bdd->query("SELECT * FROM utilisateur WHERE role = 'employe' ORDER BY nom")->fetchAll();

$titrePage = 'Administration';
require_once __DIR__ . '/../includes/header.php';
?>

<h1 class="text-bordeaux">Administration</h1>

<nav class="mb-4" aria-label="Menu administration">
    <a href="/espace_admin/statistiques.php" class="btn btn-bordeaux mb-1">Statistiques et chiffre d'affaires</a>
    <a href="/espace_employe/index.php" class="btn btn-outline-secondary mb-1">Espace employé (commandes, menus...)</a>
</nav>

<h2 class="h4">Créer un compte employé</h2>

<?php foreach ($erreurs as $err) : ?>
    <div class="alert alert-danger" role="alert"><?= e($err) ?></div>
<?php endforeach; ?>

<form method="post" class="row g-3 card p-3 mb-4 flex-row">
    <?= champCsrf() ?>
    <input type="hidden" name="action" value="creer">
    <div class="col-md-3">
        <label for="nom" class="form-label">Nom</label>
        <input type="text" id="nom" name="nom" class="form-control" required>
    </div>
    <div class="col-md-3">
        <label for="prenom" class="form-label">Prénom</label>
        <input type="text" id="prenom" name="prenom" class="form-control" required>
    </div>
    <div class="col-md-3">
        <label for="email" class="form-label">Mail (identifiant)</label>
        <input type="email" id="email" name="email" class="form-control" required>
    </div>
    <div class="col-md-3">
        <label for="mot_de_passe" class="form-label">Mot de passe</label>
        <input type="password" id="mot_de_passe" name="mot_de_passe" class="form-control" required autocomplete="new-password">
    </div>
    <div class="col-12">
        <button type="submit" class="btn btn-bordeaux">Créer le compte</button>
    </div>
</form>

<h2 class="h4">Employés</h2>
<div class="table-responsive">
<table class="table table-striped">
    <caption class="visually-hidden">Liste des employés</caption>
    <thead>
    <tr><th scope="col">Nom</th><th scope="col">Mail</th><th scope="col">Statut</th><th scope="col">Action</th></tr>
    </thead>
    <tbody>
    <?php foreach ($employes as $emp) : ?>
        <tr>
            <td><?= e($emp['prenom'] . ' ' . $emp['nom']) ?></td>
            <td><?= e($emp['email']) ?></td>
            <td><?= $emp['actif'] ? 'Actif' : 'Désactivé' ?></td>
            <td>
                <form method="post">
                    <?= champCsrf() ?>
                    <input type="hidden" name="id" value="<?= $emp['id'] ?>">
                    <?php if ($emp['actif']) : ?>
                        <button type="submit" name="action" value="desactiver" class="btn btn-sm btn-danger">Désactiver</button>
                    <?php else : ?>
                        <button type="submit" name="action" value="activer" class="btn btn-sm btn-success">Réactiver</button>
                    <?php endif; ?>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
