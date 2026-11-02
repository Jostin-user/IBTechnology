<?php
require_once __DIR__ . '/../../includes/init.php';
require_once __DIR__ . '/../../helpers/SlugHelper.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($categorie['nom']) ?> - IbTechnologie</title>
    <meta name="description" content="<?= htmlspecialchars($categorie['description'] ?? 'Découvrez nos produits de la catégorie ' . $categorie['nom']) ?>">
    <link rel="canonical" href="<?= $_SERVER['REQUEST_URI'] ?>">
    <?= SlugHelper::generateMetaTags(
        $categorie['nom'] . ' - IbTechnologie',
        $categorie['description'] ?? 'Découvrez nos produits de la catégorie ' . $categorie['nom'],
        'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']
    ) ?>
    <link rel="stylesheet" href="<?=BASE_URL?>assets/css/style.css">
</head>
<body>
    <?php require_once __DIR__ . '/../../layouts/header.php'; ?>

    <main class="container my-5">
        <div class="row mb-4">
            <div class="col-md-12">
                <h1><?= htmlspecialchars($categorie['nom']) ?></h1>
                <?php if (!empty($categorie['description'])): ?>
                    <p class="text-muted"><?= htmlspecialchars($categorie['description']) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="row">
            <!-- Barre latérale des filtres -->
            <div class="col-md-3 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Filtres</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="/categories/<?= htmlspecialchars($categorie['slug']) ?>">
                            <!-- Recherche -->
                            <div class="mb-3">
                                <label class="form-label">Rechercher</label>
                                <input type="text" class="form-control" name="q" 
                                    value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                            </div>

                            <!-- Prix -->
                            <div class="mb-3">
                                <label class="form-label">Prix</label>
                                <input type="number" class="form-control mb-2" name="min" 
                                    placeholder="Min" value="<?= htmlspecialchars($_GET['min'] ?? '') ?>">
                                <input type="number" class="form-control" name="max" 
                                    placeholder="Max" value="<?= htmlspecialchars($_GET['max'] ?? '') ?>">
                            </div>

                            <!-- Marque -->
                            <?php if (!empty($marques)): ?>
                                <div class="mb-3">
                                    <label class="form-label">Marque</label>
                                    <select class="form-select" name="marque">
                                        <option value="">Toutes les marques</option>
                                        <?php foreach ($marques as $marque): ?>
                                            <option value="<?= htmlspecialchars($marque) ?>"
                                                <?= ($_GET['marque'] ?? '') === $marque ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($marque) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php endif; ?>

                            <!-- Stock -->
                            <div class="mb-3">
                                <label class="form-label">Disponibilité</label>
                                <select class="form-select" name="stock">
                                    <option value="">Tous</option>
                                    <option value="disponnible" <?= ($_GET['stock'] ?? '') === 'disponnible' ? 'selected' : '' ?>>
                                        En stock
                                    </option>
                                    <option value="rupture" <?= ($_GET['stock'] ?? '') === 'rupture' ? 'selected' : '' ?>>
                                        Rupture
                                    </option>
                                </select>
                            </div>

                            <!-- Tri -->
                            <div class="mb-3">
                                <label class="form-label">Tri</label>
                                <select class="form-select" name="tri">
                                    <option value="">Par défaut</option>
                                    <option value="prix_asc" <?= ($_GET['tri'] ?? '') === 'prix_asc' ? 'selected' : '' ?>>
                                        Prix croissant
                                    </option>
                                    <option value="prix_desc" <?= ($_GET['tri'] ?? '') === 'prix_desc' ? 'selected' : '' ?>>
                                        Prix décroissant
                                    </option>
                                    <option value="nom_asc" <?= ($_GET['tri'] ?? '') === 'nom_asc' ? 'selected' : '' ?>>
                                        Nom A-Z
                                    </option>
                                    <option value="nom_desc" <?= ($_GET['tri'] ?? '') === 'nom_desc' ? 'selected' : '' ?>>
                                        Nom Z-A
                                    </option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Grille des produits -->
            <div class="col-md-9">
                <?php if (empty($produits)): ?>
                    <div class="alert alert-info">
                        Aucun produit trouvé dans cette catégorie.
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($produits as $produit): ?>
                            <div class="col-md-4 mb-4">
                                <div class="card h-100">
                                    <img src="<?=BASE_URL?>uploads/<?= htmlspecialchars($produit['image']) ?>" 
                                        class="card-img-top" alt="<?= htmlspecialchars($produit['nom']) ?>">
                                    <div class="card-body">
                                        <h5 class="card-title"><?= htmlspecialchars($produit['nom']) ?></h5>
                                        <p class="card-text text-muted">
                                            <?= htmlspecialchars(substr($produit['description'], 0, 100)) ?>...
                                        </p>
                                        <p class="card-text">
                                            <strong class="text-primary">
                                                <?= number_format($produit['prix'], 0, ',', ' ') ?> FCFA
                                            </strong>
                                        </p>
                                        <?php if ($produit['stock'] <= 0): ?>
                                            <span class="badge bg-danger">Rupture</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-footer bg-white">
                                        <a href="/produits/slug/<?= htmlspecialchars($produit['slug']) ?>" 
                                            class="btn btn-sm btn-primary w-100">
                                            Voir les détails
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center">
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?= $i === $pageActuelle ? 'active' : '' ?>">
                                        <a class="page-link" 
                                            href="/categories/<?= htmlspecialchars($categorie['slug']) ?>?p=<?= $i ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
</body>
</html>
