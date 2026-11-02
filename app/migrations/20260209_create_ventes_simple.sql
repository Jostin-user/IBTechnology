-- Migration simpifiée pour les ventes
-- Juste le nécessaire : historique des commandes livrées

ALTER TABLE commandes ADD COLUMN IF NOT EXISTS date_livraison DATETIME;
ALTER TABLE commandes ADD COLUMN IF NOT EXISTS date_vente DATETIME;

-- Table pour l'historique simplifié des ventes
CREATE TABLE IF NOT EXISTS ventes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    commande_id INT NOT NULL UNIQUE,
    utilisateur_id INT NOT NULL,
    montant_total DECIMAL(10,2) NOT NULL,
    frais_livraison DECIMAL(10,2) DEFAULT 0,
    nombre_articles INT NOT NULL,
    statut ENUM('en_attente', 'confirmee', 'livree', 'annulee') DEFAULT 'en_attente',
    date_vente DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_livraison DATETIME,
    notes TEXT,
    FOREIGN KEY (commande_id) REFERENCES commandes(id) ON DELETE CASCADE,
    FOREIGN KEY (utilisateur_id) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_date_vente (date_vente),
    INDEX idx_statut (statut),
    INDEX idx_utilisateur (utilisateur_id),
    INDEX idx_montant (montant_total)
);

-- Résumé quotidien des ventes (pour les rapports)
CREATE TABLE IF NOT EXISTS ventes_synthese (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date_jour DATE NOT NULL UNIQUE,
    nombre_ventes INT DEFAULT 0,
    nombre_articles_vendus INT DEFAULT 0,
    montant_total DECIMAL(12,2) DEFAULT 0,
    montant_moyen_panier DECIMAL(10,2) DEFAULT 0,
    nombre_clients INT DEFAULT 0,
    date_mise_a_jour DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_date (date_jour)
);

-- Table des retours/réclamations (simple)
CREATE TABLE IF NOT EXISTS ventes_retours (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vente_id INT NOT NULL,
    produit_id INT NOT NULL,
    quantite INT NOT NULL,
    raison VARCHAR(255),
    statut ENUM('demande', 'approuve', 'refuse', 'traite') DEFAULT 'demande',
    date_demande DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_traitement DATETIME,
    notes TEXT,
    FOREIGN KEY (vente_id) REFERENCES ventes(id) ON DELETE CASCADE,
    FOREIGN KEY (produit_id) REFERENCES produits(id) ON DELETE RESTRICT,
    INDEX idx_vente (vente_id),
    INDEX idx_statut (statut)
);
