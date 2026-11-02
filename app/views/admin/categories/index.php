<?php
// Suppression si demandé
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];

    // Vérifier si la catégorie peut être supprimée
    $category = $categorieModel->getById($id);
    if (!$category) {
        $deleteError = 'Catégorie introuvable.';
    } else {
        // Vérifier les produits
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM produits WHERE categorie_id = ? AND actif = '1'");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $productsResult = $stmt->get_result()->fetch_assoc();

        // Vérifier les sous-catégories
        $children = $categorieModel->getChildren($id);
        $hasChildren = !empty($children);

        if ($productsResult['count'] > 0 && $hasChildren) {
            $deleteMessage = 'Catégorie désactivée avec succès (contenait ' . $productsResult['count'] . ' produit(s) actif(s) et des sous-catégories).';
        } elseif ($productsResult['count'] > 0) {
            $deleteMessage = 'Catégorie désactivée avec succès (contenait ' . $productsResult['count'] . ' produit(s) actif(s)).';
        } elseif ($hasChildren) {
            $deleteMessage = 'Catégorie désactivée avec succès (contenait des sous-catégories).';
        } else {
            $deleteMessage = 'Catégorie supprimée définitivement.';
        }

        $categorieModel->delete($id);
    }

    // Rediriger avec le message
    $redirectUrl = BASE_URL.'admin/categorie';
    if (isset($deleteMessage)) {
        $redirectUrl .= '?message=' . urlencode($deleteMessage);
    } elseif (isset($deleteError)) {
        $redirectUrl .= '&error=' . urlencode($deleteError);
    }
    if (isset($filter) && $filter !== 'all') {
        $redirectUrl .= '&filter=' . $filter;
    }
    header('Location: ' . $redirectUrl);
    exit;
}

// Gestion de l'ajout via POST (pour le modal)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_category') {
    $nom = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $parent_id = (int) $_POST['parent_id'] ?? null;

    if ($nom !== '') {
        if (
            $categorieModel->create([
                'nom' => $nom,
                'description' => $description,
                'parent_id' => $parent_id
            ])
        ) {
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit;
        }
    }
    $error = 'Le nom est requis.';
}

// Gestion de l'édition via POST (pour le modal)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_category') {
    $id = (int) $_POST['id'];
    $nom = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $parent_id = (int)$_POST['parent_id'] ?? null;

    // Empêcher de se définir parent soi-même ou descendant
    if ($parent_id == $id) {
        $editError = 'Une catégorie ne peut pas être son propre parent.';
    } else {
        // Vérifier que parent n'est pas un descendant
        $desc = $categorieModel->getDescendantIds($id);
        if (in_array((int) $parent_id, $desc)) {
            $editError = 'Le parent choisi est une sous-catégorie. Choisissez un autre parent.';
        } else {
            $categorieModel->update($id, [
                'nom' => $nom,
                'description' => $description,
                'parent_id' => $parent_id
            ]);
            // Rediriger vers la page sans le paramètre edit
            $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            header('Location: ' . $url);
            exit;
        }
    }
}

// Récupération de la catégorie à éditer si demandé
$editCategory = null;
if (isset($_GET['edit'])) {
    $editId = (int) $_GET['edit'];
    $editCategory = $categorieModel->getById($editId);
}

// Gestion du filtre
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all'; // all, active, inactive

$tree = $categorieModel->getTreeAdmin();

// Appliquer le filtre
if ($filter === 'active') {
    $tree = array_filter($tree, function ($node) {
        return ($node['actif'] ?? '1') === '1';
    });
} elseif ($filter === 'inactive') {
    $tree = array_filter($tree, function ($node) {
        return ($node['actif'] ?? '1') === '0';
    });
}

