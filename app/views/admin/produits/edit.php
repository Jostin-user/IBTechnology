<?php
// Sécurité : accès admin uniquement
require_once __DIR__ . '/../../../middlewares/AdminMiddleware.php';
require_once __DIR__ . '/../../../models/Categorie.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../controllers/admin/ProduitController.php';

AdminMiddleware::verifierAdmin();

// Vérification de l'ID du produit
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    header('Location: ../../produits?error=invalid_id');
    exit;
}

$db = (new Database())->getConnexion();
$controller = new ProduitController($db);
$categorieModel = new Categorie($db);

// Récupération du produit
$produit = $controller->getProduitById($id);
if (!$produit) {
    header('Location: ../../produits?error=product_not_found');
    exit;
}

$categoriesTree = $categorieModel->getTree();

// Génération du token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification du token CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $errors[] = "Token de sécurité invalide.";
    } else {
        // Validation et sécurité
        $nom = trim($_POST['nom'] ?? '');
        if (empty($nom) || strlen($nom) < 2) {
            $errors[] = "Le nom doit contenir au moins 2 caractères.";
        } elseif (strlen($nom) > 150) {
            $errors[] = "Le nom ne peut pas dépasser 150 caractères.";
        }

        $marque = trim($_POST['marque'] ?? '');
        if (strlen($marque) > 40) {
            $errors[] = "La marque ne peut pas dépasser 40 caractères.";
        }

        $description = trim($_POST['description'] ?? '');
        if (strlen($description) > 1000) {
            $errors[] = "La description ne peut pas dépasser 1000 caractères.";
        }

        // Validation des nombres
        $prix = $_POST['prix'] ?? '';
        if (!is_numeric($prix) || $prix < 0) {
            $errors[] = "Le prix doit être un nombre positif.";
        }

        $prix_promo = $_POST['prix_promo'] ?? '';
        if (!empty($prix_promo) && (!is_numeric($prix_promo) || $prix_promo < 0)) {
            $errors[] = "Le prix promotionnel doit être un nombre positif.";
        }
        $categorie_id = (int) ($_POST['categorie_id'] ?? 0);
        if ($categorie_id <= 0) {
            $errors[] = "Veuillez sélectionner une catégorie valide.";
        }

        // Gestion de l'image (optionnelle pour l'édition)
        $imageName = $produit['image']; // Garder l'ancienne image par défaut

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg','image/jpg', 'image/png', 'image/gif', 'image/webp'];
            $maxSize = 5 * 1024 * 1024; // 5MB

            if (!in_array($_FILES['image']['type'], $allowedTypes)) {
                $errors[] = "Type d'image non autorisé. Utilisez JPG, PNG, GIF ou WebP.";
            } elseif ($_FILES['image']['size'] > $maxSize) {
                $errors[] = "L'image ne peut pas dépasser 5MB.";
            } elseif (!getimagesize($_FILES['image']['tmp_name'])) {
                $errors[] = "Le fichier uploadé n'est pas une image valide.";
            } else {
                // Génération d'un nom de fichier sécurisé
                $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $extension = strtolower($extension);
                $imageName = time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;

                $uploadPath = __DIR__. '/../../../../public/uploads/' . $imageName;
                $_SESSION['erreur']=$uploadPath ;

                // Création du dossier uploads s'il n'existe pas
                $uploadDir = dirname($uploadPath);
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                    // Changement des permissions du fichier
                    chmod($uploadPath, 0644);
                } else {
                    $errors[] = "Erreur lors de l'upload de l'image.";
                }
            }
        }

        if (empty($errors)) {
            $data = [
                'nom' => htmlspecialchars($nom, ENT_QUOTES, 'UTF-8'),
                'description' => htmlspecialchars($description, ENT_QUOTES, 'UTF-8'),
                'prix' => (float) $prix,
                'prix_promo' => !empty($prix_promo) ? (float) $prix_promo : null,
                'categorie_id' => $categorie_id,
                'marque' => htmlspecialchars($marque, ENT_QUOTES, 'UTF-8'),
                'image' => $imageName,
                'actif' => isset($_POST['actif']) ? 1 : 0
            ];

            if ($controller->modifierProduit($id, $data)) {
                $success = true;
                // Mettre à jour les données du produit pour l'affichage
                $produit = $controller->getProduitById($id);
            } else {
                $errors[] = "Erreur lors de la modification du produit.";
                // Supprimer la nouvelle image en cas d'erreur
                if (isset($uploadPath) && $imageName !== $produit['image'] && file_exists($uploadPath)) {
                    unlink($uploadPath);
                }
            }
        }
    }
}

