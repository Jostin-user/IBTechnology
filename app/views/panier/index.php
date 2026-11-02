<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IB Technology | Panier</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/panier.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/header.css?v=20261102-cart">
</head>

<body>
    <?php
    require_once __DIR__ . '/../../layouts/header.php';
    ?>

    <style>
        body {
            background-color: #010101;
        }
    </style>

    <div class="cart-container">
        <div class="cart-header">
            <h1>Votre Panier</h1>
            <p><?php echo count($produits); ?> article(s) dans votre panier</p>
        </div>

        <?php if (empty($produits)): ?>
            <div class="empty-cart">
                <div class="empty-cart-icon">🛒</div>
                <h2>Votre panier est vide</h2>
                <p>Découvrez nos produits et commencez vos achats !</p>
                <a href="<?php echo BASE_URL; ?>boutique" class="btn-continue-shopping">
                    Continuer mes achats
                </a>
            </div>
        <?php else: ?>
            <div class="cart-content">
                <!-- Cart Items -->
                <div class="cart-itemes">
                    <?php foreach ($produits as $item): ?>
                        <div class="cart-iteme" data-produit-id="<?php echo $item['id']; ?>">
                            <img src="<?= BASE_URL ?>uploads/<?= $item['image']; ?>"
                                alt="<?php echo htmlspecialchars($item['nom']); ?>" class="cart-item-image">

                            <div class="cart-item-details">
                                <div>
                                    <a href="<?=BASE_URL ?>produits/<?php echo $item['id']; ?>"
                                        class="cart-item-title">
                                        <?php echo htmlspecialchars($item['nom']); ?>
                                    </a>
                                    <div class="cart-item-price">
                                        <?php echo number_format($item['prix'], 0, ',', ' '); ?> FCFA
                                    </div>
                                </div>

                                <div class="quantity-controls">
                                    <button class="quantity-btn decrease-qty"
                                        data-produit-id="<?php echo $item['id']; ?>">-</button>
                                    <input type="number" class="quantity-input" value="<?php echo $item['quantite']; ?>" min="1"
                                        max="<?php echo $item['details']['stock']; ?>" readonly>
                                    <button class="quantity-btn increase-qty"
                                        data-produit-id="<?php echo $item['id']; ?>">+</button>
                                </div>

                                <button class="remove-btn" data-produit-id="<?php echo $item['id']; ?>">
                                    Retirer du panier
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Cart Summary -->
                <div class="cart-summary">
                    <h3 class="summary-title">Récapitulatif de la commande</h3>

                    <div class="summary-row">
                        <span>Sous-total (<?php echo count($produits); ?> articles)</span>
                        <span class="amount"><?php echo number_format($total, 0, ',', ' '); ?> FCFA</span>
                    </div>

                    <div class="summary-row">
                        <span>Livraison</span>
                        <span class="amount">Calculée à l'étape suivante</span>
                    </div>

                    <div class="summary-row total">
                        <span>Total</span>
                        <span class="amount"><?php echo number_format($total, 0, ',', ' '); ?> FCFA</span>
                    </div>

                    <a href="<?= BASE_URL ?>commandes/valider"><button class="btn-commander" id="btn-commander">
                        Passer la commande
                    </button></a>

                    <div class="shipping-notice">
                        <strong>Livraison gratuite</strong> dès 50 000 FCFA d'achat
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Cart functionality
        document.addEventListener('DOMContentLoaded', function () {
            // Quantity controls
            document.querySelectorAll('.increase-qty').forEach(btn => {
                btn.addEventListener('click', function () {
                    const produitId = this.dataset.produitId;
                    updateQuantity(produitId, 1);
                });
            });

            document.querySelectorAll('.decrease-qty').forEach(btn => {
                btn.addEventListener('click', function () {
                    const produitId = this.dataset.produitId;
                    updateQuantity(produitId, -1);
                });
            });

            // Remove item
            document.querySelectorAll('.remove-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const produitId = this.dataset.produitId;
                    removeItem(produitId);
                });
            });

            // Checkout button
            const checkoutBtn = document.getElementById('checkout-btn');
            if (checkoutBtn) {
                checkoutBtn.addEventListener('click', function () {
                    // Redirect to checkout or show login if not authenticated
                    window.location.href = '<?=BASE_URL?>commandes';
                });
            }
        });

        function updateQuantity(produitId, change) {
            const item = document.querySelector(`[data-produit-id="${produitId}"]`);
            const input = item.querySelector('.quantity-input');
            const currentQty = parseInt(input.value);

            let newQty = currentQty + change;
            if (newQty < 1) newQty = 1;
            if (newQty > parseInt(input.max)) newQty = parseInt(input.max);

            if (newQty !== currentQty) {
                item.classList.add('loading');

                fetch('<?=BASE_URL?>panier/' + (change > 0 ? 'augmenterquantite' : 'diminuerquantite'), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ produit_id: produitId })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            input.value = newQty;
                            location.reload(); // Reload to update totals
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        item.classList.remove('loading');
                    });
            }
        }

        function removeItem(produitId) {
            const item = document.querySelector(`[data-produit-id="${produitId}"]`);
            item.classList.add('loading');

            fetch('<?=BASE_URL?>panier/supprimer', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ produit_id: produitId })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload(); // Reload to update cart
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    item.classList.remove('loading');
                });
        }
    </script>

    <?php
    require_once __DIR__ . '/../../layouts/footer.php';
    ?>

</body>

</html>