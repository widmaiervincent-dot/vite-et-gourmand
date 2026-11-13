-- ============================================
-- Jeu de données de test pour Vite & Gourmand
-- A lancer après creation_bdd.sql
-- ============================================
USE vite_gourmand;

-- Comptes de test
-- admin    : jose@vite-gourmand.fr     / Admin@Jose2026
-- employé  : julie@vite-gourmand.fr    / Employe@Julie26
-- client   : client.test@mail.fr       / Client@Test2026
-- le compte admin est créé ici car il ne doit pas pouvoir être créé depuis l'appli
INSERT INTO utilisateur (nom, prenom, email, telephone, adresse, code_postal, ville, mot_de_passe, role) VALUES
('Garcia', 'José', 'jose@vite-gourmand.fr', '0601020304', '12 rue Sainte-Catherine', '33000', 'Bordeaux', '$2y$10$CLOECmPkZ9TGJ1c8j7F7uOPRxm1HgIwxcMf5157jDUldRDvzEUaNm', 'admin'),
('Martin', 'Julie', 'julie@vite-gourmand.fr', '0605060708', '12 rue Sainte-Catherine', '33000', 'Bordeaux', '$2y$10$7.7SM67o7nfmjKuCSx8UQu97rnDM08/He4Ww4whyeMbkUH6ChObmK', 'employe'),
('Dupont', 'Paul', 'client.test@mail.fr', '0611223344', '5 cours de l''Intendance', '33000', 'Bordeaux', '$2y$10$NNWGDoBzC3BXU1jPCVmt0eB1W2lAs51/1Z000d7dtOtavmzl3136K', 'utilisateur');

INSERT INTO theme (libelle) VALUES ('Noël'), ('Pâques'), ('Classique'), ('Évènement');

INSERT INTO regime (libelle) VALUES ('Classique'), ('Végétarien'), ('Vegan'), ('Sans gluten');

INSERT INTO allergene (libelle) VALUES
('Gluten'), ('Lait'), ('Oeufs'), ('Fruits à coque'), ('Poisson'), ('Crustacés'), ('Céleri'), ('Moutarde'), ('Soja'), ('Sésame');

INSERT INTO plat (nom, type, description) VALUES
('Foie gras maison et chutney de figues', 'entree', 'Foie gras mi-cuit fait maison, pain d''épices'),
('Velouté de potimarron', 'entree', 'Velouté onctueux, éclats de noisettes'),
('Salade de chèvre chaud', 'entree', 'Toasts de chèvre, miel et salade verte'),
('Houmous et légumes croquants', 'entree', 'Pois chiches, tahini et crudités de saison'),
('Dinde farcie aux marrons', 'plat', 'Dinde rôtie, farce aux marrons, pommes grenaille'),
('Gigot d''agneau de 7 heures', 'plat', 'Agneau confit, flageolets'),
('Entrecôte bordelaise', 'plat', 'Sauce au vin rouge et échalotes, frites maison'),
('Risotto aux champignons', 'plat', 'Riz arborio, cèpes et parmesan'),
('Curry de légumes au lait de coco', 'plat', 'Légumes de saison, riz basmati'),
('Bûche chocolat praliné', 'dessert', 'Bûche maison chocolat noir et praliné'),
('Nid de Pâques au chocolat', 'dessert', 'Gâteau chocolat et petits oeufs'),
('Cannelés bordelais', 'dessert', 'Les vrais cannelés de Bordeaux'),
('Salade de fruits frais', 'dessert', 'Fruits de saison, menthe fraîche');

INSERT INTO plat_allergene (plat_id, allergene_id) VALUES
(1, 1), (2, 2), (2, 4), (3, 1), (3, 2), (4, 10),
(5, 1), (5, 4), (7, 2), (8, 2), (10, 1), (10, 2), (10, 3), (10, 4),
(11, 1), (11, 2), (11, 3), (12, 1), (12, 2), (12, 3);

