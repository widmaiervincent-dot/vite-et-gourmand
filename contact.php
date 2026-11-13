<?php
require_once 'includes/fonctions.php';

$erreur = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifierCsrf();
    $titre = trim($_POST['titre']);
    $description = trim($_POST['description']);
    $email = trim($_POST['email']);

    if ($titre == '' || $description == '') {
        $erreur = 'Merci de remplir tous les champs.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur = 'L\'adresse mail n\'est pas valide.';
    } else {
        $contenu = '<h2>Nouvelle demande de contact</h2>'
            . '<p><strong>De :</strong> ' . e($email) . '</p>'
            . '<p><strong>Titre :</strong> ' . e($titre) . '</p>'
            . '<p>' . nl2br(e($description)) . '</p>';
        // le mail part vers l'entreprise, avec le mail du visiteur en "répondre à"
        envoyerMail(MAIL_ENTREPRISE, 'Contact : ' . $titre, $contenu, $email);

        setMessage('success', 'Votre message a bien été envoyé, nous vous répondrons rapidement.');
        header('Location: /contact.php');
        exit;
    }
}

$titrePage = 'Contact';
require_once 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <h1 class="text-bordeaux">Nous contacter</h1>
        <?php if ($erreur) : ?>
            <div class="alert alert-danger" role="alert"><?= e($erreur) ?></div>
        <?php endif; ?>
        <form method="post">
            <?= champCsrf() ?>
            <div class="mb-3">
                <label for="email" class="form-label">Votre adresse mail *</label>
                <input type="email" class="form-control" id="email" name="email" required autocomplete="email"
                       value="<?= e($_SESSION['utilisateur']['email'] ?? '') ?>">
            </div>
            <div class="mb-3">
                <label for="titre" class="form-label">Titre *</label>
                <input type="text" class="form-control" id="titre" name="titre" required maxlength="150">
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Votre demande *</label>
                <textarea class="form-control" id="description" name="description" rows="6" required></textarea>
            </div>
            <button type="submit" class="btn btn-bordeaux">Envoyer</button>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
