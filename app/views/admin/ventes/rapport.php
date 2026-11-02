<?php
require_once __DIR__ . '/../../../middlewares/AdminMiddleware.php';
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | Rapport des Ventes</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin/admin.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin/admin-ventes.css">
</head>

<body>
    <?php require_once __DIR__ . '/../layouts/header_admin.php'; ?>

<div class="container-fluid mt-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0">
                <i class="fas fa-chart-bar"></i> Rapport des Ventes
            </h1>
        </div>
    </div>

    <!-- Filtres -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="get" class="form-inline">
                        <div class="form-group mr-3">
                            <label for="date_debut" class="mr-2">Du:</label>
                            <input type="date" name="date_debut" id="date_debut" class="form-control"
                                   value="<?php echo htmlspecialchars($_GET['date_debut'] ?? date('Y-01-01')); ?>">
                        </div>

                        <div class="form-group mr-3">
                            <label for="date_fin" class="mr-2">Au:</label>
                            <input type="date" name="date_fin" id="date_fin" class="form-control"
                                   value="<?php echo htmlspecialchars($_GET['date_fin'] ?? date('Y-m-d')); ?>">
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Filtrer
                        </button>

                        <button type="button" class="btn btn-info ml-2" onclick="window.print()">
                            <i class="fas fa-print"></i> Imprimer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques globales -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Nombre de Ventes</h6>
                    <h3 class="text-primary"><?php echo $stats['nombre_ventes'] ?? 0; ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Articles Vendus</h6>
                    <h3 class="text-info"><?php echo $stats['articles_vendus'] ?? 0; ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Montant Total</h6>
                    <h3 class="text-success">
                        <?php echo number_format($stats['montant_total'] ?? 0, 0, ',', ' '); ?> FCFA
                    </h3>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="text-muted mb-1">Clients</h6>
                    <h3 class="text-warning"><?php echo $stats['nombre_clients'] ?? 0; ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Ventes par jour -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h6 class="m-0"><i class="fas fa-calendar-alt"></i> Ventes par Jour</h6>
                </div>
                <div class="card-body">
                    <?php if ($ventes_par_jour): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Date</th>
                                        <th class="text-center">Ventes</th>
                                        <th class="text-center">Articles</th>
                                        <th class="text-right">Montant</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ventes_par_jour as $v): ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y', strtotime($v['jour'])); ?></td>
                                            <td class="text-center"><?php echo $v['nombre_ventes']; ?></td>
                                            <td class="text-center"><?php echo $v['articles']; ?></td>
                                            <td class="text-right">
                                                <strong><?php echo number_format($v['montant'], 0, ',', ' '); ?> FCFA</strong>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">Aucune donnée</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Clients Top Acheteurs -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h6 class="m-0"><i class="fas fa-user-tie"></i> Top Clients</h6>
                </div>
                <div class="card-body">
                    <?php if ($clients_top): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Client</th>
                                        <th class="text-center">Achats</th>
                                        <th class="text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($clients_top as $c): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($c['nom_complet']); ?></strong>
                                                <br>
                                                <small class="text-muted"><?php echo $c['email']; ?></small>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-primary"><?php echo $c['nombre_achats']; ?></span>
                                            </td>
                                            <td class="text-right">
                                                <strong><?php echo number_format($c['montant_total'], 0, ',', ' '); ?> FCFA</strong>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">Aucune donnée</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Produits Populaires -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h6 class="m-0"><i class="fas fa-fire"></i> Produits Populaires</h6>
                </div>
                <div class="card-body">
                    <?php if ($produits_populaires): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Produit</th>
                                        <th class="text-center">Vendus</th>
                                        <th class="text-center">Commandes</th>
                                        <th class="text-right">Montant Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($produits_populaires as $p): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($p['nom']); ?></strong>
                                                <br>
                                                <small class="text-muted"><?php echo number_format($p['prix'], 0, ',', ' '); ?> FCFA/unité</small>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-success"><?php echo $p['total_vendu']; ?></span>
                                            </td>
                                            <td class="text-center"><?php echo $p['nombre_commandes']; ?></td>
                                            <td class="text-right">
                                                <strong><?php echo number_format($p['montant_total'], 0, ',', ' '); ?> FCFA</strong>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">Aucune donnée</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style media="print">
    .btn, .form-inline, .no-print { display: none !important; }
    body { font-size: 12px; }
</style>

<?php require_once __DIR__ . '/../layouts/footer_admin.php'; ?>
</body>
</html>
