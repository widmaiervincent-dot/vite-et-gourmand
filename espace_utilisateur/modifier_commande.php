<?php
require_once __DIR__ . '/../includes/fonctions.php';
require_once __DIR__ . '/../config/mongo.php';
verifierRole(['utilisateur']);

$id = (int)($_GET['id'] ?? 0);

$requete = $bdd->prepare("SELECT c.*, m.titre, m.prix, m.nb_personnes_min
                          FROM commande c JOIN menu m ON m.id = c.menu_id
                          WHERE c.id = ? AND c.utilisateur_id = ?");
$requete->execute([$id, $_SESSION['utilisateur']['id']]);
$commande = $requete->fetch();

// modification possible seulement si la commande n'est pas encore acceptée
if (!$commande || $commande['statut'] != 'en attente') {
    setMessage('danger', 'Cette commande ne peut plus être modifiée.');
    header('Location: /espace_utilisateur/index.php');
    exit;
}

$erreurs = [];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifierCsrf();

    $nbPersonnes = (int)$_POST['nb_personnes'];
    $adresse = trim($_POST['adresse']);
    $ville = trim($_POST['ville']);
    $distance = (float)($_POST['distance_km'] ?? 0);
    $date = $_POST['date_prestation'];
    $heure = $_POST['heure_livraison'];

    if ($nbPersonnes < $commande['nb_personnes_min']) {
        $erreurs[] = 'Minimum ' . $commande['nb_personnes_min'] . ' personnes pour ce menu.';
    }
    if ($adresse == '' || $ville == '' || $heure == '') {
        $erreurs[] = 'Tous les champs sont obligatoires.';
    }
    if ($date == '' || strtotime($date) < strtotime('tomorrow')) {
        $erreurs[] = 'La date doit être au plus tôt demain.';
    }

    if (strtolower($ville) != 'bordeaux' && $distance <= 0) {
        $erreurs[] = 'Indiquez la distance depuis Bordeaux pour calculer la livraison.';
    }

    if (count($erreurs) == 0) {
        // le menu ne change pas, on recalcule juste le prix
        $menu = ['prix' => $commande['prix'], 'nb_personnes_min' => $commande['nb_personnes_min']];
        $prix = calculerPrix($menu, $nbPersonnes, $ville, $distance);

        $requete = $bdd->prepare("UPDATE commande SET nb_personnes = ?, adresse_prestation = ?, ville_prestation = ?, distance_km = ?,
                                  date_prestation = ?, heure_livraison = ?, prix_menu = ?, reduction = ?, prix_livraison = ?, prix_total = ?
                                  WHERE id = ?");
        $requete->execute([$nbPersonnes, $adresse, $ville, $distance, $date, $heure,
            $prix['prix_menu'], $prix['reduction'], $prix['livraison'], $prix['total'], $id]);

        majStatCommande($id, ['nb_personnes' => $nbPersonnes, 'prix_total' => (float)$prix['total']]);

        setMessage('success', 'Votre commande a été modifiée.');
        header('Location: /espace_utilisateur/commande_detail.php?id=' . $id);
        exit;
    }
}

$titrePage = 'Modifier ma commande';
require_once __DIR__ . '/../includes/header.php';
?>

<h1 class="text-bordeaux">Modifier la commande <?= e($commande['numero']) ?></h1>

<?php foreach ($erreurs as $err) : ?>
    <div class="alert alert-danger" role="alert"><?= e($err) ?></div>
<?php endforeach; ?>

<form method="post" class="row g-3">
    <?= champCsrf() ?>
    <div class="col-md-6">
        <label for="menu_id" class="form-label">Menu (non modifiable)</label>
        <select id="menu_id" class="form-select" disabled>
            <option value="<?= $commande['menu_id'] ?>" data-prix="<?= $commande['prix'] ?>" data-min="<?= $commande['nb_personnes_min'] ?>" selected>
                <?= e($commande['titre']) ?>
            </option>
        </select>
    </div>
    <div class="col-md-3">
        <label for="nb_personnes" class="form-label">Nombre de personnes</label>
        <input type="number" id="nb_personnes" name="nb_personnes" class="form-control" value="<?= e($commande['nb_personnes']) ?>" min="<?= $commande['nb_personnes_min'] ?>" aria-describedby="aideMin">
        <small id="aideMin" class="text-muted"></small>
    </div>
    <div class="col-md-6">
        <label for="adresse" class="form-label">Adresse</label>
        <input type="text" id="adresse" name="adresse" class="form-control" value="<?= e($commande['adresse_prestation']) ?>" required>
    </div>
    <div class="col-md-3">
        <label for="ville" class="form-label">Ville</label>
        <input type="text" id="ville" name="ville" class="form-control" value="<?= e($commande['ville_prestation']) ?>" required>
    </div>
    <div class="col-md-3" id="blocDistance">
        <label for="distance_km" class="form-label">Distance depuis Bordeaux (km)</label>
        <input type="number" id="distance_km" name="distance_km" class="form-control" min="0" step="0.1" value="<?= e($commande['distance_km']) ?>">
    </div>
    <div class="col-md-3">
        <label for="date_prestation" class="form-label">Date</label>
        <input type="date" id="date_prestation" name="date_prestation" class="form-control" value="<?= e($commande['date_prestation']) ?>" required>
    </div>
    <div class="col-md-3">
        <label for="heure_livraison" class="form-label">Heure</label>
        <input type="time" id="heure_livraison" name="heure_livraison" class="form-control" value="<?= e(substr($commande['heure_livraison'], 0, 5)) ?>" required>
    </div>

    <div class="col-12 card p-3" aria-live="polite">
        <p class="mb-1">Prix du menu : <span id="prixMenu"></span></p>
        <p class="mb-1">Réduction : <span id="prixReduction"></span></p>
        <p class="mb-1">Livraison : <span id="prixLivraison"></span></p>
        <p class="h5 mb-0">Total : <span id="prixTotal"></span></p>
    </div>

    <div class="col-12">
        <button type="submit" class="btn btn-bordeaux">Enregistrer les modifications</button>
        <a href="/espace_utilisateur/commande_detail.php?id=<?= $id ?>" class="btn btn-outline-secondary">Annuler</a>
    </div>
</form>

<script src="/js/commande.js"></script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
