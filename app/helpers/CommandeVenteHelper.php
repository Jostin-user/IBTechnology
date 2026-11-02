<?php

/**
 * Helper pour intégrer la gestion des ventes avec le système de commandes
 */
class CommandeVenteHelper
{
    private $vente;
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
        require_once __DIR__ . '/../models/Vente.php';
        $this->vente = new Vente($db);
    }

    /**
     * Créer une vente à partir d'une commande validée
     */
    public function creerVenteDepuisCommande($commande_id, $utilisateur_id)
    {
        // Récupérer les détails de la commande
        $stmt = $this->db->prepare(
            "SELECT montant_total, shipping_cost FROM commandes WHERE id = ?"
        );
        $stmt->bind_param("i", $commande_id);
        $stmt->execute();
        $commande = $stmt->get_result()->fetch_assoc();

        if (!$commande) {
            return false;
        }

        // Créer la vente
        return $this->vente->creerVente(
            $commande_id,
            $utilisateur_id,
            $commande['montant_total'],
            $commande['shipping_cost']
        );
    }

    /**
     * Obtenir les ventes des 30 derniers jours
     */
    public function getVentesRecentes($limit = 10)
    {
        $filtres = [
            'date_debut' => date('Y-m-d', strtotime('-30 days'))
        ];

        return $this->vente->getHistoriqueVentes($filtres, $limit, 0);
    }

    /**
     * Obtenir le CA du jour
     */
    public function getCADuJour()
    {
        $aujourd_hui = date('Y-m-d');
        $stats = $this->vente->getStatistiques($aujourd_hui, $aujourd_hui);
        return $stats['montant_total'] ?? 0;
    }

    /**
     * Obtenir le CA du mois
     */
    public function getCAMois()
    {
        $debut = date('Y-m-01');
        $fin = date('Y-m-d');
        $stats = $this->vente->getStatistiques($debut, $fin);
        return $stats['montant_total'] ?? 0;
    }

    /**
     * Obtenir le nombre de ventes du jour
     */
    public function getNombreVentesJour()
    {
        $aujourd_hui = date('Y-m-d');
        $stats = $this->vente->getStatistiques($aujourd_hui, $aujourd_hui);
        return $stats['nombre_ventes'] ?? 0;
    }

    /**
     * Vérifier si une vente existe pour une commande
     */
    public function venteExiste($commande_id)
    {
        $stmt = $this->db->prepare("SELECT id FROM ventes WHERE commande_id = ?");
        $stmt->bind_param("i", $commande_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return !empty($result);
    }

    /**
     * Marquer une commande comme livrée et mettre à jour la vente
     */
    public function marquerlCommandeLivree($commande_id)
    {
        // Vérifier si une vente existe
        $stmt = $this->db->prepare("SELECT id FROM ventes WHERE commande_id = ?");
        $stmt->bind_param("i", $commande_id);
        $stmt->execute();
        $vente = $stmt->get_result()->fetch_assoc();

        if ($vente) {
            // Confirmer la livraison
            return $this->vente->confirmerLivraison($vente['id']);
        }

        return false;
    }

    /**
     * Obtenir les statistiques rapides
     */
    public function getStatistiquesRapides()
    {
        return [
            'ca_jour' => $this->getCADuJour(),
            'ca_mois' => $this->getCAMois(),
            'ventes_jour' => $this->getNombreVentesJour(),
            'ventes_recentes' => $this->getVentesRecentes(5)
        ];
    }

    /**
     * Obtenir le modèle Vente pour utilisation directe
     */
    public function getVenteModel()
    {
        return $this->vente;
    }
}
