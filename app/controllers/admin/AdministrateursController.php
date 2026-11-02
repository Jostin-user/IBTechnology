<?php
require_once __DIR__ . '/../../middlewares/AdminMiddleware.php';
require_once __DIR__ . '/../../models/AdminUser.php';

class AdministrateursController
{
    private $adminUserModel;
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
        $this->adminUserModel = new AdminUser($db);
    }

    public function index()
    {
        AdminMiddleware::verifierSuperAdmin();
        $admins = $this->adminUserModel->getAll();
        require_once __DIR__ . '/../../views/admin/administration/index.php';
    }

    public function create()
    {
        AdminMiddleware::verifierSuperAdmin();
        require_once __DIR__ . '/../../views/admin/administration/create.php';
    }

    public function store()
    {
        AdminMiddleware::verifierSuperAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: '.BASE_URL.'admin/administration');
            exit;
        }

        $data = [
            'nom_complet' => trim($_POST['nom_complet'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'mot_de_passe' => $_POST['mot_de_passe'] ?? '',
            'role' => ($_POST['role'] ?? 'admin'),
            'statut' => ($_POST['statut'] ?? 'inactive')
        ];

        if (empty($data['nom_complet']) || empty($data['email']) || empty($data['mot_de_passe'])) {
            $_SESSION['error'] = 'Les champs nom, email et mot de passe sont requis.';
            header('Location: '.BASE_URL.'admin/administration');
            exit;
        }

        if ($this->adminUserModel->create($data)) {
            $_SESSION['success'] = 'Administrateur créé avec succès.';
        } else {
            $_SESSION['error'] = 'Erreur lors de la création de l\'administrateur.';
        }
        header('Location: '.BASE_URL.'admin/administration');
        exit;
    }

    public function edit()
    {
        AdminMiddleware::verifierSuperAdmin();
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            header('Location: '.BASE_URL.'admin/administration');
            exit;
        }
        $admin = $this->adminUserModel->getById($id);
        if (!$admin) {
            $_SESSION['error'] = 'Administrateur introuvable.';
            header('Location: '.BASE_URL.'admin/administration');
            exit;
        }
        require_once __DIR__ . '/../../views/admin/administration/edit.php';
    }

    public function update()
    {
        AdminMiddleware::verifierSuperAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: '.BASE_URL.'admin/administration');
            exit;
        }
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id <= 0) {
            $_SESSION['error'] = 'ID invalide.';
            header('Location: '.BASE_URL.'admin/administration');
            exit;
        }
        $data = [
            'nom_complet' => trim($_POST['nom_complet'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'telephone' => trim($_POST['telephone'] ?? ''),
            'adresse' => trim($_POST['adresse'] ?? ''),
            'role' => $_POST['role'] ?? 'admin',
            'statut' => $_POST['statut'] ?? 'inactive',
            'mot_de_passe' => $_POST['mot_de_passe'] ?? ''
        ];

        if (empty($data['nom_complet']) || empty($data['email'])) {
            $_SESSION['error'] = 'Nom et email requis.';
            header('Location: '.BASE_URL.'admin/administration');
            exit;
        }

        if ($this->adminUserModel->update($id, $data)) {
            $_SESSION['success'] = 'Administrateur mis à jour.';
        } else {
            $_SESSION['error'] = 'Erreur lors de la mise à jour.';
        }
        header('Location: '.BASE_URL.'admin/administration');
        exit;
    }

    public function delete()
    {
        AdminMiddleware::verifierSuperAdmin();
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            $_SESSION['error'] = 'ID invalide.';
            header('Location: '.BASE_URL.'admin/administration');
            exit;
        }
        if ($this->adminUserModel->delete($id)) {
            $_SESSION['success'] = 'Administrateur supprimé.';
        } else {
            $_SESSION['error'] = 'Erreur lors de la suppression.';
        }
        header('Location: '.BASE_URL.'admin/administration');
        exit;
    }

    public function toggleStatus()
    {
        AdminMiddleware::verifierSuperAdmin();
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            $_SESSION['error'] = 'ID invalide.';
            header('Location: '.BASE_URL.'admin/administration');
            exit;
        }
        if ($this->adminUserModel->toggleStatus($id)) {
            $_SESSION['success'] = 'Statut mis à jour.';
        } else {
            $_SESSION['error'] = 'Erreur lors de la mise à jour du statut.';
        }
        header('Location: '.BASE_URL.'admin/administration');
        exit;
    }
}
?>