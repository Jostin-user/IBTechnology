<?php

class Commande
{
    private $db;
    public $lastError = '';

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Récupère toutes les commandes d'un utilisateur
     */
    public function getCommandesByUtilisateur($utilisateurId)
    {
        $stmt = $this->db->prepare("
            SELECT c.*, p.statut as statut_paiement, p.methode as methode_paiement
            FROM commandes c
            LEFT JOIN paiements p ON c.id = p.commande_id
            WHERE c.utilisateur_id = ?
            ORDER BY c.date_commande DESC
        ");
        $stmt->bind_param("i", $utilisateurId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Récupère les détails d'une commande spécifique
     */
    public function getCommandeById($commandeId, $utilisateurId = null)
    {
        $sql = "
            SELECT c.*, p.statut as statut_paiement, p.methode as methode_paiement
            FROM commandes c
            LEFT JOIN paiements p ON c.id = p.commande_id
            WHERE c.id = ?
        ";
        $params = [$commandeId];
        $types = "i";

        if ($utilisateurId !== null) {
            $sql .= " AND c.utilisateur_id = ?";
            $params[] = $utilisateurId;
            $types .= "i";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Récupère les produits d'une commande
     */
    public function getProduitsCommande($commandeId)
    {
        $stmt = $this->db->prepare("
            SELECT cp.*, p.nom, p.image, p.marque
            FROM commande_produits cp
            JOIN produits p ON cp.produit_id = p.id
            WHERE cp.commande_id = ?
        ");
        $stmt->bind_param("i", $commandeId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Crée une nouvelle commande à partir du panier
     */
    public function creerCommande($utilisateurId, $panierId)
    {
        // Récupérer les produits du panier
        $stmt = $this->db->prepare("
            SELECT pp.produit_id, pp.quantite, pr.prix
            FROM panier_produits pp
            JOIN produits pr ON pp.produit_id = pr.id
            WHERE pp.panier_id = ?
        ");
        $stmt->bind_param("i", $panierId);
        $stmt->execute();
        $produits = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        if (empty($produits)) {
            return false;
        }

        // Calculer le montant total
        $montantTotal = 0;
        foreach ($produits as $produit) {
            $montantTotal += $produit['prix'] * $produit['quantite'];
        }

        // Commencer une transaction
        $this->db->begin_transaction();

        try {
            // Créer la commande
            $stmt = $this->db->prepare("
                INSERT INTO commandes (utilisateur_id, montant_total, statut, date_commande)
                VALUES (?, ?, 'en_attente', NOW())
            ");
            $stmt->bind_param("id", $utilisateurId, $montantTotal);
            $stmt->execute();
            $commandeId = $this->db->insert_id;

            // Ajouter les produits à la commande
            $stmt = $this->db->prepare("
                INSERT INTO commande_produits (commande_id, produit_id, quantite, prix)
                VALUES (?, ?, ?, ?)
            ");
            foreach ($produits as $produit) {
                $stmt->bind_param("iiid", $commandeId, $produit['produit_id'], $produit['quantite'], $produit['prix']);
                $stmt->execute();
            }

            // Créer l'entrée de paiement
            $stmt = $this->db->prepare("
                INSERT INTO paiements (commande_id, methode, statut)
                VALUES (?, 'livraison', 'en_attente')
            ");
            $stmt->bind_param("i", $commandeId);
            $stmt->execute();

            // Valider la transaction
            $this->db->commit();

            return $commandeId;

        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    /**
     * Annule une commande
     */
    public function annulerCommande($commandeId, $utilisateurId)
    {
        $stmt = $this->db->prepare("
            UPDATE commandes
            SET statut = 'annulee'
            WHERE id = ? AND utilisateur_id = ? AND statut IN ('en_attente', 'validee')
        ");
        $stmt->bind_param("ii", $commandeId, $utilisateurId);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }

    /**
     * Met à jour le statut d'une commande (pour admin)
     */
    public function updateStatut($commandeId, $statut)
    {
        $stmt = $this->db->prepare("
            UPDATE commandes
            SET statut = ?
            WHERE id = ?
        ");
        $stmt->bind_param("si", $statut, $commandeId);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }

    /**
     * Récupère toutes les commandes (pour admin)
     */
    public function getAllCommandes()
    {
        $result = $this->db->query("
            SELECT c.*, u.nom_complet, u.email, p.statut as statut_paiement
            FROM commandes c
            JOIN users u ON c.utilisateur_id = u.id
            LEFT JOIN paiements p ON c.id = p.commande_id
            ORDER BY c.date_commande DESC
        ");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Crée une commande à partir du panier en session
     * $panier: array d'items [id, nom, prix, quantite, image]
     */
    public function creerDepuisPanierSession($utilisateurId, $panier, $shippingCost = 0.0, $subtotal = 0.0, $adresse = [])
    {
        if (empty($panier)) {
            return false;
        }

        // Calculer montant total
        $montantTotal = 0.0;
        foreach ($panier as $item) {
            $montantTotal += ($item['prix'] * $item['quantite']);
        }
        $montantTotal += floatval($shippingCost);

        // Démarrer la transaction et verrouiller les lignes produits
        $this->db->begin_transaction();
        try {
            // Vérifier le stock pour chaque produit (verrouillage pour éviter race conditions)
            $checkStmt = $this->db->prepare("SELECT stock, nom FROM produits WHERE id = ? FOR UPDATE");
            foreach ($panier as $item) {
                $pid = (int) $item['id'];
                $qte = (int) $item['quantite'];
                $checkStmt->bind_param('i', $pid);
                $checkStmt->execute();
                $res = $checkStmt->get_result()->fetch_assoc();
                if (!$res) {
                    $this->lastError = 'Produit introuvable (ID: ' . $pid . ').';
                    $this->db->rollback();
                    return false;
                }
                if ((int) $res['stock'] < $qte) {
                    $this->lastError = 'Stock insuffisant pour le produit "' . $res['nom'] . '". Quantité demandée: ' . $qte . ', disponible: ' . $res['stock'] . '.';
                    $this->db->rollback();
                    return false;
                }
            }
            // Insérer la commande. NOTE: Requires columns shipping_cost, subtotal, adresse_livraison, ville, code_postal, pays, telephone, note_livraison in commandes table.
            $stmt = $this->db->prepare("INSERT INTO commandes (utilisateur_id, montant_total, shipping_cost, subtotal, adresse_livraison, ville, quartier, telephone, note_livraison) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $adresse_livraison = $adresse['adresse'] ?? '';
            $ville = $adresse['ville'] ?? '';
            $quartier = $adresse['quartier'] ?? '';
            $telephone = $adresse['telephone'] ?? '';
            $note = $adresse['note_livraison'] ?? '';

            $stmt->bind_param('idddsssss', $utilisateurId, $montantTotal, $shippingCost, $subtotal, $adresse_livraison, $ville, $quartier, $telephone, $note);
            if (!$stmt->execute()) {
                $this->lastError = 'Erreur SQL lors de la création de la commande : ' . $stmt->error;
                $this->db->rollback();
                return false;
            }
            $commandeId = $this->db->insert_id;

            // Insérer les produits
            $stmt = $this->db->prepare("INSERT INTO commande_produits (commande_id, produit_id, quantite, prix) VALUES (?, ?, ?, ?)");
            foreach ($panier as $item) {
                $pid = $item['id'];
                $qte = $item['quantite'];
                $prix = $item['prix'];
                $stmt->bind_param('iiid', $commandeId, $pid, $qte, $prix);
                $stmt->execute();
            }

            // Décrémenter le stock pour chaque produit
            $updateStockStmt = $this->db->prepare("UPDATE produits SET stock = stock - ? WHERE id = ?");
            foreach ($panier as $item) {
                $pid = (int) $item['id'];
                $qte = (int) $item['quantite'];
                $updateStockStmt->bind_param('ii', $qte, $pid);
                $updateStockStmt->execute();
            }

            // Enregistrer paiement à la livraison
            $stmt = $this->db->prepare("INSERT INTO paiements (commande_id, methode, statut) VALUES (?, 'livraison', 'en_attente')");
            $stmt->bind_param('i', $commandeId);
            $stmt->execute();

            $this->db->commit();
            return $commandeId;
        } catch (Throwable $e) {
            $this->db->rollback();
            $this->lastError = 'Erreur lors de la création de la commande : ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Récupère toutes les commandes pour admin avec filtres et pagination
     */
    public function getAllCommandesAdmin($filtres = [], $limit = 10, $offset = 0)
    {
        $where = [];
        $params = [];
        $types = '';

        if (!empty($filtres['q'])) {
            $where[] = "(c.id LIKE ? OR u.nom_complet LIKE ? OR u.email LIKE ?)";
            $search = '%' . $filtres['q'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $types .= 'sss';
        }

        if (!empty($filtres['statut'])) {
            $where[] = "c.statut = ?";
            $params[] = $filtres['statut'];
            $types .= 's';
        }

        if (!empty($filtres['date_debut'])) {
            $where[] = "DATE(c.date_commande) >= ?";
            $params[] = $filtres['date_debut'];
            $types .= 's';
        }

        if (!empty($filtres['date_fin'])) {
            $where[] = "DATE(c.date_commande) <= ?";
            $params[] = $filtres['date_fin'];
            $types .= 's';
        }

        if (!empty($filtres['client'])) {
            $where[] = "u.nom_complet LIKE ?";
            $params[] = '%' . $filtres['client'] . '%';
            $types .= 's';
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "
            SELECT c.*, u.nom_complet, u.email, p.statut as statut_paiement, p.methode as methode_paiement
            FROM commandes c
            JOIN users u ON c.utilisateur_id = u.id
            LEFT JOIN paiements p ON c.id = p.commande_id
            $whereClause
            ORDER BY c.date_commande DESC
            LIMIT ? OFFSET ?
        ";

        $params[] = $limit;
        $params[] = $offset;
        $types .= 'ii';

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Compte le nombre total de commandes pour admin avec filtres
     */
    public function countCommandesAdmin($filtres = [])
    {
        $where = [];
        $params = [];
        $types = '';

        if (!empty($filtres['q'])) {
            $where[] = "(c.id LIKE ? OR u.nom_complet LIKE ? OR u.email LIKE ?)";
            $search = '%' . $filtres['q'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $types .= 'sss';
        }

        if (!empty($filtres['statut'])) {
            $where[] = "c.statut = ?";
            $params[] = $filtres['statut'];
            $types .= 's';
        }

        if (!empty($filtres['date_debut'])) {
            $where[] = "DATE(c.date_commande) >= ?";
            $params[] = $filtres['date_debut'];
            $types .= 's';
        }

        if (!empty($filtres['date_fin'])) {
            $where[] = "DATE(c.date_commande) <= ?";
            $params[] = $filtres['date_fin'];
            $types .= 's';
        }

        if (!empty($filtres['client'])) {
            $where[] = "u.nom_complet LIKE ?";
            $params[] = '%' . $filtres['client'] . '%';
            $types .= 's';
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "
            SELECT COUNT(*) as total
            FROM commandes c
            JOIN users u ON c.utilisateur_id = u.id
            LEFT JOIN paiements p ON c.id = p.commande_id
            $whereClause
        ";

        $stmt = $this->db->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['total'];
    }

    /**
     * Met à jour le statut d'une commande avec commentaire
     */
    public function updateStatutCommande($commandeId, $statut, $commentaire = '')
    {
        $this->db->begin_transaction();

        try {
            // Mettre à jour le statut
            $stmt = $this->db->prepare("
                UPDATE commandes
                SET statut = ?
                WHERE id = ?
            ");
            $stmt->bind_param("si", $statut, $commandeId);
            $stmt->execute();

            // Ajouter un commentaire si fourni (si la table existe)
            if (!empty($commentaire)) {
                try {
                    $stmt = $this->db->prepare("
                        INSERT INTO commande_logs (commande_id, action, commentaire, date_action)
                        VALUES (?, 'changement_statut', ?, NOW())
                    ");
                    $stmt->bind_param("is", $commandeId, $commentaire);
                    $stmt->execute();
                } catch (Exception $e) {
                    // Table n'existe pas, ignorer
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    /**
     * Compte le nombre de commandes pour un utilisateur
     */
    public function countCommandesByUser($userId)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM commandes WHERE utilisateur_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['count'];
    }

    // recherche rapide pour suggestions admin
    public function searchSuggestions($q, $limit = 5)
    {
        $like = '%' . $q . '%';
        $stmt = $this->db->prepare("SELECT c.id, c.utilisateur_id, c.montant_total, c.statut, c.date_commande, u.nom_complet FROM commandes c JOIN users u ON c.utilisateur_id = u.id WHERE c.id LIKE ? OR u.nom_complet LIKE ? OR u.email LIKE ? ORDER BY c.date_commande DESC LIMIT ?");
        $stmt->bind_param('sssi', $like, $like, $like, $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}