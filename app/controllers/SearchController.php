
<?php

require_once __DIR__ . '/../models/Produit.php';
require_once __DIR__ . '/../models/Client.php';
require_once __DIR__ . '/../models/Commande.php';
require_once __DIR__ . '/../models/AdminUser.php';
require_once __DIR__ . '/../models/Categorie.php';

class SearchController
{
    private $produitModel;
    private $clientModel;
    private $commandeModel;
    private $adminUserModel;
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
        $this->produitModel = new Produit($db);
        $this->clientModel = new Client($db);
        $this->commandeModel = new Commande($db);
        $this->adminUserModel = new AdminUser($db);
    }

    public function suggestions()
    {
        $q = $_GET['q'] ?? '';

        if(strlen($q) < 2){
            echo json_encode([]);
            return;
        }

        $out = [];

        // Produits
        $prods = $this->produitModel->searchSuggestions($q);
        foreach ($prods as $p) {
            $out[] = [
                'type' => 'produit',
                'id' => $p[0],
                'title' => $p[1],
                'slug' => $p[2] ?? '',
                'image' => $p[3] ?? null,
                'subtitle' => trim(($p[5] ?? '') . (($p[5] ?? '') && ($p[6] ?? '') ? ' · ' : '') . ($p[6] ?? '')),
                'url_public' => BASE_URL . 'produits/slug/' . ($p[2] ?? $p[0])
            ];
        }

        // Categories
        $categorieModel = new Categorie($this->db);
        if ($categorieModel) {
            $cats = $categorieModel->searchSuggestions($q);
            foreach ($cats as $cat) {
                $out[] = [
                    'type' => 'categorie',
                    'id' => $cat['id'],
                    'title' => $cat['nom'],
                    'slug' => $cat['slug'] ?? '',
                    'url_public' => BASE_URL . 'boutique/' . ($cat['slug'] ?? $cat['id'])
                ];
            }
        }

        header('Content-Type: application/json');
        echo json_encode($out);
        exit ;
    }
}

?>