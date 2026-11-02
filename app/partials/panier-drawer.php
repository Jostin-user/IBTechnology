<!-- Overlay -->
<div id="panier-overlay"></div>

<!-- Panier Drawer -->
<div id="panier-drawer">
    <div class="panier-header">
        <h3>Votre panier</h3>
        <button id="close-panier">✕</button>
    </div>

    <div id="cart-items" class="cart-items">

    </div>

    <div class="cart-footer">
        <div class="cart-total">
            <span>Total</span>
            <strong id="cart-total">0 FCFA</strong>
        </div>

        <a href="<?= BASE_URL ?>commandes/valider" class="btn-checkout">
            Commander
        </a>
    </div>
</div>

<script>
    const btnPanier = document.getElementById('btn-panier');
    const drawer = document.getElementById('panier-drawer');
    const overlay = document.getElementById('panier-overlay');
    const closeBtn = document.getElementById('close-panier');

    btnPanier.addEventListener('click', () => {
        drawer.classList.add('active');
        overlay.classList.add('active');
    });

    closeBtn.addEventListener('click', closePanier);
    overlay.addEventListener('click', closePanier);

    function closePanier() {
        drawer.classList.remove('active');
        overlay.classList.remove('active');
    }

</script>