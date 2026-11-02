<?php

require_once __DIR__ . '/../../models/Produit.php';
require_once __DIR__ . '/../../models/Categorie.php';

class ProduitController
{
    private Produit $produit;
    private $categorieModel;
    public int $totalPage;
    public function __construct($db)
    {
        $this->produit = new Produit($db);
        $this->categorieModel = new Categorie($db);
        $this->totalPage = 1;
    }

    // public function index()
    // {
    //     return $this->produit->getProduits();
    // }

    public function store($data)
    {
        return $this->produit->creerProduit($data);
    }

    public function modifierProduit($id, $data)
    {
        return $this->produit->modifierProduit(filter_var($id, FILTER_VALIDATE_INT) ?: 0, $data);
    }

    public function supprimerProduit($id)
    {
        return $this->produit->supprimerProduit(filter_var($id, FILTER_VALIDATE_INT) ?: 0);
    }

    public function getProduitsParCategorie($categorieId, $limit)
    {
        return $this->produit->getProduitsParCategorie($categorieId, $limit);
    }

    public function getProduitById($id)
    {
        return $this->produit->getProduitById(filter_var($id, FILTER_VALIDATE_INT) ?: 0);
    }
    public function adminListe()
    {
        require_once __DIR__ . '/../../models/Categorie.php';

        // Pagination
        $pageActuelle = isset($_GET['p']) && $_GET['p'] > 0 ? (int) $_GET['p']  : 1;
        $parPage = 15;
        $pageCourante = isset($_GET['p']) ? max(1, (int) $_GET['p']) : 1;
        $offset = ($pageCourante - 1) * $parPage;

        // Filtres
        $filtres = [
            'q' => isset($_GET['q']) ? trim($_GET['q']) : null,
            'categorie' => isset($_GET['categorie']) ? trim($_GET['categorie']) : null,
            'statut' => isset($_GET['statut']) ? trim($_GET['statut']) : null,
            'stock' => isset($_GET['stock']) ? trim($_GET['stock']) : null
        ];

        // Données
        $produits = $this->produit->rechercheProduitPaginee($filtres, $parPage, $offset);
        $totalProduits = $this->produit->countProduit($filtres);
        $categories = $this->categorieModel->getAll();
        $totalPages = ceil($totalProduits / $parPage);

        require_once __DIR__ . '/../../views/admin/produits/index.php';
        return $produits;
    }

    public function ajouter()
    {
        $categorieModel = $this->categorieModel;
        require_once __DIR__ . '/../../views/admin/produits/create.php';
    }

    public function modifier()
    {
        require_once __DIR__ . '/../../views/admin/produits/edit.php';
    }

    // revoir

    public function index()
    {
        $produitsParPage = 5; // Augmenté pour une meilleure expérience

        $pageActuelle = isset($_GET['p']) && $_GET['p'] > 0
            ? (int) $_GET['p']
            : 1;

        $categorieId = isset($_GET['categorie']) ? (int) $_GET['categorie'] : null;
        $min = isset($_GET['min']) && $_GET['min'] !== '' ? (int) $_GET['min'] : null;
        $max = isset($_GET['max']) && $_GET['max'] !== '' ? (int) $_GET['max'] : null;
        $stock = isset($_GET['stock']) ? $_GET['stock'] : null;
        $tri = isset($_GET['tri']) ? $_GET['tri'] : null;

        $offset = ($pageActuelle - 1) * $produitsParPage;

        $filtres = [
            'q' => $_GET['q'] ?? null,
            'categorie' => $categorieId,
            'statut' => null, // Pour l'admin uniquement
            'stock' => $stock,
            'min' => $min,
            'max' => $max,
            'tri' => $tri
        ];

        $totalProduits = $this->produit->countProduit($filtres);

        $totalPages = ceil($totalProduits / $produitsParPage);
        $produits = $this->produit->rechercheProduitPaginee($filtres, $produitsParPage, $offset);

        // Fournir l'arbre de catégories pour affichage (head, filtres, etc.)
        $categories = $this->categorieModel->getTree();

        require_once '../app/views/produits/index.php';
    }

    public function details($id)
    {
        $id = (int) $id;

        require_once __DIR__ . '/../models/Produit.php';

        $produit = $this->produit->getProduitById($id);

        if (!$produit) {
            header('Location: ' . BASE_URL . 'boutique');
            exit;
        }

        // Récupérer la catégorie du produit
        $categorie = $this->categorieModel->getById($produit['categorie_id']);

        // 🔥 Produits similaires
        $produitsSimilaires = $this->produit->getProduitsSimilaires(
            $produit['categorie_id'],
            $produit['id'],
            4
        );
        require_once __DIR__ . '/../views/produits/details.php';
    }

    public function delete()
    {
        // Récupérer l'ID depuis POST pour plus de sécurité
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

        if ($id <= 0) {
            header('Location: ' . BASE_URL . 'admin/produits?error=invalid_id');
            exit;
        }

        if ($this->supprimerProduit($id)) {
            header('Location:' . BASE_URL . 'admin/produits?deleted=1');
        } else {
            header('Location: ' . BASE_URL . 'admin/produits?error=delete_failed');
        }
        exit;
    }
}

?>