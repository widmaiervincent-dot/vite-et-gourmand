<?php
require_once __DIR__ . '/../includes/fonctions.php';
require_once __DIR__ . '/../config/mongo.php';
verifierRole(['admin']);

// les statistiques viennent de MongoDB (base non relationnelle)
$collection = getCollectionStats();

$menus = $bdd->query('SELECT id, titre FROM menu ORDER BY titre')->fetchAll();

// filtres pour le chiffre d'affaires
$filtreMenu = (int)($_GET['menu'] ?? 0);
$dateDebut = $_GET['date_debut'] ?? date('Y-01-01');
$dateFin = $_GET['date_fin'] ?? date('Y-m-d');

$nbParMenu = [];
$chiffreAffaires = [];
$totalCA = 0;
$erreurMongo = false;

if ($collection == null) {
    $erreurMongo = true;
} else {
    try {
        // 1) nombre de commandes par menu (hors commandes annulées)
        $resultat = $collection->aggregate([
            ['$match' => ['statut' => ['$ne' => 'annulée']]],
            ['$group' => ['_id' => '$menu_titre', 'total' => ['$sum' => 1]]],
            ['$sort' => ['total' => -1]],
        ]);
        foreach ($resultat as $ligne) {
            $nbParMenu[$ligne['_id']] = $ligne['total'];
        }

        // 2) chiffre d'affaires par menu sur la période
        $filtre = [
            'statut' => ['$ne' => 'annulée'],
            'date_commande' => [
                '$gte' => new MongoDB\BSON\UTCDateTime(strtotime($dateDebut) * 1000),
                '$lte' => new MongoDB\BSON\UTCDateTime(strtotime($dateFin . ' 23:59:59') * 1000),
            ],
        ];
        if ($filtreMenu > 0) {
            $filtre['menu_id'] = $filtreMenu;
        }
        $resultat = $collection->aggregate([
            ['$match' => $filtre],
            ['$group' => ['_id' => '$menu_titre', 'ca' => ['$sum' => '$prix_total'], 'nb' => ['$sum' => 1]]],
            ['$sort' => ['ca' => -1]],
        ]);
        foreach ($resultat as $ligne) {
            $chiffreAffaires[] = ['menu' => $ligne['_id'], 'ca' => $ligne['ca'], 'nb' => $ligne['nb']];
            $totalCA += $ligne['ca'];
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
        $erreurMongo = true;
    }
}

$titrePage = 'Statistiques';
require_once __DIR__ . '/../includes/header.php';
?>

<h1 class="text-bordeaux">Statistiques</h1>

<?php if ($erreurMongo) : ?>
    <div class="alert alert-warning" role="alert">La base MongoDB n'est pas accessible, les statistiques ne peuvent pas être affichées.</div>
<?php endif; ?>

<section class="card p-3 mb-4">
    <h2 class="h4">Nombre de commandes par menu</h2>
    <canvas id="graphiqueCommandes" height="120" role="img" aria-label="Graphique du nombre de commandes par menu"></canvas>

    <!-- tableau pour l'accessibilité (les lecteurs d'écran ne lisent pas le graphique) -->
    <table class="table table-sm mt-3">
        <caption>Données du graphique</caption>
        <thead><tr><th scope="col">Menu</th><th scope="col">Commandes</th></tr></thead>
        <tbody>
        <?php foreach ($nbParMenu as $menu => $nb) : ?>
            <tr><td><?= e($menu) ?></td><td><?= e($nb) ?></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>

<section class="card p-3">
    <h2 class="h4">Chiffre d'affaires</h2>
    <form method="get" class="row g-2 mb-3">
        <div class="col-md-4">
            <label for="menu" class="form-label">Menu</label>
            <select id="menu" name="menu" class="form-select">
                <option value="0">Tous les menus</option>
                <?php foreach ($menus as $m) : ?>
                    <option value="<?= $m['id'] ?>" <?= $filtreMenu == $m['id'] ? 'selected' : '' ?>><?= e($m['titre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label for="date_debut" class="form-label">Du</label>
            <input type="date" id="date_debut" name="date_debut" class="form-control" value="<?= e($dateDebut) ?>">
        </div>
        <div class="col-md-3">
            <label for="date_fin" class="form-label">Au</label>
            <input type="date" id="date_fin" name="date_fin" class="form-control" value="<?= e($dateFin) ?>">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-bordeaux w-100">Calculer</button>
        </div>
    </form>

    <table class="table table-striped">
        <caption class="visually-hidden">Chiffre d'affaires par menu</caption>
        <thead><tr><th scope="col">Menu</th><th scope="col">Nb commandes</th><th scope="col">Chiffre d'affaires</th></tr></thead>
        <tbody>
        <?php foreach ($chiffreAffaires as $ligne) : ?>
            <tr><td><?= e($ligne['menu']) ?></td><td><?= e($ligne['nb']) ?></td><td><?= formatPrix($ligne['ca']) ?></td></tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
        <tr><th scope="row" colspan="2">Total</th><th><?= formatPrix($totalCA) ?></th></tr>
        </tfoot>
    </table>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const labels = <?= json_encode(array_keys($nbParMenu)) ?>;
    const valeurs = <?= json_encode(array_values($nbParMenu)) ?>;

    new Chart(document.getElementById('graphiqueCommandes'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Nombre de commandes',
                data: valeurs,
                backgroundColor: '#e67e22'
            }]
        },
        options: {
            scales: {y: {beginAtZero: true, ticks: {precision: 0}}}
        }
    });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
