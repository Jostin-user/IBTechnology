<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>IB Technology | Boutique de produits</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/header.css?v=20261102-cart">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/boutique.css?v=20261102-search-fixed">
    <style>
        .breadcrumb-list {
            list-style: none;
            display: flex;
            gap: 10px;
            padding: 10px 0;
        }

        .breadcrumb-list li+li::before {
            content: ">";
            /* Le séparateur */
            margin-right: 10px;
            color: #888;
        }

        .breadcrumb-list a {
            text-decoration: none;
            color: #007bff;
        }

        .breadcrumb-list li.active {
            color: #666;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <?php require_once __DIR__ . "/../../layouts/header.php"; ?>
    <?php
    require_once __DIR__ . '/../../controllers/ProduitController.php';

    ?>

    <div class="boutique-container">

        <!-- Titre -->
        <section class="boutique-header">
            <h1>Notre Boutique</h1>
            <p>Découvrez notre sélection de produits de qualité</p>
            <nav class="breadcrumb-container">
                <ol class="breadcrumb-list">
                    <!-- Lien vers l'accueil toujours présent -->
                    <li><a href="./">Accueil</a></li>

                    <?php
                    $list = $categorieModel->getCheminCategorie($categorieId, $db);
                    $total = count($list) ?? 0;
                    if ($total < 1) {
                        echo "<li class='active'>boutique</li>";
                    }
                    foreach ($list as $index => $cat):
                        // Si c'est le dernier élément, on ne met pas de lien (c'est la page actuelle)
                        if ($index === $total - 1): ?>
                            <li class="active"><?php echo htmlspecialchars($cat['nom']); ?></li>
                        <?php else: ?>
                            <li>
                                <a href="?categorie=<?php echo $cat['id']; ?>">
                                    <?php echo htmlspecialchars($cat['nom']); ?>
                                </a>
                            </li>
                        <?php endif;
                    endforeach; ?>
                </ol>
            </nav>
        </section>

        <!-- Barre de recherche et tri -->
        <div class="filters-header">
            <div class="search-bar">
                <form method="GET" action="">
                    <input type="hidden" name="categorie" value="<?= htmlspecialchars($_GET['categorie'] ?? '') ?>">
                    <input type="hidden" name="marque" value="<?= htmlspecialchars($_GET['marque'] ?? '') ?>">
                    <input type="hidden" name="min" value="<?= htmlspecialchars($_GET['min'] ?? '') ?>">
                    <input type="hidden" name="max" value="<?= htmlspecialchars($_GET['max'] ?? '') ?>">
                    <input type="hidden" name="tri" value="<?= htmlspecialchars($_GET['tri'] ?? '') ?>">
                    <input type="text" name="q" placeholder="Rechercher un produit..."
                        value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                    <button type="submit">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.35-4.35"></path>
                        </svg>
                    </button>
                </form>
            </div>

            <div class="sort-select">
                <form method="GET" action="">
                    <input type="hidden" name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                    <input type="hidden" name="categorie" value="<?= htmlspecialchars($_GET['categorie'] ?? '') ?>">
                    <input type="hidden" name="marque" value="<?= htmlspecialchars($_GET['marque'] ?? '') ?>">
                    <input type="hidden" name="min" value="<?= htmlspecialchars($_GET['min'] ?? '') ?>">
                    <input type="hidden" name="max" value="<?= htmlspecialchars($_GET['max'] ?? '') ?>">
                    <select name="tri" onchange="this.form.submit()">
                        <option value="">Trier par</option>
                        <option value="prix_asc" <?= ($_GET['tri'] ?? '') === 'prix_asc' ? 'selected' : '' ?>>Prix
                            croissant</option>
                        <option value="prix_desc" <?= ($_GET['tri'] ?? '') === 'prix_desc' ? 'selected' : '' ?>>Prix
                            décroissant</option>
                        <option value="nom_asc" <?= ($_GET['tri'] ?? '') === 'nom_asc' ? 'selected' : '' ?>>Nom A-Z
                        </option>
                        <option value="nom_desc" <?= ($_GET['tri'] ?? '') === 'nom_desc' ? 'selected' : '' ?>>Nom Z-A
                        </option>
                        <option value="nouveau" <?= ($_GET['tri'] ?? '') === 'nouveau' ? 'selected' : '' ?>>Nouveautés
                        </option>
                    </select>
                </form>
            </div>
        </div>

        <div class="boutique-content">

            <!-- Bouton filtres mobile -->
            <button class="filters-toggle" id="filters-toggle">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="21" y1="4" x2="14" y2="4"></line>
                    <line x1="10" y1="4" x2="3" y2="4"></line>
                    <line x1="21" y1="12" x2="12" y2="12"></line>
                    <line x1="8" y1="12" x2="3" y2="12"></line>
                    <line x1="21" y1="20" x2="16" y2="20"></line>
                    <line x1="12" y1="20" x2="3" y2="20"></line>
                    <line x1="14" y1="2" x2="14" y2="6"></line>
                    <line x1="8" y1="10" x2="8" y2="14"></line>
                    <line x1="16" y1="18" x2="16" y2="22"></line>
                </svg>
                Filtres
            </button>

            <!-- Filtres -->
            <aside class="boutique-filtres" id="boutique-filtres">
                <form method="GET" action="" id="filters-form">
                    <input type="hidden" name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                    <input type="hidden" name="tri" value="<?= htmlspecialchars($_GET['tri'] ?? '') ?>">

                    <!-- Catégories -->
                    <div class="filter-section">
                        <h3>Catégories</h3>
                        <select name="categorie" onchange="this.form.submit()">
                            <option value="">Toutes les catégories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= ($_GET['categorie'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                    <?= str_repeat('— ', $cat['level'] ?? 0) . htmlspecialchars($cat['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Marques -->
                    <div class="filter-section">
                        <h3>Marques</h3>
                        <select name="marque" onchange="this.form.submit()">
                            <option value="">Toutes les marques</option>
                            <?php foreach ($marques as $marque): ?>
                                <option value="<?= htmlspecialchars($marque['marque']) ?>" <?= ($_GET['marque'] ?? '') === $marque['marque'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($marque['marque']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Prix -->
                    <div class="filter-section">
                        <h3>Prix (FCFA)</h3>
                        <div class="price-filter">
                            <div class="price-inputs">
                                <input type="number" name="min" placeholder="Min"
                                    value="<?= htmlspecialchars($_GET['min'] ?? '') ?>" min="0">
                                <input type="number" name="max" placeholder="Max"
                                    value="<?= htmlspecialchars($_GET['max'] ?? '') ?>" min="0">
                            </div>
                            <button type="submit">Appliquer les filtres</button>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="filter-section">
                        <a href="<?= BASE_URL ?>boutique" class="clear-filters">Effacer tous les
                            filtres</a>
                    </div>

                </form>
            </aside>




            <!-- Produits -->
            <section class="boutique-produits">
                <?php if (empty($produits)): ?>
                    <div class="no-results">
                        <h3>Aucun produit trouvé</h3>
                        <p>Essayez de modifier vos critères de recherche ou <a href="<?=BASE_URL?>boutique"
                                style="color: #0f62fe;">effacez les filtres</a>.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($produits as $p): ?>
                        <div class="product-card">
                            <a href="<?= BASE_URL ?>produits/<?= $p['id'] ?>">
                                <img src="<?= BASE_URL ?>uploads/<?= htmlspecialchars($p['image']) ?>"
                                    alt="<?= htmlspecialchars($p['nom']) ?>" loading="lazy">
                            </a>

                            <div class="product-info">
                                <h4>
                                    <a href="<?= BASE_URL ?>produits/<?= $p['id'] ?>"
                                        style="color: inherit; text-decoration: none;">
                                        <?= htmlspecialchars($p['nom']) ?>
                                    </a>
                                </h4>

                                <div class="product-category">
                                    <?= htmlspecialchars($p['categorie']) ?>
                                </div>

                                <div class="product-price">
                                    <?= number_format($p['prix'], 0, ',', ' ') ?> FCFA
                                </div>

                                <?php if ($p['stock'] > 0): ?>
                                    <button class="btn-ajoutPanier" data-id="<?= $p['id'] ?>">
                                        <span class="btn-text">Ajouter au panier</span>
                                        <span class="btn-icon" style="display:none;">🛒</span>
                                        <span class="btn-spinner"></span>
                                    </button>
                                <?php else: ?>
                                    <span class="rupture">Rupture de stock</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>

        </div>

    </div>
    <?php if ($totalPages > 1): ?>

        <nav class="pagination">

            <!-- Bouton précédent -->
            <?php if ($pageActuelle > 1): ?>
                <a href="?p=<?= $pageActuelle - 1 ?>">«</a>
            <?php endif; ?>

            <!-- Première page -->
            <a href="?p=1" class="<?= $pageActuelle == 1 ? 'active' : '' ?>">
                1
            </a>

            <!-- Points de suspension avant -->
            <?php if ($pageActuelle > 4): ?>
                <span class="dots">…</span>
            <?php endif; ?>

            <!-- Fenêtre centrale -->
            <?php
            $start = max(2, $pageActuelle - 2);
            $end = min($totalPages - 1, $pageActuelle + 2);

            for ($i = $start; $i <= $end; $i++):
                ?>
                <a href="?p=<?= $i ?>" class="<?= $i == $pageActuelle ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <!-- Points de suspension après -->
            <?php if ($pageActuelle < $totalPages - 3): ?>
                <span class="dots">…</span>
            <?php endif; ?>

            <!-- Dernière page -->
            <?php if ($totalPages > 1): ?>
                <a href="?p=<?= $totalPages ?>" class="<?= $pageActuelle == $totalPages ? 'active' : '' ?>">
                    <?= $totalPages ?>
                </a>
            <?php endif; ?>

            <!-- Bouton suivant -->
            <?php if ($pageActuelle < $totalPages): ?>
                <a href="?p=<?= $pageActuelle + 1 ?>">»</a>
            <?php endif; ?>

        </nav>


    <?php endif; ?>


    <script>
        // Gestion de l'ajout au panier
        document.addEventListener('DOMContentLoaded', function () {
            const addToCartButtons = document.querySelectorAll('.btn-ajoutPanier');

            // Toggle filtres mobile
            const filtersToggle = document.getElementById('filters-toggle');
            const boutiqueFiltres = document.getElementById('boutique-filtres');

            if (filtersToggle && boutiqueFiltres) {
                filtersToggle.addEventListener('click', function () {
                    boutiqueFiltres.classList.toggle('open');
                    const isOpen = boutiqueFiltres.classList.contains('open');
                    filtersToggle.innerHTML = isOpen ?
                        '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg> Fermer les filtres' :
                        '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="21" y1="4" x2="14" y2="4"></line><line x1="10" y1="4" x2="3" y2="4"></line><line x1="21" y1="12" x2="12" y2="12"></line><line x1="8" y1="12" x2="3" y2="12"></line><line x1="21" y1="20" x2="16" y2="20"></line><line x1="12" y1="20" x2="3" y2="20"></line><line x1="14" y1="2" x2="14" y2="6"></line><line x1="8" y1="10" x2="8" y2="14"></line><line x1="16" y1="18" x2="16" y2="22"></line></svg> Filtres';
                });
            }

            // Auto-submit des filtres radio (optionnel - pour une expérience plus fluide)
            const filterRadios = document.querySelectorAll('#filters-form input[type="radio"]');
            filterRadios.forEach(radio => {
                radio.addEventListener('change', function () {
                    // Délai court pour permettre à l'utilisateur de changer d'avis
                    setTimeout(() => {
                        document.getElementById('filters-form').submit();
                    }, 300);
                });
            });

            // Validation des prix
            const minPriceInput = document.querySelector('input[name="min"]');
            const maxPriceInput = document.querySelector('input[name="max"]');

            function validatePrices() {
                const minVal = parseInt(minPriceInput.value) || 0;
                const maxVal = parseInt(maxPriceInput.value) || 0;

                if (maxVal > 0 && minVal > maxVal) {
                    maxPriceInput.setCustomValidity('Le prix maximum doit être supérieur au prix minimum');
                } else {
                    maxPriceInput.setCustomValidity('');
                    minPriceInput.setCustomValidity('');
                }
            }

            if (minPriceInput && maxPriceInput) {
                minPriceInput.addEventListener('input', validatePrices);
                maxPriceInput.addEventListener('input', validatePrices);
            }

            // Animation des cartes produits au scroll
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);

            document.querySelectorAll('.product-card').forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(card);
            });
        });
    </script>
    <?php require_once __DIR__ . "/../../layouts/footer.php"; ?>

</body>

</html>