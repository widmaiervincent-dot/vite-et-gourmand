<?php
require_once __DIR__ . '/../includes/fonctions.php';
verifierRole(['employe', 'admin']);

// suppression d'un menu
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['supprimer'])) {
    verifierCsrf();
    try {
        // on note les images du menu avant de le supprimer (les lignes menu_image partent en cascade)
        $requete = $bdd->prepare('SELECT chemin FROM menu_image WHERE menu_id = ?');
        $requete->execute([(int)$_POST['supprimer']]);
        $imagesMenu = $requete->fetchAll(PDO::FETCH_COLUMN);

        $bdd->prepare('DELETE FROM menu WHERE id = ?')->execute([(int)$_POST['supprimer']]);

        // le menu est bien supprimé : on efface les fichiers envoyés par les employés (menu_xxx)
        foreach ($imagesMenu as $chemin) {
            if (strpos($chemin, 'images/menus/menu_') === 0 && file_exists(__DIR__ . '/../' . $chemin)) {
                unlink(__DIR__ . '/../' . $chemin);
            }
        }
        setMessage('success', 'Menu supprimé.');
    } catch (PDOException $e) {
        // si des commandes utilisent ce menu, la clé étrangère bloque la suppression
        setMessage('danger', 'Impossible de supprimer ce menu car il a déjà été commandé. Mettez son stock à 0 à la place.');
    }
    header('Location: /espace_employe/menus.php');
    exit;
}

$menus = $bdd->query("SELECT m.*, t.libelle AS theme, r.libelle AS regime
                      FROM menu m JOIN theme t ON t.id = m.theme_id JOIN regime r ON r.id = m.regime_id
                      ORDER BY m.titre")->fetchAll();

$titrePage = 'Gérer les menus';
require_once __DIR__ . '/../includes/header.php';
?>

<h1 class="text-bordeaux">Gérer les menus</h1>
<p><a href="/espace_employe/menu_form.php" class="btn btn-bordeaux">+ Ajouter un menu</a></p>

<div class="table-responsive">
    <table class="table table-striped">
        <caption class="visually-hidden">Liste des menus</caption>
        <thead>
        <tr>
            <th scope="col">Titre</th>
            <th scope="col">Thème</th>
            <th scope="col">Régime</th>
            <th scope="col">Pers. min</th>
            <th scope="col">Prix</th>
            <th scope="col">Stock</th>
            <th scope="col">Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($menus as $m) : ?>
            <tr>
                <td><?= e($m['titre']) ?></td>
                <td><?= e($m['theme']) ?></td>
                <td><?= e($m['regime']) ?></td>
                <td><?= e($m['nb_personnes_min']) ?></td>
                <td><?= formatPrix($m['prix']) ?></td>
                <td><?= e($m['stock']) ?></td>
                <td>
                    <a href="/espace_employe/menu_form.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-outline-secondary">Modifier</a>
                    <form method="post" class="d-inline" onsubmit="return confirm('Supprimer ce menu ?');">
                        <?= champCsrf() ?>
                        <button type="submit" name="supprimer" value="<?= $m['id'] ?>" class="btn btn-sm btn-danger">Supprimer</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<p><a href="/espace_employe/index.php">&larr; Retour</a></p>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
