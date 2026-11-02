-- Migration: ajouter parent_id pour gestion des sous-catégories
-- Exécuter manuellement via MySQL (phpMyAdmin ou mysql client)

ALTER TABLE categories
  ADD COLUMN parent_id INT NULL AFTER description;

-- Ajouter une contrainte FK pour garder l'intégrité (optionnel)
ALTER TABLE categories
  ADD CONSTRAINT fk_categories_parent
    FOREIGN KEY (parent_id) REFERENCES categories(id)
    ON DELETE SET NULL
    ON UPDATE CASCADE;

-- Exemple pour remplir des parent_id (à adapter selon vos données)
-- UPDATE categories SET parent_id = NULL WHERE parent_condition_here;
