<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Commandes - IB Technology</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/header.css?v=20261102-cart">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/commande.css">
</head>

<body>
    <?php require_once __DIR__ . "/../../layouts/header.php"; ?>

        <!-- État vide (si aucune commande) -->
        <!-- <div class="empty-orders">
            <p>Vous n'avez pas encore passé de commande.</p>
            <a href="/boutique" class="btn-shop">Commencer mes achats</a>
        </div> -->
        <div class="account-container">
            <main class="orders-content" style="margin: auto;">
                <h1>Mes Commandes</h1>

            <?php if (empty($commandes)): ?>
                <div class="empty-orders">
                    <p>Vous n'avez pas encore passé de commande.</p>
                    <a href="boutique" class="btn-shop">Commencer mes achats</a>
                </div>
            <?php else: ?>
                <?php foreach ($commandes as $commande): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div>
                                <span class="order-ref">#CMD-<?= str_pad($commande['id'], 4, '0', STR_PAD_LEFT) ?></span>
                                <span class="order-date">Passée le <?= date('d M Y', strtotime($commande['date_commande'])) ?></span>
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

                        <div class="order-body">
                            <div class="order-items-preview">
                                <?php
                                $produits = array_slice($commande['produits'], 0, 2);
                                foreach ($produits as $produit):
                                ?>
                                    <img src="<?= BASE_URL ?>uploads/<?= htmlspecialchars($produit['image'] ?? 'default.jpg') ?>"
                                         alt="<?= htmlspecialchars($produit['nom']) ?>">
                                <?php endforeach; ?>
                                <?php if (count($commande['produits']) > 2): ?>
                                    <span class="more-items">+<?= count($commande['produits']) - 2 ?> autre(s) article(s)</span>
                                <?php endif; ?>
                            </div>
                            <div class="order-actions">
                                <a href="commandes/details/<?= $commande['id'] ?>" class="btn-detail">Voir les détails</a>
                                <a href="https://wa.me/2250779302210" class="btn-whatsapp">
                                    <img src="<?= BASE_URL ?>assets/images/whatsapp-icon.png" width="18" alt=""> Suivre sur WhatsApp
                                </a>
                            </div>
                        </div>

                        <div class="order-actions">
                            <?php if ($commande['statut'] === 'livree'): ?>
                                <button class="btn-review" onclick="openReviewModal()">⭐ Noter le produit</button>
                            <?php elseif (in_array($commande['statut'], ['en_attente', 'validee'])): ?>
                                <button class="btn-cancel" onclick="confirmerAnnulation(<?= $commande['id'] ?>)">Annuler la commande</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </main>
    </div>

    <script>
        function confirmerAnnulation(commandeId) {
            if (confirm('Êtes-vous sûr de vouloir annuler cette commande ?')) {
                window.location.href = 'commandes/annuler/' + commandeId;
            }
        }
        function openReviewModal() {
            // Pour l'instant, on redirige vers une page de notation
            window.location.href = 'commandes/noter/' + <?= $commande['id'] ?>;
        }
    </script>

    <?php require_once __DIR__ . "/../../layouts/footer.php"; ?>
</body>

</html>