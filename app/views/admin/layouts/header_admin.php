<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();

require_once __DIR__ . '/../../../models/Admin.php';

// Vérifier l'accès admin
AdminMiddleware::verifierAdmin();

$db = (new Database())->getConnexion();
$adminModel = new Admin($db);
$pendingOrders = (int) ($adminModel->commandesEnAttente() ?? 0);

// Helpers
function base_url($path = '')
{
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}

$currentUri = $_SERVER['REQUEST_URI'] ?? '';
function is_active($uriPart)
{
    global $currentUri;
    return strpos($currentUri, $uriPart) !== false ? 'active' : '';
}
?>

<div class="admin-wrapper">

    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">
        <div class="logo">
            <img src="<?= base_url('assets/images/logo.png') ?>" alt="IB Technology">
        </div>

        <nav class="menu">
            <a href="<?= base_url('admin') ?>" class="menu-item <?= is_active('/admin') ?>">📊 Dashboard</a>
            <a href="<?= base_url('admin/produits') ?>" class="menu-item <?= is_active('/admin/produits') ?>">📦
                Produits</a>
            <a href="<?= base_url('admin/stock') ?>" class="menu-item <?= is_active('/admin/commandes') ?>">🏭 Stock
                <!-- <span class="badge"><?= $pendingOrders > 0 ? $pendingOrders : '' ?></span></a> -->

            <a href="<?= base_url('admin/commandes') ?>" class="menu-item <?= is_active('/admin/commandes') ?>">🧾
                Commandes <span class="badge"><?= $pendingOrders > 0 ? $pendingOrders : '' ?></span></a>
            <a href="<?= base_url('admin/clients') ?>" class="menu-item <?= is_active('/admin/clients') ?>">👥
                Clients</a>
            <a href="<?= base_url('admin/categories') ?>" class="menu-item <?= is_active('/admin/categories') ?>">🗂️
                Catégories</a>
            <a href="<?= base_url('admin/livraison') ?>" class="menu-item <?= is_active('/admin/livraison') ?>">📍
                Livraison (zones et frais)</a>
            <a href="<?= base_url('admin/ventes') ?>" class="menu-item <?= is_active('/admin/vente') ?>">🏷️ Ventes</a>
            <a href="<?= base_url('logout') ?>" class="menu-item logout">🚪 Déconnexion</a>
        </nav>
    </aside>
    <div id="overlay"></div>


    <!-- CONTENT -->
    <main class="main-content">

        <!-- TOPBAR -->
        <header class="topbar">
            <button class="menu-toggle" id="menuToggle">☰</button>
            <div class="topbar-left">
                <div class="admin-search" style="position:relative;display:inline-block;">
                    <input id="globalAdminSearch" type="search" name="q" placeholder="Recherche globale (produits, clients, commandes...)" autocomplete="off" style="width:360px;padding:8px;border-radius:6px;border:1px solid rgba(0,0,0,0.08)">
                    <div id="searchSuggestions" class="search-suggestions" style="display:none;position:absolute;left:0;top:42px;width:420px;z-index:1200;background:#0f1724;border:1px solid rgba(255,255,255,0.04);border-radius:8px;box-shadow:0 6px 18px rgba(2,6,23,0.6);overflow:hidden;">
                        <div id="suggestionsInner" style="max-height:320px;overflow:auto;color:var(--text);">
                        </div>
                    </div>
                </div>
            </div>
            <style>
                .search-suggestions .suggestion-item{display:flex;gap:10px;padding:10px;border-bottom:1px solid rgba(255,255,255,0.02);cursor:pointer; text-decoration: none; color: #fff;}
                .search-suggestions .suggestion-item:hover{background:rgba(161, 149, 149, 0.02)}
                .search-suggestions .suggestion-type{font-size:0.75rem;color:var(--muted);margin-left:auto}
                .search-suggestions img.thumb{width:48px;height:48px;border-radius:6px;object-fit:cover}
                .search-suggestions .title{font-weight:600}
                .search-suggestions .subtitle{font-size:0.85rem;color:var(--muted)}
            </style>
            <script>
                (function(){
                    const input = document.getElementById('globalAdminSearch');
                    const container = document.getElementById('searchSuggestions');
                    const inner = document.getElementById('suggestionsInner');
                    let timeout = null;

                    function render(results){
                        inner.innerHTML = '';
                        if(!results || results.length === 0){
                            inner.innerHTML = '<div style="padding:12px;color:var(--muted)">Aucun résultat</div>';
                            container.style.display = 'block';
                            return;
                        }
                        results.forEach(r => {
                            const item = document.createElement('a');
                            item.className = 'suggestion-item';
                            item.href = r.url_admin||'#';
                            item.innerHTML = `\
                                ${r.image ? '<img src="<?=BASE_URL?>uploads/'+r.image+'" class="thumb">' : ''}\
                                <div style="flex:1;min-width:0">\
                                    <div class=\"title\">${escapeHtml(r.title)}</div>\
                                    ${r.subtitle ? '<div class="subtitle">'+escapeHtml(r.subtitle)+'</div>' : ''}\
                                </div>\
                                <div class="suggestion-type">${escapeHtml(r.type)}</div>`;
                            inner.appendChild(item);
                        });
                        container.style.display = 'block';
                    }

                    function escapeHtml(s){
                        if(!s) return '';
                        return String(s).replace(/[&<>"']/g, function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"}[c];});
                    }

                    function fetchSuggestions(q){
                        fetch('<?= base_url('admin/search/suggestions') ?>?q='+encodeURIComponent(q))
                            .then(r => r.json())
                            .then(data => {
                                render(data);
                            }).catch(err => {
                                console.error(err);
                            });
                    }

                    input.addEventListener('input', function(e){
                        const v = this.value.trim();
                        clearTimeout(timeout);
                        if(v.length < 2){ container.style.display='none'; return; }
                        timeout = setTimeout(()=> fetchSuggestions(v), 220);
                    });

                    // close on outside click
                    document.addEventListener('click', function(e){
                        if(!document.querySelector('.admin-search').contains(e.target)){
                            container.style.display='none';
                        }
                    });
                })();
            </script>
            <div class="admin-info">
                <a href="<?= base_url('') ?>" class="website-link" target="_blank">🌐 Voir le site</a>
                <span class="admin-name">👤 <?= htmlspecialchars($_SESSION['user']['nom'] ?? 'Admin'); ?></span>
                <a href="<?= base_url('admin/profile') ?>" class="profile-link">Profil</a>
            </div>
        </header>
        <!-- Balise fermer dans le footer_admin -->