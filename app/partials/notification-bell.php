<?php
// Récupérer les notifications non lues si l'utilisateur est connecté
$unreadCount = 0;
$recentNotifications = [];

if (isset($_SESSION['user_id'])) {
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../helpers/NotificationService.php';
    $db = (new Database())->getConnexion();
    $notificationService = new NotificationService($db);
    $unreadCount = $notificationService->countNonLues($_SESSION['user_id']);
    $recentNotifications = $notificationService->getNotificationsNonLues($_SESSION['user_id']);
    // Limiter à 5 récentes
    $recentNotifications = array_slice($recentNotifications, 0, 5);
}
?>

<style>
    @media (max-width: 700px) {
        .notification-bell {
            display: none !important;
        }
    }
    .notification-bell {
        position: relative;
        cursor: pointer;
        font-size: 20px;
        transition: all 0.3s;
    }

    .notification-bell:hover {
        transform: scale(1.1);
    }

    .notification-badge {
        position: absolute;
        top: -8px;
        right: -8px;
        background: #dc3545;
        color: white;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: bold;
    }

    .notification-dropdown {
        position: absolute;
        top: 50px;
        right: -10px;
        width: 250px;
        background: #ffd;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        z-index: 1000;
        display: none;
        max-height: 500px;
        overflow-y: auto;
    }

    .notification-dropdown.active {
        display: block;
    }

    .notification-dropdown-header {
        padding: 15px;
        border-bottom: 1px solid #eee;
        font-weight: bold;
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: #061a4a;
    }

    .notification-dropdown-header h3 {
        margin: 0;
        font-size: 16px;
    }

    .notification-dropdown-item {
        padding: 12px 15px;
        border-bottom: 1px solid #f0f0f0;
        cursor: pointer;
        transition: background 0.2s;
        display: flex;
        gap: 10px;
        align-items: flex-start;
    }

    .notification-dropdown-item:hover {
        background: #f9f9f9;
    }

    .notification-dropdown-item.unread {
        background: #f0f7ff;
    }

    .notification-item-icon {
        font-size: 16px;
        flex-shrink: 0;
    }

    .notification-item-content {
        flex: 1;
    }

    .notification-item-title {
        font-weight: bold;
        font-size: 13px;
        color: #333;
        margin: 0;
    }

    .notification-item-message {
        font-size: 12px;
        color: #666;
        margin: 3px 0 0 0;
    }

    .notification-item-time {
        font-size: 11px;
        color: #999;
        margin-top: 3px;
    }

    .notification-dropdown-footer {
        padding: 12px 15px;
        text-align: center;
        border-top: 1px solid #eee;
    }

    .notification-dropdown-footer a {
        color: #007bff;
        text-decoration: none;
        font-size: 13px;
        font-weight: bold;
    }

    .notification-dropdown-footer a:hover {
        text-decoration: underline;
    }

    .notification-empty {
        padding: 30px 15px;
        text-align: center;
        color: #999;
        font-size: 13px;
    }

    .notification-bell-icon {
        display: inline-block;
    }

    @media (min-width : 450px) {
        .notification-dropdown {
            width: 350px;
            margin-left: 15px;
            margin-right: 20px;
        }

    }
</style>

