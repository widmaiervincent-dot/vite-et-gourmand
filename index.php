<?php
$titrePage = 'Accueil';
require_once 'includes/header.php';

// on récupère les avis validés uniquement
$requete = $bdd->query("SELECT a.note, a.commentaire, a.date_avis, u.prenom
                        FROM avis a
                        JOIN utilisateur u ON u.id = a.utilisateur_id
                        WHERE a.statut = 'valide'
                        ORDER BY a.date_avis DESC
                        LIMIT 6");
$avis = $requete->fetchAll();
?>

<section class="banniere mb-5">
    <h1>Vite &amp; Gourmand</h1>
    <p class="lead">Votre traiteur à Bordeaux depuis 25 ans</p>
    <a href="/menus.php" class="btn btn-dore btn-lg">Découvrir nos menus</a>
</section>

<section class="mb-5">
    <h2 class="text-bordeaux">Qui sommes-nous ?</h2>
    <p>
        Vite &amp; Gourmand, c'est avant tout l'histoire de Julie et José, deux passionnés de cuisine
        installés à Bordeaux depuis 25 ans. Nous préparons des repas pour tous vos évènements :
        un repas de Noël en famille, un déjeuner de Pâques, un anniversaire ou un séminaire d'entreprise.
    </p>
    <p>
        Nos menus changent régulièrement en fonction des saisons et des produits que nous trouvons
        chez nos producteurs locaux.
    </p>
</section>

<section class="mb-5">
    <h2 class="text-bordeaux">Une équipe de professionnels</h2>
    <div class="row">
        <div class="col-md-4 mb-3">
            <div class="card h-100 p-3">
                <h3 class="h5">25 ans d'expérience</h3>
                <p>Plus de 25 ans à régaler les Bordelais pour toutes les occasions.</p>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card h-100 p-3">
                <h3 class="h5">Produits frais et locaux</h3>
                <p>Nous travaillons avec les producteurs de la région et le marché des Capucins.</p>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card h-100 p-3">
                <h3 class="h5">Livraison soignée</h3>
                <p>Nous livrons à l'heure que vous choisissez, à Bordeaux et aux alentours.</p>
            </div>
        </div>
    </div>
</section>

<section>
    <h2 class="text-bordeaux">Ce que pensent nos clients</h2>
    <?php if (count($avis) == 0) : ?>
        <p>Pas encore d'avis pour le moment.</p>
    <?php else : ?>
        <div class="row">
            <?php foreach ($avis as $a) : ?>
                <div class="col-md-4 mb-3">
                    <div class="card h-100 p-3">
                        <p class="etoiles mb-1" aria-label="Note : <?= e($a['note']) ?> sur 5">
                            <?= str_repeat('★', $a['note']) . str_repeat('☆', 5 - $a['note']) ?>
                        </p>
                        <p>"<?= e($a['commentaire']) ?>"</p>
                        <p class="small text-muted mb-0"><?= e($a['prenom']) ?>, le <?= date('d/m/Y', strtotime($a['date_avis'])) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php require_once 'includes/footer.php'; ?>