INSERT INTO menu (titre, description, theme_id, regime_id, nb_personnes_min, prix, conditions, stock) VALUES
('Menu Réveillon', 'Un menu de fête pour réunir toute la famille autour des grands classiques de Noël.', 1, 1, 6, 270.00, 'A commander au minimum 2 semaines avant la prestation. Le foie gras doit être conservé au réfrigérateur (entre 0 et 4°C).', 10),
('Menu Pâques Gourmand', 'Agneau de 7 heures et chocolat, le menu idéal pour le week-end de Pâques.', 2, 1, 4, 160.00, 'A commander au minimum 10 jours avant la prestation.', 8),
('Menu Bordelais', 'Le meilleur de notre région : entrecôte bordelaise et cannelés.', 3, 1, 2, 70.00, 'A commander au minimum 3 jours avant. Conserver au frais.', 20),
('Menu Végétarien du Marché', 'Des produits frais du marché des Capucins, sans viande ni poisson.', 3, 2, 2, 56.00, 'A commander au minimum 3 jours avant.', 15),
('Menu Vegan Évènement', 'Pour vos évènements (anniversaires, séminaires), un menu 100% végétal.', 4, 3, 10, 350.00, 'A commander au minimum 3 semaines avant. Matériel de service prêté (à restituer).', 5);

INSERT INTO menu_plat (menu_id, plat_id) VALUES
(1, 1), (1, 5), (1, 10),
(2, 3), (2, 6), (2, 11),
(3, 1), (3, 7), (3, 12),
(4, 2), (4, 3), (4, 8), (4, 12),
(5, 4), (5, 9), (5, 13);

INSERT INTO menu_image (menu_id, chemin, texte_alt) VALUES
(1, 'images/menus/noel.jpg', 'Table de Noël avec dinde farcie'),
(2, 'images/menus/paques.jpg', 'Gigot d''agneau et décoration de Pâques'),
(3, 'images/menus/bordelais.jpg', 'Entrecôte bordelaise et cannelés'),
(4, 'images/menus/vegetarien.jpg', 'Risotto aux champignons'),
(5, 'images/menus/vegan.jpg', 'Buffet de légumes et curry');

INSERT INTO horaire (jour, heure_ouverture, heure_fermeture, ferme) VALUES
('Lundi', NULL, NULL, 1),
('Mardi', '09:00', '18:00', 0),
('Mercredi', '09:00', '18:00', 0),
('Jeudi', '09:00', '18:00', 0),
('Vendredi', '09:00', '19:00', 0),
('Samedi', '10:00', '19:00', 0),
('Dimanche', '10:00', '13:00', 0);

-- une commande terminée avec un avis validé pour avoir quelque chose sur l'accueil
INSERT INTO commande (numero, utilisateur_id, menu_id, nb_personnes, adresse_prestation, ville_prestation, distance_km, date_prestation, heure_livraison, prix_menu, reduction, prix_livraison, prix_total, statut) VALUES
('CMD-20261001-001', 3, 3, 2, '5 cours de l''Intendance', 'Bordeaux', 0, '2026-10-10', '12:00', 70.00, 0, 0, 70.00, 'terminée');

INSERT INTO commande_suivi (commande_id, statut, date_modification) VALUES
(1, 'en attente', '2026-10-01 10:00:00'),
(1, 'accepté', '2026-10-01 14:00:00'),
(1, 'en préparation', '2026-10-10 08:00:00'),
(1, 'en cours de livraison', '2026-10-10 11:15:00'),
(1, 'livré', '2026-10-10 11:50:00'),
(1, 'terminée', '2026-10-10 18:00:00');

INSERT INTO avis (commande_id, utilisateur_id, note, commentaire, statut) VALUES
(1, 3, 5, 'Super entrecôte et les cannelés étaient parfaits, je recommande !', 'valide');
