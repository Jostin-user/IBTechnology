<?php
require_once __DIR__ . '/../middlewares/AuthMiddleware.php';
require_once __DIR__ . '/../helpers/NotificationService.php';

class NotificationController
{
    private $db;
    private $notificationService;

    public function __construct()
    {
        require_once __DIR__ . '/../config/database.php';
        $this->db = (new Database())->getConnexion();
        $this->notificationService = new NotificationService($this->db);
    }

    /**
     * Affiche la page des notifications
     */
    public function index()
    {
        AuthMiddleware::verifierConnexion();

        $utilisateurId = $_SESSION['user_id'];

        // Pagination
        $parPage = 20;
        $pageCourante = isset($_GET['p']) ? max(1, (int) $_GET['p']) : 1;
        $offset = ($pageCourante - 1) * $parPage;

        // Récupérer les notifications
        $notifications = $this->notificationService->getAllNotifications($utilisateurId, $parPage, $offset);

        // Afficher la vue
        require_once __DIR__ . '/../views/notifications/index.php';
    }

    /**
     * Récupère les notifications non lues (API)
     */
    public function getNonLues()
    {
        AuthMiddleware::verifierConnexion();

        $utilisateurId = $_SESSION['user_id'];
        $notifications = $this->notificationService->getNotificationsNonLues($utilisateurId);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'notifications' => $notifications,
            'count' => count($notifications)
        ]);
        exit;
    }

    /**
     * Compte les notifications non lues (API)
     */
    public function countNonLues()
    {
        AuthMiddleware::verifierConnexion();

        $utilisateurId = $_SESSION['user_id'];
        $count = $this->notificationService->countNonLues($utilisateurId);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'count' => $count
        ]);
        exit;
    }

    /**
     * Marque une notification comme lue
     */
    public function marquerCommeLue($id)
    {
        AuthMiddleware::verifierConnexion();

        $utilisateurId = $_SESSION['user_id'];

        if ($this->notificationService->marquerCommeLue(is_numeric($id)?$id: 0, $utilisateurId)) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Notification marquée comme lue'
            ]);
        } else {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour'
            ]);
        }
        exit;
    }

    /**
     * Marque toutes les notifications comme lues
     */
    public function marquerToutCommeLu()
    {
        AuthMiddleware::verifierConnexion();

        $utilisateurId = $_SESSION['user_id'];

        if ($this->notificationService->marquerToutCommeLu($utilisateurId)) {

            $_SESSION['success'] = 'Toutes les notifications sont marquées comme lues';
            echo json_encode([
                'success' => true,
                'message' => 'Toutes les notifications sont marquées comme lues'
            ]);
            exit;
        } else {
            $_SESSION['error'] = 'Erreur lors de la mise à jour';
            echo json_encode([
                'success' => true,
                'message' => 'Toutes les notifications sont marquées comme lues'
            ]);
            exit;
        }
    }

    /**
     * Supprime une notification
     */
    public function supprimer($id)
    {
        AuthMiddleware::verifierConnexion();

        $utilisateurId = $_SESSION['user_id'];

        if ($this->notificationService->supprimer(filter_var($id, FILTER_VALIDATE_INT) ?: 0, $utilisateurId)) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Notification supprimée'
            ]);
            $_SESSION['success'] = 'Notification supprimée';
        } else {
            $_SESSION['error'] = 'Erreur lors de la suppression';
            echo json_encode([
                'success' => false,
                'message' => 'Erreur lors de la suppression'
            ]);
        }
        exit;
    }
}
