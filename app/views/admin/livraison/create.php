
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin.css">
    <title>Ajout de lieu lvrason</title>
</head>
<body>
 
<?php
require_once __DIR__ . '/../layouts/header_admin.php';
?>

<div class="container-fluid">
    <div class="row">

        <!-- Main content -->
        <main class="col-md-10 ml-sm-auto col-lg-10 px-4">
            <div
                class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Ajouter une zone de livraison</h1>
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
                    <i class="fas fa-plus"></i> Nouvelle zone
                </div>
                <div class="card-body">
                    <form action="/admin/livraison/store" method="POST" id="zoneForm">
                        <div class="form-group">
                            <label for="nom">Nom de la zone *</label>
                            <input type="text" class="form-control" id="nom" name="nom" required>
                            <small class="form-text text-muted">Entrez le nom de la région, ville ou quartier</small>
                        </div>

                        <div class="form-group">
                            <label for="type">Type de zone *</label>
                            <select class="form-control" id="type" name="type" required>
                                <option value="">Sélectionnez un type</option>
                                <option value="region">Région</option>
                                <option value="ville">Ville</option>
                                <option value="quartier">Quartier</option>
                            </select>
                        </div>

                        <div class="form-group" id="parentGroup" style="display: none;">
                            <label for="parent_id">Zone parente</label>
                            <select class="form-control" id="parent_id" name="parent_id">
                                <option value="">Sélectionnez une zone parente</option>
                            </select>
                            <small class="form-text text-muted">Pour une ville, sélectionnez la région. Pour un
                                quartier, sélectionnez la ville.</small>
                        </div>

                        <div class="form-group">
                            <label for="frais_livraison">Frais de livraison (FCFA)</label>
                            <input type="number" class="form-control" id="frais_livraison" name="frais_livraison"
                                min="0" step="100" value="0">
                            <small class="form-text text-muted">Coût de livraison pour cette zone (0 = gratuit)</small>
                        </div>

                        <div class="form-group form-check">
                            <input type="checkbox" class="form-check-input" id="actif" name="actif" checked>
                            <label class="form-check-label" for="actif">
                                Zone active
                            </label>
                            <small class="form-text text-muted">Cochez pour rendre cette zone disponible pour les
                                livraisons</small>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Enregistrer
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
        }
    });

    function loadRegions() {
        fetch('/admin/livraison/regions')
            .then(response => response.json())
            .then(data => {
                const select = document.getElementById('parent_id');
                select.innerHTML = '<option value="">Sélectionnez une région</option>';
                data.forEach(region => {
                    select.innerHTML += `<option value="${region.id}">${region.nom}</option>`;
                });
            })
            .catch(error => console.error('Erreur:', error));
    }

    function loadVilles() {
        fetch('/admin/livraison/villes')
            .then(response => response.json())
            .then(data => {
                const select = document.getElementById('parent_id');
                select.innerHTML = '<option value="">Sélectionnez une ville</option>';
                data.forEach(ville => {
                    select.innerHTML += `<option value="${ville.id}">${ville.nom}</option>`;
                });
            })
            .catch(error => console.error('Erreur:', error));
    }

    // Charger les régions par défaut si le type est pré-sélectionné
    document.addEventListener('DOMContentLoaded', function () {
        const typeSelect = document.getElementById('type');
        if (typeSelect.value) {
            typeSelect.dispatchEvent(new Event('change'));
        }
    });
</script>

<?php
require_once __DIR__ . '/../../../layouts/footer.php';
?>