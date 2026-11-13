<?php
require_once __DIR__ . '/../includes/fonctions.php';
verifierRole(['employe', 'admin']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifierCsrf();
    $decision = $_POST['decision'] == 'valide' ? 'valide' : 'refuse';
    $bdd->prepare('UPDATE avis SET statut = ? WHERE id = ?')->execute([$decision, (int)$_POST['id']]);
    setMessage('success', $decision == 'valide' ? 'Avis validé, il est visible sur l\'accueil.' : 'Avis refusé.');
    header('Location: /espace_employe/avis.php');
    exit;
}

$avis = $bdd->query("SELECT a.*, u.prenom, u.nom, c.numero
                     FROM avis a
                     JOIN utilisateur u ON u.id = a.utilisateur_id
                     JOIN commande c ON c.id = a.commande_id
                     ORDER BY a.statut = 'en attente' DESC, a.date_avis DESC")->fetchAll();

$titrePage = 'Avis clients';
require_once __DIR__ . '/../includes/header.php';
?>

<h1 class="text-bordeaux">Avis clients</h1>

<?php foreach ($avis as $a) : ?>
    <div class="card p-3 mb-3">
        <p class="mb-1"><strong><?= e($a['prenom'] . ' ' . $a['nom']) ?></strong> - commande <?= e($a['numero']) ?> - <?= date('d/m/Y', strtotime($a['date_avis'])) ?></p>
        <p class="etoiles mb-1" aria-label="Note <?= $a['note'] ?> sur 5"><?= str_repeat('★', $a['note']) . str_repeat('☆', 5 - $a['note']) ?></p>
        <p><?= e($a['commentaire']) ?></p>
        <p class="small">Statut : <strong><?= e($a['statut']) ?></strong></p>
        <?php if ($a['statut'] == 'en attente') : ?>
            <form method="post">
                <?= champCsrf() ?>
                <input type="hidden" name="id" value="<?= $a['id'] ?>">
                <button type="submit" name="decision" value="valide" class="btn btn-success btn-sm">Valider</button>
                <button type="submit" name="decision" value="refuse" class="btn btn-danger btn-sm">Refuser</button>
            </form>
        <?php endif; ?>
    </div>
<?php endforeach; ?>

<?php if (count($avis) == 0) : ?>
    <p>Aucun avis pour le moment.</p>
<?php endif; ?>

<p><a href="/espace_employe/index.php">&larr; Retour</a></p>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
