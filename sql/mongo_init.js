// Données de départ pour MongoDB (statistiques des commandes)
// A lancer avec : mongosh "mongodb://localhost:27017/vite_gourmand" sql/mongo_init.js

db = db.getSiblingDB('vite_gourmand');
db.statistiques_commandes.drop();

db.statistiques_commandes.insertMany([
    {
        commande_id: 1,
        menu_id: 3,
        menu_titre: 'Menu Bordelais',
        nb_personnes: 2,
        prix_total: 70.0,
        statut: 'terminée',
        date_commande: new Date('2026-10-01T10:00:00Z')
    }
]);

db.statistiques_commandes.createIndex({ commande_id: 1 }, { unique: true });
print('Collection statistiques_commandes prête');
