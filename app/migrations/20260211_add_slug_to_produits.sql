-- Ajouter colonne slug à la table produits
ALTER TABLE produits 
ADD COLUMN slug VARCHAR(255) UNIQUE NULL AFTER nom,
ADD INDEX idx_slug_produits (slug);

-- Générer les slugs pour les produits existants
UPDATE produits 
SET slug = LOWER(CONCAT(
    REPLACE(
        REPLACE(
            REPLACE(
                REPLACE(TRIM(nom), ' ', '-'),
                'é', 'e'
            ),
            'è', 'e'
        ),
        'ê', 'e'
    ),
    '-',
    REPLACE(id, ' ', '')
))
WHERE slug IS NULL;

-- Rendre la colonne slug non-nullable après peuplement
ALTER TABLE produits MODIFY slug VARCHAR(255) NOT NULL;
