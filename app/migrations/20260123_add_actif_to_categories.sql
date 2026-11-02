-- Migration: Ajouter le champ actif à la table categories
-- Date: 2026-01-23
-- Description: Permet de désactiver les catégories au lieu de les supprimer

ALTER TABLE categories
ADD COLUMN actif ENUM('0', '1') NOT NULL DEFAULT '1' AFTER parent_id;