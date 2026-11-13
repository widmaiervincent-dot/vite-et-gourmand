<?php
require_once 'includes/fonctions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$requete = $bdd->prepare("SELECT m.*, t.libelle AS theme, r.libelle AS regime
                          FROM menu m
                          JOIN theme t ON t.id = m.theme_id
                          JOIN regime r ON r.id = m.regime_id
                          WHERE m.id = ?");
$requete->execute([$id]);
$menu = $requete->fetch();

if (!$menu) {
    setMessage('warning', 'Ce menu n\'existe pas.');
    header('Location: /menus.php');
    exit;
}

// images
$requete = $bdd->prepare('SELECT * FROM menu_image WHERE menu_id = ?');
$requete->execute([$id]);
$images = $requete->fetchAll();

// plats du menu avec leurs allergènes
$requete = $bdd->prepare("SELECT p.*, GROUP_CONCAT(a.libelle SEPARATOR ', ') AS allergenes
                          FROM plat p
                          JOIN menu_plat mp ON mp.plat_id = p.id
                          LEFT JOIN plat_allergene pa ON pa.plat_id = p.id
                          LEFT JOIN allergene a ON a.id = pa.allergene_id
                          WHERE mp.menu_id = ?
                          GROUP BY p.id
                          ORDER BY FIELD(p.type, 'entree', 'plat', 'dessert')");
$requete->execute([$id]);
$plats = $requete->fetchAll();

$titrePage = $menu['titre'];
require_once 'includes/header.php';

$nomsTypes = ['entree' => 'Entrées', 'plat' => 'Plats', 'dessert' => 'Desserts'];
?>

<nav aria-label="Fil d'ariane">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/menus.php">Nos menus</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= e($menu['titre']) ?></li>
    </ol>
</nav>

<h1 class="text-bordeaux"><?= e($menu['titre']) ?></h1>
<p>
    <span class="badge bg-secondary"><?= e($menu['theme']) ?></span>
    <span class="badge bg-success"><?= e($menu['regime']) ?></span>
</p>

<!-- les conditions doivent être bien visibles -->
<div class="conditions-menu mb-4" role="note">
    <h2 class="h5">⚠ Conditions de ce menu</h2>
    <p class="mb-0"><?= nl2br(e($menu['conditions'])) ?></p>
</div>

<?php if (count($images) > 0) : ?>
    <div class="row galerie mb-4">
        <?php foreach ($images as $img) : ?>
            <div class="col-md-4 mb-3">
                <img src="/<?= e($img['chemin']) ?>" alt="<?= e($img['texte_alt']) ?>">
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-8">
        <h2 class="h4">Présentation</h2>
        <p><?= nl2br(e($menu['description'])) ?></p>

        <h2 class="h4">Composition du menu</h2>
        <?php foreach ($nomsTypes as $type => $nomType) : ?>
            <h3 class="h5 mt-3"><?= $nomType ?></h3>
            <ul>
                <?php foreach ($plats as $plat) : ?>
                    <?php if ($plat['type'] == $type) : ?>
                        <li>
                            <strong><?= e($plat['nom']) ?></strong> - <?= e($plat['description']) ?>
                            <br><small>Allergènes : <?= $plat['allergenes'] ? e($plat['allergenes']) : 'aucun' ?></small>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        <?php endforeach; ?>
    </div>

    <div class="col-md-4">
        <div class="card p-3">
            <p>Pour <strong><?= e($menu['nb_personnes_min']) ?> personnes minimum</strong></p>
            <p class="h4"><?= formatPrix($menu['prix']) ?></p>
            <p class="small">soit <?= formatPrix($menu['prix'] / $menu['nb_personnes_min']) ?> par personne</p>
            <p>Stock : <?= $menu['stock'] > 0 ? e($menu['stock']) . ' commande(s) possible(s)' : 'épuisé' ?></p>

            <?php if ($menu['stock'] > 0) : ?>
                <?php if (estConnecte()) : ?>
                    <a href="/commande.php?menu=<?= $menu['id'] ?>" class="btn btn-bordeaux btn-lg">Commander</a>
                <?php else : ?>
                    <p>Vous devez être connecté pour commander.</p>
                    <a href="/connexion.php?redirection=<?= urlencode('/commande.php?menu=' . $menu['id']) ?>" class="btn btn-bordeaux mb-2">Se connecter</a>
                    <a href="/inscription.php" class="btn btn-outline-secondary">Créer un compte</a>
                <?php endif; ?>
            <?php else : ?>
                <button class="btn btn-secondary" disabled>Menu épuisé</button>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
