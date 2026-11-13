<?php
// renvoie la liste des menus en JSON selon les filtres (appelé en JS avec fetch)
require_once '../config/bdd.php';
header('Content-Type: application/json; charset=utf-8');

$sql = "SELECT m.id, m.titre, m.description, m.nb_personnes_min, m.prix, m.stock,
               t.libelle AS theme, r.libelle AS regime,
               (SELECT chemin FROM menu_image WHERE menu_id = m.id LIMIT 1) AS image,
               (SELECT texte_alt FROM menu_image WHERE menu_id = m.id LIMIT 1) AS image_alt
        FROM menu m
        JOIN theme t ON t.id = m.theme_id
        JOIN regime r ON r.id = m.regime_id
        WHERE 1 = 1";
$params = [];

// on ajoute les conditions seulement si le filtre est rempli
if (!empty($_GET['prix_max'])) {
    $sql .= " AND m.prix <= :prix_max";
    $params['prix_max'] = (float)$_GET['prix_max'];
}
if (!empty($_GET['fourchette_min'])) {
    $sql .= " AND m.prix >= :fmin";
    $params['fmin'] = (float)$_GET['fourchette_min'];
}
if (!empty($_GET['fourchette_max'])) {
    $sql .= " AND m.prix <= :fmax";
    $params['fmax'] = (float)$_GET['fourchette_max'];
}
if (!empty($_GET['theme'])) {
    $sql .= " AND m.theme_id = :theme";
    $params['theme'] = (int)$_GET['theme'];
}
if (!empty($_GET['regime'])) {
    $sql .= " AND m.regime_id = :regime";
    $params['regime'] = (int)$_GET['regime'];
}
if (!empty($_GET['nb_personnes'])) {
    // les menus dont le minimum est inférieur ou égal au nombre demandé
    $sql .= " AND m.nb_personnes_min <= :nb";
    $params['nb'] = (int)$_GET['nb_personnes'];
}

$sql .= " ORDER BY m.titre";

$requete = $bdd->prepare($sql);
$requete->execute($params);
echo json_encode($requete->fetchAll());
