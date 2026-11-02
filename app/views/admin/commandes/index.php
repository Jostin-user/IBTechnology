<?php
require_once __DIR__ . '/../../../middlewares/AdminMiddleware.php';
AdminMiddleware::verifierAdmin();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Admin | Gestion des Commandes</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin/admin.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin/commande.css">
</head>

<body>
    <?php require_once __DIR__ . '/../layouts/header_admin.php'; ?>

    <div class="admin-container">
        <h1>Gestion des Commandes</h1>

        <!-- Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div style="background: #10b981; color: white; padding: 10px; margin-bottom: 20px; border-radius: 4px;">
                <?= $_SESSION['success'] ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div style="background: #ef4444; color: white; padding: 10px; margin-bottom: 20px; border-radius: 4px;">
                <?= $_SESSION['error'] ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Filtres -->
        <form method="GET" class="filters">
            <input type="text" name="q" placeholder="Rechercher (ID, client, email)"
                value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">

            <select name="statut">
                <option value="">Tous les statuts</option>
                <option value="en_attente" <?= ($_GET['statut'] ?? '') === 'en_attente' ? 'selected' : '' ?>>En attente
                </option>
                <option value="validee" <?= ($_GET['statut'] ?? '') === 'validee' ? 'selected' : '' ?>>Validée</option>
                <option value="en_preparation" <?= ($_GET['statut'] ?? '') === 'en_preparation' ? 'selected' : '' ?>>En
                    préparation</option>
                <option value="expediee" <?= ($_GET['statut'] ?? '') === 'expediee' ? 'selected' : '' ?>>Expédiée</option>
                <option value="livree" <?= ($_GET['statut'] ?? '') === 'livree' ? 'selected' : '' ?>>Livrée</option>
                <option value="annulee" <?= ($_GET['statut'] ?? '') === 'annulee' ? 'selected' : '' ?>>Annulée</option>
            </select>

            <input type="date" name="date_debut" value="<?= htmlspecialchars($_GET['date_debut'] ?? '') ?>">

            <input type="date" name="date_fin" value="<?= htmlspecialchars($_GET['date_fin'] ?? '') ?>">

            <input type="text" name="client" placeholder="Nom du client"
                value="<?= htmlspecialchars($_GET['client'] ?? '') ?>">

            <button type="submit">Filtrer</button>
            <a href="<?=BASE_URL?>admin/commandes"
                style="padding: 8px 16px; background: #6b7280; color: white; text-decoration: none; border-radius: 4px;">Réinitialiser</a>
        </form>

        <!-- Statistiques rapides -->
        <div
            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-bottom: 20px;">
            <div style="background: #1e293b; padding: 15px; border-radius: 8px; text-align: center;">
                <div style="font-size: 24px; font-weight: bold; color: #3b82f6;"><?= count($commandes) ?></div>
                <div style="color: #9ca3af;">Commandes affichées</div>
            </div>
            <div style="background: #1e293b; padding: 15px; border-radius: 8px; text-align: center;">
                <div style="font-size: 24px; font-weight: bold; color: #10b981;">
                    <?= array_sum(array_column($commandes, 'montant_total')) ?> FCFA
                </div>
                <div style="color: #9ca3af;">Montant total</div>
            </div>
        </div>

        <!-- Tableau des commandes -->
        <div style="overflow-x: auto;">
            <table
                style="width: 100%; border-collapse: collapse; background: #1e293b; border-radius: 8px; overflow: hidden;">
                <thead>
                    <tr style="background: #0f172a;">
                        <th style="padding: 12px; text-align: left; color: #e5e7eb;">ID</th>
                        <th style="padding: 12px; text-align: left; color: #e5e7eb;">Client</th>
                        <th style="padding: 12px; text-align: left; color: #e5e7eb;">Montant</th>
                        <th style="padding: 12px; text-align: left; color: #e5e7eb;">Statut</th>
                        <th style="padding: 12px; text-align: left; color: #e5e7eb;">Paiement</th>
                        <th style="padding: 12px; text-align: left; color: #e5e7eb;">Date</th>
                        <th style="padding: 12px; text-align: left; color: #e5e7eb;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($commandes as $commande): ?>
                        <tr style="border-bottom: 1px solid #374151;">
                            <td style="padding: 12px; color: #e5e7eb;">
                                #<?= str_pad($commande['id'], 4, '0', STR_PAD_LEFT) ?>
                            </td>
                            <td style="padding: 12px; color: #e5e7eb;">
                                <div style="font-weight: bold;"><?= htmlspecialchars($commande['nom_complet']) ?></div>
                                <div style="font-size: 12px; color: #9ca3af;"><?= htmlspecialchars($commande['email']) ?>
                                </div>
                            </td>
                            <td style="padding: 12px; color: #e5e7eb; font-weight: bold;">
                                <?= number_format($commande['montant_total'], 0, ',', ' ') ?> FCFA
                            </td>
                            <td style="padding: 12px;">
                                <span class="status-badge status-<?= $commande['status_class'] ?>">
                                    <?= $commande['status_label'] ?>
                                </span>
                            </td>
                            <td style="padding: 12px; color: #e5e7eb;">
                                <?= ucfirst($commande['methode_paiement'] ?? 'N/A') ?>
                                <br>
                                <span style="font-size: 12px; color: #9ca3af;">
                                    <?= ucfirst($commande['statut_paiement'] ?? 'N/A') ?>
                                </span>
                            </td>
                            <td style="padding: 12px; color: #e5e7eb;">
                                <?= date('d/m/Y H:i', strtotime($commande['date_commande'])) ?>
                            </td>
                            <td style="padding: 12px;">
                                <div class="actions">
                                    <a href="<?=BASE_URL?>admin/commandes/details/<?= $commande['id'] ?>"
                                        class="btn-action btn-view">Voir</a>
                                    <?php if (in_array($commande['statut'], ['en_attente', 'validee'])): ?>
                                        <button onclick="confirmerAnnulation(<?= $commande['id'] ?>)"
                                            class="btn-action btn-cancel">Annuler</button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div style="margin-top: 20px; text-align: center;">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?p=<?= $i ?>&<?= http_build_query(array_diff_key($_GET, ['p' => ''])) ?>"
                        style="display: inline-block; padding: 8px 12px; margin: 0 2px; background: <?= $i == $pageCourante ? '#3b82f6' : '#374151' ?>; color: white; text-decoration: none; border-radius: 4px;">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>

        <?php if (empty($commandes)): ?>
            <div style="text-align: center; padding: 40px; color: #9ca3af;">
                Aucune commande trouvée avec ces critères.
            </div>
        <?php endif; ?>
    </div>

    <script>
        function confirmerAnnulation(commandeId) {
            if (confirm('Êtes-vous sûr de vouloir annuler cette commande ?')) {
                window.location.href = '<?=BASE_URL?>admin/commandes/annuler/' + commandeId;
            }
        }
    </script>

    <?php require_once __DIR__ . '/../layouts/footer_admin.php'; ?>
</body>

</html>