<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Admin | Gestion des zones de livraison</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin/admin.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin/livraison.css">
</head>

<body>
    <?php require_once __DIR__ . '/../layouts/header_admin.php'; ?>

    <div class="admin-container">
        <div class="page-header">
            <h1>Gestion des zones de livraison</h1>
        </div>

        <!-- Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['success'] ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?= $_SESSION['error'] ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Statistiques -->
        <div class="stats">
            <div class="stat-card">
                <h3><?php echo count($zones ?? []); ?></h3>
                <p>Zones totales</p>
            </div>
            <div class="stat-card">
                <h3><?php echo count(array_filter($zones ?? [], fn($z) => $z['actif'] == '1')); ?></h3>
                <p>Zones actives</p>
            </div>
            <div class="stat-card">
                <h3><?php echo count(array_filter($zones ?? [], fn($z) => $z['type'] === 'region')); ?></h3>
                <p>Régions</p>
            </div>
            <div class="stat-card">
                <h3><?php echo count(array_filter($zones ?? [], fn($z) => $z['type'] === 'ville')); ?></h3>
                <p>Villes</p>
            </div>
        </div>

        <!-- Filtres -->
        <form method="GET" class="filters">
            <input type="text" name="q" placeholder="Rechercher une zone..."
                value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">

            <select name="type">
                <option value="">Tous les types</option>
                <option value="region" <?= ($_GET['type'] ?? '') === 'region' ? 'selected' : '' ?>>Région</option>
                <option value="ville" <?= ($_GET['type'] ?? '') === 'ville' ? 'selected' : '' ?>>Ville</option>
                <option value="quartier" <?= ($_GET['type'] ?? '') === 'quartier' ? 'selected' : '' ?>>Quartier</option>
            </select>

            <select name="actif">
                <option value="">Tous les statuts</option>
                <option value="1" <?= ($_GET['actif'] ?? '') === '1' ? 'selected' : '' ?>>Actif</option>
                <option value="0" <?= ($_GET['actif'] ?? '') === '0' ? 'selected' : '' ?>>Inactif</option>
            </select>

            <button type="submit">🔍 Filtrer</button>
            <a href="<?=BASE_URL?>admin/livraison" class="btn-reset">🔄 Réinitialiser</a>
        </form>

        <button class="btn-add" onclick="openModale('add')">
            ➕ Ajouter une zone
        </button>

        <!-- Table des zones -->
        <div class="table-container">
            <div class="table-header">
                <h3>📍 Liste des zones (<?php echo count($zones ?? []); ?>)</h3>
            </div>

            <div class="table-wrapper">
                <?php if (empty($zones)): ?>
                    <div style="text-align: center; padding: 40px; color: #9ca3af;">
                        Aucune zone trouvée avec ces critères.
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Type</th>
                                <th>Parent</th>
                                <th>Frais livraison</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($zones as $zone): ?>
                                <tr>
                                    <td>#<?= str_pad($zone['id'], 3, '0', STR_PAD_LEFT) ?></td>
                                    <td><?= htmlspecialchars($zone['nom']) ?></td>
                                    <td>
                                        <span class="badge badge-<?= $zone['type'] ?>">
                                            <?= ucfirst($zone['type']) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($zone['parent_nom'] ?? '-') ?></td>
                                    <td><?= number_format($zone['frais_livraison'] ?? 0, 0, ',', ' ') ?> FCFA</td>
                                    <td>
                                        <span class="badge badge-<?= $zone['actif'] == '1' ? 'actif' : 'inactif' ?>">
                                            <?= $zone['actif'] == '1' ? 'Actif' : 'Inactif' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="actions">
                                            <button class="btn-action btn-edit" onclick="openModale('edit', <?= $zone['id'] ?>)">
                                                ✏️
                                            </button>
                                            <a href="./livraison/toggle-status?id=<?= $zone['id'] ?>"
                                                class="btn-action btn-toggle"
                                                title="<?= $zone['actif'] == '1' ? 'Désactiver' : 'Activer' ?>">
                                                <?= $zone['actif'] == '1' ? '👁️' : '🙈' ?>
                                            </a>
                                            <button class="btn-action btn-delete"
                                                onclick="confirmDelete(<?= $zone['id'] ?>, '<?= htmlspecialchars($zone['nom']) ?>')">
                                                🗑️
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if (($totalPages ?? 1) > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= ($totalPages ?? 1); $i++): ?>
                        <a href="?p=<?= $i ?>&<?= http_build_query(array_diff_key($_GET, ['p' => ''])) ?>"
                            class="page-link <?= $i == ($pageCourante ?? 1) ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modale Ajout/Édition -->
    <div id="zoneModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Ajouter une zone</h2>
                <button class="modal-close" onclick="closeModale()">&times;</button>
            </div>
            <form id="zoneForm" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" id="formAction" value="add">
                    <input type="hidden" name="id" id="zoneId" value="">

                    <div class="form-group">
                        <label for="nom">Nom de la zone *</label>
                        <input type="text" id="nom" name="nom" required placeholder="Ex: Abidjan, Plateau, etc.">
                    </div>

                    <div class="form-group">
                        <label for="type">Type de zone *</label>
                        <select id="type" name="type" required onchange="updateParentOptions()">
                            <option value="">Sélectionner un type</option>
                            <option value="region">Région</option>
                            <option value="ville">Ville</option>
                            <option value="quartier">Quartier</option>
                        </select>
                    </div>

                    <div class="form-group" id="parentGroup" style="display: none;">
                        <label for="parent_id">Zone parente</label>
                        <select id="parent_id" name="parent_id">
                            <option value="">Sélectionner une zone parente</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="frais_livraison">Frais de livraison (FCFA)</label>
                        <input type="number" id="frais_livraison" name="frais_livraison" min="0" step="100"
                            placeholder="0">
                    </div>

                    <div class="form-group">
                        <label for="description">Description (optionnel)</label>
                        <textarea id="description" name="description"
                            placeholder="Description de la zone..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeModale()">Annuler</button>
                    <button type="submit" class="btn-primary" id="submitBtn">Ajouter</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let currentZoneId = null;
        let modale=document.getElementById('zoneModal');

        function openModale(action, zoneId = null) {
            const modal = document.getElementById('zoneModal');
            const form = document.getElementById('zoneForm');
            const title = document.getElementById('modalTitle');
            const submitBtn = document.getElementById('submitBtn');

            if (action === 'add') {
                title.textContent = 'Ajouter une zone';
                submitBtn.textContent = 'Ajouter';
                form.reset();
                document.getElementById('formAction').value = 'add_zone';
                document.getElementById('zoneId').value = '';
                updateParentOptions();
            } else if (action === 'edit' && zoneId) {
                title.textContent = 'Modifier la zone';
                submitBtn.textContent = 'Modifier';
                document.getElementById('formAction').value = 'edit_zone';
                loadZoneData(zoneId);
            }

            modal.classList.add('show');
        }

        function closeModale() {
            document.getElementById('zoneModal').classList.remove('show');
            currentZoneId = null;
        }

        function updateParentOptions() {
            const type = document.getElementById('type').value;
            const parentGroup = document.getElementById('parentGroup');
            const parentSelect = document.getElementById('parent_id');

            if (type === 'ville') {
                parentGroup.style.display = 'block';
                loadRegions();
            } else if (type === 'quartier') {
                parentGroup.style.display = 'block';
                loadVilles();
            } else {
                parentGroup.style.display = 'none';
                parentSelect.innerHTML = '<option value="">Sélectionner une zone parente</option>';
            }
        }

        async function loadRegions() {
            try {
                const response = await fetch('<?= BASE_URL?>admin/livraison/regions');
                const regions = await response.json();
                const parentSelect = document.getElementById('parent_id');
                parentSelect.innerHTML = '<option value="">Sélectionner une région</option>';
                regions.forEach(region => {
                    parentSelect.innerHTML += `<option value="${region.id}">${region.nom}</option>`;
                });
            } catch (error) {
                console.error('Erreur lors du chargement des régions:', error);
            }
        }

        async function loadVilles() {
            try {
                const response = await fetch('<?= BASE_URL?>admin/livraison/villes');
                const villes = await response.json();
                const parentSelect = document.getElementById('parent_id');
                parentSelect.innerHTML = '<option value="">Sélectionner une ville</option>';
                villes.forEach(ville => {
                    parentSelect.innerHTML += `<option value="${ville.id}">${ville.nom}</option>`;
                });
            } catch (error) {
                console.error('Erreur lors du chargement des villes:', error);
            }
        }

        async function loadZoneData(zoneId) {
            try {
                const response = await fetch(`<?= BASE_URL?>admin/livraison/edit-data?id=${zoneId}`);
                const zone = await response.json();

                document.getElementById('zoneId').value = zone.id;
                document.getElementById('nom').value = zone.nom;
                document.getElementById('type').value = zone.type;
                document.getElementById('frais_livraison').value = zone.frais_livraison || '';
                document.getElementById('description').value = zone.description || '';

                updateParentOptions();

                // Attendre que les options soient chargées puis sélectionner la valeur
                setTimeout(() => {
                    if (zone.parent_id) {
                        document.getElementById('parent_id').value = zone.parent_id;
                    }
                }, 500);

            } catch (error) {
                console.error('Erreur lors du chargement des données:', error);
            }
        }

        function confirmDelete(zoneId, zoneName) {
            if (confirm(`Êtes-vous sûr de vouloir supprimer la zone "${zoneName}" ?`)) {
                window.location.href = `<?= BASE_URL?>admin/livraison/delete?id=${zoneId}`;
            }
        }

        // Fermer la modale en cliquant en dehors
        document.getElementById('zoneModal').addEventListener('click', function (e) {
            if (e.target === this) {
                closeModale();
            }
        });

        // Gestionnaire pour le formulaire
        document.getElementById('zoneForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('<?= BASE_URL?>admin/livraison/store', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Erreur: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Une erreur est survenue');
                });
        });
    </script>

    <?php require_once __DIR__ . '/../layouts/footer_admin.php'; ?>
</body>

</html>