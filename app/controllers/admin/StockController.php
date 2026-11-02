<?php

require_once __DIR__ . '/../../models/Stock.php';
require_once __DIR__ . '/../../models/Categorie.php';

class StockController
{
    private Stock $stock;
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
        $this->stock = new Stock($db);
    }

    /**
     * Afficher le tableau de bord du stock
     */
    public function dashboard()
    {
        $stats = $this->stock->getStatistiques();
        $ruptures = $this->stock->getProduitEnRupture();
        $critiques = $this->stock->getProduitStockCritique();
        $mouvements = $this->stock->getMouvementsRecents(10);

        require_once __DIR__ . '/../../views/admin/stock/dashboard.php';
    }

    /**
     * Afficher la gestion complète du stock
     */
    public function index()
    {
        $parPage = 20;
        $page = isset($_GET['p']) ? max(1, (int) $_GET['p']) : 1;
        $offset = ($page - 1) * $parPage;

        $filtres = [
            'search' => $_GET['search'] ?? null,
            'categorie' => $_GET['categorie'] ?? null,
            'etat' => $_GET['etat'] ?? null
        ];

        $produits = $this->stock->getStockComplet($filtres, $parPage, $offset);
        $total = $this->stock->countStock($filtres);
        $totalPages = ceil($total / $parPage);
        $categories = (new Categorie($this->db))->getAll();

        require_once __DIR__ . '/../../views/admin/stock/index.php';
    }

    /**
     * Détails d'un produit et son historique
     */
    public function details()
    {
        if (!isset($_GET['id'])) {
            header('Location: '.BASE_URL.'admin/stock');
            exit;
        }

        $produit_id = (int) $_GET['id'];
        $produit = $this->stock->getProduit($produit_id);

        if (!$produit) {
            http_response_code(404);
            die('Produit non trouvé');
        }

        $parPage = 15;
        $page = isset($_GET['p']) ? max(1, (int) $_GET['p']) : 1;
        $offset = ($page - 1) * $parPage;

        $mouvements = $this->stock->getHistoriqueMouvements($produit_id, $parPage, $offset);
        $totalMouvements = $this->stock->countHistoriqueMouvements($produit_id);
        $totalPages = ceil($totalMouvements / $parPage);

        require_once __DIR__ . '/../../views/admin/stock/details.php';
    }

    /**
     * Ajouter un mouvement de stock
     */
    public function ajouterMouvement()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: '.BASE_URL.'admin/stock');
            exit;
        }

        $produit_id = (int) ($_POST['produit_id'] ?? 0);
        $type = $_POST['type'] ?? null;
        $quantite = (int) ($_POST['quantite'] ?? 0);
        $raison = $_POST['raison'] ?? null;
        $notes = $_POST['notes'] ?? null;

        if (!$produit_id || !$type || $quantite <= 0) {
            $_SESSION['error'] = 'Données invalides';
            header('Location: '.BASE_URL.'admin/stock/details?id=' . $produit_id);
            exit;
        }

        $admin_id = $_SESSION['user_id'] ?? null;

        if ($this->stock->ajouterMouvement($produit_id, $type, $quantite, $raison, $notes, $admin_id)) {
            $_SESSION['success'] = 'Mouvement enregistré avec succès';
        } else {
            $_SESSION['error'] = 'Erreur lors de l\'enregistrement du mouvement';
        }

        header('Location: '.BASE_URL.'admin/stock/details?id=' . $produit_id);
        exit;
    }

    /**
     * Ajustement de stock
     */
    public function ajuster()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: '.BASE_URL.'admin/stock');
            exit;
        }

        $produit_id = (int) ($_POST['produit_id'] ?? 0);
        $nouveau_stock = (int) ($_POST['nouveau_stock'] ?? 0);
        $raison = $_POST['raison'] ?? null;
        $description = $_POST['description'] ?? null;

        if (!$produit_id || $nouveau_stock < 0 || !$raison) {
            $_SESSION['error'] = 'Données invalides';
            header('Location: '.BASE_URL.'admin/stock/details?id=' . $produit_id);
            exit;
        }

        $admin_id = $_SESSION['user_id'] ?? null;

        if ($this->stock->ajouterAjustement($produit_id, $nouveau_stock, $raison, $description, $admin_id)) {
            $_SESSION['success'] = 'Ajustement enregistré avec succès';
        } else {
            $_SESSION['error'] = 'Erreur lors de l\'ajustement';
        }

        header('Location: '.BASE_URL.'admin/stock/details?id=' . $produit_id);
        exit;
    }

    /**
     * Définir les seuils d'alerte
     */
    public function definirSeuils()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: '.BASE_URL.'admin/stock');
            exit;
        }

        $produit_id = (int) ($_POST['produit_id'] ?? 0);
        $seuil_min = (int) ($_POST['seuil_min'] ?? 10);
        $seuil_max = (int) ($_POST['seuil_max'] ?? 1000);

        if (!$produit_id || $seuil_min < 0 || $seuil_max <= $seuil_min) {
            $_SESSION['error'] = 'Seuils invalides';
            header('Location: '.BASE_URL.'admin/stock/details?id=' . $produit_id);
            exit;
        }

        if ($this->stock->definirSeuilsAlerte($produit_id, $seuil_min, $seuil_max)) {
            $this->stock->verifierAlertesStock($produit_id);
            $_SESSION['success'] = 'Seuils définis avec succès';
        } else {
            $_SESSION['error'] = 'Erreur lors de la définition des seuils';
        }

        header('Location: '.BASE_URL.'admin/stock/details?id=' . $produit_id);
        exit;
    }

    /**
     * Rapport de stock
     */
    public function rapport()
    {
        $date_debut = $_GET['date_debut'] ?? date('Y-01-01');
        $date_fin = $_GET['date_fin'] ?? date('Y-m-d');

        $rapport = $this->stock->getRapportStock($date_debut, $date_fin);
        $stats = $this->stock->getStatistiques();

        require_once __DIR__ . '/../../views/admin/stock/rapport.php';
    }

    /**
     * Export CSV
     */
    public function exporterCSV()
    {
        $filtres = [
            'search' => $_GET['search'] ?? null,
            'categorie' => $_GET['categorie'] ?? null,
            'etat' => $_GET['etat'] ?? null
        ];

        $produits = $this->stock->getStockComplet($filtres, -1, 0);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="stock_' . date('Y-m-d_His') . '.csv"');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8

        // En-tête
        fputcsv($output, [
            'ID',
            'Produit',
            'Catégorie',
            'Stock',
            'Stock Réservé',
            'Stock Disponible',
            'Seuil Min',
            'Seuil Max',
            'État Alerte',
            'Prix Unitaire',
            'Marque',
            'Actif'
        ], ';'); // Délimiteur ; pour Excel

        // Données
        foreach ($produits as $p) {
            fputcsv($output, [
                $p['id'],
                $p['nom'],
                $p['categorie'],
                $p['stock'],
                $p['stock_reserve'],
                $p['stock_disponible'],
                $p['seuil_minimum'],
                $p['seuil_maximum'],
                $p['alerte_etat'],
                $p['prix'],
                $p['marque'],
                $p['actif'] ? 'Oui' : 'Non'
            ], ';'); // Délimiteur ; pour Excel
        }

        fclose($output);
        exit;
    }
}
