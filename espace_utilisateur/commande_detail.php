<?php
require_once __DIR__ . '/../includes/fonctions.php';
require_once __DIR__ . '/../config/mongo.php';
verifierRole(['utilisateur']);

$id = (int)($_GET['id'] ?? 0);
$idUtilisateur = $_SESSION['utilisateur']['id'];

// on vérifie que la commande appartient bien à l'utilisateur connecté
$requete = $bdd->prepare("SELECT c.*, m.titre FROM commande c JOIN menu m ON m.id = c.menu_id
                          WHERE c.id = ? AND c.utilisateur_id = ?");
$requete->execute([$id, $idUtilisateur]);
$commande = $requete->fetch();

if (!$commande) {
    setMessage('danger', 'Commande introuvable.');
    header('Location: /espace_utilisateur/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifierCsrf();

    // annulation (seulement si pas encore acceptée)
    if ($_POST['action'] == 'annuler' && $commande['statut'] == 'en attente') {
        $bdd->prepare("UPDATE commande SET statut = 'annulée', motif_annulation = 'Annulée par le client' WHERE id = ?")->execute([$id]);
        $bdd->prepare("INSERT INTO commande_suivi (commande_id, statut) VALUES (?, 'annulée')")->execute([$id]);
        $bdd->prepare('UPDATE menu SET stock = stock + 1 WHERE id = ?')->execute([$commande['menu_id']]);
        majStatCommande($id, ['statut' => 'annulée']);
        setMessage('success', 'Votre commande a été annulée.');
    }

    // avis (seulement si terminée)
    if ($_POST['action'] == 'avis' && $commande['statut'] == 'terminée') {
        $note = (int)$_POST['note'];
        $commentaire = trim($_POST['commentaire']);
        if ($note < 1 || $note > 5) {
            setMessage('danger', 'La note doit être entre 1 et 5.');
        } else {
            // INSERT IGNORE car il ne peut y avoir qu'un avis par commande
            $requete = $bdd->prepare('INSERT IGNORE INTO avis (commande_id, utilisateur_id, note, commentaire) VALUES (?, ?, ?, ?)');
            $requete->execute([$id, $idUtilisateur, $note, $commentaire]);
            setMessage('success', 'Merci pour votre avis ! Il sera visible après validation par notre équipe.');
        }
    }

    header('Location: /espace_utilisateur/commande_detail.php?id=' . $id);
    exit;
}

// historique du suivi
$requete = $bdd->prepare('SELECT * FROM commande_suivi WHERE commande_id = ? ORDER BY date_modification');
$requete->execute([$id]);
$suivi = $requete->fetchAll();

// avis déjà donné ?
$requete = $bdd->prepare('SELECT * FROM avis WHERE commande_id = ?');
$requete->execute([$id]);
$avis = $requete->fetch();

$titrePage = 'Commande ' . $commande['numero'];
require_once __DIR__ . '/../includes/header.php';
?>

<h1 class="text-bordeaux">Commande <?= e($commande['numero']) ?></h1>
<p>Statut actuel : <span class="badge badge-statut"><?= e($commande['statut']) ?></span></p>

<div class="row">
    <div class="col-md-6">
        <div class="card p-3 mb-3">
            <h2 class="h5">Détail</h2>
            <ul class="list-unstyled mb-0">
                <li>Menu : <?= e($commande['titre']) ?></li>
                <li>Nombre de personnes : <?= e($commande['nb_personnes']) ?></li>
                <li>Date : <?= date('d/m/Y', strtotime($commande['date_prestation'])) ?> à <?= e(substr($commande['heure_livraison'], 0, 5)) ?></li>
                <li>Adresse : <?= e($commande['adresse_prestation']) ?>, <?= e($commande['ville_prestation']) ?></li>
                <li>Prix du menu : <?= formatPrix($commande['prix_menu']) ?></li>
                <li>Réduction : - <?= formatPrix($commande['reduction']) ?></li>
                <li>Livraison : <?= formatPrix($commande['prix_livraison']) ?></li>
                <li><strong>Total : <?= formatPrix($commande['prix_total']) ?></strong></li>
            </ul>
        </div>

        <?php if ($commande['statut'] == 'en attente') : ?>
            <a href="/espace_utilisateur/modifier_commande.php?id=<?= $id ?>" class="btn btn-outline-secondary mb-2">Modifier la commande</a>
            <form method="post" onsubmit="return confirm('Voulez-vous vraiment annuler cette commande ?');">
                <?= champCsrf() ?>
                <input type="hidden" name="action" value="annuler">
                <button type="submit" class="btn btn-danger">Annuler la commande</button>
            </form>
        <?php endif; ?>
    </div>

    <div class="col-md-6">
        <?php if ($commande['statut'] != 'en attente') : ?>
            <div class="card p-3 mb-3">
                <h2 class="h5">Suivi de la commande</h2>
                <ol>
                    <?php foreach ($suivi as $s) : ?>
                        <li><?= e($s['statut']) ?> - le <?= date('d/m/Y à H:i', strtotime($s['date_modification'])) ?></li>
                    <?php endforeach; ?>
                </ol>
                <?php if ($commande['motif_annulation']) : ?>
                    <p>Motif d'annulation : <?= e($commande['motif_annulation']) ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($commande['statut'] == 'terminée') : ?>
            <div class="card p-3">
                <h2 class="h5">Votre avis</h2>
                <?php if ($avis) : ?>
                    <p class="etoiles"><?= str_repeat('★', $avis['note']) . str_repeat('☆', 5 - $avis['note']) ?></p>
                    <p><?= e($avis['commentaire']) ?></p>
                    <p class="small text-muted">Statut : <?= e($avis['statut']) ?></p>
                <?php else : ?>
                    <form method="post">
                        <?= champCsrf() ?>
                        <input type="hidden" name="action" value="avis">
                        <div class="mb-3">
                            <label for="note" class="form-label">Note (1 à 5)</label>
                            <select id="note" name="note" class="form-select" required>
                                <option value="5">5 - Excellent</option>
                                <option value="4">4 - Très bien</option>
                                <option value="3">3 - Bien</option>
                                <option value="2">2 - Moyen</option>
                                <option value="1">1 - Décevant</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="commentaire" class="form-label">Commentaire</label>
                            <textarea id="commentaire" name="commentaire" class="form-control" rows="4" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-bordeaux">Envoyer mon avis</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<p class="mt-3"><a href="/espace_utilisateur/index.php">&larr; Retour à mes commandes</a></p>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
