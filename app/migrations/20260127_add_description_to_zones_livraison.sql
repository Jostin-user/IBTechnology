-- Migration pour ajouter la colonne description à la table zones_livraison
-- Date: 2026-01-27

ALTER TABLE zones_livraison ADD COLUMN description TEXT NULL AFTER frais_livraison;