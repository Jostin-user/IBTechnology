
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | Détails du Produit - Stock</title>
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
                        <i class="fas fa-box"></i> Détails du Produit
                    </h1>
                    <a href="../../admin" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
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

        <div class="row">
            <!-- Informations produit -->
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h6 class="m-0">Informations Produit</h6>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <?php if ($produit['image']): ?>
                                <img src="<?= BASE_URL ?>/uploads/<?php echo htmlspecialchars($produit['image']); ?>"
                                    alt="<?php echo htmlspecialchars($produit['nom']); ?>" class="img-fluid rounded"
                                    style="max-height: 200px;">
                            <?php else: ?>
                                <div class="bg-light p-5 rounded">
                                    <i class="fas fa-image fa-2x text-muted"></i>
                                </div>
                            <?php endif; ?>
                        </div>

                        <h5 class="mb-3"><?php echo htmlspecialchars($produit['nom']); ?></h5>

                        <table class="table table-sm table-borderless">
                            <tbody>
                                <tr>
                                    <td class="text-muted">Marque:</td>
                                    <td><strong><?php echo htmlspecialchars($produit['marque'] ?? '-'); ?></strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Prix:</td>
                                    <td><strong><?php echo number_format($produit['prix'], 0, ',', ' '); ?>
                                            FCFA</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Statut:</td>
                                    <td>
                                        <span
                                            class="badge <?php echo ($produit['actif']) ? 'badge-success' : 'badge-danger'; ?>">
                                            <?php echo ($produit['actif']) ? 'Actif' : 'Inactif'; ?>
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Stock Info Card -->
                <div class="card mt-3">
                    <div class="card-header bg-info text-white">
                        <h6 class="m-0">Information de Stock</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <small class="text-muted d-block mb-1">STOCK TOTAL</small>
                            <h4 class="mb-0"><?php echo $produit['stock']; ?> unités</h4>
                        </div>

                        <div class="mb-3 pb-3 border-bottom">
                            <small class="text-muted d-block mb-1">STOCK RÉSERVÉ</small>
                            <h5 class="mb-0"><?php echo $produit['stock_reserve']; ?> unités</h5>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block mb-1">STOCK DISPONIBLE</small>
                            <h5 class="mb-0 text-success">
                                <?php echo ($produit['stock'] - $produit['stock_reserve']); ?> unités
                            </h5>
                        </div>

                        <?php
                        $etat = $produit['alerte_etat'] ?? 'normal';
                        $badge_class = [
                            'rupture' => 'danger',
                            'critique' => 'danger',
                            'faible' => 'warning',
                            'normal' => 'success'
                        ][$etat] ?? 'secondary';
                        ?>
                        <div class="alert alert-<?php echo $badge_class; ?> mb-0">
                            <small>
                                <i class="fas fa-info-circle"></i>
                                État: <strong><?php echo ucfirst($etat); ?></strong>
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulaires actions -->
            <div class="col-lg-8">
                <!-- Ajouter Mouvement -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h6 class="m-0"><i class="fas fa-exchange-alt"></i> Ajouter un Mouvement</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="<?= BASE_URL ?>admin/stock/ajouterMouvement">
                            <input type="hidden" name="produit_id" value="<?php echo $produit['id']; ?>">

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="type">Type de mouvement *</label>
                                    <select name="type" id="type" class="form-control" required>
                                        <option value="">-- Sélectionner --</option>
                                        <option value="entree">Entrée (Achat)</option>
                                        <option value="sortie">Sortie (Vente)</option>
                                        <option value="retour">Retour Client</option>
                                        <option value="perte">Perte/Casse</option>
                                    </select>
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="quantite">Quantité *</label>
                                    <input type="number" name="quantite" id="quantite" class="form-control" min="1"
                                        required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="raison">Raison *</label>
                                <input type="text" name="raison" id="raison" class="form-control"
                                    placeholder="Ex: Achat fournisseur, Commande #123" required>
                            </div>

                            <div class="form-group">
                                <label for="notes">Notes</label>
                                <textarea name="notes" id="notes" class="form-control" rows="2"
                                    placeholder="Notes supplémentaires..."></textarea>
                            </div>

                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-plus"></i> Enregistrer le mouvement
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Ajustement Stock -->
                <div class="card mb-4">
                    <div class="card-header bg-warning text-white">
                        <h6 class="m-0"><i class="fas fa-wrench"></i> Ajustement de Stock</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="<?= BASE_URL ?>admin/stock/ajuster">
                            <input type="hidden" name="produit_id" value="<?php echo $produit['id']; ?>">

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Stock actuel</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" value="<?php echo $produit['stock']; ?>"
                                            disabled>
                                        <div class="input-group-append">
                                            <span class="input-group-text">unités</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="nouveau_stock">Nouveau stock *</label>
                                    <div class="input-group">
                                        <input type="number" name="nouveau_stock" id="nouveau_stock"
                                            class="form-control" min="0" required>
                                        <div class="input-group-append">
                                            <span class="input-group-text">unités</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="raison_ajust">Raison *</label>
                                <select name="raison" id="raison_ajust" class="form-control" required>
                                    <option value="">-- Sélectionner --</option>
                                    <option value="inventaire">Inventaire</option>
                                    <option value="correction">Correction d'erreur</option>
                                    <option value="perte">Perte/Casse</option>
                                    <option value="autre">Autre</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea name="description" id="description" class="form-control" rows="2"></textarea>
                            </div>

                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-check"></i> Appliquer l'ajustement
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Seuils d'alerte -->
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h6 class="m-0"><i class="fas fa-bell"></i> Seuils d'Alerte</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="<?= BASE_URL?>admin/stock/definirSeuils">
                            <input type="hidden" name="produit_id" value="<?php echo $produit['id']; ?>">

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="seuil_min">Seuil minimum *</label>
                                    <input type="number" name="seuil_min" id="seuil_min" class="form-control"
                                        value="<?php echo $produit['seuil_minimum'] ?? 10; ?>" min="0" required>
                                    <small class="text-muted">Alerte si stock inférieur à cette valeur</small>
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="seuil_max">Seuil maximum *</label>
                                    <input type="number" name="seuil_max" id="seuil_max" class="form-control"
                                        value="<?php echo $produit['seuil_maximum'] ?? 1000; ?>" min="1" required>
                                    <small class="text-muted">Stock idéal maximum</small>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-secondary">
                                <i class="fas fa-save"></i> Enregistrer les seuils
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Historique des mouvements -->
        <div class="row mt-4" id="mouvements">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h6 class="m-0">
                            <i class="fas fa-history"></i> Historique des Mouvements (<?php echo $totalMouvements; ?>)
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php if ($mouvements): ?>
                            <div class="table-responsive">
                                <table class="table table-hover table-striped">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Type</th>
                                            <th class="text-center">Quantité</th>
                                            <th class="text-center">Avant</th>
                                            <th class="text-center">Après</th>
                                            <th>Raison</th>
                                            <th>Admin</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($mouvements as $m): ?>
                                            <tr>
                                                <td><?php echo date('d/m/Y H:i', strtotime($m['date_mouvement'])); ?></td>
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
                                                <td class="text-center">
                                                    <strong><?php echo abs($m['quantite']); ?></strong>
                                                </td>
                                                <td class="text-center"><?php echo $m['quantite_precedente']; ?></td>
                                                <td class="text-center"><?php echo $m['quantite_nouvelle']; ?></td>
                                                <td><?php echo htmlspecialchars($m['raison'] ?? '-'); ?></td>
                                                <td><?php echo htmlspecialchars($m['admin_nom'] ?? 'Système'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle"></i> Aucun mouvement enregistré
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
                            <a class="page-link" href="?id=<?php echo $produit['id']; ?>&p=<?php echo $i; ?>">
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

</html>