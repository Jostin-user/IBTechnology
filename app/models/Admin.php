<?php

class Admin
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function totalProduits()
    {
        return $this->db->query("SELECT COUNT(*) AS total FROM produits")
                        ->fetch_assoc()['total'];
    }

    public function produitsStockBas($seuil = 5)
    {
        return $this->db->query(
            "SELECT COUNT(*) AS total FROM stock_alertes sa
            INNER JOIN produits p ON p.id=sa.produit_id WHERE p.stock <= sa.seuil_minimum"
        )->fetch_assoc()['total'];
    }

    public function totalCommandes()
    {
        return $this->db->query("SELECT COUNT(*) AS total FROM commandes")
                        ->fetch_assoc()['total'];
    }

    public function commandesEnAttente()
    {
        return $this->db->query(
            "SELECT COUNT(*) AS total FROM commandes WHERE statut = 'en_attente'"
        )->fetch_assoc()['total'];
    }

    public function chiffreAffaires()
    {
        return $this->db->query(
            "SELECT SUM(montant_total) AS total FROM commandes WHERE statut = 'livree'"
        )->fetch_assoc()['total'] ?? 0;
    }

    public function totalClients()
    {
        return $this->db->query(
            "SELECT COUNT(*) AS total FROM users WHERE role = 'client'"
        )->fetch_assoc()['total'];
    }
}
?>