function renderRow($nodes, $level = 0)
{
    foreach ($nodes as $n) {
        $isActive = $n['actif'];
        $statusClass = $isActive ? 'status-active' : 'status-inactive';
        $statusText = $isActive ? 'Active' : 'Désactivée';
        $toggleIcon = $isActive ? '🔴' : '🟢';
        $toggleText = $isActive ? 'Désactiver' : 'Activer';

        echo '<tr class="' . ($isActive ? 'active' : 'inactive') . '">';
        echo '<td>' . str_repeat('&nbsp;&nbsp;&nbsp;', $level) . htmlspecialchars($n['nom']) . '</td>';
        echo '<td>' . htmlspecialchars($n['description'] ?? '') . '</td>';
        echo '<td><span class="status-badge ' . $statusClass . '">' . $statusText . '</span></td>';

        echo '<td><a href="?edit=' . $n['id'] . '" class="edit-link" data-id="' . $n['id'] . '">✏️</a> </td>';
        echo '<td>';

        echo '<button class="toggle-status-btn" data-id="' . $n['id'] . '" data-current-status="' . ($isActive ? '1' : '0') . '" title="' . $toggleText . '">' . $toggleIcon . '</button></td> ';

        echo '<td><a href="?delete=' . $n['id'] . '" onclick="return confirm(\'Attention : Cette action va désactiver cette catégorie et toutes ses sous-catégories si elles contiennent des produits ou des sous-catégories. Sinon, elle sera supprimée définitivement. Continuer ?\')">🗑</a>';
        echo '</td>';
        echo '</tr>';
        if (!empty($n['children']))
            renderRow($n['children'], $level + 1);
    }
}

function renderOptions($nodes, $prefix = '')
{
    foreach ($nodes as $node) {
        // Pour l'ajout, permettre de sélectionner toutes les catégories
        echo '<option value="' . $node['id'] . '">' . $prefix . htmlspecialchars($node['nom']) . '</option>';
        if (!empty($node['children'])) {
            renderOptions($node['children'], $prefix . '-- ');
        }
    }
}

