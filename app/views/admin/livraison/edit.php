<?php
require_once __DIR__ . '/../../../layouts/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-2 d-none d-md-block bg-light sidebar">
            <div class="sidebar-sticky">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/dashboard">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/produits">
                            <i class="fas fa-box"></i> Produits
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/categories">
                            <i class="fas fa-tags"></i> Catégories
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/commandes">
                            <i class="fas fa-shopping-cart"></i> Commandes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/clients">
                            <i class="fas fa-users"></i> Clients
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="/admin/livraison">
                            <i class="fas fa-truck"></i> Zones de livraison
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main content -->
        <main class="col-md-10 ml-sm-auto col-lg-10 px-4">
            <div
                class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Modifier la zone de livraison</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="/admin/livraison" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                </div>
            </div>

            <!-- Messages -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['error'];
                    unset($_SESSION['error']); ?>
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <!-- Formulaire -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-edit"></i> Modifier la zone
                </div>
                <div class="card-body">
                    <form action="/admin/livraison/update" method="POST" id="zoneForm">
                        <input type="hidden" name="id" value="<?php echo $this->zoneModel->id; ?>">

                        <div class="form-group">
                            <label for="nom">Nom de la zone *</label>
                            <input type="text" class="form-control" id="nom" name="nom"
                                value="<?php echo htmlspecialchars($this->zoneModel->nom); ?>" required>
                            <small class="form-text text-muted">Entrez le nom de la région, ville ou quartier</small>
                        </div>

                        <div class="form-group">
                            <label for="type">Type de zone *</label>
                            <select class="form-control" id="type" name="type" required>
                                <option value="">Sélectionnez un type</option>
                                <option value="region" <?php echo $this->zoneModel->type === 'region' ? 'selected' : ''; ?>>Région</option>
                                <option value="ville" <?php echo $this->zoneModel->type === 'ville' ? 'selected' : ''; ?>>
                                    Ville</option>
                                <option value="quartier" <?php echo $this->zoneModel->type === 'quartier' ? 'selected' : ''; ?>>Quartier</option>
                            </select>
                        </div>

                        <div class="form-group" id="parentGroup"
                            style="<?php echo in_array($this->zoneModel->type, ['ville', 'quartier']) ? '' : 'display: none;'; ?>">
                            <label for="parent_id">Zone parente</label>
                            <select class="form-control" id="parent_id" name="parent_id" <?php echo in_array($this->zoneModel->type, ['ville', 'quartier']) ? 'required' : ''; ?>>
                                <option value="">Sélectionnez une zone parente</option>
                                <?php if ($this->zoneModel->type === 'ville'): ?>
                                    <?php foreach ($regions as $region): ?>
                                        <option value="<?php echo $region['id']; ?>" <?php echo $this->zoneModel->parent_id == $region['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($region['nom']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php elseif ($this->zoneModel->type === 'quartier'): ?>
                                    <?php foreach ($villes as $ville): ?>
                                        <option value="<?php echo $ville['id']; ?>" <?php echo $this->zoneModel->parent_id == $ville['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($ville['nom']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <small class="form-text text-muted">Pour une ville, sélectionnez la région. Pour un
                                quartier, sélectionnez la ville.</small>
                        </div>

                        <div class="form-group">
                            <label for="frais_livraison">Frais de livraison (FCFA)</label>
                            <input type="number" class="form-control" id="frais_livraison" name="frais_livraison"
                                min="0" step="100" value="<?php echo $this->zoneModel->frais_livraison; ?>">
                            <small class="form-text text-muted">Coût de livraison pour cette zone (0 = gratuit)</small>
                        </div>

                        <div class="form-group form-check">
                            <input type="checkbox" class="form-check-input" id="actif" name="actif" <?php echo $this->zoneModel->actif === '1' ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="actif">
                                Zone active
                            </label>
                            <small class="form-text text-muted">Cochez pour rendre cette zone disponible pour les
                                livraisons</small>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Mettre à jour
                            </button>
                            <a href="/admin/livraison" class="btn btn-secondary ml-2">
                                <i class="fas fa-times"></i> Annuler
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
    document.getElementById('type').addEventListener('change', function () {
        const type = this.value;
        const parentGroup = document.getElementById('parentGroup');
        const parentSelect = document.getElementById('parent_id');

        if (type === 'region') {
            parentGroup.style.display = 'none';
            parentSelect.required = false;
            parentSelect.value = '';
        } else if (type === 'ville') {
            parentGroup.style.display = 'block';
            parentSelect.required = true;
            loadRegions();
        } else if (type === 'quartier') {
            parentGroup.style.display = 'block';
            parentSelect.required = true;
            loadVilles();
        } else {
            parentGroup.style.display = 'none';
            parentSelect.required = false;
            parentSelect.value = '';
        }
    });

    function loadRegions() {
        fetch('/admin/livraison/regions')
            .then(response => response.json())
            .then(data => {
                const select = document.getElementById('parent_id');
                const currentValue = select.value;
                select.innerHTML = '<option value="">Sélectionnez une région</option>';
                data.forEach(region => {
                    const selected = currentValue == region.id ? 'selected' : '';
                    select.innerHTML += `<option value="${region.id}" ${selected}>${region.nom}</option>`;
                });
            })
            .catch(error => console.error('Erreur:', error));
    }

    function loadVilles() {
        fetch('/admin/livraison/villes')
            .then(response => response.json())
            .then(data => {
                const select = document.getElementById('parent_id');
                const currentValue = select.value;
                select.innerHTML = '<option value="">Sélectionnez une ville</option>';
                data.forEach(ville => {
                    const selected = currentValue == ville.id ? 'selected' : '';
                    select.innerHTML += `<option value="${ville.id}" ${selected}>${ville.nom}</option>`;
                });
            })
            .catch(error => console.error('Erreur:', error));
    }
</script>

<?php
require_once __DIR__ . '/../../../layouts/footer.php';
?>