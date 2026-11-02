<?php
require_once __DIR__ . '/../../middlewares/AdminMiddleware.php';
require_once __DIR__ . '/../../models/Notification.php';
require_once __DIR__ . '/../../helpers/Logger.php';

class NotificationAdminController
{
    private $notificationModel;
    private $db;
    private $logger;

    public function __construct($db)
    {
        $this->db = $db;
        $this->notificationModel = new Notification($db);
        $this->logger = new Logger(__DIR__ . '/../../storage/Logs');
    }

    public function index()
    {
        AdminMiddleware::verifierAdmin();

        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 50;
        $offset = ($page - 1) * $perPage;

        $total = $this->notificationModel->countAll();
        $notifications = $this->notificationModel->getAllForAdmin($perPage, $offset);

        require_once __DIR__ . '/../../views/admin/notifications/index.php';
    }

    public function markRead()
    {
        AdminMiddleware::verifierAdmin();
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id > 0) {
            $this->notificationModel->marquerCommeLue($id);
            $this->logger->info("Admin marked notification as read", ['notification_id' => $id, 'admin_id' => $_SESSION['user_id'] ?? null]);
        }
        header('Location: ' . BASE_URL . 'admin/notifications');
        exit;
    }

    public function delete()
    {
        AdminMiddleware::verifierAdmin();
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id > 0) {
            $this->notificationModel->supprimer($id);
            $this->logger->info("Admin deleted notification", ['notification_id' => $id, 'admin_id' => $_SESSION['user_id'] ?? null]);
        }
        header('Location: ' . BASE_URL . 'admin/notifications');
        exit;
    }

    public function markAllRead()
    {
        AdminMiddleware::verifierAdmin();
        // mark all visible as read for admin view - we can iterate and mark
        $all = $this->notificationModel->getAllForAdmin(1000, 0);
        foreach ($all as $n) {
            $this->notificationModel->marquerCommeLue($n['id']);
        }
        $this->logger->info('Admin marked all notifications as read', ['admin_id' => $_SESSION['user_id'] ?? null]);
        header('Location: ' . BASE_URL . 'admin/notifications');
        exit;
    }

    public function deleteAll()
    {
        AdminMiddleware::verifierAdmin();
        // delete all notifications (use with caution)
        $this->db->query("DELETE FROM notifications");
        $this->logger->info('Admin deleted all notifications', ['admin_id' => $_SESSION['user_id'] ?? null]);
        header('Location: ' . BASE_URL . 'admin/notifications');
        exit;
    }
}

?>