function renderOptionsEdit($nodes, $currentId = null, $selectedParentId = null, $prefix = '')
{
    foreach ($nodes as $node) {
        // Pour l'édition, permettre de sélectionner toutes les catégories sauf celle qu'on édite
        if ($node['id'] != $currentId) {
            $selected = ($node['id'] == $selectedParentId) ? 'selected' : '';
            echo '<option value="' . $node['id'] . '" ' . $selected . '>' . $prefix . htmlspecialchars($node['nom']) . '</option>';
            if (!empty($node['children'])) {
                renderOptionsEdit($node['children'], $currentId, $selectedParentId, $prefix . '-- ');
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | Gesrion de categories</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin/admin.css">
</head>

<body>
    <?php
    require_once __DIR__ . '/../layouts/header_admin.php';
    ?>

    <div class="admin-container">
        <h1 style="z-index: 600;">Gestion des catégories</h1>

        <!-- Filtres -->
        <div class="filters" style="margin-bottom: 20px; margin-top: 15px;">
            <span style="margin-right: 10px; font-weight: bold;">Filtrer :</span>
            <a href="?filter=all" class="filter-btn <?php echo $filter === 'all' ? 'active' : ''; ?>"
                style="margin-right: 10px; padding: 5px 10px; text-decoration: none; border: 1px solid #ccc; border-radius: 4px; background: <?php echo $filter === 'all' ? '#38bdf8' : '#f8f9fa'; ?>; color: <?php echo $filter === 'all' ? 'white' : 'black'; ?>;">Toutes</a>
            <a href="?filter=active" class="filter-btn <?php echo $filter === 'active' ? 'active' : ''; ?>"
                style="margin-right: 10px; padding: 5px 10px; text-decoration: none; border: 1px solid #ccc; border-radius: 4px; background: <?php echo $filter === 'active' ? '#10b981' : '#f8f9fa'; ?>; color: <?php echo $filter === 'active' ? 'white' : 'black'; ?>;">Actives</a>
            <a href="?filter=inactive" class="filter-btn <?php echo $filter === 'inactive' ? 'active' : ''; ?>"
                style="margin-right: 10px; padding: 5px 10px; text-decoration: none; border: 1px solid #ccc; border-radius: 4px; background: <?php echo $filter === 'inactive' ? '#ef4444' : '#f8f9fa'; ?>; color: <?php echo $filter === 'inactive' ? 'white' : 'black'; ?>;">Inactives</a>
        </div>

        <a href="#" id="openModalBtn" style="color:#38bdf8;">➕ Ajouter une catégorie</a>

        <?php if (!empty($error)): ?>
            <div
                style="color:red; margin-top:10px; padding: 10px; border: 1px solid red; border-radius: 4px; background-color: #fee;">
                <strong>Erreur :</strong> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($editError)): ?>
            <div
                style="color:red; margin-top:10px; padding: 10px; border: 1px solid red; border-radius: 4px; background-color: #fee;">
                <strong>Erreur d'édition :</strong> <?= htmlspecialchars($editError) ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['message'])): ?>
            <div
                style="color:green; margin-top:10px; padding: 10px; border: 1px solid green; border-radius: 4px; background-color: #efe;">
                <strong>Succès :</strong> <?= htmlspecialchars($_GET['message']) ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div
                style="color:red; margin-top:10px; padding: 10px; border: 1px solid red; border-radius: 4px; background-color: #fee;">
                <strong>Erreur :</strong> <?= htmlspecialchars($_GET['error']) ?>
            </div>
        <?php endif; ?>

        <table style="width:100%; margin-top:15px; border-collapse:collapse;">
            <thead>
                <tr>
                    <th style="width: 30%;">Nom</th>
                    <th style="width: 25%;">Description</th>
                    <th style="width: 15%;">Statut</th>
                    <th style="width: 10%;">modifier</th>
                    <th style="width: 10%;">Activer/desactiver</th>
                    <th style="width: 10%;">suprimer</th>
                </tr>
            </thead>
            <tbody>
                <?php renderRow($tree); ?>
            </tbody>
        </table>
    </div>

    <!-- Modal pour ajouter une catégorie -->
    <div id="categoryModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Ajouter une catégorie</h2>

            <form method="POST" id="categoryForm">
                <input type="hidden" name="action" value="add_category">

                <label>Nom</label>
                <input type="text" name="nom" required>

                <label>Description</label>
                <textarea name="description"></textarea>

                <label>Parent</label>
                <select name="parent_id">
                    <option value="">-- Aucune (racine) --</option>
                    <?php renderOptions($tree); ?>
                </select>

                <div style="margin-top:15px;">
                    <button type="submit">Créer</button>
                    <button type="button" id="cancelBtn">Annuler</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal pour modifier une catégorie -->
    <div id="editCategoryModal" class="modal" >
        <div class="modal-content" >
            <span class="close edit-close">&times;</span>
            <h2>Modifier la catégorie</h2>

            <form method="POST" id="editCategoryForm" >
                <input type="hidden" name="action" value="edit_category">
                <input type="hidden" name="id" id="editCategoryId"
                    value="<?php echo $editCategory ? $editCategory['id'] : ''; ?>">

                <label>Nom</label>
                <input type="text" name="nom" id="editCategoryNom"
                    value="<?php echo $editCategory ? htmlspecialchars($editCategory['nom']) : ''; ?>" required>

                <label>Description</label>
                <textarea name="description"
                    id="editCategoryDescription"><?php echo $editCategory ? htmlspecialchars($editCategory['description'] ?? '') : ''; ?></textarea>

                <label>Parent</label>
                <select name="parent_id" id="editCategoryParent">
                    <option value="">-- Aucune (racine) --</option>
                    <?php renderOptionsEdit($tree, $editCategory ? $editCategory['id'] : null, $editCategory ? ($editCategory['parent_id'] ?? null) : null); ?>
                </select>

                <div style="margin-top:15px; margin-right: 5px ;">
                    <button type="button" id="editCancelBtn">Annuler</button>
                    <button type="submit">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <?php require_once __DIR__ . '/../layouts/footer_admin.php'; ?>
</body>

</html>