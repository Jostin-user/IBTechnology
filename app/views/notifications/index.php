<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications Commandes</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/header.css?v=20261102-cart">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/notifications.css">

    <style>
        /*.notifications-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .notifications-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eee;
        }

        .notifications-header h1 {
            margin: 0;
            font-size: 28px;
            color: #333;
        }

        .notifications-actions {
            display: flex;
            gap: 10px;
        }

        .btn-action {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            transition: all 0.3s;
        }

        .btn-mark-all {
            background: #007bff;
            color: white;
        }

        .btn-mark-all:hover {
            background: #0056b3;
        }

        .notification-item {
            background: white;
            border-left: 4px solid #28a745;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .notification-item.unread {
            background: #f0f7ff;
            border-left-color: #007bff;
        }

        .notification-item.status-validee {
            border-left-color: #28a745;
        }

        .notification-item.status-en_preparation {
            border-left-color: #ffc107;
        }

        .notification-item.status-en_livraison,
        .notification-item.status-expediee {
            border-left-color: #17a2b8;
        }

        .notification-item.status-livree {
            border-left-color: #28a745;
        }

        .notification-item.status-annulee {
            border-left-color: #dc3545;
        }

        .notification-item:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .notification-content {
            flex: 1;
        }

        .notification-title {
            font-weight: bold;
            font-size: 16px;
            color: #333;
            margin: 0 0 5px 0;
        }

        .notification-message {
            color: #666;
            font-size: 14px;
            margin: 0 0 10px 0;
            line-height: 1.5;
        }

        .notification-meta {
            display: flex;
            gap: 15px;
            font-size: 12px;
            color: #999;
        }

        .notification-meta a {
            color: #007bff;
            text-decoration: none;
            cursor: pointer;
        }

        .notification-meta a:hover {
            text-decoration: underline;
        }

        .notification-actions {
            display: flex;
            gap: 10px;
            margin-left: 20px;
        }

        .btn-small {
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 12px;
            background: #f0f0f0;
            color: #333;
            transition: all 0.2s;
        }

        .btn-small:hover {
            background: #e0e0e0;
        }

        .btn-delete {
            color: #dc3545;
        }

        .btn-delete:hover {
            background: #f8d7da;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }

        .empty-state h2 {
            color: #666;
            margin: 0 0 10px 0;
        }

        .empty-state p {
            color: #999;
            margin: 0;
        }

        .unread-badge {
            display: inline-block;
            width: 8px;
            height: 8px;
            background: #007bff;
            border-radius: 50%;
            margin-right: 10px;
        }

        .notification-command-id {
            background: #f0f0f0;
            padding: 2px 8px;
            border-radius: 3px;
            font-weight: bold;
            color: #333;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #eee;
        }

        .pagination a,
        .pagination span {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-decoration: none;
            color: #007bff;
            transition: all 0.2s;
        }

        .pagination a:hover {
            background: #007bff;
            color: white;
        }

        .pagination span.current {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }

        .pagination span.disabled {
            color: #ccc;
            cursor: not-allowed;
        }*/
    </style>
</head>

<body>
    <?php require_once __DIR__ . '/../../layouts/header.php'; ?>
    <div class="notifications-container">
        <div class="notifications-header">
            <h1>📬 Mes Notifications</h1>
            <?php if (!empty($notifications)): ?>
                <div class="notifications-actions">
                    <button class="btn-action btn-mark-all" onclick="marquerToutCommeLu()">
                        Marquer tout comme lu
                    </button>
                </div>
            <?php endif; ?>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"
                style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <?php echo htmlspecialchars($_SESSION['success']);
                unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"
                style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <?php echo htmlspecialchars($_SESSION['error']);
                unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($notifications)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">📭</div>
                <h2>Aucune notification</h2>
                <p>Vous n'avez pas de notifications pour le moment.</p>
            </div>
        <?php else: ?>
            <?php foreach ($notifications as $notification): ?>
                <div
                    class="notification-item <?php echo $notification['lu'] ? '' : 'unread'; ?> status-<?php echo htmlspecialchars($notification['commande_statut']); ?>">
                    <div class="notification-content">
                        <?php if (!$notification['lu']): ?>
                            <span class="unread-badge"></span>
                        <?php endif; ?>
                        <p class="notification-title">
                            <?php echo htmlspecialchars($notification['titre']); ?>
                        </p>
                        <p class="notification-message">
                            <?php echo htmlspecialchars($notification['message']); ?>
                        </p>
                        <div class="notification-meta">
                            <span>Commande <span
                                    class="notification-command-id">#<?php echo str_pad($notification['commande_id'], 4, '0', STR_PAD_LEFT); ?></span></span>
                            <a href="commandes/details/<?php echo $notification['commande_id']; ?>">Voir la commande</a>
                            <span><?php echo date('d/m/Y à H:i', strtotime($notification['date_creation'])); ?></span>
                        </div>
                    </div>
                    <div class="notification-actions">
                        <?php if (!$notification['lu']): ?>
                            <button class="btn-small" onclick="marquerCommeLue(<?php echo $notification['id']; ?>)">
                                Marquer comme lu
                            </button>
                        <?php endif; ?>
                        <button class="btn-small btn-delete"
                            onclick="supprimerNotification(<?php echo $notification['id']; ?>)">
                            Supprimer
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script>
        function marquerCommeLue(notificationId) {
           
            fetch('notifications/mark-read/' + notificationId, {
                method: 'POST'
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
        }

        function marquerToutCommeLu() {
            if (confirm('Marquer toutes les notifications comme lues ?')) {
                fetch('notifications/mark-all-read', {
                    method: 'POST'
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
                        alert('Une erreur est survenue');
                    });
            }
        }

        function supprimerNotification(notificationId) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cette notification ?')) {
                fetch('notifications/delete/' + notificationId, {
                    method: 'POST'
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
            }
        }
    </script>

    <?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
</body>

</html>