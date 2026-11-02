-- Migration pour créer la table des zones de livraison
-- Date: 2026-01-26

CREATE TABLE zones_livraison (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    type ENUM('region', 'ville', 'quartier') NOT NULL,
    parent_id INT NULL,
    frais_livraison DECIMAL(10,2) DEFAULT 0,
    actif ENUM('0', '1') DEFAULT '1',
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES zones_livraison(id) ON DELETE SET NULL
);

-- Insérer quelques données de base pour les régions
INSERT INTO zones_livraison (nom, type, frais_livraison) VALUES
('Dakar', 'region', 1000),
('Thiès', 'region', 2000),
('Saint-Louis', 'region', 1000),
('Kaolack', 'region', 1000),
('Ziguinchor', 'region', 500),
('Diourbel', 'region', 2000),
('Fatick', 'region', 4000),
('Kolda', 'region', 200),
('Louga', 'region', 0),
('Matam', 'region', 550),
('Kaffrine', 'region', 1500),
('Kédougou', 'region', 0),
('Sédhiou', 'region', 0);