<?php if (isset($_SESSION['user_id'])): ?>
    <div class="notification-bell" onclick="toggleNotificationDropdown()" id="notificationBell">
        <span class="notification-bell-icon">🔔</span>
        <?php if ($unreadCount > 0): ?>
            <span class="notification-badge"
                id="notificationBadge"><?php echo min($unreadCount, 9); ?><?php echo $unreadCount > 9 ? '+' : ''; ?></span>
        <?php endif; ?>

        <div class="notification-dropdown" id="notificationDropdown">
            <div class="notification-dropdown-header">
                <h3>Notifications</h3>
                <span onclick="closeNotificationDropdown()" style="cursor: pointer; font-size: 18px;">✕</span>
            </div>

            <?php if (empty($recentNotifications)): ?>
                <div class="notification-empty">
                    Aucune nouvelle notification
                </div>
            <?php else: ?>
                <?php foreach ($recentNotifications as $notif): ?>
                    <div class="notification-dropdown-item <?php echo !$notif['lu'] ? 'unread' : ''; ?>">
                        <div class="notification-item-icon">
                            <?php
                            $icons = [
                                'en_attente' => '⏳',
                                'validee' => '✓',
                                'en_preparation' => '📦',
                                'en_livraison' => '🚚',
                                'expediee' => '🚀',
                                'livree' => '✅',
                                'annulee' => '❌'
                            ];
                            echo $icons[$notif['commande_statut']] ?? '📢';
                            ?>
                        </div>
                        <div class="notification-item-content"
                            onclick="window.location.href = 'commandes/details/<?php echo $notif['commande_id']; ?>'">
                            <p class="notification-item-title">
                                <?php echo htmlspecialchars(substr($notif['titre'], 0, 50)); ?>
                            </p>
                            <p class="notification-item-message">
                                <?php echo htmlspecialchars(substr($notif['message'], 0, 60)); ?>...
                            </p>
                            <p class="notification-item-time">
                                <?php
                                $time = strtotime($notif['date_creation']);
                                $now = time();
                                $diff = $now - $time;

                                if ($diff < 60) {
                                    echo 'À l\'instant';
                                } elseif ($diff < 3600) {
                                    echo floor($diff / 60) . ' min';
                                } elseif ($diff < 86400) {
                                    echo floor($diff / 3600) . 'h';
                                } else {
                                    echo date('d/m', $time);
                                }
                                ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <div class="notification-dropdown-footer">
                <a href="notifications">Voir toutes les notifications →</a>
            </div>
        </div>
    </div>

    <script>
        function toggleNotificationDropdown() {
            const dropdown = document.getElementById('notificationDropdown');
            dropdown.classList.toggle('active');

            // Marquer les notifications comme vues au clic
            if (dropdown.classList.contains('active')) {
                markNotificationsAsRead();
            }
        }

        function closeNotificationDropdown() {
            document.getElementById('notificationDropdown').classList.remove('active');
        }

        // Fermer le dropdown quand on clique ailleurs
        document.addEventListener('click', function (event) {
            const bell = document.getElementById('notificationBell');
            if (!bell || !bell.contains(event.target)) {
                closeNotificationDropdown();
            }
        });

        function markNotificationsAsRead() {
            const unreadItems = document.querySelectorAll('.notification-dropdown-item.unread');
            if (unreadItems.length === 0) return;

            unreadItems.forEach(item => {
                item.classList.remove('unread');
            });

            // Optionnel: faire un appel AJAX pour marquer comme lues en base de données
            fetch('./api/notifications/mark-all-read', {
                method: 'POST'
            }).catch(err => console.log('Notification mark read failed silently'));
        }

        // Mettre à jour les notifications automatiquement toutes les 30 secondes
        setInterval(function () {
            fetch('./api/notifications/count')
                .then(response => response.json())
                .then(data => {
                    const badge = document.getElementById('notificationBadge');
                    if (data.count > 0) {
                        if (!badge) {
                            const bell = document.getElementById('notificationBell');
                            const newBadge = document.createElement('span');
                            newBadge.className = 'notification-badge';
                            newBadge.id = 'notificationBadge';
                            newBadge.textContent = Math.min(data.count, 9) + (data.count > 9 ? '+' : '');
                            bell.appendChild(newBadge);
                        } else {
                            badge.textContent = Math.min(data.count, 9) + (data.count > 9 ? '+' : '');
                        }
                    } else if (badge) {
                        badge.remove();
                    }
                })
                .catch(err => console.log('Update failed silently'));
        }, 30000);
    </script>
<?php endif; ?>