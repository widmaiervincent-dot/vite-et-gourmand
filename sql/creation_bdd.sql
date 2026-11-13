-- ============================================
-- Projet ECF : Vite & Gourmand
-- Script de création de la base de données
-- SGBD : MySQL / MariaDB
-- ============================================

DROP DATABASE IF EXISTS vite_gourmand;
CREATE DATABASE vite_gourmand CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE vite_gourmand;

-- table des utilisateurs (clients, employés et admin)
CREATE TABLE utilisateur (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    telephone VARCHAR(20),
    adresse VARCHAR(255),
    code_postal VARCHAR(10),
    ville VARCHAR(100),
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('utilisateur', 'employe', 'admin') NOT NULL DEFAULT 'utilisateur',
    actif TINYINT(1) NOT NULL DEFAULT 1,
    token_reset VARCHAR(100) NULL,
    token_expiration DATETIME NULL,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE theme (
    id INT AUTO_INCREMENT PRIMARY KEY,
    libelle VARCHAR(50) NOT NULL
);

CREATE TABLE regime (
    id INT AUTO_INCREMENT PRIMARY KEY,
    libelle VARCHAR(50) NOT NULL
);

CREATE TABLE menu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(100) NOT NULL,
    description TEXT,
    theme_id INT NOT NULL,
    regime_id INT NOT NULL,
    nb_personnes_min INT NOT NULL,
    prix DECIMAL(8,2) NOT NULL, -- prix pour le nombre de personnes minimum
    conditions TEXT,
    stock INT NOT NULL DEFAULT 0,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (theme_id) REFERENCES theme(id),
    FOREIGN KEY (regime_id) REFERENCES regime(id)
);

-- galerie d'images d'un menu
CREATE TABLE menu_image (
    id INT AUTO_INCREMENT PRIMARY KEY,
    menu_id INT NOT NULL,
    chemin VARCHAR(255) NOT NULL,
    texte_alt VARCHAR(255),
    FOREIGN KEY (menu_id) REFERENCES menu(id) ON DELETE CASCADE
);

CREATE TABLE plat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    type ENUM('entree', 'plat', 'dessert') NOT NULL,
    description TEXT
);

CREATE TABLE allergene (
    id INT AUTO_INCREMENT PRIMARY KEY,
    libelle VARCHAR(50) NOT NULL
);

-- un plat peut avoir plusieurs allergènes
CREATE TABLE plat_allergene (
    plat_id INT NOT NULL,
    allergene_id INT NOT NULL,
    PRIMARY KEY (plat_id, allergene_id),
    FOREIGN KEY (plat_id) REFERENCES plat(id) ON DELETE CASCADE,
    FOREIGN KEY (allergene_id) REFERENCES allergene(id) ON DELETE CASCADE
);

-- un plat peut être dans plusieurs menus
CREATE TABLE menu_plat (
    menu_id INT NOT NULL,
    plat_id INT NOT NULL,
    PRIMARY KEY (menu_id, plat_id),
    FOREIGN KEY (menu_id) REFERENCES menu(id) ON DELETE CASCADE,
    FOREIGN KEY (plat_id) REFERENCES plat(id) ON DELETE CASCADE
);

CREATE TABLE horaire (
    id INT AUTO_INCREMENT PRIMARY KEY,
    jour VARCHAR(20) NOT NULL,
    heure_ouverture TIME NULL,
    heure_fermeture TIME NULL,
    ferme TINYINT(1) NOT NULL DEFAULT 0
);

CREATE TABLE commande (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero VARCHAR(30) NOT NULL UNIQUE,
    utilisateur_id INT NOT NULL,
    menu_id INT NOT NULL,
    nb_personnes INT NOT NULL,
    adresse_prestation VARCHAR(255) NOT NULL,
    ville_prestation VARCHAR(100) NOT NULL,
    distance_km DECIMAL(6,2) DEFAULT 0,
    date_prestation DATE NOT NULL,
    heure_livraison TIME NOT NULL,
    prix_menu DECIMAL(8,2) NOT NULL,
    reduction DECIMAL(8,2) NOT NULL DEFAULT 0,
    prix_livraison DECIMAL(8,2) NOT NULL DEFAULT 0,
    prix_total DECIMAL(8,2) NOT NULL,
    pret_materiel TINYINT(1) NOT NULL DEFAULT 0,
    statut VARCHAR(50) NOT NULL DEFAULT 'en attente',
    motif_annulation TEXT NULL,
    mode_contact VARCHAR(20) NULL,
    date_commande DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(id),
    FOREIGN KEY (menu_id) REFERENCES menu(id)
);

-- historique des statuts d'une commande (pour le suivi)
CREATE TABLE commande_suivi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    commande_id INT NOT NULL,
    statut VARCHAR(50) NOT NULL,
    date_modification DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (commande_id) REFERENCES commande(id) ON DELETE CASCADE
);

CREATE TABLE avis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    commande_id INT NOT NULL UNIQUE,
    utilisateur_id INT NOT NULL,
    note INT NOT NULL CHECK (note BETWEEN 1 AND 5),
    commentaire TEXT,
    statut ENUM('en attente', 'valide', 'refuse') NOT NULL DEFAULT 'en attente',
    date_avis DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (commande_id) REFERENCES commande(id) ON DELETE CASCADE,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(id)
);
