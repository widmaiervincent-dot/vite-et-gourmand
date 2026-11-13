<?php
require_once __DIR__ . '/../includes/fonctions.php';
verifierRole(['employe', 'admin']);

$allergenes = $bdd->query('SELECT * FROM allergene ORDER BY libelle')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifierCsrf();

    if (isset($_POST['supprimer'])) {
        $bdd->prepare('DELETE FROM plat WHERE id = ?')->execute([(int)$_POST['supprimer']]);
        setMessage('success', 'Plat supprimé.');
    } else {
        $idPlat = (int)$_POST['id'];
        $nom = trim($_POST['nom']);
        $type = $_POST['type'];
        $description = trim($_POST['description']);
        $allergenesChoisis = $_POST['allergenes'] ?? [];

        if ($nom == '' || !in_array($type, ['entree', 'plat', 'dessert'])) {
            setMessage('danger', 'Nom ou type invalide.');
        } else {
            if ($idPlat > 0) {
                $bdd->prepare('UPDATE plat SET nom = ?, type = ?, description = ? WHERE id = ?')->execute([$nom, $type, $description, $idPlat]);
            } else {
                $bdd->prepare('INSERT INTO plat (nom, type, description) VALUES (?, ?, ?)')->execute([$nom, $type, $description]);
                $idPlat = $bdd->lastInsertId();
            }
            // allergènes
            $bdd->prepare('DELETE FROM plat_allergene WHERE plat_id = ?')->execute([$idPlat]);
            $requete = $bdd->prepare('INSERT INTO plat_allergene (plat_id, allergene_id) VALUES (?, ?)');
            foreach ($allergenesChoisis as $a) {
                $requete->execute([$idPlat, (int)$a]);
            }
            setMessage('success', 'Plat enregistré.');
        }
    }
    header('Location: /espace_employe/plats.php');
    exit;
}

// plat en cours de modification
$platModif = null;
$allergenesPlat = [];
if (isset($_GET['modifier'])) {
    $requete = $bdd->prepare('SELECT * FROM plat WHERE id = ?');
    $requete->execute([(int)$_GET['modifier']]);
    $platModif = $requete->fetch();
    $requete = $bdd->prepare('SELECT allergene_id FROM plat_allergene WHERE plat_id = ?');
    $requete->execute([(int)$_GET['modifier']]);
    $allergenesPlat = $requete->fetchAll(PDO::FETCH_COLUMN);
}

$plats = $bdd->query("SELECT p.*, GROUP_CONCAT(a.libelle SEPARATOR ', ') AS allergenes
                      FROM plat p
                      LEFT JOIN plat_allergene pa ON pa.plat_id = p.id
                      LEFT JOIN allergene a ON a.id = pa.allergene_id
                      GROUP BY p.id
                      ORDER BY FIELD(p.type, 'entree', 'plat', 'dessert'), p.nom")->fetchAll();

$titrePage = 'Gérer les plats';
require_once __DIR__ . '/../includes/header.php';
?>

<h1 class="text-bordeaux">Gérer les plats</h1>

<form method="post" class="card p-3 mb-4">
    <?= champCsrf() ?>
    <input type="hidden" name="id" value="<?= e($platModif['id'] ?? 0) ?>">
    <h2 class="h5"><?= $platModif ? 'Modifier le plat' : 'Ajouter un plat' ?></h2>
    <div class="row g-3">
        <div class="col-md-5">
            <label for="nom" class="form-label">Nom</label>
            <input type="text" id="nom" name="nom" class="form-control" required value="<?= e($platModif['nom'] ?? '') ?>">
        </div>
        <div class="col-md-3">
            <label for="type" class="form-label">Type</label>
            <select id="type" name="type" class="form-select">
                <option value="entree" <?= ($platModif['type'] ?? '') == 'entree' ? 'selected' : '' ?>>Entrée</option>
                <option value="plat" <?= ($platModif['type'] ?? '') == 'plat' ? 'selected' : '' ?>>Plat</option>
                <option value="dessert" <?= ($platModif['type'] ?? '') == 'dessert' ? 'selected' : '' ?>>Dessert</option>
            </select>
        </div>
        <div class="col-12">
            <label for="description" class="form-label">Description</label>
            <input type="text" id="description" name="description" class="form-control" value="<?= e($platModif['description'] ?? '') ?>">
        </div>
        <fieldset class="col-12">
            <legend class="fs-6">Allergènes</legend>
            <?php foreach ($allergenes as $a) : ?>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="allergenes[]" value="<?= $a['id'] ?>" id="all<?= $a['id'] ?>"
                        <?= in_array($a['id'], $allergenesPlat) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="all<?= $a['id'] ?>"><?= e($a['libelle']) ?></label>
                </div>
            <?php endforeach; ?>
        </fieldset>
        <div class="col-12">
            <button type="submit" class="btn btn-bordeaux">Enregistrer</button>
            <?php if ($platModif) : ?>
                <a href="/espace_employe/plats.php" class="btn btn-outline-secondary">Annuler</a>
            <?php endif; ?>
        </div>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-striped">
        <caption class="visually-hidden">Liste des plats</caption>
        <thead>
        <tr><th scope="col">Type</th><th scope="col">Nom</th><th scope="col">Allergènes</th><th scope="col">Actions</th></tr>
        </thead>
        <tbody>
        <?php foreach ($plats as $p) : ?>
            <tr>
                <td><?= e($p['type']) ?></td>
                <td><?= e($p['nom']) ?></td>
                <td><?= e($p['allergenes'] ?? '-') ?></td>
                <td>
                    <a href="?modifier=<?= $p['id'] ?>" class="btn btn-sm btn-outline-secondary">Modifier</a>
                    <form method="post" class="d-inline" onsubmit="return confirm('Supprimer ce plat ?');">
                        <?= champCsrf() ?>
                        <button type="submit" name="supprimer" value="<?= $p['id'] ?>" class="btn btn-sm btn-danger">Supprimer</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<p><a href="/espace_employe/index.php">&larr; Retour</a></p>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
