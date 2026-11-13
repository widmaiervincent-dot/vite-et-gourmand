<?php
require_once __DIR__ . '/../includes/fonctions.php';
require_once __DIR__ . '/../config/mongo.php';
verifierRole(['employe', 'admin']);

$id = (int)($_GET['id'] ?? 0);

$requete = $bdd->prepare("SELECT c.*, m.titre, u.nom, u.prenom, u.email, u.telephone
                          FROM commande c
                          JOIN menu m ON m.id = c.menu_id
                          JOIN utilisateur u ON u.id = c.utilisateur_id
                          WHERE c.id = ?");
$requete->execute([$id]);
$commande = $requete->fetch();

if (!$commande) {
    setMessage('danger', 'Commande introuvable.');
    header('Location: /espace_employe/index.php');
    exit;
}

$erreur = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifierCsrf();

    // une commande annulée ou terminée ne peut plus être modifiée
    // (sinon une double annulation remettrait 2 fois le stock)
    if ($commande['statut'] == 'annulée' || $commande['statut'] == 'terminée') {
        setMessage('danger', 'Cette commande est ' . $commande['statut'] . ', elle ne peut plus être modifiée.');
        header('Location: /espace_employe/commande_gerer.php?id=' . $id);
        exit;
    }

    // ---- changement de statut ----
    if ($_POST['action'] == 'statut') {
        $nouveauStatut = $_POST['statut'];
        $pretMateriel = isset($_POST['pret_materiel']) ? 1 : 0;

        if (!in_array($nouveauStatut, listeStatuts()) || $nouveauStatut == 'annulée') {
            $erreur = 'Statut invalide.';
        } else {
            $bdd->prepare('UPDATE commande SET statut = ?, pret_materiel = ? WHERE id = ?')->execute([$nouveauStatut, $pretMateriel, $id]);
            $bdd->prepare('INSERT INTO commande_suivi (commande_id, statut) VALUES (?, ?)')->execute([$id, $nouveauStatut]);
            majStatCommande($id, ['statut' => $nouveauStatut]);

            // mails automatiques selon le statut
            if ($nouveauStatut == 'en attente du retour de matériel') {
                $contenu = '<p>Bonjour ' . e($commande['prenom']) . ',</p>'
                    . '<p>Du matériel vous a été prêté pour votre commande ' . e($commande['numero']) . '.</p>'
                    . '<p>Si le matériel n\'est pas restitué sous <strong>10 jours ouvrés</strong>, des frais de <strong>600 €</strong> '
                    . 'vous seront facturés (voir nos conditions générales de vente).</p>'
                    . '<p>Pour rendre le matériel, merci de prendre contact avec nous.</p>';
                envoyerMail($commande['email'], 'Restitution du matériel - commande ' . $commande['numero'], $contenu);
            }
            if ($nouveauStatut == 'terminée') {
                $contenu = '<p>Bonjour ' . e($commande['prenom']) . ',</p>'
                    . '<p>Votre commande ' . e($commande['numero']) . ' est terminée. Merci de votre confiance !</p>'
                    . '<p>Vous pouvez vous connecter à <a href="' . URL_SITE . '/connexion.php">votre compte</a> pour donner votre avis.</p>';
                envoyerMail($commande['email'], 'Donnez-nous votre avis !', $contenu);
            }

            setMessage('success', 'Statut mis à jour.');
            header('Location: /espace_employe/commande_gerer.php?id=' . $id);
            exit;
        }
    }

    // ---- annulation : il faut avoir contacté le client ----
    if ($_POST['action'] == 'annuler') {
        $modeContact = $_POST['mode_contact'] ?? '';
        $motif = trim($_POST['motif'] ?? '');

        if (!in_array($modeContact, ['GSM', 'mail']) || $motif == '') {
            $erreur = 'Vous devez indiquer le mode de contact et le motif de l\'annulation.';
        } else {
            $bdd->prepare("UPDATE commande SET statut = 'annulée', motif_annulation = ?, mode_contact = ? WHERE id = ?")->execute([$motif, $modeContact, $id]);
            $bdd->prepare("INSERT INTO commande_suivi (commande_id, statut) VALUES (?, 'annulée')")->execute([$id]);
            $bdd->prepare('UPDATE menu SET stock = stock + 1 WHERE id = ?')->execute([$commande['menu_id']]);
            majStatCommande($id, ['statut' => 'annulée']);
            setMessage('success', 'Commande annulée.');
            header('Location: /espace_employe/commande_gerer.php?id=' . $id);
            exit;
        }
    }
}

