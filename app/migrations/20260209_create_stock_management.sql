-- Table des catégories de stock
CREATE TABLE IF NOT EXISTS stock_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    description TEXT,
    actif TINYINT(1) DEFAULT 1,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Table des mouvements de stock
CREATE TABLE IF NOT EXISTS stock_mouvements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produit_id INT NOT NULL,
    type ENUM('entree', 'sortie', 'retour', 'ajustement', 'perte') NOT NULL,
    quantite INT NOT NULL,
    quantite_precedente INT,
    quantite_nouvelle INT,
    raison VARCHAR(255),
    notes TEXT,
    admin_id INT,
    date_mouvement DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (produit_id) REFERENCES produits(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_produit (produit_id),
    INDEX idx_type (type),
    INDEX idx_date (date_mouvement)
);

-- Table des alertes de stock
CREATE TABLE IF NOT EXISTS stock_alertes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produit_id INT NOT NULL,
    seuil_minimum INT NOT NULL DEFAULT 10,
    seuil_maximum INT NOT NULL DEFAULT 1000,
    stock_critique TINYINT(1) DEFAULT 0,
    etat ENUM('normal', 'faible', 'critique', 'rupture') DEFAULT 'normal',
    date_derniere_alerte DATETIME,
    notifiee TINYINT(1) DEFAULT 0,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_mise_a_jour DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_produit (produit_id),
    FOREIGN KEY (produit_id) REFERENCES produits(id) ON DELETE CASCADE
);

-- Table des ajustements de stock
CREATE TABLE IF NOT EXISTS stock_ajustements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produit_id INT NOT NULL,
    ancien_stock INT NOT NULL,
    nouveau_stock INT NOT NULL,
    raison ENUM('inventaire', 'correction', 'perte', 'casse', 'autre') NOT NULL,
    description TEXT,
    admin_id INT NOT NULL,
    date_ajustement DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (produit_id) REFERENCES produits(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_produit (produit_id),
    INDEX idx_date (date_ajustement)
);

-- Ajouter les colonnes manquantes à la table produits si elles n'existent pas
ALTER TABLE produits ADD COLUMN IF NOT EXISTS prix_promo DECIMAL(10,2);
ALTER TABLE produits ADD COLUMN IF NOT EXISTS actif TINYINT(1) DEFAULT 1;
ALTER TABLE produits ADD COLUMN IF NOT EXISTS stock_reserve INT DEFAULT 0;
ALTER TABLE produits ADD COLUMN IF NOT EXISTS date_derniere_vente DATETIME;

-- Index de performance
ALTER TABLE produits ADD INDEX IF NOT EXISTS idx_stock (stock);
ALTER TABLE produits ADD INDEX IF NOT EXISTS idx_actif (actif);
ALTER TABLE produits ADD INDEX IF NOT EXISTS idx_categorie_stock (categorie_id, stock);
