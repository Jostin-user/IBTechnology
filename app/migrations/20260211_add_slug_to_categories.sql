-- Ajouter colonne slug à la table categories
ALTER TABLE categories 
ADD COLUMN slug VARCHAR(255) UNIQUE NULL AFTER nom,
ADD INDEX idx_slug_categories (slug);

-- Générer les slugs pour les catégories existantes
UPDATE categories 
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
ALTER TABLE categories MODIFY slug VARCHAR(255) NOT NULL;
