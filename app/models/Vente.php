<?php

class Vente
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Créer une vente à partir d'une commande livrée
     */
    public function creerVente($commande_id, $utilisateur_id, $montant_total, $frais_livraison = 0)
    {
        // Récupérer les articles de la commande
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as nombre FROM commande_produits WHERE commande_id = ?"
        );
        $stmt->bind_param("i", $commande_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $nombre_articles = $result['nombre'];

        $stmt = $this->db->prepare(
            "INSERT INTO ventes (commande_id, utilisateur_id, montant_total, frais_livraison, nombre_articles, statut, date_vente)
             VALUES (?, ?, ?, ?, ?, 'confirmee', NOW())"
        );

        $statut = 'confirmee';
        $stmt->bind_param("iidii", $commande_id, $utilisateur_id, $montant_total, $frais_livraison, $nombre_articles);
        
        if ($stmt->execute()) {
            // Mettre à jour la synthèse du jour
            $this->mettreAJourSynthese();
            return $this->db->insert_id;
        }

        return false;
    }

    /**
     * Confirmer la livraison d'une vente
     */
    public function confirmerLivraison($vente_id)
    {
        $date_livraison = date('Y-m-d H:i:s');
        
        $stmt = $this->db->prepare(
            "UPDATE ventes SET statut = 'livree', date_livraison = ? WHERE id = ?"
        );
        $stmt->bind_param("si", $date_livraison, $vente_id);
        
        if ($stmt->execute()) {
            $this->mettreAJourSynthese();
            return true;
        }

        return false;
    }

    /**
     * Annuler une vente
     */
    public function annulerVente($vente_id, $notes = '')
    {
        $stmt = $this->db->prepare(
            "UPDATE ventes SET statut = 'annulee', notes = ? WHERE id = ?"
        );
        $stmt->bind_param("si", $notes, $vente_id);
        
        if ($stmt->execute()) {
            $this->mettreAJourSynthese();
            return true;
        }

        return false;
    }

    /**
     * Obtenir une vente par son ID
     */
    public function getVenteById($vente_id)
    {
        $stmt = $this->db->prepare(
            "SELECT v.*, u.nom_complet, u.email, u.telephone, c.adresse_livraison, c.ville, c.date_commande
             FROM ventes v
             LEFT JOIN users u ON v.utilisateur_id = u.id
             LEFT JOIN commandes c ON v.commande_id = c.id
             WHERE v.id = ?"
        );
        $stmt->bind_param("i", $vente_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Obtenir les ventes d'un utilisateur
     */
    public function getVentesUtilisateur($utilisateur_id, $limit = 50, $offset = 0)
    {
        $stmt = $this->db->prepare(
            "SELECT v.*, u.nom_complet, COUNT(DISTINCT cp.produit_id) as nombre_produits
             FROM ventes v
             LEFT JOIN users u ON v.utilisateur_id = u.id
             LEFT JOIN commande_produits cp ON v.commande_id = cp.commande_id
             WHERE v.utilisateur_id = ?
             GROUP BY v.id
             ORDER BY v.date_vente DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->bind_param("iii", $utilisateur_id, $limit, $offset);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Compter les ventes d'un utilisateur
     */
    public function countVentesUtilisateur($utilisateur_id)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM ventes WHERE utilisateur_id = ?");
        $stmt->bind_param("i", $utilisateur_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['total'];
    }

    /**
     * Obtenir l'historique complet des ventes (admin)
     */
    public function getHistoriqueVentes($filtres = [], $limit = 50, $offset = 0)
    {
        $sql = "
            SELECT v.*, u.nom_complet, u.email
            FROM ventes v
            LEFT JOIN users u ON v.utilisateur_id = u.id
            WHERE 1=1
        ";

        $params = [];
        $types = "";

        // Filtres
        if (!empty($filtres['statut'])) {
            $sql .= " AND v.statut = ?";
            $types .= "s";
            $params[] = $filtres['statut'];
        }

        if (!empty($filtres['date_debut'])) {
            $sql .= " AND DATE(v.date_vente) >= ?";
            $types .= "s";
            $params[] = $filtres['date_debut'];
        }

        if (!empty($filtres['date_fin'])) {
            $sql .= " AND DATE(v.date_vente) <= ?";
            $types .= "s";
            $params[] = $filtres['date_fin'];
        }

        if (!empty($filtres['utilisateur_id'])) {
            $sql .= " AND v.utilisateur_id = ?";
            $types .= "i";
            $params[] = (int)$filtres['utilisateur_id'];
        }

        if (!empty($filtres['search'])) {
            $sql .= " AND (u.nom_complet LIKE ? OR u.email LIKE ?)";
            $types .= "ss";
            $search_term = "%" . $filtres['search'] . "%";
            $params[] = $search_term;
            $params[] = $search_term;
        }

        $sql .= " ORDER BY v.date_vente DESC LIMIT ? OFFSET ?";
        $types .= "ii";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        if ($types) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Compter les ventes selon filtres
     */
    public function countVentes($filtres = [])
    {
        $sql = "SELECT COUNT(*) as total FROM ventes v LEFT JOIN users u ON v.utilisateur_id = u.id WHERE 1=1";

        $params = [];
        $types = "";

        if (!empty($filtres['statut'])) {
            $sql .= " AND v.statut = ?";
            $types .= "s";
            $params[] = $filtres['statut'];
        }

        if (!empty($filtres['date_debut'])) {
            $sql .= " AND DATE(v.date_vente) >= ?";
            $types .= "s";
            $params[] = $filtres['date_debut'];
        }

        if (!empty($filtres['date_fin'])) {
            $sql .= " AND DATE(v.date_vente) <= ?";
            $types .= "s";
            $params[] = $filtres['date_fin'];
        }

        $stmt = $this->db->prepare($sql);
        if ($types) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['total'];
    }

    /**
     * Obtenir les détails des produits vendus
     */
    public function getDetailsProduits($vente_id)
    {
        $stmt = $this->db->prepare(
            "SELECT cp.produit_id, cp.quantite, cp.prix, p.nom, p.image, p.marque
             FROM commande_produits cp
             JOIN produits p ON cp.produit_id = p.id
             WHERE cp.commande_id = (SELECT commande_id FROM ventes WHERE id = ?)"
        );
        $stmt->bind_param("i", $vente_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtenir les statistiques des ventes
     */
    public function getStatistiques($date_debut = null, $date_fin = null)
    {
        $sql = "SELECT 
                COUNT(*) as nombre_ventes,
                SUM(nombre_articles) as articles_vendus,
                SUM(montant_total) as montant_total,
                AVG(montant_total) as montant_moyen,
                COUNT(DISTINCT utilisateur_id) as nombre_clients
            FROM ventes
            WHERE statut IN ('confirmee', 'livree')";

        $params = [];
        $types = "";

        if ($date_debut) {
            $sql .= " AND DATE(date_vente) >= ?";
            $types .= "s";
            $params[] = $date_debut;
        }

        if ($date_fin) {
            $sql .= " AND DATE(date_vente) <= ?";
            $types .= "s";
            $params[] = $date_fin;
        }

        $stmt = $this->db->prepare($sql);
        if ($types) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Obtenir les produits les plus vendus
     */
    public function getProduitsPopulaires($date_debut = null, $date_fin = null, $limit = 10)
    {
        $sql = "
            SELECT 
                p.id,
                p.nom,
                p.prix,
                p.image,
                SUM(cp.quantite) as total_vendu,
                COUNT(DISTINCT v.id) as nombre_commandes,
                SUM(cp.quantite * cp.prix) as montant_total
            FROM commande_produits cp
            JOIN produits p ON cp.produit_id = p.id
            JOIN ventes v ON cp.commande_id = v.commande_id
            WHERE v.statut IN ('confirmee', 'livree')
        ";

        $params = [];
        $types = "";

        if ($date_debut) {
            $sql .= " AND DATE(v.date_vente) >= ?";
            $types .= "s";
            $params[] = $date_debut;
        }

        if ($date_fin) {
            $sql .= " AND DATE(v.date_vente) <= ?";
            $types .= "s";
            $params[] = $date_fin;
        }

        $sql .= " GROUP BY p.id ORDER BY total_vendu DESC LIMIT ?";
        $types .= "i";
        $params[] = $limit;

        $stmt = $this->db->prepare($sql);
        if ($types) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtenir les ventes par jour
     */
    public function getVentesParJour($date_debut = null, $date_fin = null)
    {
        $date_debut = $date_debut ?? date('Y-m-01');
        $date_fin = $date_fin ?? date('Y-m-d');

        $stmt = $this->db->prepare(
            "SELECT 
                DATE(date_vente) as jour,
                COUNT(*) as nombre_ventes,
                SUM(nombre_articles) as articles,
                SUM(montant_total) as montant
            FROM ventes
            WHERE statut IN ('confirmee', 'livree')
            AND DATE(date_vente) BETWEEN ? AND ?
            GROUP BY DATE(date_vente)
            ORDER BY jour DESC"
        );
        $stmt->bind_param("ss", $date_debut, $date_fin);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtenir les clients avec le plus d'achats
     */
    public function getClientsTopAcheteurs($limit = 10)
    {
        $stmt = $this->db->prepare(
            "SELECT 
                u.id,
                u.nom_complet,
                u.email,
                COUNT(v.id) as nombre_achats,
                SUM(v.montant_total) as montant_total,
                AVG(v.montant_total) as montant_moyen,
                MAX(v.date_vente) as dernier_achat
            FROM users u
            JOIN ventes v ON u.id = v.utilisateur_id
            WHERE v.statut IN ('confirmee', 'livree')
            GROUP BY u.id
            ORDER BY montant_total DESC
            LIMIT ?"
        );
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Créer une demande de retour
     */
    public function creerRetour($vente_id, $produit_id, $quantite, $raison = '')
    {
        $stmt = $this->db->prepare(
            "INSERT INTO ventes_retours (vente_id, produit_id, quantite, raison, statut, date_demande)
             VALUES (?, ?, ?, ?, 'demande', NOW())"
        );

        $statut = 'demande';
        $stmt->bind_param("iis", $vente_id, $produit_id, $quantite);
        return $stmt->execute();
    }

    /**
     * Obtenir les retours en attente
     */
    public function getRetoursEnAttente()
    {
        $stmt = $this->db->prepare(
            "SELECT vr.*, p.nom as produit_nom, v.montant_total
             FROM ventes_retours vr
             LEFT JOIN produits p ON vr.produit_id = p.id
             LEFT JOIN ventes v ON vr.vente_id = v.id
             WHERE vr.statut IN ('demande', 'approuve')
             ORDER BY vr.date_demande DESC"
        );
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Approuver un retour
     */
    public function approuverRetour($retour_id)
    {
        $stmt = $this->db->prepare(
            "UPDATE ventes_retours SET statut = 'approuve' WHERE id = ?"
        );
        $stmt->bind_param("i", $retour_id);
        return $stmt->execute();
    }

    /**
     * Traiter un retour
     */
    public function traiterRetour($retour_id, $notes = '')
    {
        $date_traitement = date('Y-m-d H:i:s');
        $stmt = $this->db->prepare(
            "UPDATE ventes_retours SET statut = 'traite', date_traitement = ?, notes = ? WHERE id = ?"
        );
        $stmt->bind_param("ssi", $date_traitement, $notes, $retour_id);
        return $stmt->execute();
    }

    /**
     * Mettre à jour la synthèse du jour
     */
    private function mettreAJourSynthese()
    {
        $date_jour = date('Y-m-d');

        $stats = $this->getStatistiques($date_jour, $date_jour);

        $stmt = $this->db->prepare(
            "INSERT INTO ventes_synthese (date_jour, nombre_ventes, nombre_articles_vendus, montant_total, montant_moyen_panier, nombre_clients)
             VALUES (?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
             nombre_ventes = ?,
             nombre_articles_vendus = ?,
             montant_total = ?,
             montant_moyen_panier = ?,
             nombre_clients = ?"
        );

        $stmt->bind_param(
            "siiddiidii",
            $date_jour,
            $stats['nombre_ventes'],
            $stats['articles_vendus'],
            $stats['montant_total'],
            $stats['montant_moyen'],
            $stats['nombre_clients'],
            $stats['nombre_ventes'],
            $stats['articles_vendus'],
            $stats['montant_total'],
            $stats['montant_moyen'],
            $stats['nombre_clients']
        );

        $stmt->execute();
    }

    /**
     * Obtenir la synthèse d'une période
     */
    public function getSynthesePeriode($date_debut, $date_fin)
    {
        $stmt = $this->db->prepare(
            "SELECT *
             FROM ventes_synthese
             WHERE date_jour BETWEEN ? AND ?
             ORDER BY date_jour DESC"
        );
        $stmt->bind_param("ss", $date_debut, $date_fin);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
