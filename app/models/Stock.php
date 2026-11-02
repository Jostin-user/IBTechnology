<?php

class Stock
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Obtenir tous les produits avec leurs informations de stock
     */
    public function getStockComplet($filtres = [], $limit = 50, $offset = 0)
    {
        $sql = "
            SELECT 
                p.id,
                p.nom,
                p.prix,
                p.categorie_id,
                c.nom AS categorie,
                p.stock,
                p.stock_reserve,
                (p.stock - p.stock_reserve) AS stock_disponible,
                p.actif,
                sa.seuil_minimum,
                sa.seuil_maximum,
                sa.etat AS alerte_etat,
                sa.stock_critique,
                p.marque,
                p.image,
                p.date_creation
            FROM produits p
            LEFT JOIN categories c ON p.categorie_id = c.id
            LEFT JOIN stock_alertes sa ON p.id = sa.produit_id
            WHERE 1=1
        ";

        $params = [];
        $types = "";

        if (!empty($filtres['search'])) {
            $sql .= " AND p.nom LIKE ?";
            $types .= "s";
            $params[] = "%" . $filtres['search'] . "%";
        }

        if (!empty($filtres['categorie'])) {
            $sql .= " AND p.categorie_id = ?";
            $types .= "i";
            $params[] = (int) $filtres['categorie'];
        }

        if (!empty($filtres['etat'])) {
            if ($filtres['etat'] === 'rupture') {
                $sql .= " AND p.stock = 0";
            } elseif ($filtres['etat'] === 'critique') {
                $sql .= " AND sa.etat = 'critique'";
            } elseif ($filtres['etat'] === 'faible') {
                $sql .= " AND sa.etat = 'faible'";
            }
        }
        $sql .= " ORDER BY p.id DESC";
        if ($limit > 0) {
            $sql .= " LIMIT ? OFFSET ?";
            $types .= "ii";
            $params[] = $limit;
            $params[] = $offset;
        }

        if ($types) {
            $stmt = $this->db->prepare($sql);
            if($stmt === false) {
                throw new Exception("Erreur de préparation SQL: " . $this->db->error);
            }
            $stmt->bind_param($types, ...$params);
        } else {
            $stmt = $this->db->prepare($sql);
        }

        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Compter les produits selon les filtres
     */
    public function countStock($filtres = [])
    {
        $sql = "SELECT COUNT(*) AS total FROM produits p LEFT JOIN stock_alertes sa ON p.id = sa.produit_id WHERE 1=1";

        $params = [];
        $types = "";

        if (!empty($filtres['search'])) {
            $sql .= " AND p.nom LIKE ?";
            $types .= "s";
            $params[] = "%" . $filtres['search'] . "%";
        }

        if (!empty($filtres['categorie'])) {
            $sql .= " AND p.categorie_id = ?";
            $types .= "i";
            $params[] = (int) $filtres['categorie'];
        }

        if (!empty($filtres['etat'])) {
            if ($filtres['etat'] === 'rupture') {
                $sql .= " AND p.stock = 0";
            } elseif ($filtres['etat'] === 'critique') {
                $sql .= " AND sa.etat = 'critique'";
            }
        }

        if ($types) {
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param($types, ...$params);
        } else {
            $stmt = $this->db->prepare($sql);
        }

        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['total'];
    }

    /**
     * Ajouter un mouvement de stock
     */
    public function ajouterMouvement($produit_id, $type, $quantite, $raison = null, $notes = null, $admin_id = null)
    {
        $produit = $this->getProduit($produit_id);
        if (!$produit) {
            return false;
        }

        $quantite_precedente = $produit['stock'];

        switch ($type) {
            case 'entree':
                $quantite_nouvelle = $quantite_precedente + $quantite;
                break;
            case 'sortie':
            case 'perte':
                $quantite_nouvelle = max(0, $quantite_precedente - $quantite);
                break;
            case 'retour':
                $quantite_nouvelle = $quantite_precedente + $quantite;
                break;
            case 'ajustement':
                $quantite_nouvelle = $quantite;
                break;
            default:
                return false;
        }

        // Enregistrer le mouvement
        $stmt = $this->db->prepare(
            "INSERT INTO stock_mouvements (produit_id, type, quantite, quantite_precedente, quantite_nouvelle, raison, notes, admin_id)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "isiiiisi",
            $produit_id,
            $type,
            $quantite,
            $quantite_precedente,
            $quantite_nouvelle,
            $raison,
            $notes,
            $admin_id
        );

        $result = $stmt->execute();

        if ($result) {
            // Mettre à jour le stock du produit
            $this->mettreAJourStock($produit_id, $quantite_nouvelle);
            // Vérifier les alertes
            $this->verifierAlertesStock($produit_id);
        }

        return $result;
    }

    /**
     * Mettre à jour le stock d'un produit
     */
    public function mettreAJourStock($produit_id, $nouveau_stock)
    {
        $stmt = $this->db->prepare("UPDATE produits SET stock = ? WHERE id = ?");
        $stmt->bind_param("ii", $nouveau_stock, $produit_id);
        return $stmt->execute();
    }

    /**
     * Obtenir un produit par ID
     */
    public function getProduit($id)
    {
        $stmt = $this->db->prepare(
            "SELECT p.*, sa.etat AS alerte_etat, sa.seuil_minimum, sa.seuil_maximum FROM produits p 
             LEFT JOIN stock_alertes sa ON p.id = sa.produit_id 
             WHERE p.id = ?"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Obtenir l'historique des mouvements d'un produit
     */
    public function getHistoriqueMouvements($produit_id, $limit = 50, $offset = 0)
    {
        $stmt = $this->db->prepare(
            "SELECT sm.*, u.nom_complet AS admin_nom
             FROM stock_mouvements sm
             LEFT JOIN users u ON sm.admin_id = u.id
             WHERE sm.produit_id = ?
             ORDER BY sm.date_mouvement DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->bind_param("iii", $produit_id, $limit, $offset);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Compter l'historique des mouvements
     */
    public function countHistoriqueMouvements($produit_id)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM stock_mouvements WHERE produit_id = ?");
        $stmt->bind_param("i", $produit_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['total'];
    }

    /**
     * Définir les seuils d'alerte d'un produit
     */
    public function definirSeuilsAlerte($produit_id, $seuil_min, $seuil_max)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO stock_alertes (produit_id, seuil_minimum, seuil_maximum)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE seuil_minimum = ?, seuil_maximum = ?"
        );
        $stmt->bind_param("iiiii", $produit_id, $seuil_min, $seuil_max, $seuil_min, $seuil_max);
        return $stmt->execute();
    }

    /**
     * Vérifier et mettre à jour les alertes de stock
     */
    public function verifierAlertesStock($produit_id)
    {
        $produit = $this->getProduit($produit_id);
        if (!$produit) {
            return;
        }

        // Récupérer ou créer les seuils
        $stmt = $this->db->prepare("SELECT * FROM stock_alertes WHERE produit_id = ?");
        $stmt->bind_param("i", $produit_id);
        $stmt->execute();
        $alerte = $stmt->get_result()->fetch_assoc();

        if (!$alerte) {
            // Créer des seuils par défaut
            $this->definirSeuilsAlerte($produit_id, 10, 1000);
            $stmt->bind_param("i", $produit_id);
            $stmt->execute();
            $alerte = $stmt->get_result()->fetch_assoc();
        }

        $stock = $produit['stock'];
        $seuil_min = $alerte['seuil_minimum'];
        $seuil_max = $alerte['seuil_maximum'];

        if ($stock == 0) {
            $etat = 'rupture';
        } elseif ($stock < $seuil_min) {
            $etat = 'critique';
        } elseif ($stock < $seuil_min * 2) {
            $etat = 'faible';
        } else {
            $etat = 'normal';
        }

        // Mettre à jour l'alerte
        $stmt = $this->db->prepare(
            "UPDATE stock_alertes SET etat = ?, stock_critique = ? WHERE produit_id = ?"
        );
        $critique = ($etat === 'critique' || $etat === 'rupture') ? 1 : 0;
        $stmt->bind_param("sii", $etat, $critique, $produit_id);
        $stmt->execute();

        return $etat;
    }

    /**
     * Obtenir les produits en rupture de stock
     */
    public function getProduitEnRupture()
    {
        $stmt = $this->db->prepare(
            "SELECT p.*, c.nom AS categorie FROM produits p
             LEFT JOIN categories c ON p.categorie_id = c.id
             WHERE p.stock = 0 AND p.actif = 1
             ORDER BY p.nom ASC"
        );
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtenir les produits avec stock critique
     */
    public function getProduitStockCritique()
    {
        $stmt = $this->db->prepare(
            "SELECT p.*, c.nom AS categorie, sa.seuil_minimum, sa.etat
             FROM produits p
             LEFT JOIN categories c ON p.categorie_id = c.id
             LEFT JOIN stock_alertes sa ON p.id = sa.produit_id
             WHERE sa.etat IN ('critique', 'faible') AND p.actif = 1
             ORDER BY p.stock ASC"
        );
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtenir les statistiques de stock
     */
    public function getStatistiques()
    {
        $result = [];

        // Nombre total de produits
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM produits WHERE actif = 1");
        $result['total_produits'] = $stmt->fetch_assoc()['total'];

        // Produits en rupture
        $stmt = $this->db->query("SELECT COUNT(*) AS total FROM produits WHERE stock = 0 AND actif = 1");
        $result['rupture'] = $stmt->fetch_assoc()['total'];

        // Stock critique
        $stmt = $this->db->query(
            "SELECT COUNT(*) AS total FROM stock_alertes WHERE etat IN ('critique', 'rupture')"
        );
        $result['critique'] = $stmt->fetch_assoc()['total'];

        // Valeur totale du stock
        $stmt = $this->db->query(
            "SELECT COALESCE(SUM(p.stock * p.prix), 0) AS valeur FROM produits p WHERE p.actif = 1"
        );
        $result['valeur_stock'] = (float) $stmt->fetch_assoc()['valeur'];

        // Mouvements du jour
        $stmt = $this->db->query(
            "SELECT COUNT(*) AS total FROM stock_mouvements WHERE DATE(date_mouvement) = CURDATE()"
        );
        $result['mouvements_jour'] = $stmt->fetch_assoc()['total'];

        return $result;
    }

    /**
     * Ajouter un ajustement de stock
     */
    public function ajouterAjustement($produit_id, $nouveau_stock, $raison, $description, $admin_id)
    {
        $produit = $this->getProduit($produit_id);
        if (!$produit) {
            return false;
        }

        $ancien_stock = $produit['stock'];

        $stmt = $this->db->prepare(
            "INSERT INTO stock_ajustements (produit_id, ancien_stock, nouveau_stock, raison, description, admin_id)
             VALUES (?, ?, ?, ?, ?, ?)"
        );

        $stmt->bind_param("iiissi", $produit_id, $ancien_stock, $nouveau_stock, $raison, $description, $admin_id);
        $result = $stmt->execute();

        if ($result) {
            // Mettre à jour le stock
            $this->mettreAJourStock($produit_id, $nouveau_stock);
            // Enregistrer comme mouvement
            $quantite = abs($nouveau_stock - $ancien_stock);
            $type = $nouveau_stock > $ancien_stock ? 'entree' : 'sortie';
            $this->ajouterMouvement($produit_id, 'ajustement', $quantite, $raison, $description, $admin_id);
            // Vérifier alertes
            $this->verifierAlertesStock($produit_id);
        }

        return $result;
    }

    /**
     * Obtenir les mouvements récents
     */
    public function getMouvementsRecents($limit = 20)
    {
        $stmt = $this->db->prepare(
            "SELECT sm.*, p.nom AS produit_nom, u.nom_complet AS admin_nom
             FROM stock_mouvements sm
             LEFT JOIN produits p ON sm.produit_id = p.id
             LEFT JOIN users u ON sm.admin_id = u.id
             ORDER BY sm.date_mouvement DESC
             LIMIT ?"
        );
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Réserver du stock
     */
    public function reserverStock($produit_id, $quantite)
    {
        $produit = $this->getProduit($produit_id);
        if (!$produit || $produit['stock'] < $quantite) {
            return false;
        }

        $nouveau_reserve = $produit['stock_reserve'] + $quantite;
        $stmt = $this->db->prepare("UPDATE produits SET stock_reserve = ? WHERE id = ?");
        $stmt->bind_param("ii", $nouveau_reserve, $produit_id);
        return $stmt->execute();
    }

    /**
     * Libérer la réservation
     */
    public function libererReservation($produit_id, $quantite)
    {
        $produit = $this->getProduit($produit_id);
        if (!$produit) {
            return false;
        }

        $nouveau_reserve = max(0, $produit['stock_reserve'] - $quantite);
        $stmt = $this->db->prepare("UPDATE produits SET stock_reserve = ? WHERE id = ?");
        $stmt->bind_param("ii", $nouveau_reserve, $produit_id);
        return $stmt->execute();
    }

    /**
     * Rapport de stock
     */
    public function getRapportStock($date_debut = null, $date_fin = null)
    {
        $sql = "
            SELECT 
                p.id,
                p.nom,
                p.stock,
                p.prix,
                c.nom AS categorie,
                COUNT(sm.id) AS mouvements_total,
                SUM(CASE WHEN sm.type = 'entree' THEN sm.quantite ELSE 0 END) AS total_entrees,
                SUM(CASE WHEN sm.type IN ('sortie', 'perte') THEN sm.quantite ELSE 0 END) AS total_sorties
            FROM produits p
            LEFT JOIN categories c ON p.categorie_id = c.id
            LEFT JOIN stock_mouvements sm ON p.id = sm.produit_id
        ";

        $params = [];
        $types = "";

        if ($date_debut && $date_fin) {
            $sql .= " WHERE DATE(sm.date_mouvement) BETWEEN ? AND ?";
            $types = "ss";
            $params = [$date_debut, $date_fin];
        }

        $sql .= " GROUP BY p.id ORDER BY p.id DESC";

        if ($types) {
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param($types, ...$params);
        } else {
            $stmt = $this->db->prepare($sql);
        }

        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
