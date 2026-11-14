-- Mise à jour d'une base déjà installée : ajoute la table des sessions sans toucher aux autres données
-- (pour une nouvelle installation, creation_bdd.sql la crée déjà)
USE vite_gourmand;

CREATE TABLE IF NOT EXISTS session_php (
    id VARCHAR(128) PRIMARY KEY,
    donnees TEXT NOT NULL,
    date_maj INT NOT NULL
);
