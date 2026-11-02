<?php
require_once __DIR__ . '/../../../middlewares/AdminMiddleware.php';
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | Rapport de Stock</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin/admin.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin/admin-stock.css">
</head>

<body>
    <?php require_once __DIR__ . '/../layouts/header_admin.php'; ?>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">
                    <i class="fas fa-file-csv"></i> Rapport de Stock
                </h1>
                <div>
                    <a href="/admin/stock" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres date -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="get" class="form-inline">
                        <div class="form-group mr-3">
                            <label for="date_debut" class="mr-2">Du:</label>
                            <input type="date" name="date_debut" id="date_debut" class="form-control"
                                   value="<?php echo htmlspecialchars($date_debut); ?>">
                        </div>

                        <div class="form-group mr-3">
                            <label for="date_fin" class="mr-2">Au:</label>
                            <input type="date" name="date_fin" id="date_fin" class="form-control"
                                   value="<?php echo htmlspecialchars($date_fin); ?>">
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Filtrer
                        </button>

                        <button type="button" class="btn btn-success ml-2" onclick="window.print()">
                            <i class="fas fa-print"></i> Imprimer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques résumé -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-muted">Total Produits</h5>
                    <h3 class="text-primary"><?php echo $stats['total_produits']; ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-muted">En Rupture</h5>
                    <h3 class="text-danger"><?php echo $stats['rupture']; ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-muted">Stock Critique</h5>
                    <h3 class="text-warning"><?php echo $stats['critique']; ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-muted">Valeur Stock</h5>
                    <h3 class="text-success">
                        <?php echo number_format($stats['valeur_stock'], 0, ',', ' '); ?> FCFA
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau rapport -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h6 class="m-0">Détail du Rapport</h6>
                </div>
                <div class="card-body">
                    <?php if ($rapport): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Produit</th>
                                        <th>Catégorie</th>
                                        <th class="text-center">Stock Actuel</th>
                                        <th class="text-center">Prix Unitaire</th>
                                        <th class="text-center">Mouvements</th>
                                        <th class="text-center">Entrées</th>
                                        <th class="text-center">Sorties</th>
                                        <th class="text-right">Valeur Stock</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $valeur_total = 0;
                                    foreach ($rapport as $r):
                                        $valeur_produit = $r['stock_actuel'] * $r['prix_unitaire'];
                                        $valeur_total += $valeur_produit;
                                        ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($r['produit']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($r['categorie'] ?? '-'); ?></td>
                                            <td class="text-center">
                                                <span class="badge badge-primary"><?php echo $r['stock_actuel']; ?></span>
                                            </td>
                                            <td class="text-center"><?php echo number_format($r['prix_unitaire'], 0, ',', ' '); ?> FCFA</td>
                                            <td class="text-center"><?php echo $r['mouvements_total']; ?></td>
                                            <td class="text-center text-success">+<?php echo $r['total_entrees']; ?></td>
                                            <td class="text-center text-warning">-<?php echo $r['total_sorties']; ?></td>
                                            <td class="text-right">
                                                <strong><?php echo number_format($valeur_produit, 0, ',', ' '); ?> FCFA</strong>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <tr class="font-weight-bold bg-light">
                                        <td colspan="7" class="text-right">VALEUR TOTALE:</td>
                                        <td class="text-right">
                                            <?php echo number_format($valeur_total, 0, ',', ' '); ?> FCFA
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle"></i> Aucune donnée disponible pour cette période
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style media="print">
    .btn, .form-inline, .no-print {
        display: none !important;
    }
</style>

<?php require_once __DIR__ . '/../layouts/footer_admin.php'; ?>
</body>
</html>
