<?php
/**
 * Helper pour intégrer la gestion de stock avec le système de commandes
 */

class CommandeStockHelper
{
    private $stock;
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
        require_once __DIR__ . '/../models/Stock.php';
        $this->stock = new Stock($db);
    }

    /**
     * Réserver le stock lors de la création d'une commande
     * À appeler avant de valider la commande
     */
    public function reserverStockCommande($commande_id)
    {
        // Récupérer les articles de la commande
        $stmt = $this->db->prepare(
            "SELECT produit_id, quantite FROM commande_produits WHERE commande_id = ?"
        );
        $stmt->bind_param("i", $commande_id);
        $stmt->execute();
        $articles = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        $success = true;
        foreach ($articles as $article) {
            // Vérifier qu'il y a assez de stock
            $produit = $this->stock->getProduit($article['produit_id']);
            $stock_dispo = $produit['stock'] - $produit['stock_reserve'];

            if ($stock_dispo < $article['quantite']) {
                $success = false;
                break;
            }

            // Réserver
            if (!$this->stock->reserverStock($article['produit_id'], $article['quantite'])) {
                $success = false;
                break;
            }
        }

        return $success;
    }

    /**
     * Confirmer la vente et consommer le stock
     * À appeler après validation du paiement
     */
    public function confirmerVente($commande_id, $admin_id = null)
    {
        $stmt = $this->db->prepare(
            "SELECT produit_id, quantite FROM commande_produits WHERE commande_id = ?"
        );
        $stmt->bind_param("i", $commande_id);
        $stmt->execute();
        $articles = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        foreach ($articles as $article) {
            // Enregistrer la sortie
            $this->stock->ajouterMouvement(
                $article['produit_id'],
                'sortie',
                $article['quantite'],
                'Commande #' . $commande_id,
                null,
                $admin_id
            );

            // Libérer la réservation
            $this->stock->libererReservation($article['produit_id'], $article['quantite']);
        }

        return true;
    }

    /**
     * Annuler une commande et restaurer le stock
     * À appeler lors de l'annulation d'une commande
     */
    public function annulerCommande($commande_id, $admin_id = null)
    {
        $stmt = $this->db->prepare(
            "SELECT produit_id, quantite FROM commande_produits WHERE commande_id = ?"
        );
        $stmt->bind_param("i", $commande_id);
        $stmt->execute();
        $articles = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        foreach ($articles as $article) {
            // Libérer la réservation
            $this->stock->libererReservation($article['produit_id'], $article['quantite']);

            // Enregistrer un retour si c'était une sortie confirmée
            // (vérifier le statut de la commande avant)
        }

        return true;
    }

    /**
     * Traiter un retour produit
     */
    public function traiterRetourProduit($commande_id, $produit_id, $quantite, $raison, $admin_id = null)
    {
        // Enregistrer le retour
        $this->stock->ajouterMouvement(
            $produit_id,
            'retour',
            $quantite,
            'Retour commande #' . $commande_id,
            $raison,
            $admin_id
        );

        return true;
    }

    /**
     * Vérifier la disponibilité avant d'ajouter au panier
     */
    public function verifierDisponibilite($produit_id, $quantite_demandee)
    {
        $produit = $this->stock->getProduit($produit_id);

        if (!$produit) {
            return [
                'disponible' => false,
                'raison' => 'Produit non trouvé',
                'stock_dispo' => 0
            ];
        }

        $stock_dispo = $produit['stock'] - $produit['stock_reserve'];

        if ($stock_dispo <= 0) {
            return [
                'disponible' => false,
                'raison' => 'Produit en rupture de stock',
                'stock_dispo' => 0
            ];
        }

        if ($stock_dispo < $quantite_demandee) {
            return [
                'disponible' => true,
                'partiellement' => true,
                'quantite_max' => $stock_dispo,
                'raison' => 'Quantité demandée supérieure au stock disponible',
                'stock_dispo' => $stock_dispo
            ];
        }

        return [
            'disponible' => true,
            'stock_dispo' => $stock_dispo,
            'quantite_possible' => $quantite_demandee
        ];
    }

    /**
     * Obtenir les produits en rupture
     */
    public function getProduitEnRupture()
    {
        return $this->stock->getProduitEnRupture();
    }

    /**
     * Vérifier si c'est une rupture de stock
     */
    public function estEnRupture($produit_id)
    {
        $produit = $this->stock->getProduit($produit_id);
        return $produit['stock'] == 0;
    }

    /**
     * Obtenir l'état du stock pour affichage au client
     */
    public function getAffichageStock($produit_id)
    {
        $produit = $this->stock->getProduit($produit_id);
        $stock_dispo = $produit['stock'] - $produit['stock_reserve'];
        $etat = $produit['alerte_etat'] ?? 'normal';

        if ($stock_dispo <= 0) {
            return [
                'label' => 'Rupture de stock',
                'badge' => 'danger',
                'actionnable' => false
            ];
        }

        if ($etat === 'critique') {
            return [
                'label' => 'Stock limité: ' . $stock_dispo . ' unités',
                'badge' => 'warning',
                'actionnable' => true,
                'quantite_max' => $stock_dispo
            ];
        }

        if ($stock_dispo < 5) {
            return [
                'label' => 'Dépêchez-vous! Seulement ' . $stock_dispo . ' en stock',
                'badge' => 'warning',
                'actionnable' => true,
                'quantite_max' => $stock_dispo
            ];
        }

        return [
            'label' => 'En stock',
            'badge' => 'success',
            'actionnable' => true,
            'quantite_max' => $stock_dispo
        ];
    }
}
