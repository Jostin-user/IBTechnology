
<?php

require_once __DIR__ . '/../../models/Produit.php';
require_once __DIR__ . '/../../models/Client.php';
require_once __DIR__ . '/../../models/Commande.php';
require_once __DIR__ . '/../../models/AdminUser.php';

class SearchController
{
    private $produitModel;
    private $clientModel;
    private $commandeModel;
    private $adminUserModel;

    public function __construct($db)
    {
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
                'url_admin' => BASE_URL . 'admin/produits/edit?id=' . $p[0],
            ];
        }

        // Clients
        $clientsRes = $this->clientModel->search($q);
        $clients = [];
        if (is_object($clientsRes)) {
            $clients = $clientsRes->fetch_all(MYSQLI_ASSOC);
        } elseif (is_array($clientsRes)) {
            $clients = $clientsRes;
        }
        foreach ($clients as $c) {
            $out[] = [
                'type' => 'client',
                'id' => $c['id'],
                'title' => $c['nom_complet'],
                'subtitle' => $c['email'] ?? '',
                'url_admin' => BASE_URL . 'admin/clients/details?id=' . $c['id']
            ];
        }

        // Commandes
        $cmds = $this->commandeModel->searchSuggestions($q);
        foreach ($cmds as $cm) {
            $out[] = [
                'type' => 'commande',
                'id' => $cm['id'],
                'title' => 'Commande #' . $cm['id'],
                'subtitle' => ($cm['nom_complet'] ?? '') . ' • ' . ($cm['montant_total'] ?? ''),
                'url_admin' => BASE_URL . 'admin/commandes/details/' . $cm['id']
            ];
        }

        // Admins
        $admins = $this->adminUserModel->search($q);
        foreach ($admins as $a) {
            $out[] = [
                'type' => 'admin',
                'id' => $a['id'],
                'title' => $a['nom_complet'],
                'subtitle' => $a['email'] ?? '',
                'url_admin' => BASE_URL . 'admin/administration/edit?id=' . $a['id']
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($out);
        exit ;
    }
}

?>