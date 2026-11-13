<?php
$titrePage = 'Nos menus';
require_once 'includes/header.php';

// listes pour les filtres
$themes = $bdd->query('SELECT * FROM theme ORDER BY libelle')->fetchAll();
$regimes = $bdd->query('SELECT * FROM regime ORDER BY libelle')->fetchAll();
?>

<h1 class="text-bordeaux">Nos menus</h1>

<form id="formFiltres" class="card p-3 mb-4" aria-label="Filtrer les menus">
    <div class="row g-3">
        <div class="col-md-2">
            <label for="prix_max" class="form-label">Prix maximum (€)</label>
            <input type="number" min="0" class="form-control" id="prix_max" name="prix_max">
        </div>
        <div class="col-md-3">
            <span class="form-label d-block">Fourchette de prix (€)</span>
            <div class="input-group">
                <label for="fourchette_min" class="visually-hidden">Prix minimum</label>
                <input type="number" min="0" class="form-control" id="fourchette_min" name="fourchette_min" placeholder="min">
                <label for="fourchette_max" class="visually-hidden">Prix maximum de la fourchette</label>
                <input type="number" min="0" class="form-control" id="fourchette_max" name="fourchette_max" placeholder="max">
            </div>
        </div>
        <div class="col-md-2">
            <label for="theme" class="form-label">Thème</label>
            <select class="form-select" id="theme" name="theme">
                <option value="">Tous</option>
                <?php foreach ($themes as $t) : ?>
                    <option value="<?= $t['id'] ?>"><?= e($t['libelle']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label for="regime" class="form-label">Régime</label>
            <select class="form-select" id="regime" name="regime">
                <option value="">Tous</option>
                <?php foreach ($regimes as $r) : ?>
                    <option value="<?= $r['id'] ?>"><?= e($r['libelle']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label for="nb_personnes" class="form-label">Nb de personnes</label>
            <input type="number" min="1" class="form-control" id="nb_personnes" name="nb_personnes"
                   aria-describedby="aideNb">
            <small id="aideNb" class="text-muted">Menus possibles pour ce nombre</small>
        </div>
        <div class="col-12 text-end">
            <button type="reset" class="btn btn-outline-secondary">Effacer les filtres</button>
        </div>
    </div>
</form>

<!-- aria-live pour que les lecteurs d'écran annoncent le changement -->
<p id="nbResultats" aria-live="polite"></p>
<div id="listeMenus" class="row"></div>

<script src="/js/menus.js"></script>
<?php require_once 'includes/footer.php'; ?>
