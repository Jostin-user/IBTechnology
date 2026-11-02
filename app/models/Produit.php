<?php

require_once __DIR__ . '/Categorie.php';
require_once __DIR__ . '/../helpers/SlugHelper.php';

class Produit
{
    private $db;
    public int $totalPage;
    public function __construct($db)
    {
        $this->db = $db;
        $this->totalPage = 1;
    }

    public function getProduits()
    {
        $result = $this->db->query("SELECT * FROM produits ORDER BY id DESC");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getProduitById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM produits WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Récupère un produit par son slug
     */
    public function getProduitBySlug($slug)
    {
        $stmt = $this->db->prepare("SELECT * FROM produits WHERE slug = ?");
        $stmt->bind_param("s", $slug);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getProduitsParCategorie($categorieId, $limit = 4)
    {
        // Si aucune catégorie fournie, retourner les derniers produits
        if (empty($categorieId)) {
            $sql = "SELECT * FROM produits ORDER BY id DESC LIMIT ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("i", $limit);
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }

        // Récupérer les descendants de la catégorie pour inclure l'arbre
        $categorieModel = new Categorie($this->db);
        $ids = $categorieModel->getDescendantIds((int) $categorieId);
        if (empty($ids))
            $ids = [(int) $categorieId];
        $ids = array_map('intval', $ids);
        $in = implode(',', $ids);

        $sql = "SELECT * FROM produits WHERE categorie_id IN ($in) ORDER BY id DESC LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function creerProduit($data)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO produits (nom, description, prix, prix_promo, stock, image, categorie_id, marque, actif)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "ssddisisi",
            $data['nom'],
            $data['description'],
            $data['prix'],
            $data['prix_promo'],
            $data['stock'],
            $data['image'],
            $data['categorie_id'],
            $data['marque'],
            $data['actif']
        );
        if ($stmt->execute()) {
            // Générer et mettre à jour le slug
            $id = $this->db->insert_id;
            $slug = SlugHelper::generateSlug($data['nom'], $id);
            $this->updateSlug($id, $slug);
            return true;
        }
        return false;
    }

    public function modifierProduit($id, $data)
    {
        if (!empty($data['image'])) {
            $stmt = $this->db->prepare(
                "UPDATE produits SET nom=?, image=?, description=?, prix=?, prix_promo=?, stock=?, categorie_id=?, marque=?, actif=? WHERE id=?"
            );
            // Note: Ne pas unlink l'ancienne image ici, gérer séparément si nécessaire
            $imagen = $data['image'];
            $stmt->bind_param(
                "sssddiisii",
                $data['nom'],
                $imagen,
                $data['description'],
                $data['prix'],
                $data['prix_promo'],
                $data['stock'],
                $data['categorie_id'],
                $data['marque'],
                $data['actif'],
                $id
            );
        } else {
            $stmt = $this->db->prepare(
                "UPDATE produits SET nom=?, description=?, prix=?, prix_promo=?, stock=?, categorie_id=?, marque=?, actif=? WHERE id=?"
            );
            $stmt->bind_param(
                "ssddiisii",
                $data['nom'],
                $data['description'],
                $data['prix'],
                $data['prix_promo'],
                $data['stock'],
                $data['categorie_id'],
                $data['marque'],
                $data['actif'],
                $id
            );
        }
        if ($stmt->execute()) {
            // Mettre à jour le slug
            $slug = SlugHelper::generateSlug($data['nom'], $id);
            $this->updateSlug($id, $slug);
            return true;
        }
        return false;
    }

    /**
     * Met à jour le slug d'un produit
     */
    private function updateSlug($id, $slug)
    {
        $stmt = $this->db->prepare("UPDATE produits SET slug = ? WHERE id = ?");
        $stmt->bind_param("si", $slug, $id);
        return $stmt->execute();
    }

    public function supprimerProduit($id)
    {
        $stmt = $this->db->prepare("DELETE FROM produits WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function rechercheProduitPaginee($filtres, $limit, $offset)
    {
        $sql = "
            SELECT p.id, p.nom, p.prix, p.prix_promo, p.categorie_id,
                p.stock, p.image, p.marque,
                c.nom AS categorie
            FROM produits p
            JOIN categories c ON p.categorie_id = c.id
            WHERE 1=1 
        ";

        $params = [];
        $types = "";
        if (!empty($filtres['min'])) {
            $sql .= " AND prix >= ?";
            $types .= "i";
            $params[] = (int) $filtres['min'];
        }

        if (!empty($filtres['max'])) {
            $sql .= " AND prix <= ?";
            $types .= "i";
            $params[] = (int) $filtres['max'];
        }
        if (!empty($filtres['q'])) {
            $sql .= " AND (p.nom LIKE ? OR p.description LIKE ? OR p.marque LIKE ? OR c.nom LIKE ?)";
            $search = '%' . $filtres['q'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $types .= "ssss";
        }

        if (!empty($filtres['categorie'])) {
            // Inclure les descendants de la catégorie
            $categorieModel = new Categorie($this->db);
            $ids = $categorieModel->getDescendantIds((int) $filtres['categorie']);
            if (empty($ids))
                $ids = [(int) $filtres['categorie']];
            $ids = array_map('intval', $ids);
            $in = implode(',', $ids);
            $sql .= " AND p.categorie_id IN ($in)";
        }

        if (!empty($filtres['marque'])) {
            $sql .= " AND p.marque = ?";
            $params[] = $filtres['marque'];
            $types .= "s";
        }

        if ($filtres['statut'] !== null && $filtres['statut'] !== '') {
            $sql .= " AND p.actif = ?";
            $params[] = (int) $filtres['statut'];
            $types .= "i";
        }

        if ($filtres['stock'] === 'disponnible') {
            $sql .= " AND p.stock > 0";
        }

        if ($filtres['stock'] === 'rupture') {
            $sql .= " AND p.stock = 0";
        }

        // Tri
        $sql .= " ORDER BY ";
        switch ($filtres['tri'] ?? null) {
            case 'prix_asc':
                $sql .= "p.prix ASC";
                break;
            case 'prix_desc':
                $sql .= "p.prix DESC";
                break;
            case 'nom_asc':
                $sql .= "p.nom ASC";
                break;
            case 'nom_desc':
                $sql .= "p.nom DESC";
                break;
            case 'nouveau':
                $sql .= "p.date_creation DESC";
                break;
            default:
                $sql .= "p.date_creation DESC"; // Par défaut, les plus récents
        }

        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";

        $stmt = $this->db->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }


    public function countProduit($filtres)
    {
        $sql = "SELECT COUNT(*) AS total
                FROM produits p
                JOIN categories c ON p.categorie_id = c.id
                WHERE 1=1";
        $params = [];
        $types = "";

        if (!empty($filtres['q'])) {
            $sql .= " AND (p.nom LIKE ? OR p.description LIKE ? OR p.marque LIKE ? OR c.nom LIKE ?)";
            $search = '%' . $filtres['q'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $types .= "ssss";
        }
        if (!empty($filtres['min'])) {
            $sql .= " AND p.prix >= ?";
            $types .= "i";
            $params[] = (int) $filtres['min'];
        }

        if (!empty($filtres['max'])) {
            $sql .= " AND p.prix <= ?";
            $types .= "i";
            $params[] = (int) $filtres['max'];
        }

        if (!empty($filtres['categorie'])) {
            $categorieModel = new Categorie($this->db);
            $ids = $categorieModel->getDescendantIds((int) $filtres['categorie']);
            if (empty($ids))
                $ids = [(int) $filtres['categorie']];
            $ids = array_map('intval', $ids);
            $in = implode(',', $ids);
            $sql .= " AND p.categorie_id IN ($in)";
        }

        if (!empty($filtres['marque'])) {
            $sql .= " AND p.marque = ?";
            $params[] = $filtres['marque'];
            $types .= "s";
        }

        if ($filtres['statut'] == null){
            $sql .= " AND p.actif ='1' ";
        }
        else if($filtres['statut'] !== null && $filtres['statut'] !== '') {
            $sql .= " AND p.actif = ?";
            $params[] = (int) $filtres['statut'];
            $types .= "i";
        }

        if ($filtres['stock'] === 'dispo') {
            $sql .= " AND p.stock > 0";
        }

        if ($filtres['stock'] === 'rupture') {
            $sql .= " AND p.stock = 0";
        }

        $stmt = $this->db->prepare($sql);

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        return $stmt->get_result()->fetch_assoc()['total'];
    }


    ///a revoir

    public function getProduitsSimilaires($categorieId, $produitId, $limit = 4)
    {
        $sql = "SELECT p.id as id, p.nom as nom, p.prix as prix, prix_promo, image
        FROM produits p
          JOIN categories c 
          ON p.categorie_id=c.id
          WHERE categorie_id = ?
          AND p.id != ?
          AND p.actif = 1
          AND c.actif=1
        ORDER BY date_creation DESC
        LIMIT ?
    ";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("iii", $categorieId, $produitId, $limit);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getUniqueMarques()
    {
        $sql = "SELECT DISTINCT marque FROM produits WHERE marque IS NOT NULL AND marque != '' ORDER BY marque";
        $result = $this->db->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getProduitsPopulaires($limit = 4)
    {
        $sql = "
            SELECT id, nom, prix, prix_promo, marque, image, vues
            FROM produits
            WHERE statut = 'disponible'
            ORDER BY vues DESC, date_creation DESC
            LIMIT ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function incrementerVues($id)
    {
        $sql = "UPDATE produits SET vues = vues + 1 WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }

    public function search($keyword)
    {
        $stmt = $this->db->prepare("
        SELECT *
        FROM produits
        WHERE nom LIKE ?
        OR description LIKE ?
        ORDER BY created_at DESC
    ");

        $search = "%" . $keyword . "%";
        $stmt->execute([$search, $search]);

        return $stmt->fetchAll();
    }
    public function searchSuggestions($keyword)
    {
        $stmt = $this->db->prepare("
        SELECT p.id, p.nom, p.slug, p.image, p.prix, p.marque, c.nom AS categorie
        FROM produits p
        JOIN categories c ON p.categorie_id = c.id
        WHERE p.actif = 1
          AND (p.nom LIKE ? OR p.description LIKE ? OR p.marque LIKE ? OR c.nom LIKE ?)
        ORDER BY
          CASE WHEN p.nom LIKE ? THEN 0 ELSE 1 END,
          p.date_creation DESC
        LIMIT 8
    ");

        $search = "%" . $keyword . "%";
        $stmt->bind_param("sssss", $search, $search, $search, $search, $search);
        $stmt->execute();

        return $stmt->get_result()->fetch_all();
    }

}

?>