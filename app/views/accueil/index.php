<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IB Technology | Boutique Tech</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/header.css?v=20261102-cart">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/footer.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/accueil.css?v=8">
</head>

<body>

    <?php require_once __DIR__ . '/../../layouts/header.php'; ?>

    <section class="hero">
        <div class="hero-content">
            <span class="hero-kicker">Nouvelle sélection tech</span>
            <h1>La technologie fiable,<br><span>livrée chez vous.</span></h1>
            <p>Téléphones, ordinateurs, accessoires et gadgets de qualité, au meilleur rapport qualité-prix.</p>
            <div class="hero-buttons">
                <a href="<?= BASE_URL ?>boutique" class="btn primary">Découvrir la boutique <span>→</span></a>
                <a href="<?= BASE_URL ?>panier" class="btn secondary">Voir mon panier</a>
            </div>
            <div class="hero-trust" aria-label="Avantages IB Technology">
                <span><b>✓</b> Produits vérifiés</span>
                <span><b>✓</b> Paiement sécurisé</span>
                <span><b>✓</b> Livraison rapide</span>
            </div>
        </div>
        <div class="hero-visual" aria-hidden="true">
            <div class="hero-glow"></div>
            <img src="<?= BASE_URL ?>uploads/product-13.jpg" alt="Ordinateur portable Lenovo">
            <div class="hero-badge"><small>À partir de</small><strong>95 000 FCFA</strong></div>
        </div>
    </section>

    <section class="categories">
        <div class="section-heading">
            <div><span>Explorer</span><h2>Nos catégories</h2></div>
            <a href="<?= BASE_URL ?>boutique">Voir tout</a>
        </div>
        <div class="category-grid">
            <a class="category-card" href="<?= BASE_URL ?>boutique?categorie=3"><span>📱</span><strong>Téléphones</strong><small>Voir les modèles →</small></a>
            <a class="category-card" href="<?= BASE_URL ?>boutique?categorie=4"><span>💻</span><strong>Ordinateurs</strong><small>Voir les modèles →</small></a>
            <a class="category-card" href="<?= BASE_URL ?>boutique?categorie=1"><span>🔌</span><strong>Chargeurs</strong><small>Voir les accessoires →</small></a>
            <a class="category-card" href="<?= BASE_URL ?>boutique?categorie=2"><span>🎧</span><strong>Audio</strong><small>Voir les écouteurs →</small></a>
        </div>
    </section>

    <section class="produits">
        <div class="section-heading">
            <div><span>Best sellers</span><h2>Produits populaires</h2></div>
            <a href="<?= BASE_URL ?>boutique">Voir la boutique</a>
        </div>
        <div class="grid popular-grid">
            <?php foreach ($produitsPopulaires as $p): ?>
                <article class="product-card popular-card">
                    <a class="product-image" href="<?= BASE_URL ?>produits/<?= $p['id'] ?>">
                        <img src="<?= BASE_URL ?>uploads/<?= htmlspecialchars($p['image']) ?>"
                            alt="<?= htmlspecialchars($p['nom']) ?>">
                    </a>
                    <div class="product-info">
                        <span class="product-brand"><?= htmlspecialchars($p['marque'] ?? '') ?></span>
                        <h3><?= htmlspecialchars($p['nom']) ?></h3>
                        <?php $prixPromo = isset($p['prix_promo']) ? $p['prix_promo'] : null; ?>
                        <p class="price"><?= number_format($prixPromo ?: $p['prix'], 0, ',', ' ') ?> FCFA</p>
                        <?php if (!empty($prixPromo)): ?><p class="old-price"><?= number_format($p['prix'], 0, ',', ' ') ?> FCFA</p><?php endif; ?>
                        <a class="btn-voirProduit" href="<?= BASE_URL ?>produits/<?= $p['id'] ?>">Voir le produit <span>→</span></a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
    <div class="section-heading categories-title"><div><span>À découvrir</span><h2>Catégories populaires</h2></div></div>
    <?php foreach ($categoriePopulaire as $cat1): ?>
        <?php if (!empty($cat1['id'])):
            $cat = $produitmodel->getProduitsParCategorie($cat1['id'], 4) ?>
            <section class="home-category">
                <div class="category-header">
                    <h2><?= htmlspecialchars($cat1['nom']) ?></h2>
                    <a href="boutique?categorie=<?= htmlspecialchars($cat1['id']) ?>" class="btn-more">
                        Voir plus →
                    </a>
                </div>

                <div class="products-grid">
                    <?php foreach ($cat as $p): ?>
                        <article class="product-card">
                            <a href="<?= BASE_URL ?>produits/<?= $p['id'] ?>"><img
                                    src="<?= BASE_URL ?>uploads/<?= htmlspecialchars($p['image']) ?>" alt=""> </a>
                            <h3><?= htmlspecialchars($p['nom']) ?></h3>
                            <p class="price">
                                <?= number_format($p['prix'], 0, ',', ' ') ?> FCFA
                            </p>
                            <button class="btn-ajoutPanier" data-id="<?= htmlspecialchars($p['id']) ?>">
                                <span class="btn-text">Ajouter au panier</span>
                                <span class="btn-icon" style="display:none;">🛒</span>
                                <span class="btn-spinner" style="display:none;"></span>
                            </button>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>

        <?php endif; ?>
    <?php endforeach; ?>

    <?php include __DIR__ . '/../../layouts/footer.php'; ?>

</body>

</html>