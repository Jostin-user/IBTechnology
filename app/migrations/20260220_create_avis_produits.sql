-- Migration: Create table avis_produits
-- Date: 2026-02-20

CREATE TABLE IF NOT EXISTS avis_produits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produit_id INT NOT NULL,
    utilisateur_id INT NOT NULL,
    note INT NOT NULL CHECK (note >= 1 AND note <= 5),
    titre VARCHAR(200) NOT NULL,
    commentaire TEXT,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_modification DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    statut ENUM('en_attente', 'approuve', 'rejete') DEFAULT 'en_attente',
    utile INT DEFAULT 0,
    FOREIGN KEY (produit_id) REFERENCES produits(id) ON DELETE CASCADE,
    FOREIGN KEY (utilisateur_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_avis (produit_id, utilisateur_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Ajouter des colonnes à la table produits pour les statistiques
ALTER TABLE produits ADD COLUMN IF NOT EXISTS note_moyenne DECIMAL(3,2) DEFAULT 0;
ALTER TABLE produits ADD COLUMN IF NOT EXISTS nombre_avis INT DEFAULT 0;
