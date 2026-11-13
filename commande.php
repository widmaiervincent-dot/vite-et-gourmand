<?php
require_once 'includes/fonctions.php';
require_once 'config/mongo.php';

// il faut être connecté pour commander
if (!estConnecte()) {
    setMessage('info', 'Connectez-vous ou créez un compte pour commander.');
    header('Location: /connexion.php?redirection=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

// infos du client pour pré-remplir
$requete = $bdd->prepare('SELECT * FROM utilisateur WHERE id = ?');
$requete->execute([$_SESSION['utilisateur']['id']]);
$client = $requete->fetch();

// menus disponibles
$menus = $bdd->query('SELECT id, titre, prix, nb_personnes_min, stock FROM menu WHERE stock > 0 ORDER BY titre')->fetchAll();

$menuChoisi = isset($_GET['menu']) ? (int)$_GET['menu'] : 0;
$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifierCsrf();

    $menuChoisi = (int)$_POST['menu_id'];
    $nbPersonnes = (int)$_POST['nb_personnes'];
    $adresse = trim($_POST['adresse']);
    $ville = trim($_POST['ville']);
    $distance = (float)($_POST['distance_km'] ?? 0);
    $date = $_POST['date_prestation'];
    $heure = $_POST['heure_livraison'];

    // on récupère le menu en base (on ne fait pas confiance au prix envoyé par le navigateur)
    $requete = $bdd->prepare('SELECT * FROM menu WHERE id = ?');
    $requete->execute([$menuChoisi]);
    $menu = $requete->fetch();

    if (!$menu) {
        $erreurs[] = 'Menu introuvable.';
    } else {
        if ($menu['stock'] <= 0) {
            $erreurs[] = 'Ce menu n\'est plus disponible.';
        }
        if ($nbPersonnes < $menu['nb_personnes_min']) {
            $erreurs[] = 'Ce menu doit être commandé pour ' . $menu['nb_personnes_min'] . ' personnes minimum.';
        }
    }
    if ($adresse == '' || $ville == '') {
        $erreurs[] = 'L\'adresse de la prestation est obligatoire.';
    }
    if ($date == '' || strtotime($date) < strtotime('tomorrow')) {
        $erreurs[] = 'La date de prestation doit être au plus tôt demain.';
    }
    if ($heure == '') {
        $erreurs[] = 'L\'heure de livraison est obligatoire.';
    }
    if (strtolower($ville) != 'bordeaux' && $distance <= 0) {
        $erreurs[] = 'Indiquez la distance depuis Bordeaux pour calculer la livraison.';
    }

    if (count($erreurs) == 0) {
        $prix = calculerPrix($menu, $nbPersonnes, $ville, $distance);
        $numero = 'CMD-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

        try {
            $bdd->beginTransaction();

            $requete = $bdd->prepare("INSERT INTO commande (numero, utilisateur_id, menu_id, nb_personnes, adresse_prestation, ville_prestation,
                                        distance_km, date_prestation, heure_livraison, prix_menu, reduction, prix_livraison, prix_total, statut)
                                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'en attente')");
            $requete->execute([
                $numero, $client['id'], $menu['id'], $nbPersonnes, $adresse, $ville,
                $distance, $date, $heure, $prix['prix_menu'], $prix['reduction'], $prix['livraison'], $prix['total']
            ]);
            $idCommande = $bdd->lastInsertId();

            $requete = $bdd->prepare("INSERT INTO commande_suivi (commande_id, statut) VALUES (?, 'en attente')");
            $requete->execute([$idCommande]);

            // on enlève 1 au stock du menu
            $requete = $bdd->prepare('UPDATE menu SET stock = stock - 1 WHERE id = ? AND stock > 0');
            $requete->execute([$menu['id']]);

            $bdd->commit();
        } catch (Exception $ex) {
            $bdd->rollBack();
            error_log($ex->getMessage());
            die('Erreur lors de l\'enregistrement de la commande.');
        }

        // statistiques dans MongoDB
        ajouterStatCommande($idCommande, $menu['id'], $menu['titre'], $nbPersonnes, $prix['total']);

        // mail de confirmation
        $contenu = '<h1>Merci pour votre commande !</h1>'
            . '<p>Commande n° ' . e($numero) . '</p>'
            . '<p>Menu : ' . e($menu['titre']) . ' pour ' . $nbPersonnes . ' personnes</p>'
            . '<p>Livraison le ' . date('d/m/Y', strtotime($date)) . ' à ' . e($heure) . ' : ' . e($adresse) . ', ' . e($ville) . '</p>'
            . '<p>Total : ' . formatPrix($prix['total']) . '</p>'
            . '<p>Vous pouvez suivre votre commande depuis votre espace.</p>';
        envoyerMail($client['email'], 'Confirmation de votre commande ' . $numero, $contenu);

        setMessage('success', 'Votre commande a bien été enregistrée. Un mail de confirmation vous a été envoyé.');
        header('Location: /espace_utilisateur/index.php');
        exit;
    }
}

$titrePage = 'Commander';
require_once 'includes/header.php';
?>

<h1 class="text-bordeaux">Commander un menu</h1>

