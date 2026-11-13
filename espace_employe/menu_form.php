<?php
require_once __DIR__ . '/../includes/fonctions.php';
verifierRole(['employe', 'admin']);

$id = (int)($_GET['id'] ?? 0);
$menu = null;
$platsDuMenu = [];

if ($id > 0) {
    $requete = $bdd->prepare('SELECT * FROM menu WHERE id = ?');
    $requete->execute([$id]);
    $menu = $requete->fetch();

    $requete = $bdd->prepare('SELECT plat_id FROM menu_plat WHERE menu_id = ?');
    $requete->execute([$id]);
    $platsDuMenu = $requete->fetchAll(PDO::FETCH_COLUMN);
}

$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifierCsrf();

    // suppression d'une image
    if (isset($_POST['supprimer_image'])) {
        $requete = $bdd->prepare('SELECT chemin FROM menu_image WHERE id = ? AND menu_id = ?');
        $requete->execute([(int)$_POST['supprimer_image'], $id]);
        $img = $requete->fetch();
        if ($img) {
            if (file_exists(__DIR__ . '/../' . $img['chemin'])) {
                unlink(__DIR__ . '/../' . $img['chemin']);
            }
            $bdd->prepare('DELETE FROM menu_image WHERE id = ?')->execute([(int)$_POST['supprimer_image']]);
        }
        header('Location: /espace_employe/menu_form.php?id=' . $id);
        exit;
    }

    $titre = trim($_POST['titre']);
    $description = trim($_POST['description']);
    $themeId = (int)$_POST['theme_id'];
    $regimeId = (int)$_POST['regime_id'];
    $nbMin = (int)$_POST['nb_personnes_min'];
    $prix = (float)$_POST['prix'];
    $conditions = trim($_POST['conditions']);
    $stock = (int)$_POST['stock'];
    $plats = $_POST['plats'] ?? [];

    if ($titre == '') {
        $erreurs[] = 'Le titre est obligatoire.';
    }
    if ($nbMin < 1 || $prix <= 0) {
        $erreurs[] = 'Le nombre de personnes et le prix doivent être positifs.';
    }
    if ($stock < 0) {
        $erreurs[] = 'Le stock ne peut pas être négatif.';
    }

    if (count($erreurs) == 0) {
        if ($id > 0) {
            $requete = $bdd->prepare('UPDATE menu SET titre = ?, description = ?, theme_id = ?, regime_id = ?, nb_personnes_min = ?, prix = ?, conditions = ?, stock = ? WHERE id = ?');
            $requete->execute([$titre, $description, $themeId, $regimeId, $nbMin, $prix, $conditions, $stock, $id]);
        } else {
            $requete = $bdd->prepare('INSERT INTO menu (titre, description, theme_id, regime_id, nb_personnes_min, prix, conditions, stock) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $requete->execute([$titre, $description, $themeId, $regimeId, $nbMin, $prix, $conditions, $stock]);
            $id = $bdd->lastInsertId();
        }

        // on remet à jour la liste des plats du menu
        $bdd->prepare('DELETE FROM menu_plat WHERE menu_id = ?')->execute([$id]);
        $requete = $bdd->prepare('INSERT INTO menu_plat (menu_id, plat_id) VALUES (?, ?)');
        foreach ($plats as $platId) {
            $requete->execute([$id, (int)$platId]);
        }

        // upload d'une image
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $infos = getimagesize($_FILES['image']['tmp_name']);
            $typesOk = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

            if ($infos === false || !isset($typesOk[$infos['mime']])) {
                setMessage('warning', 'Menu enregistré mais l\'image n\'est pas valide (jpg, png ou webp).');
            } elseif ($_FILES['image']['size'] > 2 * 1024 * 1024) {
                setMessage('warning', 'Menu enregistré mais l\'image fait plus de 2 Mo.');
            } else {
                // nom aléatoire pour éviter les problèmes
                $nomFichier = 'images/menus/' . uniqid('menu_') . '.' . $typesOk[$infos['mime']];
                move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../' . $nomFichier);
                $alt = trim($_POST['image_alt']) != '' ? trim($_POST['image_alt']) : $titre;
                $bdd->prepare('INSERT INTO menu_image (menu_id, chemin, texte_alt) VALUES (?, ?, ?)')->execute([$id, $nomFichier, $alt]);
            }
        }

        if (!isset($_SESSION['message'])) {
            setMessage('success', 'Menu enregistré.');
        }
        header('Location: /espace_employe/menu_form.php?id=' . $id);
        exit;
    }
}

$themes = $bdd->query('SELECT * FROM theme')->fetchAll();
$regimes = $bdd->query('SELECT * FROM regime')->fetchAll();
$tousLesPlats = $bdd->query("SELECT * FROM plat ORDER BY FIELD(type, 'entree', 'plat', 'dessert'), nom")->fetchAll();

