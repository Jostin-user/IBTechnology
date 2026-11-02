<?php
require_once __DIR__ . '/../models/Admin.php';

class AdminController
{
    private $adminModel;

    public function __construct($db)
    {
        $this->adminModel = new Admin($db);
    }

    public function dashboard()
    {
        return [
            'produits' => $this->adminModel->totalProduits(),
            'stockBas' => $this->adminModel->produitsStockBas(),
            'commandes' => $this->adminModel->totalCommandes(),
            'commandesAttente' => $this->adminModel->commandesEnAttente(),
            'chiffreAffaires' => $this->adminModel->chiffreAffaires(),
            'clients' => $this->adminModel->totalClients()
        ];
    }
}

?>