$requete = $bdd->prepare('SELECT * FROM commande_suivi WHERE commande_id = ? ORDER BY date_modification');
$requete->execute([$id]);
$suivi = $requete->fetchAll();

$titrePage = 'Gérer la commande';
require_once __DIR__ . '/../includes/header.php';
?>

<h1 class="text-bordeaux">Commande <?= e($commande['numero']) ?></h1>

<?php if ($erreur) : ?>
    <div class="alert alert-danger" role="alert"><?= e($erreur) ?></div>
<?php endif; ?>

<div class="row">
    <div class="col-md-6">
        <div class="card p-3 mb-3">
            <h2 class="h5">Client</h2>
            <p class="mb-0"><?= e($commande['prenom'] . ' ' . $commande['nom']) ?><br>
                GSM : <a href="tel:<?= e($commande['telephone']) ?>"><?= e($commande['telephone']) ?></a><br>
                Mail : <a href="mailto:<?= e($commande['email']) ?>"><?= e($commande['email']) ?></a></p>
        </div>
        <div class="card p-3 mb-3">
            <h2 class="h5">Commande</h2>
            <ul class="list-unstyled mb-0">
                <li>Menu : <?= e($commande['titre']) ?> - <?= e($commande['nb_personnes']) ?> personnes</li>
                <li>Le <?= date('d/m/Y', strtotime($commande['date_prestation'])) ?> à <?= e(substr($commande['heure_livraison'], 0, 5)) ?></li>
                <li>Adresse : <?= e($commande['adresse_prestation'] . ', ' . $commande['ville_prestation']) ?></li>
                <li>Total : <?= formatPrix($commande['prix_total']) ?></li>
                <li>Statut : <strong><?= e($commande['statut']) ?></strong></li>
                <?php if ($commande['motif_annulation']) : ?>
                    <li>Annulation (<?= e($commande['mode_contact']) ?>) : <?= e($commande['motif_annulation']) ?></li>
                <?php endif; ?>
            </ul>
        </div>
        <div class="card p-3 mb-3">
            <h2 class="h5">Historique</h2>
            <ol class="mb-0">
                <?php foreach ($suivi as $s) : ?>
                    <li><?= e($s['statut']) ?> - <?= date('d/m/Y H:i', strtotime($s['date_modification'])) ?></li>
                <?php endforeach; ?>
            </ol>
        </div>
    </div>

    <div class="col-md-6">
        <?php if ($commande['statut'] != 'annulée' && $commande['statut'] != 'terminée') : ?>
            <form method="post" class="card p-3 mb-3">
                <?= champCsrf() ?>
                <input type="hidden" name="action" value="statut">
                <h2 class="h5">Changer le statut</h2>
                <label for="statut" class="form-label">Nouveau statut</label>
                <select id="statut" name="statut" class="form-select mb-2">
                    <?php foreach (listeStatuts() as $s) : ?>
                        <?php if ($s != 'annulée') : ?>
                            <option value="<?= e($s) ?>" <?= $commande['statut'] == $s ? 'selected' : '' ?>><?= e($s) ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                <div class="form-check mb-2">
                    <input type="checkbox" class="form-check-input" id="pret_materiel" name="pret_materiel" <?= $commande['pret_materiel'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="pret_materiel">Du matériel a été prêté au client</label>
                </div>
                <button type="submit" class="btn btn-bordeaux">Mettre à jour</button>
            </form>

            <form method="post" class="card p-3 border-danger">
                <?= champCsrf() ?>
                <input type="hidden" name="action" value="annuler">
                <h2 class="h5">Annuler la commande</h2>
                <p class="small">Vous devez d'abord avoir contacté le client.</p>
                <fieldset class="mb-2">
                    <legend class="form-label fs-6">Mode de contact utilisé</legend>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="mode_contact" id="contactGsm" value="GSM" required>
                        <label class="form-check-label" for="contactGsm">Appel GSM</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="mode_contact" id="contactMail" value="mail">
                        <label class="form-check-label" for="contactMail">Mail</label>
                    </div>
                </fieldset>
                <label for="motif" class="form-label">Motif</label>
                <textarea id="motif" name="motif" class="form-control mb-2" rows="3" required></textarea>
                <button type="submit" class="btn btn-danger">Annuler la commande</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<p class="mt-3"><a href="/espace_employe/index.php">&larr; Retour aux commandes</a></p>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
