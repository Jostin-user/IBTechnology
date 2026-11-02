<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>IB Technology | Details produit </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/header.css?v=20261102-cart">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/details-produit.css">
    <style>
       
    </style>
</head>

<body>

    <?php require_once __DIR__ . '/../../layouts/header.php'; ?>

    <section class="product-details-container">

        <nav class="breadcrumb-container">
            <ol class="breadcrumb-list">
                <!-- Lien vers l'accueil toujours présent -->
                <li><a href="../">Accueil</a></li>
                <li><a href="../boutique">Boutique</a> </li>
                <?php
                $categorieId = $produit['categorie_id'];
                $list = $categorieModel->getCheminCategorie($categorieId, $db);

                foreach ($list as $index => $cat): ?>
                    <li>
                        <a href="<?= BASE_URL ?>boutique?categorie=<?php echo $cat['id']; ?>">
                            <?php echo htmlspecialchars($cat['nom']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
                <li><?= htmlspecialchars($produit['nom']) ?></li>
            </ol>
        </nav>

        <!-- Main Product Details -->
        <div class="product-details-main">
            <!-- Product Gallery -->
            <div class="product-gallery">
                <img src="<?= BASE_URL ?>uploads/<?= htmlspecialchars($produit['image']) ?>"
                    alt="<?= htmlspecialchars($produit['nom']) ?>" class="main-image" id="mainImage">

                <div class="thumbnail-grid">
                    <img src="<?= BASE_URL ?>uploads/<?= htmlspecialchars($produit['image']) ?>" alt="Vue principale"
                        class="thumbnail active" onclick="changeImage(this.src)">
                    <!-- Ici on pourrait ajouter d'autres images du produit si disponibles -->
                </div>
            </div>

            <!-- Product Information -->
            <div class="product-info">
                <h1><?= htmlspecialchars($produit['nom']) ?></h1>
                <span class="product-brand">Marque: <?= htmlspecialchars($produit['marque'] ?? 'N/A') ?></span>

                <!-- Rating -->
                <div class="product-rating">
                    <?php
                    $note_moyenne = $produit['note_moyenne'] ?? 0;
                    $nombre_avis = $produit['nombre_avis'] ?? 0;
                    
                    // Générer les étoiles
                    $stars = '';
                    for ($i = 0; $i < 5; $i++) {
                        $stars .= $i < round($note_moyenne) ? '★' : '☆';
                    }
                    ?>
                    <div class="stars"><?= $stars ?></div>
                    <span class="rating-count">(<?= number_format($note_moyenne, 1, ',', ' ') ?>/5 - <?= $nombre_avis ?> avis)</span>
                </div>

                <!-- Price -->
                <div class="product-price">
                    <span class="price-current"><?= number_format($produit['prix'], 0, ',', ' ') ?> FCFA</span>
                </div>

                <!-- Availability -->
                <div class="product-availability">
                    <?php if ($produit['stock'] > 0): ?>
                        <span class="availability-in-stock">
                            ✓ En stock (<?= $produit['stock'] ?> disponible<?= $produit['stock'] > 1 ? 's' : '' ?>)
                        </span>
                    <?php else: ?>
                        <span class="availability-out-of-stock">
                            ✗ Rupture de stock
                        </span>
                    <?php endif; ?>
                </div>

                <!-- Description -->
                <div class="product-description">
                    <h3 class="description-title">Description</h3>
                    <div class="description-content">
                        <?= $produit['description'] ?>
                    </div>
                </div>

                <!-- Caracterstique (pour plus tard) -->
                <!-- <div class="product-features">
                    <h3 class="features-title">Caractéristiques principales</h3>
                    <div class="features-list">
                        <div class="feature-item">Garantie 1 an</div>
                        <div class="feature-item">Livraison gratuite</div>
                        <div class="feature-item">Support technique</div>
                        <div class="feature-item">Retours sous 30 jours</div>
                    </div>
                </div> -->

                <!-- Quantity and Actions -->
                <?php if ($produit['stock'] > 0): ?>
                    <form id="addToCartForm" action="/panier/ajouter" method="POST">
                        <input type="hidden" name="id" value="<?= $produit['id'] ?>">

                        <div class="quantity-selector">
                            <label style="color: #ffffff; font-weight: 600;">Quantité:</label>
                            <div class="quantity-controls">
                                <button type="button" class="quantity-btn" onclick="updateQuantity(-1)">-</button>
                                <input type="number" name="quantite" value="1" min="1" max="<?= $produit['stock'] ?>"
                                    class="quantity-input" id="quantityInput">
                                <button type="button" class="quantity-btn" onclick="updateQuantity(1)">+</button>
                            </div>
                        </div>

                        <div class="product-actions">
                            <button type="submit" class="btn-add-to-cart">
                                🛒 Ajouter au panier
                            </button>
                            <!-- <button type="button" class="btn-wishlist" onclick="addToWishlist()">
                                ♥ Ajouter aux favoris
                            </button> -->
                        </div>
                    </form>
                <?php else: ?>
                    <div class="product-actions">
                        <button type="button" class="btn-add-to-cart" disabled>
                            Rupture de stock
                        </button>
                        <button type="button" class="btn-wishlist" onclick="addToWishlist()">
                            ♥ Ajouter aux favoris
                        </button>
                    </div>
                <?php endif; ?>

                <!-- Social Share -->
                <div class="social-share">
                    <div class="share-title">Partager ce produit:</div>
                    <div class="share-buttons">
                        <button class="share-btn share-facebook" onclick="shareOnFacebook()">
                            <i>f</i>
                        </button>
                        <button class="share-btn share-twitter" onclick="shareOnTwitter()">
                            <i>t</i>
                        </button>
                        <button class="share-btn share-whatsapp" onclick="shareOnWhatsApp()">
                            <i>w</i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        <?php if (!empty($produitsSimilaires)): ?>
            <section class="related-products">
                <h2 class="related-title">Produits similaires</h2>
                <div class="related-grid">
                    <?php foreach ($produitsSimilaires as $item): ?>
                        <div class="related-card">
                            <img src="<?= BASE_URL ?>uploads/<?= htmlspecialchars($item['image']) ?>"
                                alt="<?= htmlspecialchars($item['nom']) ?>" class="related-image">
                            <div class="related-info">
                                <h3 class="related-name"><?= htmlspecialchars($item['nom']) ?></h3>
                                <div class="related-price">
                                    <?= number_format($item['prix'], 0, ',', ' ') ?> FCFA
                                </div>
                                <a href="<?= $item['id'] ?>" class="related-btn">Voir le produit</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </section>

    <script>

        function updateQuantity(change) {
            const input = document.getElementById('quantityInput');
            const currentValue = parseInt(input.value);
            const maxValue = parseInt(input.max);
            const newValue = currentValue + change;

            if (newValue >= 1 && newValue <= maxValue) {
                input.value = newValue;
            }
        }

        function addToWishlist() {
            // Implémenter la logique d'ajout aux favoris
            alert('Fonctionnalité à implémenter: Ajouter aux favoris');
        }

        function shareOnFacebook() {
            const url = window.location.href;
            window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`, '_blank');
        }

        function shareOnTwitter() {
            const url = window.location.href;
            const text = "Découvrez ce produit incroyable: " + document.title;
            window.open(`https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${encodeURIComponent(text)}`, '_blank');
        }

        function shareOnWhatsApp() {
            const url = window.location.href;
            const text = "Découvrez ce produit: " + document.title + " " + url;
            window.open(`https://wa.me/?text=${encodeURIComponent(text)}`, '_blank');
        }

        // Gestionnaire pour le formulaire d'ajout au panier
        document.getElementById('addToCartForm')?.addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch('<?= BASE_URL ?>panier/ajouter', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Mettre à jour le compteur du panier
                        updateCartCount(data.total);
                        alert(data.message || 'Produit ajouté au panier !');
                    } else {
                        alert(data.message || 'Erreur lors de l\'ajout au panier');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Erreur lors de l\'ajout au panier');
                });
        });

        function updateCartCount(count) {
            const cartBadge = document.querySelector('.badge');
            if (cartBadge) {
                cartBadge.textContent = count;
            }
        }
    </script>

    <?php
    // Inclure la section avis
    require_once __DIR__ . '/../../partials/avis-section.php';
    ?>

    <?php require_once __DIR__ . '/../../layouts/footer.php'; ?>

</body>

</html>