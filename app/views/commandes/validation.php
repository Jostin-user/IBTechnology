<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IB Technology | Validation de commande</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/commande.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/header.css?v=20261102-cart">
</head>

<body>
    <?php
    require_once __DIR__ . '/../../layouts/header.php';
    ?>
    <div class="checkout-container">
        <div class="checkout-header">
            <h1>Finalisez votre commande</h1>
            <p>Veuillez compléter les informations ci-dessous pour procéder a la commande</p>
        </div>

        <div class="checkout-steps">
            <div class="step active">
                <div class="step-circle">1</div>
                <div class="step-label">Panier</div>
            </div>
            <div class="step active">
                <div class="step-circle">2</div>
                <div class="step-label">Livraison</div>
            </div>
            <div class="step active">
                <div class="step-circle">3</div>
                <div class="step-label">Paiement</div>
            </div>
            <div class="step">
                <div class="step-circle">4</div>
                <div class="step-label">Confirmation</div>
            </div>
        </div>

        <form method="POST" action="<?php echo BASE_URL; ?>commandes" class="checkout-form-wrapper">
            <div class="checkout-content">
                <div>
                    <div class="checkout-form">
                        <!-- Adresse de Livraison -->
                        <div class="form-section">
                            <h2 class="form-section-title">📍 Adresse de Livraison</h2>

                            <div class="form-group">
                                <label for="nom">Nom complet *</label>
                                <input type="text" id="nom" name="nom" required
                                    value="<?php echo htmlspecialchars($_SESSION['user_nom'] ?? ''); ?>"
                                    placeholder="Votre nom complet">
                            </div>

                            <div class="form-group">
                                <label for="email">Adresse email *</label>
                                <input type="email" id="email" name="email" required
                                    value="<?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?>"
                                    placeholder="votre.email@exemple.com">
                            </div>

                            <div class="form-group">
                                <label for="telephone">Téléphone *</label>
                                <input type="tel" id="telephone" name="telephone" required
                                    placeholder="Votre numéro de téléphone">
                            </div>
                            <div class="form-group">
                                <label for="pays">Pays *</label>
                                <select id="pays" name="pays" required>
                                    <option value="cote_ivoire" selected>Côte d'Ivoire</option>
                                </select>
                            </div>


                            <div class="form-group">
                                <label for="adresse">Région *</label>
                                <select name="region" id="region" required>
                                    <option value="">Selectionnez votre region</option>

                                </select>
                            </div>

                            <div class="form-group">
                                <label for="adresse">Adresse complète *</label>
                                <input type="text" id="adresse" name="adresse" required
                                    value="<?= htmlspecialchars($_SESSION['user_adresse'] ?? '') ?>"
                                    placeholder="Numéro, rue, immeuble...">
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="ville">Ville *</label>
                                    <select name="ville" id="ville" required>
                                        <option value="">Selectionner une ville</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="quartier">Quartier</label>
                                    <select name="quartier" id="quartier" required>
                                        <option value="">Selectionner quartier</option>
                                    </select>
                                        
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="note_livraison">Note de livraison (optionnel)</label>
                                <textarea id="note_livraison" name="note_livraison"
                                    placeholder="Instructions spéciales pour la livraison..."></textarea>
                            </div>
                        </div>

                        <!-- Mode de Livraison -->
                        <div class="form-section">
                            <h2 class="form-section-title">🚚 Mode de Livraison</h2>

                            <div class="shipping-options">
                                <label class="shipping-option selected">
                                    <input type="radio" name="methode_livraison" value="standard" checked>
                                    <div class="shipping-info">
                                        <div class="shipping-name">Livraison Standard</div>
                                        <div class="shipping-details">Délai: 3-5 jours ouvrables</div>
                                    </div>
                                    <div class="shipping-price">0 FCFA</div>
                                </label>
                            </div>
                        </div>

                        <!-- Mode de Paiement -->
                        <div class="form-section">
                            <h2 class="form-section-title">💳 Mode de Paiement</h2>

                            <div class="payment-options">
                                <label class="payment-option selected">
                                    <input type="radio" name="methode_paiement" value="a_la_livraison" checked>
                                    <div class="payment-info">
                                        <div class="payment-name">À la Livraison</div>
                                        <div class="payment-description">Paiement à la réception du colis (Cash)</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- CGU -->
                        <div class="form-section">
                            <div class="form-group">
                                <label style="display: flex; align-items: flex-start; gap: 10px; margin-bottom: 0;">
                                    <input type="checkbox" name="cgu" id="cgu" required
                                        style="width: auto; margin-top: 3px;">
                                    <span>J'accepte les <strong>conditions générales</strong> et la <strong>politique de
                                            confidentialité</strong> *</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Récapitulatif -->
                <div class="checkout-summary">
                    <h3 class="summary-title">📦 Votre Commande</h3>

                    <div class="summary-items">
                        <?php
                        $panier = $_SESSION['panier'] ?? [];
                        $montant_total = 0;

                        foreach ($panier as $item):
                            $sous_total = $item['prix'] * $item['quantite'];
                            $montant_total += $sous_total;
                            ?>
                            <div class="summary-item">
                                <div>
                                    <div class="summary-item-name"><?php echo htmlspecialchars($item['nom']); ?></div>
                                    <div class="summary-item-qty">x<?php echo $item['quantite']; ?></div>
                                </div>
                                <div class="summary-item-price">
                                    <?php echo number_format($sous_total, 0, ',', ' '); ?> FCFA
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="summary-totals">
                        <div class="summary-row">
                            <span>Sous-total</span>
                            <span><?php echo number_format($montant_total, 0, ',', ' '); ?> FCFA</span>
                        </div>

                        <div class="summary-row">
                            <span>Livraison</span>
                            <span id="shipping-cost">0 </span>
                        </div>

                        <div class="summary-row total">
                            <span>Total</span>
                            <span class="amount" id="total-amount">
                                <?php echo number_format($montant_total, 0, ',', ' '); ?> FCFA
                            </span>
                        </div>
                    </div>

                    <button type="submit" class="btn-confirm" name="valider_commande">
                        ✓ Confirmer la Commande
                    </button>

                    <a href="<?php echo BASE_URL; ?>panier" class="btn-back">
                        ← Retour au panier
                    </a>

                    <div class="alert info">
                        <span>🔒</span>
                        <span>Vos données sont sécurisées</span>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const shippingRadios = document.querySelectorAll('input[name="methode_livraison"]');
            const shippingOptions = document.querySelectorAll('.shipping-option');
            const shippingCostElement = document.getElementById('shipping-cost');
            const totalAmountElement = document.getElementById('total-amount');
            let zoneDeliveryCost = 0;

            const regionSelect = document.getElementById('region');
            const villeSelect = document.getElementById('ville');
            const quartierSelect = document.getElementById('quartier');

            function resetSelect(select, placeholder) {
                select.innerHTML = '';
                const option = document.createElement('option');
                option.value = '';
                option.textContent = placeholder;
                select.appendChild(option);
            }

            async function loadZones(url, select, placeholder, valueField = 'id', labelField = 'nom') {
                try {
                    const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
                    if (!response.ok) throw new Error('Erreur HTTP ' + response.status);
                    const data = await response.json();
                    resetSelect(select, placeholder);
                    data.forEach(zone => {
                        const option = document.createElement('option');
                        option.value = zone[valueField];
                        option.textContent = zone[labelField];
                        option.dataset.frais = zone.frais_livraison || 0;
                        select.appendChild(option);
                    });
                } catch (error) {
                    console.error('Chargement des zones:', error);
                    resetSelect(select, placeholder);
                }
            }

            loadZones('<?= BASE_URL ?>api/livraison/regions', regionSelect, 'Sélectionnez votre région');

            regionSelect.addEventListener('change', function () {
                zoneDeliveryCost = 0;
                resetSelect(villeSelect, 'Sélectionnez une ville');
                resetSelect(quartierSelect, 'Sélectionnez un quartier');
                villeSelect.disabled = true;
                quartierSelect.disabled = true;

                const selected = this.options[this.selectedIndex];
                if (selected && selected.dataset.frais) {
                    zoneDeliveryCost = Number(selected.dataset.frais) || 0;
                }
                recalcTotal(currentShippingMethodCost());

                if (!this.value) return;
                villeSelect.disabled = false;
                loadZones('<?= BASE_URL ?>api/livraison/villes?region_id=' + encodeURIComponent(this.value), villeSelect, 'Sélectionnez une ville');
            });

            villeSelect.addEventListener('change', function () {
                resetSelect(quartierSelect, 'Sélectionnez un quartier');
                quartierSelect.disabled = true;

                const selected = this.options[this.selectedIndex];
                if (selected && selected.dataset.frais) {
                    zoneDeliveryCost = Number(selected.dataset.frais) || zoneDeliveryCost;
                    recalcTotal(currentShippingMethodCost());
                }
                if (!this.value) return;
                quartierSelect.disabled = false;
                loadZones('<?= BASE_URL ?>api/livraison/quartiers?ville_id=' + encodeURIComponent(this.value), quartierSelect, 'Sélectionnez un quartier');
            });

            quartierSelect.addEventListener('change', function () {
                const selected = this.options[this.selectedIndex];
                if (selected && selected.dataset.frais) {
                    zoneDeliveryCost = Number(selected.dataset.frais) || zoneDeliveryCost;
                    recalcTotal(currentShippingMethodCost());
                }
            });

            // Subtotal from PHP
            const subtotal = Number(<?php echo json_encode($montant_total); ?>) || 0;
            const taxRate = 0; // no tax per specification

            function formatFCFA(amount) {
                return amount.toLocaleString('fr-FR') + ' FCFA';
            }

            function currentShippingMethodCost() {
                const checked = document.querySelector('input[name="methode_livraison"]:checked');
                if (!checked) return 0;
                if (checked.value === 'express') return 5000;
                if (checked.value === 'ultrarapid') return 10000;
                return 0;
            }

            function recalcTotal(methodShippingCost) {
                const tax = Math.round(subtotal * taxRate);
                const shippingCost = Number(methodShippingCost || 0) + Number(zoneDeliveryCost || 0);
                const total = subtotal + tax + shippingCost;
                shippingCostElement.textContent = formatFCFA(shippingCost);
                // Update tax display if you want (tax is 0)
                totalAmountElement.textContent = formatFCFA(total);
            }

            // Initialize with current selection
            (function initShipping() {
                const checked = document.querySelector('input[name="methode_livraison"]:checked');
                let cost = 0;
                if (checked) {
                    if (checked.value === 'express') cost = 5000;
                    else if (checked.value === 'ultrarapid') cost = 10000;
                }
                recalcTotal(cost);
            })();

            shippingRadios.forEach(radio => {
                radio.addEventListener('change', function () {
                    shippingOptions.forEach(option => option.classList.remove('selected'));
                    this.closest('.shipping-option').classList.add('selected');

                    let shippingCost = 0;
                    if (this.value === 'express') shippingCost = 5000;
                    else if (this.value === 'ultrarapid') shippingCost = 10000;

                    recalcTotal(shippingCost);
                });
            });

            const paymentRadios = document.querySelectorAll('input[name="methode_paiement"]');
            const paymentOptions = document.querySelectorAll('.payment-option');

            paymentRadios.forEach(radio => {
                radio.addEventListener('change', function () {
                    paymentOptions.forEach(option => option.classList.remove('selected'));
                    this.closest('.payment-option').classList.add('selected');
                });
            });

            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function (e) {
                    const cgu = document.getElementById('cgu');
                    if (!cgu.checked) {
                        e.preventDefault();
                        alert('Veuillez accepter les conditions générales');
                        cgu.focus();
                        return;
                    }

                    // Before submit, append calculated amounts as hidden inputs
                    const checkedShipping = document.querySelector('input[name="methode_livraison"]:checked');
                    let shippingCost = 0;
                    if (checkedShipping) {
                        if (checkedShipping.value === 'express') shippingCost = 5000;
                        else if (checkedShipping.value === 'ultrarapid') shippingCost = 10000;
                    }

                    shippingCost += Number(zoneDeliveryCost || 0);
                    const existingShip = document.querySelector('input[name="shipping_cost"]');
                    if (existingShip) existingShip.value = shippingCost;
                    else {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'shipping_cost';
                        input.value = shippingCost;
                        form.appendChild(input);
                    }

                    const existingSubtotal = document.querySelector('input[name="subtotal"]');
                    if (existingSubtotal) existingSubtotal.value = subtotal;
                    else {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'subtotal';
                        input.value = subtotal;
                        form.appendChild(input);
                    }
                });
            }
        });
    </script>

    <?php
    require_once __DIR__ . '/../../layouts/footer.php';
    ?>

</body>

</html>