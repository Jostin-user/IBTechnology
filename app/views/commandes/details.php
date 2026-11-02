<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails Commande - IB Technology</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/commande.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/header.css?v=20261102-cart">
</head>
<?php require_once __DIR__ . "/../../layouts/header.php"; ?>

<body>

    <div class="account-container">
        <main class="orders-content">
            <h1>Détails de la Commande #CMD-<?= str_pad($commande['id'], 4, '0', STR_PAD_LEFT) ?></h1>

            <div class="order-card">
                <?php if(isset($_SESSION['success'])):
                ?>
                <p class='success'><?=$_SESSION['success'] ?></p>
                <?php unset($_SESSION['success']) ; endif ?>
                <div class="order-header">
                    <div>
                        <span class="order-ref">#CMD-<?= str_pad($commande['id'], 4, '0', STR_PAD_LEFT) ?></span>
                        <span class="order-date">Passée le
                            <?= date('d M Y à H:i', strtotime($commande['date_commande'])) ?></span>
                    </div>
                    <div class="order-status status-<?= $commande['status_class'] ?>">
                        <?= $commande['status_label'] ?>
                    </div>
                </div>

                <!-- Barre de Progression (Stepper) -->
                <div class="order-tracking">
                    <?php foreach ($commande['tracking_steps'] as $index => $step): ?>
                        <div class="step <?= $step['status'] ?>">
                            <div class="circle">
                                <?php if ($step['status'] === 'completed'): ?>
                                    ✓
                                <?php elseif ($step['status'] === 'active'): ?>
                                    <?= $index + 1 ?>
                                <?php else: ?>
                                    <?= $index + 1 ?>
                                <?php endif; ?>
                            </div>
                            <span><?= $step['label'] ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Informations de livraison et facturation -->
                <div class="order-info-grid">
                    <div class="order-info-section">
                        <h3>Adresse de livraison</h3>
                        <div class="address-info">
                            <p><strong><?= htmlspecialchars($_SESSION['user_nom'] ?? 'Client') ?></strong></p>
                            <p><?= htmlspecialchars($commande['adresse_livraison'] ?? $_SESSION['user_adresse'] ?? 'Adresse non spécifiée') ?>
                            </p>
                            <p><?= htmlspecialchars($commande['ville_livraison'] ?? $_SESSION['user_ville'] ?? '') ?>
                            </p>
                            <p>Côte d'Ivoire</p>
                        </div>
                    </div>

                    <div class="order-info-section">
                        <h3>Adresse de facturation</h3>
                        <div class="address-info">
                            <p><strong><?= htmlspecialchars($_SESSION['user_nom'] ?? 'Client') ?></strong></p>
                            <p><?= htmlspecialchars($commande['adresse_facturation'] ?? $_SESSION['user_adresse'] ?? 'Adresse non spécifiée') ?>
                            </p>
                            <p><?= htmlspecialchars($commande['ville_facturation'] ?? $_SESSION['user_ville'] ?? '') ?>
                            </p>
                            <p>Côte d'Ivoire</p>
                        </div>
                    </div>
                </div>

                <!-- Informations de paiement -->
                <div class="order-info">
                    <h3>Informations de paiement</h3>
                    <div class="payment-info">
                        <div class="info-row">
                            <span>Méthode de paiement:</span>
                            <span><?= ucfirst($commande['methode_paiement'] ?? 'Paiement à la livraison') ?></span>
                        </div>
                        <div class="info-row">
                            <span>Statut du paiement:</span>
                            <span class="payment-status <?= $commande['statut_paiement'] ?? 'en_attente' ?>">
                                <?= ucfirst($commande['statut_paiement'] ?? 'En attente') ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Produits commandés -->
                <div class="order-products">
                    <h3>Produits commandés</h3>
                    <div class="products-list">
                        <?php foreach ($commande['produits'] as $produit): ?>
                            <div class="product-item">
                                <img src="<?= BASE_URL ?>uploads/<?= htmlspecialchars($produit['image'] ?? 'default.jpg') ?>"
                                    alt="<?= htmlspecialchars($produit['nom']) ?>" class="product-image">
                                <div class="product-details">
                                    <h4><?= htmlspecialchars($produit['nom']) ?></h4>
                                    <p class="product-brand">Marque: <?= htmlspecialchars($produit['marque'] ?? 'N/A') ?>
                                    </p>
                                    <p class="product-quantity">Quantité: <?= $produit['quantite'] ?></p>
                                    <p class="product-price">Prix unitaire:
                                        <?= number_format($produit['prix'], 0, ',', ' ') ?> FCFA</p>
                                    <p class="product-total">Sous-total:
                                        <?= number_format($produit['prix'] * $produit['quantite'], 0, ',', ' ') ?> FCFA</p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Résumé de la commande -->
                <div class="order-summary">
                    <h3>Résumé de la commande</h3>
                    <div class="summary-details">
                        <div class="summary-row">
                            <span>Sous-total:</span>
                            <span><?= number_format($commande['montant_total'] * 0.85, 0, ',', ' ') ?> FCFA</span>
                        </div>
                        <div class="summary-row">
                            <span>Frais de livraison:</span>
                            <span>Gratuit</span>
                        </div>
                        <div class="summary-row">
                            <span>TVA (18%):</span>
                            <span><?= number_format($commande['montant_total'] * 0.15, 0, ',', ' ') ?> FCFA</span>
                        </div>
                        <div class="summary-row total">
                            <span><strong>Total:</strong></span>
                            <span><strong><?= number_format($commande['montant_total'], 0, ',', ' ') ?>
                                    FCFA</strong></span>
                        </div>
                    </div>
                </div>

                <!-- Chronologie de la commande -->
                <div class="order-timeline">
                    <h3>Chronologie de la commande</h3>
                    <div class="timeline">
                        <?php
                        $events = [
                            ['date' => $commande['date_commande'], 'event' => 'Commande passée', 'icon' => '📦'],
                            ['date' => date('Y-m-d H:i:s', strtotime($commande['date_commande'] . ' +1 day')), 'event' => 'Commande confirmée', 'icon' => '✅'],
                            ['date' => date('Y-m-d H:i:s', strtotime($commande['date_commande'] . ' +2 days')), 'event' => 'Préparation en cours', 'icon' => '🔄'],
                            ['date' => date('Y-m-d H:i:s', strtotime($commande['date_commande'] . ' +3 days')), 'event' => 'Expédiée', 'icon' => '🚚'],
                            ['date' => date('Y-m-d H:i:s', strtotime($commande['date_commande'] . ' +5 days')), 'event' => 'Livrée', 'icon' => '🏠']
                        ];

                        $currentStatusIndex = [
                            'en_attente' => 0,
                            'validee' => 1,
                            'en_livraison' => 3,
                            'livree' => 4
                        ][$commande['statut']] ?? 0;

                        foreach ($events as $index => $event):
                            $isCompleted = $index <= $currentStatusIndex;
                            $isCurrent = $index === $currentStatusIndex;
                            ?>
                            <div
                                class="timeline-item <?= $isCompleted ? 'completed' : ($isCurrent ? 'current' : 'pending') ?>">
                                <div class="timeline-icon">
                                    <?= $event['icon'] ?>
                                </div>
                                <div class="timeline-content">
                                    <h4><?= $event['event'] ?></h4>
                                    <?php if ($isCompleted || $isCurrent): ?>
                                        <p class="timeline-date"><?= date('d M Y à H:i', strtotime($event['date'])) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="order-actions">
                    <a href="<?=BASE_URL ;?>commandes" class="btn-detail">← Retour aux commandes</a>
                    <?php if ($commande['statut'] === 'livree'): ?>
                        <button class="btn-review" onclick="openReviewModal()">⭐ Noter les produits</button>
                        <button class="btn-reorder" onclick="reorderCommande(<?= $commande['id'] ?>)">🛒 Commander à
                            nouveau</button>
                    <?php elseif (in_array($commande['statut'], ['en_attente', 'validee'])): ?>
                        <button class="btn-cancel" onclick="confirmerAnnulation(<?= $commande['id'] ?>)">Annuler la
                            commande</button>
                    <?php endif; ?>
                    <a href="../telecharger-facture/<?= $commande['id'] ?>" class="btn-download">📄 Télécharger
                        la facture</a>
                    <a href="https://wa.me/225XXXXXXXX" class="btn-whatsapp">
                        <img src="<?= BASE_URL ?>assets/images/whatsapp-icon.png" width="18" alt=""> Contacter
                        le support
                    </a>
                </div>
            </div>
        </main>
    </div>

    <script>
        function confirmerAnnulation(commandeId) {
            if (confirm('Êtes-vous sûr de vouloir annuler cette commande ? Cette action est irréversible.')) {
                window.location.href = 'commandes/annuler/' + commandeId;
            }
        }

        function reorderCommande(commandeId) {
            if (confirm('Voulez-vous ajouter ces produits à votre panier ?')) {
                // Ici, on pourrait faire un appel AJAX pour ajouter les produits au panier
                window.location.href = 'panier/ajouter-commande/' + commandeId;
            }
        }

        function openReviewModal() {
            // Pour l'instant, on redirige vers une page de notation
            window.location.href = '../noter/' + <?= $commande['id'] ?>;
        }
    </script>

    <?php require_once __DIR__ . "/../../layouts/footer.php"; ?>
</body>

</html>