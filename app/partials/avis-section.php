<!-- Avis Produits -->
<div id="avis-section" class="avis-section">
    <h3 class="avis-title">📝 Avis Clients</h3>

    <!-- Statistiques des avis -->
    <div class="avis-stats" id="avis-stats">
        <div class="stats-left">
            <div class="moyenne-note">
                <div class="note-valeur" id="moyenne-note">0.0</div>
                <div class="stars-display" id="stars-display">★★★★☆</div>
                <div class="note-count" id="note-count">0 avis</div>
            </div>

            <div class="distribution-notes">
                <div class="distribution-row">
                    <span class="stars-label">5 ★</span>
                    <div class="bar-container">
                        <div class="bar" style="width: 0%" id="bar-5"></div>
                    </div>
                    <span class="count" id="count-5">0</span>
                </div>
                <div class="distribution-row">
                    <span class="stars-label">4 ★</span>
                    <div class="bar-container">
                        <div class="bar" style="width: 0%" id="bar-4"></div>
                    </div>
                    <span class="count" id="count-4">0</span>
                </div>
                <div class="distribution-row">
                    <span class="stars-label">3 ★</span>
                    <div class="bar-container">
                        <div class="bar" style="width: 0%" id="bar-3"></div>
                    </div>
                    <span class="count" id="count-3">0</span>
                </div>
                <div class="distribution-row">
                    <span class="stars-label">2 ★</span>
                    <div class="bar-container">
                        <div class="bar" style="width: 0%" id="bar-2"></div>
                    </div>
                    <span class="count" id="count-2">0</span>
                </div>
                <div class="distribution-row">
                    <span class="stars-label">1 ★</span>
                    <div class="bar-container">
                        <div class="bar" style="width: 0%" id="bar-1"></div>
                    </div>
                    <span class="count" id="count-1">0</span>
                </div>
            </div>
        </div>

        <div class="stats-right">
            <button class="btn-ajouter-avis" id="btn-ajouter-avis">✍️ Ajouter un avis</button>
            <p class="avis-message" id="avis-message" style="display:none;"></p>
        </div>
    </div>

    <!-- Formulaire d'ajout d'avis (caché par défaut) -->
    <div id="avis-form-container" class="avis-form-container" style="display: none;">
        <form id="avis-form" class="avis-form">
            <input type="hidden" name="produit_id" value="<?php echo $produit['id']; ?>">

            <div class="form-group">
                <label for="avis-titre">Titre de votre avis *</label>
                <input type="text" id="avis-titre" name="titre" required maxlength="200"
                    placeholder="Ex: Excellent rapport qualité-prix">
            </div>

            <div class="form-group">
                <label for="avis-note">Note *</label>
                <div class="rating-input">
                    <input type="radio" id="star-5" name="note" value="5">
                    <label for="star-5" class="star-label">★</label>

                    <input type="radio" id="star-4" name="note" value="4">
                    <label for="star-4" class="star-label">★</label>

                    <input type="radio" id="star-3" name="note" value="3">
                    <label for="star-3" class="star-label">★</label>

                    <input type="radio" id="star-2" name="note" value="2">
                    <label for="star-2" class="star-label">★</label>

                    <input type="radio" id="star-1" name="note" value="1">
                    <label for="star-1" class="star-label">★</label>
                </div>
            </div>

            <div class="form-group">
                <label for="avis-commentaire">Votre avis (optionnel)</label>
                <textarea id="avis-commentaire" name="commentaire" maxlength="2000"
                    placeholder="Partagez vos impressions sur ce produit..."></textarea>
                <small id="char-count">0/2000</small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">Soumettre l'avis</button>
                <button type="button" class="btn-cancel" id="btn-annuler-avis">Annuler</button>
            </div>
        </form>
    </div>

    <!-- Liste des avis -->
    <div class="avis-list" id="avis-list">
        <div class="loading">Chargement des avis...</div>
    </div>

    <!-- Pagination -->
    <div class="avis-pagination" id="avis-pagination"></div>
</div>

