<?php
require_once __DIR__ . '/../../../middlewares/AdminMiddleware.php';
AdminMiddleware::verifierAdmin();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Admin | Détails Commande #<?= str_pad($commande['id'], 4, '0', STR_PAD_LEFT) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="<?=BASE_URL?>assets/css/admin/admin.css">
    <link rel="stylesheet" href="<?=BASE_URL?>assets/css/admin/detail-commande.css">
</head>

<body>
    <?php require_once __DIR__ . '/../layouts/header_admin.php'; ?>

    <div class="admin-container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h1>Détails de la Commande #<?= str_pad($commande['id'], 4, '0', STR_PAD_LEFT) ?></h1>
            <a href="<?=BASE_URL?>admin/commandes" class="btn btn-secondary">← Retour à la liste</a>
        </div>

        <!-- Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div style="background: #10b981; color: white; padding: 10px; margin-bottom: 20px; border-radius: 4px;">
                <?= $_SESSION['success'] ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div style="background: #ef4444; color: white; padding: 10px; margin-bottom: 20px; border-radius: 4px;">
                <?= $_SESSION['error'] ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="order-details">
            <!-- Informations principales -->
            <div class="order-info">
                <div class="status-section">
                    <h2>Statut de la commande</h2>
                    <span class="status-badge status-<?= $commande['status_class'] ?>">
                        <?= $commande['status_label'] ?>
                    </span>
                </div>

                <!-- Barre de progression -->
                <div class="tracking-steps">
                    <?php foreach ($commande['tracking_steps'] as $index => $step): ?>
                        <div class="step <?= $step['status'] ?>">
                            <div class="step-circle">
                                <?php if ($step['status'] === 'completed'): ?>
                                    ✓
                                <?php elseif ($step['status'] === 'active'): ?>
                                    <?= $index + 1 ?>
                                <?php else: ?>
                                    <?= $index + 1 ?>
                                <?php endif; ?>
                            </div>
                            <span style="color: #e5e7eb; font-size: 12px;"><?= $step['label'] ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Informations client et livraison -->
                <div class="info-grid">
                    <div class="info-section">
                        <h3>Informations client</h3>
                        <p><strong>Nom:</strong> <?= htmlspecialchars($client['nom_complet'] ?? 'N/A') ?>
                        </p>
                        <p><strong>Email:</strong> <?= htmlspecialchars($client['email'] ?? 'N/A') ?></p>
                        <p><strong>Téléphone:</strong>
                            <?= htmlspecialchars($client['telephone'] ?? 'N/A') ?></p>
                    </div>

                    <div class="info-section">
                        <h3>Adresse de livraison</h3>
                        <p><?= htmlspecialchars($commande['adresse_livraison'] ?? 'N/A') ?></p>
                        <p><?= htmlspecialchars($commande['ville'] ?? '') ?>,
                            <?= htmlspecialchars($commande['quartier'] ?? '') ?>
                        </p>
                        <p>Côte d'Ivoire</p>
                        <?php if (!empty($commande['note_livraison'])): ?>
                            <p><strong>Note:</strong> <?= htmlspecialchars($commande['note_livraison']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Informations de paiement -->
                <div class="info-section">
                    <h3>Informations de paiement</h3>
                    <p><strong>Méthode:</strong>
                        <?= ucfirst($commande['methode_paiement'] ?? 'Paiement à la livraison') ?></p>
                    <p><strong>Statut:</strong> <?= ucfirst($commande['statut_paiement'] ?? 'En attente') ?></p>
                    <p><strong>Montant total:</strong> <?= number_format($commande['montant_total'], 0, ',', ' ') ?>
                        FCFA</p>
                    <p><strong>Date de commande:</strong>
                        <?= date('d/m/Y à H:i', strtotime($commande['date_commande'])) ?></p>
                </div>

                <!-- Changement de statut -->
                <div class="status-form">
                    <h3>Changer le statut</h3>
                    <form method="POST" action="<?=BASE_URL?>admin/commandes/updateStatus/<?= $commande['id'] ?>">
                        <select name="statut" required>
                            <option value="">Sélectionner un statut</option>
                            <option value="en_attente" <?= $commande['statut'] === 'en_attente' ? 'selected' : '' ?>>En
                                attente</option>
                            <option value="validee" <?= $commande['statut'] === 'validee' ? 'selected' : '' ?>>Validée</option>
                            <option value="en_preparation" <?= $commande['statut'] === 'en_preparation' ? 'selected' : '' ?>>En preparation </option>
                            <option value="expediee" <?= $commande['statut'] === 'expediee' ? 'selected' : '' ?>>Expediee</option>
                            <option value="livree" <?= $commande['statut'] === 'livree' ? 'selected' : '' ?>>Livrée
                            </option>
                            <option value="annulee" <?= $commande['statut'] === 'annulee' ? 'selected' : '' ?>>Annulée
                            </option>
                        </select>
                        <textarea name="commentaire" placeholder="Commentaire (optionnel)" rows="3"></textarea>
                        <button type="submit">Mettre à jour le statut</button>
                    </form>
                </div>

                <!-- Actions -->
                <div class="actions">
                    <?php if (in_array($commande['statut'], ['en_attente', 'validee'])): ?>
                        <a href="<?=BASE_URL?>admin/commandes/annuler/<?= $commande['id'] ?>" class="btn btn-danger"
                            onclick="return confirm('Êtes-vous sûr de vouloir annuler cette commande ?')">Annuler la
                            commande</a>
                    <?php endif; ?>
                    <a href="../telecharger-facture/<?= $commande['id'] ?>" class="btn btn-primary"
                        style="color : white" target="_blank">Télécharger la facture</a>
                </div>
            </div>

            <!-- Produits commandés -->
            <div class="order-products">
                <h3>Produits commandés (<?= count($commande['produits']) ?>)</h3>
                <?php foreach ($commande['produits'] as $produit): ?>
                    <div class="product-item">
                        <img src="<?= BASE_URL ?>uploads/<?= htmlspecialchars($produit['image'] ?? 'default.jpg') ?>"
                            alt="<?= htmlspecialchars($produit['nom']) ?>" class="product-image">
                        <div style="flex: 1;">
                            <div style="font-weight: bold; color: #e5e7eb;"><?= htmlspecialchars($produit['nom']) ?></div>
                            <div style="color: #9ca3af; font-size: 14px;">Marque:
                                <?= htmlspecialchars($produit['marque'] ?? 'N/A') ?>
                            </div>
                            <div style="color: #9ca3af; font-size: 14px;">Quantité: <?= $produit['quantite'] ?></div>
                        </div>
                        <div style="text-align: right; color: #e5e7eb;">
                            <div style="font-weight: bold;"><?= number_format($produit['prix'], 0, ',', ' ') ?> FCFA</div>
                            <div style="font-size: 14px; color: #9ca3af;">Total:
                                <?= number_format($produit['prix'] * $produit['quantite'], 0, ',', ' ') ?> FCFA
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div style="border-top: 1px solid #374151; padding-top: 15px; margin-top: 15px;">
                    <div style="display: flex; justify-content: space-between; font-weight: bold; color: #e5e7eb;">
                        <span>Total:</span>
                        <span><?= number_format($commande['montant_total'], 0, ',', ' ') ?> FCFA</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/../layouts/footer_admin.php'; ?>
</body>

</html>