<?php
/**
 * Script helper pour afficher les slugs dans les différentes vues
 * 
 * Ce fichier fournit des fonctions utilitaires pour faciliter
 * l'intégration des slugs dans les templates existants
 */

require_once __DIR__ . '/SlugHelper.php';

/**
 * Génère un lien SEO-friendly vers un produit
 * 
 * @param array $produit Données du produit
 * @param string $type 'slug' ou 'id' (par défaut 'slug')
 * @return string URL du produit
 */
function getProduitUrl($produit, $type = 'slug')
{
    if ($type === 'slug' && !empty($produit['slug'])) {
        return '/produits/slug/' . htmlspecialchars($produit['slug']);
    }
    return '/produits/' . (int) $produit['id'];
}

/**
 * Génère un lien SEO-friendly vers une catégorie
 * 
 * @param array $categorie Données de la catégorie
 * @param string $type 'slug' ou 'id' (par défaut 'slug')
 * @return string URL de la catégorie
 */
function getCategorieUrl($categorie, $type = 'slug')
{
    if ($type === 'slug' && !empty($categorie['slug'])) {
        return '/categories/' . htmlspecialchars($categorie['slug']);
    }
    return '/boutique?categorie=' . (int) $categorie['id'];
}

/**
 * Affiche un card de produit avec slug
 * 
 * @param array $produit Données du produit
 * @return void
 */
function afficherCardProduit($produit)
{
    $url = getProduitUrl($produit);
    $image = htmlspecialchars($produit['image']);
    $nom = htmlspecialchars($produit['nom']);
    $prix = number_format($produit['prix'], 0, ',', ' ');
    $description = htmlspecialchars(substr($produit['description'], 0, 100)) . '...';
    $stockClass = $produit['stock'] <= 0 ? 'opacity-50' : '';
    $badgeStock = $produit['stock'] <= 0 ? '<span class="badge bg-danger ms-2">Rupture</span>' : '';

    echo <<<HTML
    <div class="col-md-4 mb-4">
        <div class="card h-100 $stockClass">
            <img src="<?=BASE_URL?>uploads/$image" class="card-img-top" alt="$nom">
            <div class="card-body">
                <h5 class="card-title">$nom</h5>
                <p class="card-text text-muted">$description</p>
                <p class="card-text">
                    <strong class="text-primary">$prix FCFA</strong>
                    $badgeStock
                </p>
            </div>
            <div class="card-footer bg-white">
                <a href="$url" class="btn btn-sm btn-primary w-100">Voir les détails</a>
            </div>
        </div>
    </div>
    HTML;
}

/**
 * Affiche un lien de navigation vers une catégorie
 * 
 * @param array $categorie Données de la catégorie
 * @param bool $isActive Marquer comme actif
 * @return void
 */
function afficherLienCategorie($categorie, $isActive = false)
{
    $url = getCategorieUrl($categorie);
    $nom = htmlspecialchars($categorie['nom']);
    $activeClass = $isActive ? 'active' : '';

    echo <<<HTML
    <li class="nav-item">
        <a class="nav-link $activeClass" href="$url">$nom</a>
    </li>
    HTML;
}

/**
 * Générateur de sitemap XML pour SEO
 * À utiliser pour générer un fichier sitemap.xml
 * 
 * @param array $produits Liste des produits avec slug
 * @param array $categories Liste des catégories avec slug
 * @param string $baseUrl URL de base du site
 * @return string XML du sitemap
 */
function genererSitemap($produits, $categories, $baseUrl = 'https://ibtechnologie.com')
{
    $sitemap = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
    $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

    // Ajouter les catégories
    foreach ($categories as $cat) {
        $url = $baseUrl . '/categories/' . htmlspecialchars($cat['slug']);
        $sitemap .= '  <url>' . PHP_EOL;
        $sitemap .= '    <loc>' . htmlspecialchars($url) . '</loc>' . PHP_EOL;
        $sitemap .= '    <changefreq>weekly</changefreq>' . PHP_EOL;
        $sitemap .= '    <priority>0.8</priority>' . PHP_EOL;
        $sitemap .= '  </url>' . PHP_EOL;
    }

    // Ajouter les produits
    foreach ($produits as $prod) {
        $url = $baseUrl . '/produits/slug/' . htmlspecialchars($prod['slug']);
        $sitemap .= '  <url>' . PHP_EOL;
        $sitemap .= '    <loc>' . htmlspecialchars($url) . '</loc>' . PHP_EOL;
        $sitemap .= '    <lastmod>' . date('Y-m-d') . '</lastmod>' . PHP_EOL;
        $sitemap .= '    <changefreq>monthly</changefreq>' . PHP_EOL;
        $sitemap .= '    <priority>0.9</priority>' . PHP_EOL;
        $sitemap .= '  </url>' . PHP_EOL;
    }

    $sitemap .= '</urlset>';

    return $sitemap;
}

/**
 * Génère les données structurées JSON-LD pour les produits
 * Améliore l'affichage dans les résultats de recherche Google
 * 
 * @param array $produit Données du produit
 * @param string $baseUrl URL de base du site
 * @return string JSON-LD
 */
function genererJsonLdProduit($produit, $baseUrl = 'https://ibtechnologie.com')
{
    $data = [
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $produit['nom'],
        'description' => $produit['description'],
        'image' => $baseUrl . '<?=BASE_URL?>uploads/' . $produit['image'],
        'url' => $baseUrl . '/produits/slug/' . $produit['slug'],
        'offers' => [
            '@type' => 'Offer',
            'price' => $produit['prix'],
            'priceCurrency' => 'XOF',
            'availability' => $produit['stock'] > 0 ? 'InStock' : 'OutOfStock'
        ]
    ];

    if (!empty($produit['marque'])) {
        $data['brand'] = [
            '@type' => 'Brand',
            'name' => $produit['marque']
        ];
    }

    return '<script type="application/ld+json">' . json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>';
}
