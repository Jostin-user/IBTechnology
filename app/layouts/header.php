<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Categorie.php';

$db = (new Database())->getConnexion();
$categorieModel = new Categorie($db);
$categories = $categorieModel->getAllParentActive();

function renderCategoriesLinks($nodes)
{
    foreach ($nodes as $cat) {
        $url = BASE_URL . "boutique/" . htmlspecialchars($cat['slug']);
        echo '<a href="' . $url . '">' . htmlspecialchars($cat['nom']) . '</a>';
    }
}
?>
<header class="main-header">
    <div class="header-container">

        <div class="hamburger" onclick="toggleMenu()">☰</div>

        <!-- Overlay -->
        <div class="overlay" id="overlay" onclick="toggleMenu()"></div>


        <!-- Menu latéral -->
        <nav class="side-menu" id="sideMenu">

            <!-- Onglets -->
            <div class="menu-tabs">
                <button class="tab active" onclick="openTab('menu')">Menu</button>
                <button class="tab" onclick="openTab('categories')">Catégories</button>
            </div>

            <!-- Contenu CATEGORIES -->
            <div class="tab-content" id="categories">
                <?php renderCategoriesLinks($categories); ?>
            </div>

            <!-- Contenu MENU -->
            <div class="tab-content active" id="menu">
                <a href="<?= BASE_URL ?>accueil">🏠 Accueil</a>
                <a href="<?= BASE_URL ?>boutique">🏬 Boutique</a>
                <a href="<?= BASE_URL ?>panier">🛒 Mon panier</a>
                <?php
                if (isset($_SESSION['user_id'])): ?>
                    <a href="<?= BASE_URL ?>commandes">📦 Mes commandes</a>
                    <a href="<?= BASE_URL ?>notifications">🔔 Notification</a>
                    <a href="<?= BASE_URL ?>compte">⚙️ Mon compte ></a>
                    <a href="<?= BASE_URL ?>aide">Aide & Contact</a><br>
                    <a href="<?= BASE_URL ?>logout" style='color : red ;'>Deconnexion</a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>login">👤 Connexion</a>
                    <a href="<?= BASE_URL ?>aide">Aide & Contact</a>
                <?php endif; ?>

            </div>


        </nav>
        <div class="overlay" id="overlay"></div>

        <!-- Logo -->
        <div class="logo">
            <img src="<?= BASE_URL ?>assets/images/logo.jpg" style=" display: none ;">
            <span>IB Technology</span>
        </div>

        <nav class="desktop-nav" aria-label="Navigation principale">
            <a href="<?= BASE_URL ?>accueil">Accueil</a>
            <a href="<?= BASE_URL ?>boutique">Boutique</a>
            <a href="<?= BASE_URL ?>boutique?categorie=accessoires">Catégories</a>
            <a href="<?= BASE_URL ?>aide">Contact</a>
        </nav>

        <!-- Actions -->
        <div class="header-actions">

            <!-- Recherche -->
            <button class="search" id="open-search" type="button" aria-label="Ouvrir la recherche">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <circle cx="11" cy="11" r="7"></circle>
                    <path d="m20 20-3.8-3.8"></path>
                </svg>
            </button>
            <!-- SEARCH BAR -->
            <div id="search-barh" class="search-barh">
                <div class="search-container">
                    <form action="<?= BASE_URL ?>boutique" method="GET">
                        <input type="text" name="q" id="search-input" placeholder="Produit, marque, catégorie..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"
                            autocomplete="off">
                        <button type="button" id="close-search" aria-label="Fermer la recherche">✕</button>
                        <button type="submit" class="search-button">Rechercher</button>
                    </form>
                </div>
                <div id="search-results"></div>

            </div>
            <script>
                const openSearch = document.getElementById('open-search');
                const searchBar = document.getElementById('search-barh');
                const closeSearch = document.getElementById('close-search');
                const input = document.getElementById('search-input');

                openSearch.addEventListener('click', () => {
                    searchBar.style.display = 'block';
                    input.focus();
                });

                closeSearch.addEventListener('click', () => {
                    searchBar.style.display = 'none';
                });

                document.addEventListener('click', (e) => {
                    if (!searchBar.contains(e.target) &&
                        !openSearch.contains(e.target)) {
                        searchBar.style.display = 'none';
                    }
                });


                const searchInput = document.getElementById('search-input');
                const resultsBox = document.getElementById('search-results');

                searchInput.addEventListener('keyup', function () {

                    const query = this.value.trim();

                    if (query.length < 2) {
                        resultsBox.style.display = 'none';
                        return;
                    }

                        fetch(`<?=BASE_URL?>search/suggestions?q=${encodeURIComponent(query)}`)
                            .then(res => res.json())
                            .then(data => {
                                resultsBox.innerHTML = '';

                                if (data.length === 0) {
                                    resultsBox.style.display = 'none';
                                    return;
                                }

                                data.forEach(itemData => {
                                    const item = document.createElement('div');
                                    item.classList.add('search-item');

                                    // determine image and title
                                    const imgSrc = itemData.image ? ('<?= BASE_URL ?>uploads/' + itemData.image) : null;
                                    const title = itemData.title || '';
                                    const subtitle = itemData.subtitle || '';
                                    let target = itemData.url_public || itemData.url_admin || '#';

                                    let html = '';
                                    if (imgSrc) html += `<img src="${imgSrc}" />`;
                                    html += `<div><strong style="color:#fff">${escapeHtml(title)}</strong>`;
                                    if (subtitle) html += `<div style="color:#9ca3af;font-size:0.9rem">${escapeHtml(subtitle)}</div>`;
                                    html += `</div>`;

                                    item.innerHTML = html;

                                    item.addEventListener('click', () => {
                                        // if relative url, prefix base
                                        if (!/^https?:\/\//i.test(target) && target.indexOf('/') === 0) {
                                            window.location.href = target;
                                        } else if (!/^https?:\/\//i.test(target)) {
                                            window.location.href = '<?= BASE_URL ?>' + target;
                                        } else {
                                            window.location.href = target;
                                        }
                                    });

                                    resultsBox.appendChild(item);
                                });

                                resultsBox.style.display = 'block';
                            }).catch(err => { console.error(err); });
                });
                function escapeHtml(s){ if(!s) return ''; return String(s).replace(/[&<>"']/g, function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"}[c];}); }
            </script>


            <!-- Panier -->
            <div class="cart">
                <span class="cart-btn" id="btn-panier">🛒</span>
                <span class="badge" id="badge"></span>
            </div>

            <!-- Notifications -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php require_once __DIR__ . '/../partials/notification-bell.php'; ?>
            <?php endif; ?>
            <!-- Compte -->
            <?php if (!isset($_SESSION['user_id'])): ?>
                <div class="account" id="iconAccount">
                    <span class="icon">👤</span>
                    <div class="dropdown">
                        <a href="<?= BASE_URL ?>login">Se connecter</a>
                        <a href="<?= BASE_URL ?>register">S’inscrire</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="account logged" id="userMenu">
                    <div class="avatar"><?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?></div>
                    <div class="dropdown">
                        <div class="dropdown-item name"><?= htmlspecialchars($_SESSION['user_name']) ?></div>
                        <a href="<?= BASE_URL ?>profile">Mon profil</a>
                        <a href="<?= BASE_URL ?>commandes">Mes commandes</a>
                        <form method="get" action="<?= BASE_URL ?>logout" style="display:inline"><button class="btn-link"
                                type="submit" style="color: red;">Déconnexion</button></form>
                    </div>
                </div>
                <script>
                    document.getElementById('userMenu').addEventListener('click', function () { this.classList.toggle('open'); });
                </script>
            <?php endif; ?>

        </div>
    </div>
</header>
<script src="<?= BASE_URL ?>assets/js/hamburger.js"></script>
<script src="<?= BASE_URL ?>assets/js/panier.js"></script>
<?php
require_once __DIR__ . "/../partials/panier-drawer.php";
?>