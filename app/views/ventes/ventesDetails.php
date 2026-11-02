<?php
require_once __DIR__ . '/../../layouts/header.php';
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de la Commande</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/client-ventes.css">
</head>

<body>
    <div class="container my-5">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">
                    <i class="fas fa-receipt"></i> Commande #<?php echo $vente['commande_id']; ?>
                </h1>
                <a href="/ventes/mes-ventes" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
            </div>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Informations -->
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h6 class="m-0">Informations de Livraison</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">Adresse</small>
                        <p class="mb-0"><?php echo htmlspecialchars($vente['adresse_livraison']); ?></p>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Ville</small>
                        <p class="mb-0"><?php echo htmlspecialchars($vente['ville']); ?></p>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Téléphone</small>
                        <p class="mb-0"><?php echo htmlspecialchars($vente['telephone']); ?></p>
                    </div>
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

                    <?php if ($vente['statut'] === 'livree'): ?>
                        <div class="alert alert-success">
                            <small>✓ Votre commande a été livrée avec succès!</small>
                        </div>
                    <?php elseif ($vente['statut'] === 'confirmee'): ?>
                        <div class="alert alert-info">
                            <small>⏳ Votre commande est confirmée et en préparation.</small>
                        </div>
                    <?php endif; ?>
                </div>
            </card>
        </div>

        <!-- Détails -->
        <div class="col-lg-8">
            <!-- Montants -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h6 class="m-0">Résumé</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-2">
                                <span class="text-muted">Date Commande:</span>
                                <strong class="float-right"><?php echo date('d/m/Y H:i', strtotime($vente['date_commande'])); ?></strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <span class="text-muted">Date Vente:</span>
                                <strong class="float-right"><?php echo date('d/m/Y H:i', strtotime($vente['date_vente'])); ?></strong>
                            </div>
                        </div>
                        <?php if ($vente['date_livraison']): ?>
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <span class="text-muted">Date Livraison:</span>
                                    <strong class="float-right"><?php echo date('d/m/Y H:i', strtotime($vente['date_livraison'])); ?></strong>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="col-12">
                            <hr>
                            <div class="mb-2">
                                <span class="text-muted">Articles:</span>
                                <strong class="float-right"><?php echo $vente['nombre_articles']; ?></strong>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="mb-2">
                                <span class="text-muted">Frais Livraison:</span>
                                <strong class="float-right"><?php echo number_format($vente['frais_livraison'], 0, ',', ' '); ?> FCFA</strong>
                            </div>
                        </div>
                        <div class="col-12">
                            <hr>
                            <div class="mb-0">
                                <h5>Montant Total:</h5>
                                <h4 class="float-right text-success"><?php echo number_format($vente['montant_total'], 0, ',', ' '); ?> FCFA</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Produits -->
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h6 class="m-0">
                        <i class="fas fa-box"></i> Produits Commandés
                    </h6>
                </div>
                <div class="card-body">
                    <?php if ($produits): ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Produit</th>
                                        <th class="text-center">Quantité</th>
                                        <th class="text-right">Prix</th>
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

                        <?php if ($vente['statut'] === 'livree'): ?>
                            <div class="mt-3">
                                <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#retourModal">
                                    <i class="fas fa-undo"></i> Demander un retour
                                </button>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Retour -->
<div class="modal fade" id="retourModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Demander un Retour</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form method="POST" action="/ventes/demanderRetour">
                <div class="modal-body">
                    <input type="hidden" name="vente_id" value="<?php echo $vente['id']; ?>">

                    <div class="form-group">
                        <label for="produit_id">Produit *</label>
                        <select name="produit_id" id="produit_id" class="form-control" required>
                            <option value="">-- Sélectionner --</option>
                            <?php foreach ($produits as $p): ?>
                                <option value="<?php echo $p['produit_id']; ?>">
                                    <?php echo htmlspecialchars($p['nom']); ?> (Qté: <?php echo $p['quantite']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="quantite">Quantité *</label>
                        <input type="number" name="quantite" id="quantite" class="form-control" min="1" required>
                    </div>

                    <div class="form-group">
                        <label for="raison">Raison *</label>
                        <select name="raison" id="raison" class="form-control" required>
                            <option value="">-- Sélectionner --</option>
                            <option>Produit défectueux</option>
                            <option>Non conforme à la description</option>
                            <option>Changement d'avis</option>
                            <option>Autre</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Soumettre la Demande</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
</body>
</html>
