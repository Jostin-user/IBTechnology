-- Migration pour ajouter les statuts manquants à la table commandes
-- Date: 2026-02-05

-- Ajouter les colonnes manquantes si elles n'existent pas
ALTER TABLE commandes 
MODIFY statut ENUM('en_attente','validee','en_preparation','en_livraison','expediee','livree','annulee') DEFAULT 'en_attente';
