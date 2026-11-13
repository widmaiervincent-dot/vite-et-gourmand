<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

// connexion à MongoDB (base non relationnelle utilisée pour les statistiques)
// retourne la collection ou null si mongo n'est pas dispo
function getCollectionStats()
{
    try {
        $client = new MongoDB\Client(MONGO_URI);
        return $client->selectDatabase(MONGO_DB)->selectCollection('statistiques_commandes');
    } catch (Exception $e) {
        error_log('Mongo : ' . $e->getMessage());
        return null;
    }
}

// quand une commande est créée on ajoute un document dans mongo
function ajouterStatCommande($commandeId, $menuId, $menuTitre, $nbPersonnes, $prixTotal)
{
    $collection = getCollectionStats();
    if ($collection == null) {
        return;
    }
    try {
        $collection->insertOne([
            'commande_id' => (int)$commandeId,
            'menu_id' => (int)$menuId,
            'menu_titre' => $menuTitre,
            'nb_personnes' => (int)$nbPersonnes,
            'prix_total' => (float)$prixTotal,
            'statut' => 'en attente',
            'date_commande' => new MongoDB\BSON\UTCDateTime(),
        ]);
    } catch (Exception $e) {
        error_log('Mongo : ' . $e->getMessage());
    }
}

// mise à jour du document (changement de statut, de prix...)
function majStatCommande($commandeId, $champs)
{
    $collection = getCollectionStats();
    if ($collection == null) {
        return;
    }
    try {
        $collection->updateOne(['commande_id' => (int)$commandeId], ['$set' => $champs]);
    } catch (Exception $e) {
        error_log('Mongo : ' . $e->getMessage());
    }
}
