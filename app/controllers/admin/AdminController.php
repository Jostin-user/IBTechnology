<?php
require_once __DIR__ . '/../../models/Admin.php';
require_once __DIR__ . '/../../middlewares/AdminMiddleware.php';
require_once __DIR__ . '/../../models/Visite.php';
class AdminController
{
    private $adminModel;
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
        $this->adminModel = new Admin($db);
    }

    public function dashboard()
    {
        // KPIs
        $data = [
            'produits' => $this->adminModel->totalProduits(),
            'stockBas' => $this->adminModel->produitsStockBas(),
            'commandes' => $this->adminModel->totalCommandes(),
            'commandesAttente' => $this->adminModel->commandesEnAttente(),
            'chiffreAffaires' => $this->adminModel->chiffreAffaires(),
            'clients' => $this->adminModel->totalClients()
        ];

        $visiteModel = new Visite($this->db);

        $stats['visites_today'] = $visiteModel->getTodayCount();
        $stats['visites_30'] = $visiteModel->getLast30DaysCount();


        // Récents: commandes et produits
        $recentCommandes = $this->db->query("SELECT id, utilisateur_id, montant_total, statut, date_commande FROM commandes ORDER BY date_commande DESC LIMIT 6")->fetch_all(MYSQLI_ASSOC);
        $recentProduits = $this->db->query("SELECT id, nom, prix, stock, image, date_creation FROM produits ORDER BY date_creation DESC LIMIT 6")->fetch_all(MYSQLI_ASSOC);

        // Charger la vue
        require_once __DIR__ . '/../../views/admin/dashboard/index.php';
        return;
    }

    public function categories()
    {
        require_once __DIR__ . '/../../models/Categorie.php';

        AdminMiddleware::verifierAdmin();

        $categorieModel = new Categorie($this->db);

        require_once __DIR__ . '/../../views/admin/categories/index.php';
    }

    public function toggleCategoryStatus()
    {
        require_once __DIR__ . '/../../models/Categorie.php';
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }

        $id = (int) ($_POST['id'] ?? 0);
        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID de catégorie manquant']);
            return;
        }

        $categorieModel = new Categorie($this->db);
        $category = $categorieModel->getById($id);

        if (!$category) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Catégorie non trouvée']);
            return;
        }

        $newStatus = $category['actif'] === '1' ? '0' : '1';
        $success = $newStatus === '1' ? $categorieModel->activateRecursive($id) : $categorieModel->desactivateRecursive($id);

        if ($success) {
            echo json_encode([
                'success' => true,
                'message' => 'Statut mis à jour',
                'new_status' => $newStatus,
                'status_text' => $newStatus === '1' ? 'Active' : 'Désactivée'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
        }
        exit;
    }
}

?>