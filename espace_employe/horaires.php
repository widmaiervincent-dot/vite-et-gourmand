<?php
require_once __DIR__ . '/../includes/fonctions.php';
verifierRole(['employe', 'admin']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifierCsrf();
    $requete = $bdd->prepare('UPDATE horaire SET heure_ouverture = ?, heure_fermeture = ?, ferme = ? WHERE id = ?');

    foreach ($_POST['horaire'] as $idHoraire => $h) {
        $ferme = isset($h['ferme']) ? 1 : 0;
        $ouverture = $ferme || $h['ouverture'] == '' ? null : $h['ouverture'];
        $fermeture = $ferme || $h['fermeture'] == '' ? null : $h['fermeture'];
        $requete->execute([$ouverture, $fermeture, $ferme, (int)$idHoraire]);
    }
    setMessage('success', 'Horaires mis à jour.');
    header('Location: /espace_employe/horaires.php');
    exit;
}

$horaires = getHoraires();

$titrePage = 'Gérer les horaires';
require_once __DIR__ . '/../includes/header.php';
?>

<h1 class="text-bordeaux">Horaires d'ouverture</h1>

<form method="post">
    <?= champCsrf() ?>
    <div class="table-responsive">
    <table class="table">
        <caption class="visually-hidden">Horaires par jour</caption>
        <thead>
        <tr><th scope="col">Jour</th><th scope="col">Ouverture</th><th scope="col">Fermeture</th><th scope="col">Fermé</th></tr>
        </thead>
        <tbody>
        <?php foreach ($horaires as $h) : ?>
            <tr>
                <th scope="row"><?= e($h['jour']) ?></th>
                <td>
                    <label for="ouv<?= $h['id'] ?>" class="visually-hidden">Ouverture <?= e($h['jour']) ?></label>
                    <input type="time" id="ouv<?= $h['id'] ?>" name="horaire[<?= $h['id'] ?>][ouverture]" class="form-control" value="<?= e(substr($h['heure_ouverture'] ?? '', 0, 5)) ?>">
                </td>
                <td>
                    <label for="ferm<?= $h['id'] ?>" class="visually-hidden">Fermeture <?= e($h['jour']) ?></label>
                    <input type="time" id="ferm<?= $h['id'] ?>" name="horaire[<?= $h['id'] ?>][fermeture]" class="form-control" value="<?= e(substr($h['heure_fermeture'] ?? '', 0, 5)) ?>">
                </td>
                <td>
                    <input type="checkbox" class="form-check-input" id="fermé<?= $h['id'] ?>" name="horaire[<?= $h['id'] ?>][ferme]" <?= $h['ferme'] ? 'checked' : '' ?>>
                    <label for="fermé<?= $h['id'] ?>" class="visually-hidden">Fermé le <?= e($h['jour']) ?></label>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <button type="submit" class="btn btn-bordeaux">Enregistrer</button>
    <a href="/espace_employe/index.php" class="btn btn-outline-secondary">Retour</a>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
