<?php
require_once __DIR__ . '/../../../middlewares/AdminMiddleware.php';
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | Détails de la Vente</title>
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
                    <i class="fas fa-receipt"></i> Détails de la Vente #<?php echo $vente['id']; ?>
                </h1>
                <a href="/admin/ventes" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
            </div>
        </div>
    </div>

    <!-- Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Informations client -->
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h6 class="m-0">Informations Client</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tbody>
                            <tr>
                                <td class="text-muted">Nom:</td>
                                <td><strong><?php echo htmlspecialchars($vente['nom_complet']); ?></strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Email:</td>
                                <td><a href="mailto:<?php echo $vente['email']; ?>"><?php echo $vente['email']; ?></a></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Téléphone:</td>
                                <td><strong><?php echo htmlspecialchars($vente['telephone']); ?></strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Adresse:</td>
                                <td><?php echo htmlspecialchars($vente['adresse_livraison']); ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Ville:</td>
                                <td><?php echo htmlspecialchars($vente['ville']); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Statut -->
            <div class="card mt-3">
                <div class="card-header bg-info text-white">
                    <h6 class="m-0">Statut</h6>
                </div>
                <div class="card-body">
                    <?php
                    $badge_class = [
                        'confirmee' => 'primary',
                        'livree' => 'success',
                        'annulee' => 'danger',
                        'en_attente' => 'warning'
                    ][$vente['statut']] ?? 'secondary';
                    $label = [
                        'confirmee' => 'Confirmée',
                        'livree' => 'Livrée',
                        'annulee' => 'Annulée',
                        'en_attente' => 'Attente'
                    ][$vente['statut']] ?? $vente['statut'];
                    ?>
                    <div class="mb-3">
                        <span class="badge badge-<?php echo $badge_class; ?> p-2" style="font-size: 1rem;">
                            <?php echo $label; ?>
                        </span>
                    </div>

                    <?php if ($vente['statut'] === 'confirmee'): ?>
                        <a href="<?=BASE_URL?>admin/ventes/confirmerLivraison/<?php echo $vente['id']; ?>" class="btn btn-success btn-sm btn-block">
                            <i class="fas fa-check"></i> Confirmer Livraison
                        </a>
                    <?php endif; ?>

                    <?php if ($vente['statut'] !== 'annulee'): ?>
                        <button type="button" class="btn btn-danger btn-sm btn-block" data-toggle="modal" data-target="#annulerModal">
                            <i class="fas fa-times"></i> Annuler Vente
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Informations vente -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h6 class="m-0">Détails Vente</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <small class="text-muted">Commande ID</small>
                                <h5>#<?php echo $vente['commande_id']; ?></h5>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <small class="text-muted">Vente ID</small>
                                <h5>#<?php echo $vente['id']; ?></h5>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <small class="text-muted">Date de Vente</small>
                                <h5><?php echo date('d/m/Y H:i', strtotime($vente['date_vente'])); ?></h5>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <small class="text-muted">Date de Commande</small>
                                <h5><?php echo date('d/m/Y H:i', strtotime($vente['date_commande'])); ?></h5>
                            </div>
                        </div>
                        <?php if ($vente['date_livraison']): ?>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <small class="text-muted">Date de Livraison</small>
                                    <h5><?php echo date('d/m/Y H:i', strtotime($vente['date_livraison'])); ?></h5>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Montants -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-white">
                    <h6 class="m-0">Résumé Financier</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-2">
                                <span class="text-muted">Montant Produits:</span>
                                <strong class="float-right"><?php echo number_format($vente['montant_total'] - $vente['frais_livraison'], 0, ',', ' '); ?> FCFA</strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <span class="text-muted">Frais Livraison:</span>
                                <strong class="float-right"><?php echo number_format($vente['frais_livraison'], 0, ',', ' '); ?> FCFA</strong>
                            </div>
                        </div>
                        <div class="col-12">
                            <hr>
                            <div class="mb-2">
                                <span class="font-weight-bold">Montant Total:</span>
                                <h4 class="float-right text-success"><?php echo number_format($vente['montant_total'], 0, ',', ' '); ?> FCFA</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Produits vendus -->
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h6 class="m-0">
                        <i class="fas fa-box"></i> Produits Vendus (<?php echo count($produits); ?>)
                    </h6>
                </div>
                <div class="card-body">
                    <?php if ($produits): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Produit</th>
                                        <th class="text-center">Qté</th>
                                        <th class="text-right">Prix Unit.</th>
                                        <th class="text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($produits as $p): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($p['nom']); ?></strong>
                                                <br>
                                                <small class="text-muted"><?php echo htmlspecialchars($p['marque'] ?? ''); ?></small>
                                            </td>
                                            <td class="text-center"><?php echo $p['quantite']; ?></td>
                                            <td class="text-right"><?php echo number_format($p['prix'], 0, ',', ' '); ?> FCFA</td>
                                            <td class="text-right">
                                                <strong><?php echo number_format($p['prix'] * $p['quantite'], 0, ',', ' '); ?> FCFA</strong>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">Aucun produit</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Annulation -->
<div class="modal fade" id="annulerModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Annuler la Vente</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST" action="<?=BASE_URL?>admin/ventes/annuler">
                <div class="modal-body">
                    <input type="hidden" name="vente_id" value="<?php echo $vente['id']; ?>">
                    <div class="form-group">
                        <label for="notes">Raison (optionnel)</label>
                        <textarea name="notes" id="notes" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">Confirmer Annulation</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer_admin.php'; ?>
</body>
</html>
