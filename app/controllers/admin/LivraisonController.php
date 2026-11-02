<?php

require_once __DIR__ . '/../../models/Zone.php';

class LivraisonController
{
    private $db;
    private $zoneModel;

    public function __construct($db = null)
    {
        if ($db === null) {
            require_once __DIR__ . '/../../config/database.php';
            $this->db = (new Database())->getConnexion();
        } else {
            $this->db = $db;
        }
        $this->zoneModel = new Zone($this->db);
    }

    // Afficher la liste des zones de livraison
    public function index()
    {
        // Pagination
        $parPage = 10;
        $pageCourante = isset($_GET['p']) ? max(1, (int) $_GET['p']) : 1;
        $offset = ($pageCourante - 1) * $parPage;

        // Filtres
        $filtres = [
            'q' => $_GET['q'] ?? null,
            'type' => $_GET['type'] ?? null,
            'actif' => $_GET['actif'] ?? null
        ];

        // Récupérer toutes les zones avec pagination simulée
        $stmt = $this->zoneModel->getAll();
        $zones = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // Appliquer les filtres
        if ($filtres['q']) {
            $zones = array_filter($zones, function ($zone) use ($filtres) {
                return stripos($zone['nom'], $filtres['q']) !== false;
            });
        }

        if ($filtres['type']) {
            $zones = array_filter($zones, function ($zone) use ($filtres) {
                return $zone['type'] === $filtres['type'];
            });
        }

        if ($filtres['actif'] !== null) {
            $zones = array_filter($zones, function ($zone) use ($filtres) {
                return $zone['actif'] === $filtres['actif'];
            });
        }

        // Pagination
        $totalZones = count($zones);
        $zones = array_slice($zones, $offset, $parPage);
        $totalPages = ceil($totalZones / $parPage);

        require_once __DIR__ . '/../../views/admin/livraison/index.php';
    }
    // Enregistrer une nouvelle zone
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: '.BASE_URL.'admin/livraison');
            exit;
        }

        $this->zoneModel->nom = $_POST['nom'] ?? '';
        $this->zoneModel->type = $_POST['type'] ?? '';
        $this->zoneModel->parent_id = !empty($_POST['parent_id']) ? $_POST['parent_id'] : null;
        $this->zoneModel->frais_livraison = $_POST['frais_livraison'] ?? 0;
        $this->zoneModel->actif = isset($_POST['actif']) ? '1' : '0';

        if (empty($this->zoneModel->nom) || empty($this->zoneModel->type)) {
            $_SESSION['error'] = 'Le nom et le type sont obligatoires.';
            header('Location: '.BASE_URL.'admin/livraison');
            exit;
        }

        if ($this->zoneModel->create()) {
            $_SESSION['success'] = 'Zone créée avec succès.';
            header('Location: '.BASE_URL.'admin/livraison');
            exit;
        } else {
            $_SESSION['error'] = 'Erreur lors de la création de la zone.';
            header('Location: '.BASE_URL.'admin/livraison/create');
            exit;
        }
    }

    // Mettre à jour une zone
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location:'.BASE_URL.'admin/livraison');
            exit;
        }

        $id = $_POST['id'] ?? 0;
        if ($id <= 0) {
            header('Location: '.BASE_URL.'admin/livraison');
            exit;
        }

        $this->zoneModel->id = $id;
        $this->zoneModel->nom = $_POST['nom'] ?? '';
        $this->zoneModel->type = $_POST['type'] ?? '';
        $this->zoneModel->parent_id = !empty($_POST['parent_id']) ? $_POST['parent_id'] : null;
        $this->zoneModel->frais_livraison = $_POST['frais_livraison'] ?? 0;
        $this->zoneModel->actif = isset($_POST['actif']) ? '1' : '0';

        if (empty($this->zoneModel->nom) || empty($this->zoneModel->type)) {
            $_SESSION['error'] = 'Le nom et le type sont obligatoires.';
            header('Location: '.BASE_URL.'admin/livraison/edit?id=' . $id);
            exit;
        }

        if ($this->zoneModel->update()) {
            $_SESSION['success'] = 'Zone mise à jour avec succès.';
            header('Location: '.BASE_URL.'admin/livraison');
            exit;
        } else {
            $_SESSION['error'] = 'Erreur lors de la mise à jour de la zone.';
            header('Location: '.BASE_URL.'admin/livraison/edit?id=' . $id);
            exit;
        }
    }

    // Supprimer une zone
    public function delete()
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        if ($id <= 0) {
            header('Location: '.BASE_URL.'admin/livraison');
            exit;
        }

        $this->zoneModel->id = $id;

        // Vérifier s'il y a des enfants
        $stmt = $this->zoneModel->getChildren($id);
        if (mysqli_num_rows($stmt->get_result())> 0) {
            $_SESSION['error'] = 'Impossible de supprimer cette zone car elle contient des sous-zones.';
            header('Location: '.BASE_URL.'admin/livraison');
            exit;
        }

        if ($this->zoneModel->delete()) {
            $_SESSION['success'] = 'Zone supprimée avec succès.';
        } else {
            $_SESSION['error'] = 'Erreur lors de la suppression de la zone.';
        }

        header('Location: '.BASE_URL.'admin/livraison');
        exit;
    }

    // Activer/Désactiver une zone
    public function toggle()
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        if ($id <= 0) {
            header('Location: '.BASE_URL.'admin/livraison');
            exit;
        }

        $this->zoneModel->id = $id;

        if ($this->zoneModel->toggleActive()) {
            $_SESSION['success'] = 'Statut de la zone mis à jour.';
        } else {
            $_SESSION['error'] = 'Erreur lors de la mise à jour du statut.';
        }

        header('Location: '.BASE_URL.'admin/livraison');
        exit;
    }

    // API pour récupérer les régions
    public function getRegions()
    {
        $stmt = $this->zoneModel->getByType('region');
        $regions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        echo json_encode($regions);
        exit;
    }

    // API pour récupérer les villes d'une région
    public function getVilles()
    {
        $regionId = isset($_GET['region_id']) ? (int) $_GET['region_id'] : 0;

        if ($regionId > 0) {
            $stmt = $this->zoneModel->getChildren($regionId);
        } else {
            // Retourner toutes les villes si pas de région spécifiée
            $stmt = $this->zoneModel->getByType('ville');
        }

        $villes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        echo json_encode($villes);
        exit;
    }

    // API pour récupérer les données d'une zone (pour les modales)
    public function getZoneData()
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        if ($id <= 0) {
            echo json_encode(['error' => 'ID invalide']);
            exit;
        }

        $this->zoneModel->id = $id;
        if (!$this->zoneModel->readOne()) {
            echo json_encode(['error' => 'Zone non trouvée']);
            exit;
        }

        $zoneData = [
            'id' => $this->zoneModel->id,
            'nom' => $this->zoneModel->nom,
            'type' => $this->zoneModel->type,
            'parent_id' => $this->zoneModel->parent_id,
            'frais_livraison' => $this->zoneModel->frais_livraison,
            'description' => $this->zoneModel->description ?? '',
            'actif' => $this->zoneModel->actif
        ];

        echo json_encode($zoneData);
        exit;
    }

    // API pour toutes les villes (pour les quartiers)
    public function getAllVilles()
    {
        $stmt = $this->zoneModel->getByType('ville');
        $villes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        echo json_encode($villes);
        exit;
    }

    // Store avec support AJAX
    public function storeAjax()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            exit;
        }

        $action = $_POST['action'] ?? '';

        if ($action === 'add_zone') {
            $this->zoneModel->nom = $_POST['nom'] ?? '';
            $this->zoneModel->type = $_POST['type'] ?? '';
            $this->zoneModel->parent_id = !empty($_POST['parent_id']) ? $_POST['parent_id'] : null;
            $this->zoneModel->frais_livraison = $_POST['frais_livraison'] ?? 0;
            $this->zoneModel->description = $_POST['description'] ?? '';
            $this->zoneModel->actif = '1'; // Nouvelle zone active par défaut

            if (empty($this->zoneModel->nom) || empty($this->zoneModel->type)) {
                echo json_encode(['success' => false, 'message' => 'Le nom et le type sont obligatoires.']);
                exit;
            }

            if ($this->zoneModel->create()) {
                echo json_encode(['success' => true, 'message' => 'Zone créée avec succès.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur lors de la création de la zone.']);
            }
        } elseif ($action === 'edit_zone') {
            $id = $_POST['id'] ?? 0;
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID invalide.']);
                exit;
            }

            $this->zoneModel->id = $id;
            $this->zoneModel->nom = $_POST['nom'] ?? '';
            $this->zoneModel->type = $_POST['type'] ?? '';
            $this->zoneModel->parent_id = !empty($_POST['parent_id']) ? $_POST['parent_id'] : null;
            $this->zoneModel->frais_livraison = $_POST['frais_livraison'] ?? 0;
            $this->zoneModel->description = $_POST['description'] ?? '';

            if (empty($this->zoneModel->nom) || empty($this->zoneModel->type)) {
                echo json_encode(['success' => false, 'message' => 'Le nom et le type sont obligatoires.']);
                exit;
            }

            if ($this->zoneModel->update()) {
                echo json_encode(['success' => true, 'message' => 'Zone mise à jour avec succès.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour de la zone.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Action non reconnue.']);
        }

        exit;
    }
}
?>