
<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Categorie.php';
$db = (new Database())->getConnexion();
$categorieModel = new Categorie($db);
$categories = $categorieModel->getAllParentActive();
?>
<link rel="stylesheet" href="<?=BASE_URL?>assets/css/footer.css">

<footer class="footer">
    <div class="container footer-grid">
        <div class="brand">
            <div class="logo">IB Technologie</div>
            <p>Votre boutique tech — téléphones, ordinateurs, accessoires et gadgets à prix compétitifs.</p>
            <small>© <?=date("Y");?> IB Technology — Tous droits réservés</small>
        </div>

        <div class="links" id="footer-links">
            <h4>
                Liens utiles
                <button class="toggle-btn" aria-expanded="false" aria-controls="list-links" onclick="toggleFooterSection('links')">☰</button>
            </h4>
            <div class="list" id="list-links">
                <a href="<?= BASE_URL ?>accueil">Accueil</a>
                <a href="<?= BASE_URL ?>boutique">Boutique</a>
                <a href="<?= BASE_URL ?>panier">Panier</a>
                <a href="<?= BASE_URL ?>commandes">Mes commandes</a>
                <a href="<?= BASE_URL ?>notifications">Notifications</a>
            </div>
        </div>

        <div class="categories" id="footer-categories">
            <h4>
                Catégories
                <button class="toggle-btn" aria-expanded="false" aria-controls="list-categories" onclick="toggleFooterSection('categories')">☰</button>
            </h4>
            <div class="list" id="list-categories">
                <?php foreach ($categories as $cat): ?>
                    <a href="<?= BASE_URL ?>boutique?categorie=<?= htmlspecialchars($cat['id']) ?>"><?= htmlspecialchars($cat['nom']) ?></a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="contact" id="footer-contact">
            <h4>
                Contact & Newsletter
                <button class="toggle-btn" aria-expanded="false" aria-controls="list-contact" onclick="toggleFooterSection('contact')">☰</button>
            </h4>
            <div class="list" id="list-contact">
                <p>Email: <a href="mailto:contact@ibtechnology.local">contact@ibtechnology.local</a></p>
                <p>Téléphone: +225 770 00 00</p>
                <p>Whatsapp : +225 000 00 00 00</p>

                <form id="newsletter-form">
                    <input type="email" name="email" placeholder="Votre adresse email" required>
                    <button class="btn" type="submit">S'abonner</button>
                </form>

                <div class="social" style="margin-top:10px;">
                    <a href="#" aria-label="Facebook">F</a>
                    <a href="#" aria-label="Twitter">T</a>
                    <a href="#" aria-label="Instagram">I</a>
                </div>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <a href="#">CGV</a> | <a href="#">Mentions légales</a> | <a href="#">Politique de confidentialité</a>
    </div>
</footer>

<script>
// Small helper: accordion toggles for footer sections on mobile
function toggleFooterSection(name){
    var el = document.getElementById('footer-' + name);
    var isOpen = el.classList.contains('open');
    el.classList.toggle('open');
    var btn = el.querySelector('.toggle-btn');
    if(btn) btn.setAttribute('aria-expanded', String(!isOpen));
}

// Newsletter: simulated submission (replace with API call server-side if desired)
document.addEventListener('DOMContentLoaded', function(){
    var form = document.getElementById('newsletter-form');
    if(form){
        form.addEventListener('submit', function(e){
            e.preventDefault();
            var email = this.email.value;
            if(!email) return alert('Veuillez entrer une adresse email valide.');
            this.reset();
            alert('Merci! Vous êtes inscrit à la newsletter.');
        });
    }
});
</script>
