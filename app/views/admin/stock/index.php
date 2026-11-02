<?php
require_once __DIR__ . '/../../../middlewares/AdminMiddleware.php';
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Admin | Gestion du stock</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin/admin.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin/admin-stock.css">
</head>

<body>
    <?php require_once __DIR__ . '/../layouts/header_admin.php'; ?>

    <div class="container-fluid mt-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="h3 mb-0">
                        <i class="fas fa-boxes"></i> Gestion du Stock
                    </h1>
                    <div>
                        <a href="./stock/exporterCSV?<?php echo http_build_query($_GET); ?>"
                            class="btn btn-success btn-sm">
                            <i class="fas fa-download"></i> Exporter CSV
                        </a>
                        <a href="./stock/dashboard" class="btn btn-info btn-sm">
                            <i class="fas fa-chart-line"></i> Tableau de bord
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form method="get" class="form-inline">
                            <div class="form-group mr-2">
                                <input type="text" name="search" class="form-control"
                                    placeholder="Rechercher un produit..."
                                    value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                            </div>

                            <div class="form-group mr-2">
                                <select name="categorie" class="form-control">
                                    <option value="">Toutes les catégories</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>" <?= ($cat['id'] == ($_GET['categorie'] ?? 0)) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['nom']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group mr-2">
                                <select name="etat" class="form-control">
                                    <option value="">Tous les états</option>
                                    <option value="rupture" <?php echo ($_GET['etat'] ?? '') === 'rupture' ? 'selected' : ''; ?>>
                                        En rupture
                                    </option>
                                    <option value="critique" <?php echo ($_GET['etat'] ?? '') === 'critique' ? 'selected' : ''; ?>>
                                        Stock critique
                                    </option>
                                    <option value="faible" <?php echo ($_GET['etat'] ?? '') === 'faible' ? 'selected' : ''; ?>>
                                        Stock faible
                                    </option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Filtrer
                            </button>
                            <a href="<?= BASE_URL?>admin/stock" class="btn btn-secondary ml-2">
                                <i class="fas fa-times"></i> Réinitialiser
                            </a>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['success'];
                unset($_SESSION['success']); ?>
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['error'];
                unset($_SESSION['error']); ?>
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <!-- Tableau de stock -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="m-0">Stock des produits (<?php echo $total; ?> produits)</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($produits): ?>
                            <div class="table-responsive">
                                <table class="table table-hover table-striped">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Produit</th>
                                            <th>Catégorie</th>
                                            <th class="text-center">Stock</th>
                                            <th class="text-center">Réservé</th>
                                            <th class="text-center">Disponible</th>
                                            <th class="text-center">Seuil Min</th>
                                            <th class="text-center">État</th>
                                            <th class="text-center">Prix</th>
                                            <th class="text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($produits as $p): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($p['nom']); ?></strong>
                                                    <br>
                                                    <small
                                                        class="text-muted"><?php echo htmlspecialchars($p['marque'] ?? '-'); ?></small>
                                                </td>
                                                <td><?php echo htmlspecialchars($p['categorie']); ?></td>
                                                <td class="text-center">
                                                    <span class="badge badge-primary">
                                                        <?php echo $p['stock']; ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-secondary">
                                                        <?php echo $p['stock_reserve']; ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <strong>
                                                        <?php echo $p['stock_disponible']; ?>
                                                    </strong>
                                                </td>
                                                <td class="text-center">
                                                    <?php echo $p['seuil_minimum'] ?? '-'; ?>
                                                </td>
                                                <td class="text-center">
                                                    <?php
                                                    $etat = $p['alerte_etat'] ?? 'normal';
                                                    $badge_class = [
                                                        'rupture' => 'danger',
                                                        'critique' => 'danger',
                                                        'faible' => 'warning',
                                                        'normal' => 'success'
                                                    ][$etat] ?? 'secondary';
                                                    $etat_label = [
                                                        'rupture' => 'Rupture',
                                                        'critique' => 'Critique',
                                                        'faible' => 'Faible',
                                                        'normal' => 'Normal'
                                                    ][$etat] ?? ucfirst($etat);
                                                    ?>
                                                    <span class="badge badge-<?php echo $badge_class; ?>">
                                                        <?php echo $etat_label; ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <?php echo number_format($p['prix'], 0, ',', ' '); ?> FCFA
                                                </td>
                                                <td class="text-right">
                                                    <a href="./stock/details?id=<?php echo $p['id']; ?>"
                                                        class="btn btn-sm btn-info" title="Voir détails">
                                                        <i class="fas fa-eye">details</i>
                                                    </a>
                                                    <a href="/stock/details/<?php echo $p['id']; ?>#mouvements"
                                                        class="btn btn-sm btn-primary" title="Historique">
                                                        <i class="fas fa-history"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle"></i> Aucun produit trouvé avec ces critères
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <nav aria-label="Page navigation" class="mt-3">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo ($i === $page) ? 'active' : ''; ?>">
                            <a class="page-link"
                                href="?p=<?php echo $i; ?>&<?php echo http_build_query(array_filter($_GET)); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>

    <?php require_once __DIR__ . '/../layouts/footer_admin.php'; ?>
</body>

</html>