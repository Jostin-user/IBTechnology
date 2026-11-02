<?php
require_once __DIR__ . '/../../../middlewares/AdminMiddleware.php';
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Admin | Historique des ventes</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin/admin.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin/admin-ventes.css">
</head>

<body>
    <?php require_once __DIR__ . '/../layouts/header_admin.php'; ?>

    <div class="container-fluid mt-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="h3 mb-0">
                        <i class="fas fa-receipt"></i> Historique des Ventes
                    </h1>
                    <div>
                        <a href="<?= BASE_URL ?>admin/ventes/rapport" class="btn btn-success btn-sm">
                            <i class="fas fa-chart-bar"></i> Rapport
                        </a>
                        <a href="<?= BASE_URL ?>admin/ventes/exporterCSV?<?php echo http_build_query($_GET); ?>"
                            class="btn btn-info btn-sm">
                            <i class="fas fa-download"></i> Export CSV
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Nombre de ventes</h6>
                        <h3 class="text-primary"><?php echo $stats['nombre_ventes'] ?? 0; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Articles vendus</h6>
                        <h3 class="text-info"><?php echo $stats['articles_vendus'] ?? 0; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Montant total</h6>
                        <h3 class="text-success"><?php echo number_format($stats['montant_total'] ?? 0, 0, ',', ' '); ?>
                            FCFA</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Panier moyen</h6>
                        <h3 class="text-warning"><?php echo number_format($stats['montant_moyen'] ?? 0, 0, ',', ' '); ?>
                            FCFA</h3>
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
                                <input type="text" name="search" class="form-control" placeholder="Rechercher client..."
                                    value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                            </div>

                            <div class="form-group mr-2">
                                <select name="statut" class="form-control">
                                    <option value="">Tous les statuts</option>
                                    <option value="confirmee" <?php echo ($_GET['statut'] ?? '') === 'confirmee' ? 'selected' : ''; ?>>Confirmée</option>
                                    <option value="livree" <?php echo ($_GET['statut'] ?? '') === 'livree' ? 'selected' : ''; ?>>Livrée</option>
                                    <option value="annulee" <?php echo ($_GET['statut'] ?? '') === 'annulee' ? 'selected' : ''; ?>>Annulée</option>
                                </select>
                            </div>

                            <div class="form-group mr-2">
                                <input type="date" name="date_debut" class="form-control"
                                    value="<?php echo htmlspecialchars($_GET['date_debut'] ?? ''); ?>">
                            </div>

                            <div class="form-group mr-2">
                                <input type="date" name="date_fin" class="form-control"
                                    value="<?php echo htmlspecialchars($_GET['date_fin'] ?? ''); ?>">
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Filtrer
                            </button>
                            <a href="<?= BASE_URL ?>admin/ventes" class="btn btn-secondary ml-2">Réinitialiser</a>
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
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        <?php endif; ?>

        <!-- Tableau des ventes -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h6 class="m-0">Ventes (<?php echo $total; ?> résultats)</h6>
                    </div>
                    <div class="card-body">
                        <?php if ($ventes): ?>
                            <div class="table-responsive">
                                <table class="table table-hover table-striped">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Commande</th>
                                            <th>Client</th>
                                            <th class="text-center">Articles</th>
                                            <th class="text-right">Montant</th>
                                            <th class="text-center">Statut</th>
                                            <th>Date</th>
                                            <th class="text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($ventes as $v): ?>
                                            <tr>
                                                <td><strong>#<?php echo $v['id']; ?></strong></td>
                                                <td>#<?php echo $v['commande_id']; ?></td>
                                                <td>
                                                    <?php echo htmlspecialchars($v['nom_complet']); ?>
                                                    <br>
                                                    <small
                                                        class="text-muted"><?php echo htmlspecialchars($v['email']); ?></small>
                                                </td>
                                                <td class="text-center"><?php echo $v['nombre_articles']; ?></td>
                                                <td class="text-right">
                                                    <strong><?php echo number_format($v['montant_total'], 0, ',', ' '); ?>
                                                        FCFA</strong>
                                                </td>
                                                <td class="text-center">
                                                    <?php
                                                    $badge_class = [
                                                        'confirmee' => 'primary',
                                                        'livree' => 'success',
                                                        'annulee' => 'danger',
                                                        'en_attente' => 'warning'
                                                    ][$v['statut']] ?? 'secondary';
                                                    $label = [
                                                        'confirmee' => 'Confirmée',
                                                        'livree' => 'Livrée',
                                                        'annulee' => 'Annulée',
                                                        'en_attente' => 'Attente'
                                                    ][$v['statut']] ?? $v['statut'];
                                                    ?>
                                                    <span class="badge badge-<?php echo $badge_class; ?>">
                                                        <?php echo $label; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($v['date_vente'])); ?></td>
                                                <td class="text-right">
                                                    <a href="<?= BASE_URL ?>admin/ventes/details?id=<?php echo $v['id']; ?>"
                                                        class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info mb-0">
                                Aucune vente trouvée
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <nav aria-label="Pagination" class="mt-3">
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