$images = [];
if ($id > 0) {
    $requete = $bdd->prepare('SELECT * FROM menu_image WHERE menu_id = ?');
    $requete->execute([$id]);
    $images = $requete->fetchAll();
}

$titrePage = $id > 0 ? 'Modifier un menu' : 'Ajouter un menu';
require_once __DIR__ . '/../includes/header.php';
?>

<h1 class="text-bordeaux"><?= e($titrePage) ?></h1>

<?php foreach ($erreurs as $err) : ?>
    <div class="alert alert-danger" role="alert"><?= e($err) ?></div>
<?php endforeach; ?>

<form method="post" enctype="multipart/form-data" class="row g-3">
    <?= champCsrf() ?>
    <div class="col-md-8">
        <label for="titre" class="form-label">Titre *</label>
        <input type="text" id="titre" name="titre" class="form-control" required value="<?= e($menu['titre'] ?? '') ?>">
    </div>
    <div class="col-md-2">
        <label for="theme_id" class="form-label">Thème</label>
        <select id="theme_id" name="theme_id" class="form-select">
            <?php foreach ($themes as $t) : ?>
                <option value="<?= $t['id'] ?>" <?= ($menu['theme_id'] ?? 0) == $t['id'] ? 'selected' : '' ?>><?= e($t['libelle']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2">
        <label for="regime_id" class="form-label">Régime</label>
        <select id="regime_id" name="regime_id" class="form-select">
            <?php foreach ($regimes as $r) : ?>
                <option value="<?= $r['id'] ?>" <?= ($menu['regime_id'] ?? 0) == $r['id'] ? 'selected' : '' ?>><?= e($r['libelle']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-12">
        <label for="description" class="form-label">Description</label>
        <textarea id="description" name="description" class="form-control" rows="3"><?= e($menu['description'] ?? '') ?></textarea>
    </div>
    <div class="col-md-4">
        <label for="nb_personnes_min" class="form-label">Nombre de personnes minimum *</label>
        <input type="number" id="nb_personnes_min" name="nb_personnes_min" class="form-control" min="1" required value="<?= e($menu['nb_personnes_min'] ?? '') ?>">
    </div>
    <div class="col-md-4">
        <label for="prix" class="form-label">Prix pour ce nombre de personnes (€) *</label>
        <input type="number" id="prix" name="prix" class="form-control" min="0" step="0.01" required value="<?= e($menu['prix'] ?? '') ?>">
    </div>
    <div class="col-md-4">
        <label for="stock" class="form-label">Stock (commandes possibles)</label>
        <input type="number" id="stock" name="stock" class="form-control" min="0" value="<?= e($menu['stock'] ?? 0) ?>">
    </div>
    <div class="col-12">
        <label for="conditions" class="form-label">Conditions du menu</label>
        <textarea id="conditions" name="conditions" class="form-control" rows="2"><?= e($menu['conditions'] ?? '') ?></textarea>
    </div>

    <fieldset class="col-12">
        <legend class="h5">Plats du menu</legend>
        <div class="row">
            <?php foreach ($tousLesPlats as $p) : ?>
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="plats[]" value="<?= $p['id'] ?>" id="plat<?= $p['id'] ?>"
                            <?= in_array($p['id'], $platsDuMenu) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="plat<?= $p['id'] ?>"><?= e($p['nom']) ?> (<?= e($p['type']) ?>)</label>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </fieldset>

    <div class="col-md-6">
        <label for="image" class="form-label">Ajouter une image (jpg, png, webp - 2 Mo max)</label>
        <input type="file" id="image" name="image" class="form-control" accept="image/jpeg,image/png,image/webp">
    </div>
    <div class="col-md-6">
        <label for="image_alt" class="form-label">Description de l'image (accessibilité)</label>
        <input type="text" id="image_alt" name="image_alt" class="form-control">
    </div>

    <div class="col-12">
        <button type="submit" class="btn btn-bordeaux">Enregistrer</button>
        <a href="/espace_employe/menus.php" class="btn btn-outline-secondary">Retour</a>
    </div>
</form>

<?php if (count($images) > 0) : ?>
    <h2 class="h5 mt-4">Galerie</h2>
    <div class="row galerie">
        <?php foreach ($images as $img) : ?>
            <div class="col-md-3 mb-3">
                <img src="/<?= e($img['chemin']) ?>" alt="<?= e($img['texte_alt']) ?>">
                <form method="post" class="mt-1">
                    <?= champCsrf() ?>
                    <button type="submit" name="supprimer_image" value="<?= $img['id'] ?>" class="btn btn-sm btn-danger">Supprimer l'image</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
