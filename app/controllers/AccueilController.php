<?php

require_once __DIR__ . '/../models/Categorie.php';
require_once __DIR__ . '/../models/Produit.php';
require_once __DIR__ . '/../controllers/ProduitController.php';

class AccueilController
{
    private $db;
    private $categorieModel;
    private $produitModel;

    public function __construct($db)
    {
        $this->db = $db;
        $this->categorieModel = new Categorie($db);
        $this->produitModel = new Produit($db);
    }

    /**
     * Page d'accueil publique
     */
    public function index()
    {
        // 1️⃣ Récupérer toutes les catégories parents actives
        $categories = $this->categorieModel->getAllParentActive();

        // 3️⃣ Charger la vue
        $produitmodel = new ProduitController($this->db);
        $produitsPopulaires = $this->produitModel->getProduitsPopulaires(4);
        $categoriePopulaire = $this->categorieModel->getCategoriePopulaire(4);
        require_once __DIR__ . '/../views/accueil/index.php';
    }

    public function aide()
    {
        require_once __DIR__ . '/../views/aide/index.php';
    }
}