<?php if (count($erreurs) > 0) : ?>
    <div class="alert alert-danger" role="alert">
        <ul class="mb-0">
            <?php foreach ($erreurs as $err) : ?>
                <li><?= e($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" id="formCommande">
    <?= champCsrf() ?>

    <fieldset class="card p-3 mb-3">
        <legend class="h5">1. Vos informations</legend>
        <div class="row g-3">
            <div class="col-md-4">
                <label for="nom" class="form-label">Nom</label>
                <input type="text" id="nom" class="form-control" value="<?= e($client['nom']) ?>" readonly>
            </div>
            <div class="col-md-4">
                <label for="prenom" class="form-label">Prénom</label>
                <input type="text" id="prenom" class="form-control" value="<?= e($client['prenom']) ?>" readonly>
            </div>
            <div class="col-md-4">
                <label for="email" class="form-label">Mail</label>
                <input type="email" id="email" class="form-control" value="<?= e($client['email']) ?>" readonly>
            </div>
            <div class="col-md-4">
                <label for="gsm" class="form-label">GSM</label>
                <input type="tel" id="gsm" class="form-control" value="<?= e($client['telephone']) ?>" readonly>
            </div>
        </div>
        <p class="small text-muted mt-2">Pour modifier ces informations, allez dans votre espace.</p>
    </fieldset>

    <fieldset class="card p-3 mb-3">
        <legend class="h5">2. La prestation</legend>
        <div class="row g-3">
            <div class="col-md-6">
                <label for="adresse" class="form-label">Adresse / lieu de la prestation *</label>
                <input type="text" id="adresse" name="adresse" class="form-control" required
                       value="<?= e($_POST['adresse'] ?? $client['adresse']) ?>">
            </div>
            <div class="col-md-3">
                <label for="ville" class="form-label">Ville *</label>
                <input type="text" id="ville" name="ville" class="form-control" required
                       value="<?= e($_POST['ville'] ?? $client['ville']) ?>">
            </div>
            <div class="col-md-3" id="blocDistance">
                <label for="distance_km" class="form-label">Distance depuis Bordeaux (km)</label>
                <input type="number" id="distance_km" name="distance_km" class="form-control" min="0" step="0.1"
                       value="<?= e($_POST['distance_km'] ?? '0') ?>" aria-describedby="aideDistance">
                <small id="aideDistance" class="text-muted">Uniquement hors Bordeaux</small>
            </div>
            <div class="col-md-3">
                <label for="date_prestation" class="form-label">Date *</label>
                <input type="date" id="date_prestation" name="date_prestation" class="form-control" required
                       min="<?= date('Y-m-d', strtotime('+1 day')) ?>" value="<?= e($_POST['date_prestation'] ?? '') ?>">
            </div>
            <div class="col-md-3">
                <label for="heure_livraison" class="form-label">Heure de livraison *</label>
                <input type="time" id="heure_livraison" name="heure_livraison" class="form-control" required
                       value="<?= e($_POST['heure_livraison'] ?? '') ?>">
            </div>
        </div>
    </fieldset>

    <fieldset class="card p-3 mb-3">
        <legend class="h5">3. Le menu</legend>
        <div class="row g-3">
            <div class="col-md-6">
                <label for="menu_id" class="form-label">Menu choisi *</label>
                <select id="menu_id" name="menu_id" class="form-select" required>
                    <option value="">-- Choisir un menu --</option>
                    <?php foreach ($menus as $m) : ?>
                        <option value="<?= $m['id'] ?>"
                                data-prix="<?= $m['prix'] ?>"
                                data-min="<?= $m['nb_personnes_min'] ?>"
                            <?= $m['id'] == $menuChoisi ? 'selected' : '' ?>>
                            <?= e($m['titre']) ?> (<?= $m['nb_personnes_min'] ?> pers. min)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="nb_personnes" class="form-label">Nombre de personnes *</label>
                <input type="number" id="nb_personnes" name="nb_personnes" class="form-control" required min="1"
                       value="<?= e($_POST['nb_personnes'] ?? '') ?>" aria-describedby="aideMin">
                <small id="aideMin" class="text-muted"></small>
            </div>
        </div>
    </fieldset>

    <section class="card p-3 mb-3" aria-labelledby="titrePrix">
        <h2 id="titrePrix" class="h5">Détail du prix</h2>
        <div aria-live="polite">
            <p class="mb-1">Prix du menu : <span id="prixMenu">0,00 €</span></p>
            <p class="mb-1">Réduction (-10%) : <span id="prixReduction">0,00 €</span></p>
            <p class="mb-1">Livraison : <span id="prixLivraison">0,00 €</span></p>
            <p class="h4">Total : <span id="prixTotal">0,00 €</span></p>
        </div>
        <p class="small text-muted mb-0">Réduction de 10% à partir de 5 personnes de plus que le minimum du menu. Livraison gratuite à Bordeaux, sinon 5 € + 0,59 €/km.</p>
    </section>

    <button type="submit" class="btn btn-bordeaux btn-lg">Valider la commande</button>
</form>

<script src="/js/commande.js"></script>
<?php require_once 'includes/footer.php'; ?>
