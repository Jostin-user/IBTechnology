<?php

require_once __DIR__ . '/../models/Produit.php';
require_once __DIR__ . '/../models/Categorie.php';

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

    public function getProduitsParCategorie($categorieId, $limit)
    {
        return $this->produit->getProduitsParCategorie($categorieId, $limit);
    }

    public function getProduitById($id)
    {
        return $this->produit->getProduitById(filter_var($id, FILTER_VALIDATE_INT) ?: 0,);
    }
    // public function adminListe()
    // {
    //     require_once __DIR__ . '/../models/Categorie.php';

    //     // Pagination
    //     $parPage = 15;
    //     $pageCourante = isset($_GET['p']) ? max(1, (int) $_GET['p']) : 1;
    //     $offset = ($pageCourante - 1) * $parPage;

    //     // Filtres
    //     $filtres = [
    //         'q' => $_GET['q'] ?? null,
    //         'categorie' => $_GET['categorie'] ?? null,
    //         'statut' => $_GET['statut'] ?? null,
    //         'stock' => $_GET['stock'] ?? null
    //     ];

    //     // Données
    //     $produits = $this->produit->rechercheProduitPaginee($filtres, $parPage, $offset);
    //     $totalProduits = $this->produit->countProduit($filtres);
    //     $categories = $this->categorieModel->getAll();
    //     $totalPages = ceil($totalProduits / $parPage);

    //     require_once __DIR__ . '/../views/admin/produits/index.php';
    //     return $produits;
    // }


    // revoir

    public function index()
    {
        $produitsParPage = 15; // Augmenté pour une meilleure expérience

        $pageActuelle = isset($_GET['p']) && $_GET['p'] > 0
            ? (int) $_GET['p']
            : 1;

        $categorieId = isset($_GET['categorie']) ? (int) $_GET['categorie'] : null;
        $min = isset($_GET['min']) && $_GET['min'] !== '' ? (int) $_GET['min'] : null;
        $max = isset($_GET['max']) && $_GET['max'] !== '' ? (int) $_GET['max'] : null;
        $stock = isset($_GET['stock']) ? $_GET['stock'] : null;
        $marque = isset($_GET['marque']) ? $_GET['marque'] : null;
        $tri = isset($_GET['tri']) ? $_GET['tri'] : null;

        $offset = ($pageActuelle - 1) * $produitsParPage;

        // $produitModel   = new Produit($db);
        $filtres = [
            'q' => $_GET['q'] ?? null,
            'categorie' => $categorieId,
            'statut' => '1', // Pour le client uniquement
            'stock' => $stock,
            'marque' => $marque,
            'min' => $min,
            'max' => $max,
            'tri' => $tri
        ];

        $totalProduits = $this->produit->countProduit($filtres);

        $totalPages = ceil($totalProduits / $produitsParPage);
        $produits = $this->produit->rechercheProduitPaginee($filtres, $produitsParPage, $offset);

        // Fournir l'arbre de catégories pour affichage (head, filtres, etc.)
        $categories = $this->categorieModel->getFlatTree();
        $marques = $this->produit->getUniqueMarques();

        require_once '../app/views/produits/index.php';
    }

    public function details($id)
    {
        $id = (int) $id;

        require_once __DIR__ . '/../models/Produit.php';

        $produit = $this->produit->getProduitById($id);

        if (!$produit) {
            header('Location: '.BASE_URL.'/boutique');
            exit;
        }

        // Incrémenter les vues
        $this->produit->incrementerVues($id);

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

    /**
     * Afficher les détails d'un produit par slug (SEO-friendly)
     */
    public function detailsBySlug($slug)
    {
        require_once __DIR__ . '/../models/Produit.php';

        $produit = $this->produit->getProduitBySlug($slug);

        if (!$produit) {
            http_response_code(404);
            require_once __DIR__ . '/../views/404.php';
            exit;
        }

        // Incrémenter les vues
        $this->produit->incrementerVues($produit['id']);

        // Récupérer la catégorie du produit
        $categorie = $this->categorieModel->getById($produit['categorie_id']);

        // Produits similaires
        $produitsSimilaires = $this->produit->getProduitsSimilaires(
            $produit['categorie_id'],
            $produit['id'],
            4
        );
        require_once __DIR__ . '/../views/produits/details.php';
    }

    /**
     * Afficher les produits d'une catégorie par slug (SEO-friendly)
     */
    public function categorieBySlug($slug)
    {
        $categorie = $this->categorieModel->getBySlug($slug);

        if (!$categorie) {
            http_response_code(404);
            require_once __DIR__ . '/../views/404.php';
            exit;
        }

        // Réutiliser la logique d'affichage des produits par catégorie
        $produitsParPage = 12;
        $pageActuelle = isset($_GET['p']) && $_GET['p'] > 0 ? (int) $_GET['p'] : 1;
        $categorieId = isset($_GET['categorie']) ? (int) $_GET['categorie'] : null;
        $offset = ($pageActuelle - 1) * $produitsParPage;

        $filtres = [
            'q' => $_GET['q'] ?? null,
            'categorie' => $categorie['id'],
            'statut' => null,
            'stock' => $_GET['stock'] ?? null,
            'marque' => $_GET['marque'] ?? null,
            'min' => isset($_GET['min']) && $_GET['min'] !== '' ? (int) $_GET['min'] : null,
            'max' => isset($_GET['max']) && $_GET['max'] !== '' ? (int) $_GET['max'] : null,
            'tri' => $_GET['tri'] ?? null
        ];

        $totalProduits = $this->produit->countProduit($filtres);
        $totalPages = ceil($totalProduits / $produitsParPage);
        $produits = $this->produit->rechercheProduitPaginee($filtres, $produitsParPage, $offset);

        // Arbre de catégories et marques
        $categories = $this->categorieModel->getFlatTree();
        $marques = $this->produit->getUniqueMarques();

        require_once __DIR__ . '/../views/produits/index.php';
    }
}

?>