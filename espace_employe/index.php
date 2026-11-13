<?php
require_once __DIR__ . '/../includes/fonctions.php';
verifierRole(['employe', 'admin']);

// filtres sur les commandes
$filtreStatut = $_GET['statut'] ?? '';
$filtreClient = trim($_GET['client'] ?? '');

$sql = "SELECT c.*, m.titre, u.nom, u.prenom, u.email, u.telephone
        FROM commande c
        JOIN menu m ON m.id = c.menu_id
        JOIN utilisateur u ON u.id = c.utilisateur_id
        WHERE 1 = 1";
$params = [];

if ($filtreStatut != '') {
    $sql .= " AND c.statut = ?";
    $params[] = $filtreStatut;
}
if ($filtreClient != '') {
    $sql .= " AND (u.nom LIKE ? OR u.prenom LIKE ? OR u.email LIKE ?)";
    $params[] = '%' . $filtreClient . '%';
    $params[] = '%' . $filtreClient . '%';
    $params[] = '%' . $filtreClient . '%';
}
$sql .= " ORDER BY c.date_prestation ASC";

$requete = $bdd->prepare($sql);
$requete->execute($params);
$commandes = $requete->fetchAll();

$titrePage = 'Espace employé';
require_once __DIR__ . '/../includes/header.php';
?>

<h1 class="text-bordeaux">Espace employé</h1>

<nav aria-label="Menu de l'espace employé" class="mb-4">
    <a href="/espace_employe/menus.php" class="btn btn-outline-secondary mb-1">Gérer les menus</a>
    <a href="/espace_employe/plats.php" class="btn btn-outline-secondary mb-1">Gérer les plats</a>
    <a href="/espace_employe/horaires.php" class="btn btn-outline-secondary mb-1">Gérer les horaires</a>
    <a href="/espace_employe/avis.php" class="btn btn-outline-secondary mb-1">Valider les avis</a>
</nav>

<h2 class="h4">Commandes</h2>

<form method="get" class="row g-2 mb-3" aria-label="Filtrer les commandes">
    <div class="col-md-4">
        <label for="statut" class="form-label">Statut</label>
        <select id="statut" name="statut" class="form-select">
            <option value="">Tous les statuts</option>
            <?php foreach (listeStatuts() as $s) : ?>
                <option value="<?= e($s) ?>" <?= $filtreStatut == $s ? 'selected' : '' ?>><?= e($s) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-4">
        <label for="client" class="form-label">Client (nom ou mail)</label>
        <input type="text" id="client" name="client" class="form-control" value="<?= e($filtreClient) ?>">
    </div>
    <div class="col-md-2 d-flex align-items-end">
        <button type="submit" class="btn btn-bordeaux w-100">Filtrer</button>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-striped">
        <caption class="visually-hidden">Liste des commandes</caption>
        <thead>
        <tr>
            <th scope="col">N°</th>
            <th scope="col">Client</th>
            <th scope="col">Menu</th>
            <th scope="col">Prestation</th>
            <th scope="col">Total</th>
            <th scope="col">Statut</th>
            <th scope="col">Action</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($commandes as $c) : ?>
            <tr>
                <td><?= e($c['numero']) ?></td>
                <td><?= e($c['prenom'] . ' ' . $c['nom']) ?><br><small><?= e($c['telephone']) ?></small></td>
                <td><?= e($c['titre']) ?> (<?= e($c['nb_personnes']) ?> pers.)</td>
                <td><?= date('d/m/Y', strtotime($c['date_prestation'])) ?> <?= e(substr($c['heure_livraison'], 0, 5)) ?></td>
                <td><?= formatPrix($c['prix_total']) ?></td>
                <td><span class="badge badge-statut"><?= e($c['statut']) ?></span></td>
                <td><a href="/espace_employe/commande_gerer.php?id=<?= $c['id'] ?>">Gérer<span class="visually-hidden"> la commande <?= e($c['numero']) ?></span></a></td>
            </tr>
        <?php endforeach; ?>
        <?php if (count($commandes) == 0) : ?>
            <tr><td colspan="7">Aucune commande trouvée.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
