document.addEventListener('DOMContentLoaded', function () {
    const addToCartButtons = document.querySelectorAll('.btn-ajoutPanier');

    addToCartButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();

            const productId = this.getAttribute('data-id');
            const buttonElement = this;

            // État de chargement
            buttonElement.classList.add('adding');
            buttonElement.disabled = true;

            // Requête AJAX
            fetch('/IbTechnologie/public/panier/ajouter', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id=' + productId + '&quantite=1'
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Animation de succès
                        buttonElement.innerHTML = '✓ Ajouté';
                        buttonElement.style.background = '#28a745';

                        // Recharger le compteur du panier si disponible
                        chargerPanier();
                        ouvrirPanier();

                        setTimeout(() => {
                            buttonElement.classList.remove('adding');
                            buttonElement.disabled = false;
                            buttonElement.innerHTML = `
                            <span class="btn-text">Ajouter au panier</span>
                            <span class="btn-icon" style="display:none;">🛒</span>
                            <span class="btn-spinner"></span>
                        `;
                            buttonElement.style.background = '';
                        }, 2500);
                    } else {
                        alert('Erreur lors de l\'ajout au panier: ' + (data.message || 'Erreur inconnue'));
                        buttonElement.classList.remove('adding');
                        buttonElement.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Erreur de connexion');
                    buttonElement.classList.remove('adding');
                    buttonElement.disabled = false;
                });
        });
    });
});

const panier = document.getElementById('panier-drawer');

function ouvrirPanier() {
    drawer.classList.add('active');
    overlay.classList.add('active');
}

function chargerPanier() {
    fetch('/IbTechnologie/public/panier/contenu')
        .then(res => res.json())
        .then(data => {
            const container = document.getElementById('cart-items');
            const totalEl = document.getElementById('cart-total');
            container.innerHTML = '';
            let total = 0;
            let totalq = 0;
            const countEl = document.getElementById('badge');
            data.forEach(item => {
                const sousTotal = item.prix * item.quantite;
                total += sousTotal;
                totalq += item.quantite;
                const countEl = document.getElementById('badge');
                container.innerHTML += `
                <div class="cart-item">
                   <a href='/IbTechnologie/public/produits/${item.id}'> <img src="/IbTechnologie/public/uploads/${item.image}"></a>
                    <div class="cart-info">
                        <h4>${item.nom}</h4>
                        <div class="cart-price">
                            ${item.prix} FCFA × ${item.quantite} = 
                            <strong>${sousTotal} FCFA</strong>
                        </div>

                        <div class="cart-qty">
                            <button onclick="modifierQuantite(${item.id}, 'diminuerquantite')">−</button>
                            <span>${item.quantite}</span>
                            <button onclick="modifierQuantite(${item.id}, 'augmenterquantite')">+</button>
                        </div>
                    </div>

                    <div class="cart-remove" onclick="supprimer(${item.id})">
                        &times;
                    </div>
                </div>
            `;
            });
            if (container.textContent == '') container.innerHTML += `<p class="panier-vide">Votre panier est vide</p> `
            totalEl.textContent = total + ' FCFA';
            countEl.textContent = totalq;
            countEl.style.display = totalq > 0 ? 'flex' : 'none';
        });
    //chargerCompteurPanier() ;
}

function modifierQuantite(id, action) {
    fetch(`/IbTechnologie/public/panier/${action}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ produit_id: id })
    })
        .then(() => chargerPanier());
}
function supprimer(id) {
    fetch('/IbTechnologie/public/panier/supprimer', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ produit_id: id })
    })
        .then(() => chargerPanier());
}
chargerPanier();