function renderOptionsEdit($nodes, $currentId = null, $prefix = '')
{
    foreach ($nodes as $node) {
        $selected = ($node['id'] == $currentId) ? 'selected' : '';
        echo '<option value="' . $node['id'] . '" ' . $selected . '>' . $prefix . htmlspecialchars($node['nom']) . '</option>';
        if (!empty($node['children'])) {
            renderOptionsEdit($node['children'], $currentId, $prefix . '-- ');
        }
    }
}
?>


<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
    <title>Admin | Modifier produit - IB Technology</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin/admin.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin/action-produit.css">
    <style>
        /* ===== IMAGE ACTUELLE ===== */
        .current-image {
            margin: 15px 0;
            padding: 15px;
            background: #1e293b;
            border-radius: 8px;
            border: 1px solid #334155;
        }

        .current-image p {
            margin: 0 0 10px 0;
            font-weight: 600;
            color: #cbd5e1;
        }

        .current-image img {
            border-radius: 6px;
            border: 2px solid #475569;
            max-width: 200px;
            max-height: 200px;
            object-fit: cover;
        }
    </style>
</head>

<body>
    <?php
    require_once __DIR__ . '/../layouts/header_admin.php';
    ?>
    <div class="admin-container">
        <a href="./../produits" class="back-btn">
            <i class="fas fa-arrow-left"></i> ← Retour à la liste
        </a>

        <h1>MODIFICATION DE PRODUIT</h1>

        <?php if ($success): ?>
            <div class="success">✅ Produit modifié avec succès</div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="admin-card">

            <h2>Modifier le produit</h2>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token"
                    value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">

                <label>Nom du produit</label>
                <input type="text" name="nom"
                    value="<?php echo htmlspecialchars($produit['nom'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>

                <label>Marque</label>
                <input type="text" name="marque"
                    value="<?php echo htmlspecialchars($produit['marque'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                    placeholder="Ex: Apple, Samsung, etc.">

                <label>Catégorie</label>
                <select name="categorie_id" required>
                    <option value="">-- Choisir une catégorie --</option>
                    <?php renderOptionsEdit($categoriesTree, $produit['categorie_id'] ?? null); ?>
                </select>
                <div>
                    <label>Prix (FCFA)</label>
                    <input type="number" name="prix"
                        value="<?php echo htmlspecialchars($produit['prix'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" step="0.01"
                        required>
                </div>

                <div>
                    <label>Prix promo (optionnel)</label>
                    <input type="number" name="prix_promo"
                        value="<?php echo htmlspecialchars($produit['prix_promo'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                        step="0.01">
                </div>

                <div>
                    <label>Stock</label>
                    <input type="number" name="stock"
                        value="<?php echo htmlspecialchars($produit['stock'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" readonly onclick="alert('stock non modifiable dans l\'edition de produit')">
                </div>

                <label>Description</label>
                <textarea name="description"
                    rows="4"><?php echo htmlspecialchars($produit['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>

                <label>Image du produit (optionnel - laisser vide pour garder l'image actuelle)</label>
                <?php if (!empty($produit['image'])): ?>
                    <div class="current-image">
                        <p>Image actuelle:</p>
                        <img src="<?php echo htmlspecialchars(BASE_URL . 'uploads/' . $produit['image'], ENT_QUOTES, 'UTF-8'); ?>"
                            alt="Image actuelle" style="max-width: 200px; max-height: 200px;">
                    </div>
                <?php endif; ?>
                <input type="file" name="image" accept="image/*">

                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="actif" <?php echo (isset($produit['actif']) && $produit['actif'] == 1) ? 'checked' : ''; ?>>
                        Produit actif
                    </label>
                </div>

                <div class="form-actions">
                    <button type="submit" name="modifierProduit">
                        ✏️ Modifier le produit
                    </button>
                </div>

            </form>

        </div>
    </div>

</body>

</html>