<?php
require_once __DIR__ . '/../includes/fonctions.php';
verifierRole(['utilisateur']);

$requete = $bdd->prepare("SELECT c.*, m.titre
                          FROM commande c
                          JOIN menu m ON m.id = c.menu_id
                          WHERE c.utilisateur_id = ?
                          ORDER BY c.date_commande DESC");
$requete->execute([$_SESSION['utilisateur']['id']]);
$commandes = $requete->fetchAll();

$titrePage = 'Mon espace';
require_once __DIR__ . '/../includes/header.php';
?>

<h1 class="text-bordeaux">Bonjour <?= e($_SESSION['utilisateur']['prenom']) ?></h1>
<p><a href="/espace_utilisateur/profil.php" class="btn btn-outline-secondary">Modifier mes informations</a></p>

<h2 class="h4 mt-4">Mes commandes</h2>

<?php if (count($commandes) == 0) : ?>
    <p>Vous n'avez pas encore passé de commande. <a href="/menus.php">Voir nos menus</a></p>
<?php else : ?>
    <div class="table-responsive">
        <table class="table table-striped">
            <caption class="visually-hidden">Liste de mes commandes</caption>
            <thead>
            <tr>
                <th scope="col">N°</th>
                <th scope="col">Menu</th>
                <th scope="col">Date prestation</th>
                <th scope="col">Personnes</th>
                <th scope="col">Total</th>
                <th scope="col">Statut</th>
                <th scope="col">Action</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($commandes as $c) : ?>
                <tr>
                    <td><?= e($c['numero']) ?></td>
                    <td><?= e($c['titre']) ?></td>
                    <td><?= date('d/m/Y', strtotime($c['date_prestation'])) ?></td>
                    <td><?= e($c['nb_personnes']) ?></td>
                    <td><?= formatPrix($c['prix_total']) ?></td>
                    <td><span class="badge badge-statut"><?= e($c['statut']) ?></span></td>
                    <td><a href="/espace_utilisateur/commande_detail.php?id=<?= $c['id'] ?>">Détail<span class="visually-hidden"> de la commande <?= e($c['numero']) ?></span></a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
