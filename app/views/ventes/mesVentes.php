<?php
require_once __DIR__ . '/../../layouts/header.php';
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Commandes</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/client-ventes.css">
</head>

<body>
    <div class="container my-5">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0">
                <i class="fas fa-receipt"></i> Mes Ventes / Commandes
            </h1>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    <?php endif; ?>

    <?php if ($ventes): ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="bg-light">
                    <tr>
                        <th>Commande</th>
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
                            <td><strong>#<?php echo $v['commande_id']; ?></strong></td>
                            <td class="text-center"><?php echo $v['nombre_articles']; ?></td>
                            <td class="text-right">
                                <strong><?php echo number_format($v['montant_total'], 0, ',', ' '); ?> FCFA</strong>
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
                            <td><?php echo date('d/m/Y', strtotime($v['date_vente'])); ?></td>
                            <td class="text-right">
                                <a href="/ventes/mes-ventes/details?id=<?php echo $v['id']; ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> Voir
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <nav aria-label="Pagination" class="mt-3">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo ($i === $page) ? 'active' : ''; ?>">
                            <a class="page-link" href="?p=<?php echo $i; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Vous n'avez pas encore de commandes.
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
</body>
</html>
