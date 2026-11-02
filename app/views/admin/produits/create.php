<?php
// Sécurité : accès admin uniquement
require_once __DIR__ . '/../../../middlewares/AdminMiddleware.php';
require_once __DIR__ . '/../../../models/Categorie.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../controllers/admin/ProduitController.php';

AdminMiddleware::verifierAdmin();
$db = (new Database())->getConnexion();

$controller = new ProduitController($db);
$categorieModel = new Categorie($db);

// Génération du token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification du token CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $errors[] = "Token de sécurité invalide.";
    } else {
        // Validation et sécurité
        $errors = [];
        // Validation et sécurité
        $errors = [];

        // Validation des champs texte
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

        $stock = $_POST['stock'] ?? '';
        if (!is_numeric($stock) || $stock < 0) {
            $errors[] = "Le stock doit être un nombre positif.";
        }

        $categorie_id = (int) ($_POST['categorie_id'] ?? 0);
        if ($categorie_id <= 0) {
            $errors[] = "Veuillez sélectionner une catégorie valide.";
        }

        // Validation de l'image
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Une image est requise.";
        } else {
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            $maxSize = 5 * 1024 * 1024; // 5MB

            if (!in_array($_FILES['image']['type'], $allowedTypes)) {
                $errors[] = "Type d'image non autorisé. Utilisez JPG, PNG, GIF ou WebP.";
            } elseif ($_FILES['image']['size'] > $maxSize) {
                $errors[] = "L'image ne peut pas dépasser 5MB.";
            } elseif (!getimagesize($_FILES['image']['tmp_name'])) {
                $errors[] = "Le fichier uploadé n'est pas une image valide.";
            }
        }

        if (empty($errors)) {
            // Génération d'un nom de fichier sécurisé
            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $extension = strtolower($extension);
            $imageName = time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;

            $uploadPath = __DIR__ . '/../../../../public/uploads/' . $imageName;

            // Création du dossier uploads s'il n'existe pas
            $uploadDir = dirname($uploadPath);
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                // Changement des permissions du fichier
                chmod($uploadPath, 0644);

                $data = [
                    'nom' => htmlspecialchars($nom, ENT_QUOTES, 'UTF-8'),
                    'description' => htmlspecialchars($description, ENT_QUOTES, 'UTF-8'),
                    'prix' => (float) $prix,
                    'prix_promo' => !empty($prix_promo) ? (float) $prix_promo : null,
                    'stock' => (int) $stock,
                    'categorie_id' => $categorie_id,
                    'marque' => htmlspecialchars($marque, ENT_QUOTES, 'UTF-8'),
                    'image' => $imageName,
                    'actif' => isset($_POST['actif']) ? 1 : 0
                ];

                if ($controller->store($data)) {
                    header('Location: ../../produits?success=1');
                    exit;
                } else {
                    $errors[] = "Erreur lors de l'ajout du produit.";
                    // Supprimer l'image uploadée en cas d'erreur
                    if (file_exists($uploadPath)) {
                        unlink($uploadPath);
                    }
                }
            } else {
                $errors[] = "Erreur lors de l'upload de l'image.";
            }
        }
    } // Fin de la vérification CSRF
}

$categoriesTree = $categorieModel->getTree();

function renderOptions($nodes, $prefix = '')
{
    foreach ($nodes as $node) {
        echo '<option value="' . $node['id'] . '">' . $prefix . htmlspecialchars($node['nom']) . '</option>';
        if (!empty($node['children'])) {
            renderOptions($node['children'], $prefix . '-- ');
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
    <title>Admin | Gestion des produits - IB Technology</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin/admin.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin/action-produit.css">
</head>

<body>
    <?php
    require_once __DIR__ . '/../layouts/header_admin.php';

    ?>
    <div class="admin-container">
        <a href="./../produits" class="back-btn">
            <i class="fas fa-arrow-left"></i> ← Retour à la liste
        </a>

        <h1>GESTION DES PRODUITS</h1>

        <?php if (isset($_GET['success'])): ?>
            <div class="success">✅ Produit ajouté avec succès</div>
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

            <h2>Ajout de produit</h2>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token"
                    value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">

                <label>Nom du produit</label>
                <input type="text" name="nom" required>

                <label>Marque</label>
                <input type="text" name="marque" placeholder="Ex: Apple, Samsung, etc.">

                <label>Catégorie</label>
                <select name="categorie_id" required>
                    <option value="">-- Choisir une catégorie --</option>
                    <?php renderOptions($categoriesTree); ?>
                </select>

                <div class="form-row">
                    <div>
                        <label>Prix (FCFA)</label>
                        <input type="number" name="prix" step="0.01" required>
                    </div>

                    <div>
                        <label>Prix promo (optionnel)</label>
                        <input type="number" name="prix_promo" step="0.01">
                    </div>

                    <div>
                        <label>Stock</label>
                        <input type="number" name="stock" required>
                    </div>
                </div>

                <label>Description</label>
                <textarea name="description" rows="4"></textarea>

                <label>Image du produit</label>
                <input type="file" name="image" accept="image/*" required>

                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="actif" checked>
                        Produit actif
                    </label>
                </div>

                <div class="form-actions">
                    <button type="submit" name="ajouterProduit">
                        ➕ Ajouter le produit
                    </button>
                </div>

            </form>

        </div>
    </div>

</body>

</html>