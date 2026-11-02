<?php
require_once __DIR__ . '/../../../middlewares/AdminMiddleware.php';
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | Tableau de bord - Stock</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin/admin.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin/admin-stock.css">
</head>

<body>
    <?php require_once __DIR__ . '/../layouts/header_admin.php'; ?>

<div class="container-fluid mt-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-3">
                <i class="fas fa-chart-line"></i> Tableau de bord - Gestion de Stock
            </h1>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-left-primary">
                <div class="card-body">
                    <div class="text-primary font-weight-bold text-lg mb-2">
                        <?php echo $stats['total_produits']; ?>
                    </div>
                    <div class="text-primary small">Produits totaux</div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-left-warning">
                <div class="card-body">
                    <div class="text-warning font-weight-bold text-lg mb-2">
                        <?php echo $stats['critique']; ?>
                    </div>
                    <div class="text-warning small">Stock critique/Rupture</div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-left-danger">
                <div class="card-body">
                    <div class="text-danger font-weight-bold text-lg mb-2">
                        <?php echo $stats['rupture']; ?>
                    </div>
                    <div class="text-danger small">En rupture de stock</div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-left-success">
                <div class="card-body">
                    <div class="text-success font-weight-bold text-lg mb-2">
                        <?php echo number_format($stats['valeur_stock'], 0, ',', ' '); ?> FCFA
                    </div>
                    <div class="text-success small">Valeur totale du stock</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Produits en rupture -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h6 class="m-0">
                        <i class="fas fa-exclamation-circle"></i> Ruptures de stock (<?php echo count($ruptures); ?>)
                    </h6>
                </div>
                <div class="card-body">
                    <?php if ($ruptures): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <tbody>
                                    <?php foreach (array_slice($ruptures, 0, 10) as $p): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($p['nom']); ?></strong>
                                                <br>
                                                <small class="text-muted"><?php echo htmlspecialchars($p['categorie'] ?? 'N/A'); ?></small>
                                            </td>
                                            <td class="text-right">
                                                <a href="<?=BASE_URL?>admin/stock/details?id=<?php echo $p['id']; ?>" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-success mb-0">✓ Tous les produits ont du stock</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Produits critique -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header bg-warning text-white">
                    <h6 class="m-0">
                        <i class="fas fa-triangle-exclamation"></i> Stock critique (<?php echo count($critiques); ?>)
                    </h6>
                </div>
                <div class="card-body">
                    <?php if ($critiques): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <tbody>
                                    <?php foreach (array_slice($critiques, 0, 10) as $p): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($p['nom']); ?></strong>
                                                <br>
                                                <small class="text-muted">Stock: <?php echo $p['stock']; ?> unités</small>
                                            </td>
                                            <td class="text-right">
                                                <span class="badge badge-warning"><?php echo $p['etat']; ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-success mb-0">✓ Aucun produit en stock critique</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Mouvements récents -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h6 class="m-0">
                        <i class="fas fa-history"></i> Mouvements récents
                    </h6>
                </div>
                <div class="card-body">
                    <?php if ($mouvements): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Produit</th>
                                        <th>Type</th>
                                        <th>Quantité</th>
                                        <th>Raison</th>
                                        <th>Admin</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($mouvements as $m): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($m['produit_nom']); ?></strong></td>
                                            <td>
                                                <?php
                                                $badge_class = [
                                                    'entree' => 'success',
                                                    'sortie' => 'warning',
                                                    'retour' => 'info',
                                                    'perte' => 'danger',
                                                    'ajustement' => 'secondary'
                                                ][$m['type']] ?? 'secondary';
                                                ?>
                                                <span class="badge badge-<?php echo $badge_class; ?>">
                                                    <?php echo ucfirst($m['type']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo $m['quantite']; ?> unités</td>
                                            <td><?php echo htmlspecialchars($m['raison'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($m['admin_nom'] ?? 'Système'); ?></td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($m['date_mouvement'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="row mt-4">
        <div class="col-12">
            <a href="../stock" class="btn btn-primary">
                <i class="fas fa-list"></i> Gestion complète du stock
            </a>
            <a href="./rapport" class="btn btn-secondary">
                <i class="fas fa-file-csv"></i> Rapport de stock
            </a>
        </div>
    </div>
</div>

<style>
    .border-left-primary {
        border-left: 4px solid #007bff;
    }
    .border-left-warning {
        border-left: 4px solid #ffc107;
    }
    .border-left-danger {
        border-left: 4px solid #dc3545;
    }
    .border-left-success {
        border-left: 4px solid #28a745;
    }
    .text-lg {
        font-size: 1.5rem;
    }
</style>

<?php require_once __DIR__ . '/../layouts/footer_admin.php'; ?>
</body>
</html>