<style>
    .avis-section {
        margin-top: 40px;
        padding: 30px;
        background: #f9f9f9;
        border-radius: 8px;
    }

    .avis-title {
        font-size: 24px;
        margin-bottom: 30px;
        color: #333;
    }

    /* Statistiques */
    .avis-stats {
        display: flex;
        gap: 40px;
        margin-bottom: 40px;
        background: white;
        padding: 20px;
        border-radius: 8px;
    }

    .stats-left {
        flex: 1;
        display: flex;
        gap: 40px;
    }

    .stats-right {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 15px;
    }

    .moyenne-note {
        text-align: center;
    }

    .note-valeur {
        font-size: 48px;
        font-weight: bold;
        color: #f39c12;
    }

    .stars-display {
        font-size: 24px;
        color: #f39c12;
        margin: 5px 0;
    }

    .note-count {
        color: #666;
        font-size: 14px;
    }

    .distribution-notes {
        flex: 1;
    }

    .distribution-row {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 8px;
    }

    .stars-label {
        width: 30px;
        font-size: 12px;
        color: #f39c12;
    }

    .bar-container {
        flex: 1;
        height: 6px;
        background: #e0e0e0;
        border-radius: 3px;
        overflow: hidden;
    }

    .bar {
        height: 100%;
        background: #f39c12;
        transition: width 0.3s;
    }

    .count {
        width: 30px;
        text-align: right;
        font-size: 12px;
        color: #666;
    }

    /* Bouton ajouter avis */
    .btn-ajouter-avis {
        background: #27ae60;
        color: white;
        border: none;
        padding: 12px 30px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
        transition: background 0.3s;
        width: 100%;
    }

    .btn-ajouter-avis:hover {
        background: #229954;
    }

    .btn-ajouter-avis:disabled {
        background: #bdc3c7;
        cursor: not-allowed;
    }

    .avis-message {
        padding: 15px;
        border-radius: 5px;
        font-size: 14px;
        text-align: center;
    }

    .avis-message.info {
        background: #d1ecf1;
        color: #0c5460;
        border: 1px solid #bee5eb;
    }

    .avis-message.warning {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeeba;
    }

    /* Formulaire */
    .avis-form-container {
        background: white;
        padding: 25px;
        border-radius: 8px;
        margin-bottom: 30px;
        border: 2px solid #27ae60;
    }

    .avis-form .form-group {
        margin-bottom: 20px;
    }

    .avis-form label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #333;
    }

    .avis-form input[type="text"],
    .avis-form textarea {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-family: inherit;
        font-size: 14px;
    }

    .avis-form textarea {
        min-height: 120px;
        resize: vertical;
    }

    .avis-form input[type="text"]:focus,
    .avis-form textarea:focus {
        outline: none;
        border-color: #27ae60;
        box-shadow: 0 0 5px rgba(39, 174, 96, 0.2);
    }

    #char-count {
        display: block;
        text-align: right;
        font-size: 12px;
        color: #999;
        margin-top: 5px;
    }

    /* Rating Input */
    .rating-input {
        display: flex;
        gap: 5px;
        font-size: 28px;
    }

    .rating-input input[type="radio"] {
        display: none;
    }

    .rating-input .star-label {
        cursor: pointer;
        color: #ddd;
        transition: color 0.2s;
    }

    .rating-input input[type="radio"]:checked ~ .star-label,
    .rating-input .star-label:hover {
        color: #f39c12;
    }

    /* Form Actions */
    .form-actions {
        display: flex;
        gap: 10px;
    }

    .btn-submit,
    .btn-cancel {
        flex: 1;
        padding: 12px;
        border: none;
        border-radius: 4px;
        font-size: 14px;
        cursor: pointer;
        transition: background 0.3s;
    }

    .btn-submit {
        background: #27ae60;
        color: white;
    }

    .btn-submit:hover {
        background: #229954;
    }

    .btn-cancel {
        background: #e0e0e0;
        color: #333;
    }

    .btn-cancel:hover {
        background: #d0d0d0;
    }

    /* Liste des avis */
    .avis-list {
        background: white;
        border-radius: 8px;
        overflow: hidden;
    }

    .avis-item {
        padding: 20px;
        border-bottom: 1px solid #eee;
        transition: background 0.2s;
    }

    .avis-item:hover {
        background: #f9f9f9;
    }

    .avis-item:last-child {
        border-bottom: none;
    }

    .avis-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 10px;
    }

    .avis-auteur {
        font-weight: 600;
        color: #333;
    }

    .avis-date {
        font-size: 12px;
        color: #999;
    }

    .avis-rating {
        color: #f39c12;
        font-size: 14px;
        margin-bottom: 10px;
    }

    .avis-titre {
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
    }

    .avis-texte {
        color: #666;
        line-height: 1.5;
        font-size: 14px;
    }

    .avis-actions {
        margin-top: 10px;
        font-size: 12px;
    }

    .btn-utile {
        background: none;
        border: none;
        color: #27ae60;
        cursor: pointer;
        padding: 0 5px;
        font-size: 12px;
    }

    .btn-utile:hover {
        text-decoration: underline;
    }

    .loading {
        padding: 40px;
        text-align: center;
        color: #999;
    }

    .empty-state {
        padding: 40px;
        text-align: center;
        color: #999;
    }

    /* Pagination */
    .avis-pagination {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-top: 20px;
        padding: 20px;
    }

    .pagination-btn {
        padding: 8px 12px;
        border: 1px solid #ddd;
        background: white;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .pagination-btn:hover:not(:disabled) {
        background: #27ae60;
        color: white;
        border-color: #27ae60;
    }

    .pagination-btn.active {
        background: #27ae60;
        color: white;
        border-color: #27ae60;
    }

    .pagination-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    @media (max-width: 768px) {
        .avis-stats {
            flex-direction: column;
            gap: 20px;
        }

        .stats-left {
            flex-direction: column;
        }

        .rating-input {
            font-size: 24px;
        }
    }
</style>

<script>
    const PRODUIT_ID = <?php echo $produit['id']; ?>;
    const BASE_URL = '<?php echo BASE_URL; ?>';
    const IS_AUTHENTICATED = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;

    document.addEventListener('DOMContentLoaded', function() {
        chargerAvis();
        chargerStatistiques();
        attacherEvenements();
    });

    function attacherEvenements() {
        // Bouton ajouter avis
        document.getElementById('btn-ajouter-avis').addEventListener('click', afficherFormulaire);
        document.getElementById('btn-annuler-avis').addEventListener('click', cacherFormulaire);

        // Formulaire
        document.getElementById('avis-form').addEventListener('submit', function(e) {
            e.preventDefault();
            soumettreAvis();
        });

        // Compteur de caractères
        const textarea = document.getElementById('avis-commentaire');
        textarea.addEventListener('input', function() {
            document.getElementById('char-count').textContent = this.value.length + '/2000';
        });

        // Rating stars
        const stars = document.querySelectorAll('.rating-input .star-label');
        stars.forEach(star => {
            star.addEventListener('click', function() {
                const radio = this.previousElementSibling;
                radio.checked = true;
            });
        });

        vérifierPeutLaisserAvis();
    }

    function chargerStatistiques() {
        fetch(`${BASE_URL}api/avis/statistiques?produit_id=${PRODUIT_ID}`)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    afficherStatistiques(data);
                }
            });
    }

    function afficherStatistiques(data) {
        const moyenne = data.moyenne || 0;
        document.getElementById('moyenne-note').textContent = moyenne.toFixed(1);

        // Étoiles
        const starCount = Math.round(moyenne);
        let stars = '';
        for (let i = 0; i < 5; i++) {
            stars += i < starCount ? '★' : '☆';
        }
        document.getElementById('stars-display').textContent = stars;

        // Compte
        document.getElementById('note-count').textContent = `${data.total} avis`;

        // Distribution
        const max = Math.max(...Object.values(data.distribution));
        for (let note = 5; note >= 1; note--) {
            const count = data.distribution[note] || 0;
            const percent = max > 0 ? (count / max) * 100 : 0;
            document.getElementById(`bar-${note}`).style.width = percent + '%';
            document.getElementById(`count-${note}`).textContent = count;
        }
    }

    function chargerAvis() {
        fetch(`${BASE_URL}api/avis/get?produit_id=${PRODUIT_ID}&page=1`)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    afficherAvis(data.avis);
                    afficherPagination(data.pages, 1);
                    afficherStatistiques(data);
                } else {
                    document.getElementById('avis-list').innerHTML = '<div class="empty-state">Aucun avis pour le moment</div>';
                }
            });
    }

    function afficherAvis(avis) {
        const container = document.getElementById('avis-list');
        if (avis.length === 0) {
            container.innerHTML = '<div class="empty-state">Aucun avis pour le moment. Soyez le premier à donner votre avis!</div>';
            return;
        }

        container.innerHTML = avis.map(a => `
            <div class="avis-item">
                <div class="avis-header">
                    <div>
                        <div class="avis-auteur">${escapeHtml(a.nom_complet)}</div>
                        <div class="avis-date">${formatDate(a.date_creation)}</div>
                    </div>
                </div>
                <div class="avis-rating">${getStars(a.note)}</div>
                <div class="avis-titre">${escapeHtml(a.titre)}</div>
                ${a.commentaire ? `<div class="avis-texte">${escapeHtml(a.commentaire)}</div>` : ''}
            </div>
        `).join('');
    }

    function afficherPagination(pages, currentPage) {
        if (pages <= 1) return;

        const container = document.getElementById('avis-pagination');
        let html = '';

        if (currentPage > 1) {
            html += `<button class="pagination-btn" onclick="chargerPageAvis(${currentPage - 1})">← Précédent</button>`;
        }

        for (let i = 1; i <= pages; i++) {
            html += `<button class="pagination-btn ${i === currentPage ? 'active' : ''}" onclick="chargerPageAvis(${i})">${i}</button>`;
        }

        if (currentPage < pages) {
            html += `<button class="pagination-btn" onclick="chargerPageAvis(${currentPage + 1})">Suivant →</button>`;
        }

        container.innerHTML = html;
    }

    function chargerPageAvis(page) {
        fetch(`${BASE_URL}api/avis/get?produit_id=${PRODUIT_ID}&page=${page}`)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    afficherAvis(data.avis);
                    afficherPagination(data.pages, page);
                }
            });
    }

    function vérifierPeutLaisserAvis() {
        if (!IS_AUTHENTICATED) {
            document.getElementById('avis-message').textContent = 'Connectez-vous pour laisser un avis';
            document.getElementById('avis-message').className = 'avis-message info';
            document.getElementById('avis-message').style.display = 'block';
            document.getElementById('btn-ajouter-avis').disabled = true;
            return;
        }

        fetch(`${BASE_URL}api/avis/peut-laisser?produit_id=${PRODUIT_ID}`)
            .then(r => r.json())
            .then(data => {
                if (!data.peut_laisser) {
                    document.getElementById('avis-message').textContent = 'Vous devez avoir acheté ce produit pour laisser un avis';
                    document.getElementById('avis-message').className = 'avis-message warning';
                    document.getElementById('avis-message').style.display = 'block';
                    document.getElementById('btn-ajouter-avis').disabled = true;
                } else if (data.avis_existant) {
                    document.getElementById('avis-message').textContent = 'Vous avez déjà laissé un avis pour ce produit. Vous pouvez le modifier.';
                    document.getElementById('avis-message').className = 'avis-message info';
                    document.getElementById('avis-message').style.display = 'block';

                    // Pré-remplir le formulaire
                    document.getElementById('avis-titre').value = data.avis_existant.titre;
                    document.getElementById('avis-commentaire').value = data.avis_existant.commentaire || '';
                    document.getElementById(`star-${data.avis_existant.note}`).checked = true;
                }
            });
    }

    function afficherFormulaire() {
        if (!IS_AUTHENTICATED) {
            window.location.href = BASE_URL + 'login';
            return;
        }
        document.getElementById('avis-form-container').style.display = 'block';
        document.querySelector('html, body').scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function cacherFormulaire() {
        document.getElementById('avis-form-container').style.display = 'none';
        document.getElementById('avis-form').reset();
    }

    function soumettreAvis() {
        const formData = new FormData(document.getElementById('avis-form'));

        fetch(`${BASE_URL}api/avis/ajouter`, {
            method: 'POST',
            body: formData
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    cacherFormulaire();
                    chargerAvis();
                    chargerStatistiques();
                } else {
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        alert('Erreur: ' + data.message);
                    }
                }
            });
    }

    function getStars(note) {
        let stars = '';
        for (let i = 0; i < 5; i++) {
            stars += i < note ? '★' : '☆';
        }
        return stars;
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('fr-FR', { year: 'numeric', month: 'long', day: 'numeric' });
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
</script>
