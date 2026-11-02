<?php
require_once __DIR__ . '/../../../middlewares/AdminMiddleware.php';

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Admin | Liste des produits</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin/admin.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin/produit.css">
</head>

<body>
    <?php
    require_once __DIR__ . '/../layouts/header_admin.php';
    ?>
    <div class="admin-container">

        <h1>Liste des produits</h1>

        <?php if (isset($_GET['deleted']) && $_GET['deleted'] == '1'): ?>
            <div style="background: #16a34a; color: white; padding: 10px; border-radius: 6px; margin-bottom: 15px;">
                ✅ Produit supprimé avec succès
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div style="background: #dc2626; color: white; padding: 10px; border-radius: 6px; margin-bottom: 15px;">
                ❌ Erreur:
                <?php
                switch ($_GET['error']) {
                    case 'invalid_id':
                        echo 'ID produit invalide';
                        break;
                    case 'delete_failed':
                        echo 'Échec de la suppression';
                        break;
                    default:
                        echo 'Une erreur est survenue';
                }
                ?>
            </div>
        <?php endif; ?>

        <form method="GET" class="admin-filter">

            <input type="hidden" name="page" value="admin-produits-liste">

            <input type="text" name="q" placeholder="Rechercher un produit..." value="<?= $_GET['q'] ?? '' ?>">

            <select name="categorie">
                <option value="">Toutes les catégories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= (($_GET['categorie'] ?? '') == $cat['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="statut">
                <option value="">Tous les statuts</option>
                <option value="1" <?= ($_GET['statut'] ?? '') === '1' ? 'selected' : '' ?>>Actif</option>
                <option value="0" <?= ($_GET['statut'] ?? '') === '0' ? 'selected' : '' ?>>Inactif</option>
            </select>

            <select name="stock">
                <option value="">Stock</option>
                <option value="dispo" <?= ($_GET['stock'] ?? '') === 'dispo' ? 'selected' : '' ?>>En stock</option>
                <option value="rupture" <?= ($_GET['stock'] ?? '') === 'rupture' ? 'selected' : '' ?>>Rupture</option>
            </select>

            <button type="submit">🔍 Filtrer</button>

        </form>


        <a href="produits/create" style="color:#38bdf8;">➕ Ajouter un produit</a>

        <div class="table-wrapper">

            <table>
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Nom</th>
                        <th>Marque</th>
                        <th>Catégorie</th>
                        <th>Prix</th>
                        <th>Stock</th>
                        <th>Statut</th>
                        <th>Modifier</th>
                        <th>Supprimer</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($produits as $produit): ?>
                        <tr>
                            <td>
                                <img src="<?= BASE_URL ?>uploads/<?= $produit['image'] ?>" alt="img">
                            </td>

                            <td><?= htmlspecialchars($produit['nom']) ?></td>

                            <td><?= htmlspecialchars($produit['marque'] ?? 'N/A') ?></td>

                            <td><?= htmlspecialchars($produit['categorie']) ?></td>

                            <td>
                                <?= number_format($produit['prix']) ?> FCFA
                                <?php if ($produit['prix']): ?>
                                    <br><small style="color:#22c55e">
                                        Promo: <?= number_format($produit['prix']) ?> FCFA
                                    </small>
                                <?php endif; ?>
                            </td>

                            <td><?= $produit['stock'] ?></td>

                            <td>
                                <?php if ($produit['prix']): ?>
                                    <span class="badge actif">Actif</span>
                                <?php else: ?>
                                    <span class="badge inactif">Inactif</span>
                                <?php endif; ?>
                            </td>

                            <td class="actions">
                                <a href="produits/edit?id=<?= $produit['id'] ?>" title="Modifier">✏️</a>
                            </td>
                            <td>
                                <form method="POST" action="./produits/delete" style="display: inline;"
                                    onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')">
                                    <input type="hidden" name="id" value="<?= $produit['id'] ?>">
                                    <button type="submit"
                                        style="background: none; border: none; color: #ef4444; cursor: pointer; font-size: 16px;"
                                        title="Supprimer">🗑️</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        </div>
    </div>
      <?php if ($totalPages > 1): ?>

        <nav class="pagination">

            <!-- Bouton précédent -->
            <?php if ($pageActuelle > 1): ?>
                <a href="?p=<?= $pageActuelle - 1 ?>">«</a>
            <?php endif; ?>

            <!-- Première page -->
            <a href="?p=1" class="<?= $pageActuelle == 1 ? 'active' : '' ?>">
                1
            </a>

            <!-- Points de suspension avant -->
            <?php if ($pageActuelle > 4): ?>
                <span class="dots">…</span>
            <?php endif; ?>

            <!-- Fenêtre centrale -->
            <?php
            $start = max(2, $pageActuelle - 2);
            $end = min($totalPages - 1, $pageActuelle + 2);

            for ($i = $start; $i <= $end; $i++):
                ?>
                <a href="?p=<?= $i ?>" class="<?= $i == $pageActuelle ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <!-- Points de suspension après -->
            <?php if ($pageActuelle < $totalPages - 3): ?>
                <span class="dots">…</span>
            <?php endif; ?>

            <!-- Dernière page -->
            <?php if ($totalPages > 1): ?>
                <a href="?p=<?= $totalPages ?>" class="<?= $pageActuelle == $totalPages ? 'active' : '' ?>">
                    <?= $totalPages ?>
                </a>
            <?php endif; ?>

            <!-- Bouton suivant -->
            <?php if ($pageActuelle < $totalPages): ?>
                <a href="?p=<?= $pageActuelle + 1 ?>">»</a>
            <?php endif; ?>

        </nav>

    <?php endif; ?>
    <?php
    require_once __DIR__ . '/../layouts/footer_admin.php';
    ?>
</body>

</html>