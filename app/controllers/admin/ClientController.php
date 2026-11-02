<?php
require_once __DIR__.'/../../middlewares/AdminMiddleware.php';
require_once __DIR__.'/../../models/Client.php';
require_once __DIR__.'/../../models/Commande.php';

class ClientController
{
    private $db;
    private $clientModel;
    private $commandeModel;

    public function __construct($db = null)
    {
        if ($db === null) {
            require_once __DIR__.'/../../config/database.php';
            $this->db = (new Database())->getConnexion();
        } else {
            $this->db = $db;
        }
        $this->clientModel = new Client($this->db);
        $this->commandeModel = new Commande($this->db);
    }

    public function index()
    {
        AdminMiddleware::verifierAdmin();

        // Pagination
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 12;
        $offset = ($page - 1) * $perPage;

        // Filtres
        $filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';

        // Récupérer tous les clients
        $clientsResult = $this->clientModel->getAll();
        $allClients = $clientsResult->fetch_all(MYSQLI_ASSOC);

        // Appliquer les filtres
        if ($filter === 'active') {
            $allClients = array_filter($allClients, fn($c) => in_array(strtolower((string)$c['statut']), ['1', 'active'], true));
        } elseif ($filter === 'inactive') {
            $allClients = array_filter($allClients, fn($c) => in_array(strtolower((string)$c['statut']), ['0', 'inactive'], true));
        }

        if ($search) {
            $allClients = array_filter($allClients, function($c) use ($search) {
                return stripos($c['nom_complet'] ?? '', $search) !== false ||
                       stripos($c['email'] ?? '', $search) !== false;
            });
        }

        // Statistiques
        $totalClients = count($allClients);
        $clientsActifs = count(array_filter($allClients, fn($c) => in_array(strtolower((string)$c['statut']), ['1', 'active'], true)));

        // Pagination
        $totalPages = ceil($totalClients / $perPage);
        $clients = array_slice($allClients, $offset, $perPage);

        // Enrichir les données avec les statistiques de commandes
        foreach ($clients as &$client) {
            $client['commandes_count'] = $this->commandeModel->countCommandesByUser((int)$client['id']);
            $client['dernier_connexion'] = $client['dernier_connexion'] ?? null;
        }

        // Statistiques générales
        $nouveauxCeMois = $this->clientModel->countNouveauxCeMois();

        require_once __DIR__ . '/../../views/admin/clients/index.php';
    }

    public function toggleStatus()
    {
        AdminMiddleware::verifierAdmin();

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            $_SESSION['error'] = 'ID client invalide.';
            header('Location: '.BASE_URL.'admin/clients');
            exit;
        }

        if ($this->clientModel->toggleStatus($id)) {
            $_SESSION['success'] = 'Statut du client mis à jour avec succès.';
        } else {
            $_SESSION['error'] = 'Erreur lors de la mise à jour du statut.';
        }

        header('Location: '.BASE_URL.'admin/clients');
        exit;
    }

    public function delete()
    {
        AdminMiddleware::verifierAdmin();

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            $_SESSION['error'] = 'ID client invalide.';
            header('Location:'.BASE_URL.'admin/clients');
            exit;
        }

        // Vérifier s'il y a des commandes pour ce client
        $commandesCount = $this->commandeModel->countCommandesByUser($id);
        if ($commandesCount > 0) {
            $_SESSION['error'] = 'Impossible de supprimer ce client car il a des commandes associées.';
            header('Location: /admin/clients');
            exit;
        }

        if ($this->clientModel->delete($id)) {
            $_SESSION['success'] = 'Client supprimé avec succès.';
        } else {
            $_SESSION['error'] = 'Erreur lors de la suppression du client.';
        }

        header('Location: '.BASE_URL.'admin/clients');
        exit;
    }

    public function edit()
    {
        AdminMiddleware::verifierAdmin();

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            $_SESSION['error'] = 'ID client invalide.';
            header('Location: '.BASE_URL.'admin/clients');
            exit;
        }

        $client = $this->clientModel->getById($id);
        if (!$client) {
            $_SESSION['error'] = 'Client introuvable.';
            header('Location: '.BASE_URL.'admin/clients');
            exit;
        }

        // Render edit view (modal handled in index, but keep route for separate page if needed)
        require_once __DIR__ . '/../../views/admin/clients/edit.php';
    }

    public function update()
    {
        AdminMiddleware::verifierAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: '.BASE_URL.'admin/clients');
            exit;
        }

        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id <= 0) {
            $_SESSION['error'] = 'ID client invalide.';
            header('Location: '.BASE_URL.'admin/clients');
            exit;
        }

        $data = [
            'nom_complet' => trim($_POST['nom_complet'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'telephone' => trim($_POST['telephone'] ?? ''),
            'adresse' => trim($_POST['adresse'] ?? ''),
            'statut' => isset($_POST['statut']) && $_POST['statut'] === 'active' ? 'active' : 'inactive',
        ];

        // Basic validation
        if (empty($data['nom_complet']) || empty($data['email'])) {
            $_SESSION['error'] = 'Le nom et l\'email sont requis.';
            header('Location: '.BASE_URL.'admin/clients');
            exit;
        }

        if ($this->clientModel->update($id, $data)) {
            $_SESSION['success'] = 'Client mis à jour avec succès.';
        } else {
            $_SESSION['error'] = 'Erreur lors de la mise à jour du client.';
        }

        header('Location: '.BASE_URL.'admin/clients');
        exit;
    }

    public function details()
    {
        AdminMiddleware::verifierAdmin();

        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            header('Location: '.BASE_URL.'admin/clients');
            exit;
        }

        $client = $this->clientModel->getById($id);
        if (!$client) {
            header('Location: '.BASE_URL.'admin/clients');
            exit;
        }

        // Récupérer les commandes du client
        $commandes = $this->commandeModel->getCommandesByUtilisateur($client['id']);

        require_once __DIR__ . '/../../views/admin/clients/show.php';
    }
}