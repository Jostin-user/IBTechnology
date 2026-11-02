<?php
// expects $notifications and $total from controller
if (!isset($notifications)) $notifications = [];
if (!isset($total)) $total = 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Notifications - Admin</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin/admin.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin/clients-dark.css">
    <style>
        .notifications-container{padding:20px}
        .notification-row{display:flex;align-items:center;justify-content:space-between;padding:12px;border-bottom:1px solid rgba(255,255,255,0.04)}
        .notification-left{display:flex;gap:12px;align-items:center}
        .notification-meta{font-size:0.9rem;color:var(--muted)}
        .actions a{margin-left:8px;color:var(--muted)}
        .empty{padding:40px;text-align:center;color:var(--muted)}
        .top-controls{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px}
    </style>
</head>
<body>
<?php require_once __DIR__ . '/../layouts/header_admin.php'; ?>
<div class="notifications-container">
    <div class="top-controls">
        <h1>Notifications</h1>
        <div>
            <a href="<?= BASE_URL ?>admin/notifications?mark_all=1" class="btn btn-outline" onclick="return confirm('Marquer toutes les notifications comme lues ?')">Marquer toutes lues</a>
            <a href="<?= BASE_URL ?>admin/notifications?delete_all=1" class="btn btn-danger" onclick="return confirm('Supprimer toutes les notifications ?')">Supprimer tout</a>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="stat-card" style="border-left:4px solid var(--success);margin-bottom:12px;padding:10px;">
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (count($notifications) === 0): ?>
        <div class="empty">
            <i class="fas fa-bell" style="font-size:2rem"></i>
            <h3>Aucune notification</h3>
        </div>
    <?php else: ?>
        <div class="list">
            <?php foreach ($notifications as $n): ?>
                <div class="notification-row">
                    <div class="notification-left">
                        <div>
                            <strong><?= htmlspecialchars($n['titre'] ?? 'Notification'); ?></strong>
                            <div class="notification-meta"><?= htmlspecialchars($n['message'] ?? '') ?></div>
                            <div class="notification-meta">Utilisateur: <?= htmlspecialchars($n['utilisateur_nom'] ?? $n['utilisateur_email'] ?? '—') ?></div>
                        </div>
                    </div>
                    <div class="actions">
                        <a href="<?= BASE_URL ?>admin/notifications?mark_read=1&id=<?= $n['id'] ?>" title="Marquer comme lue"><i class="fas fa-check"></i></a>
                        <a href="<?= BASE_URL ?>admin/notifications?delete=1&id=<?= $n['id'] ?>" title="Supprimer" onclick="return confirm('Supprimer cette notification ?')"><i class="fas fa-trash"></i></a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($total > count($notifications)): ?>
        <div style="margin-top:12px;color:var(--muted)">Affichage de <?= count($notifications) ?> sur <?= $total ?> notifications</div>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../layouts/footer_admin.php'; ?>
</body>
</html>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications | Commandes</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/header.css?v=20261102-cart">

    <style>
       /* .admin-notifications-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .admin-header h1 {
            margin: 0;
            font-size: 28px;
            color: #333;
        }

        .admin-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-left: 4px solid #007bff;
        }

        .stat-card.unread {
            border-left-color: #dc3545;
        }

        .stat-card.total {
            border-left-color: #28a745;
        }

        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #333;
            margin: 0;
        }

        .stat-label {
            font-size: 14px;
            color: #666;
            margin: 5px 0 0 0;
        }

        .notification-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .notification-table th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: bold;
            color: #333;
            border-bottom: 2px solid #dee2e6;
        }

        .notification-table td {
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
        }

        .notification-table tr:hover {
            background: #f9f9f9;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }

        .badge.unread {
            background: #cfe2ff;
            color: #084298;
        }

        .badge.read {
            background: #d1e7dd;
            color: #0f5132;
        }

        .command-link {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }

        .command-link:hover {
            text-decoration: underline;
        }

        .empty-message {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
            padding-top: 20px;
        }

        .pagination a,
        .pagination span {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-decoration: none;
            color: #007bff;
        }

        .pagination a:hover {
            background: #007bff;
            color: white;
        }

        .pagination span.current {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }*/
    </style>
</head>
<body>
  
<div class="admin-notifications-container">
    <div class="admin-header">
        <h1>📬 Gestion des Notifications</h1>
    </div>

    <p style="color: #666; margin-bottom: 20px;">
        Cette page affiche les notifications système. Les notifications sont envoyées automatiquement aux clients
        quand
        vous changez le statut de leurs commandes.
    </p>

    <div class="admin-stats">
        <div class="stat-card total">
            <p class="stat-number">0</p>
            <p class="stat-label">Notifications totales</p>
        </div>
        <div class="stat-card unread">
            <p class="stat-number">0</p>
            <p class="stat-label">Notifications non lues</p>
        </div>
    </div>

    <table class="notification-table">
        <thead>
            <tr>
                <th>Client</th>
                <th>Titre</th>
                <th>Commande</th>
                <th>Statut</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="5" class="empty-message">
                    Les notifications s'affichent ici au fur et à mesure que les clients les reçoivent.
                </td>
            </tr>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
  
</body>

</html>