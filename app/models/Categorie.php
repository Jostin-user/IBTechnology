<?php

require_once __DIR__ . '/../helpers/SlugHelper.php';

class Categorie
{

    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getAll()
    {
        $sql = "SELECT * FROM categories ORDER BY nom ASC";
        return $this->db->query($sql);
    }
    public function getAllParentActive()
    {
        $sql = "SELECT * FROM categories WHERE parent_id IS NULL AND actif='1' ORDER BY nom ASC";
        return $this->db->query($sql);
    }
    // Retourne toutes les catégories sous forme de tableau associatif (actives seulement)
    public function getAllArray()
    {
        $result = $this->db->query("SELECT * FROM categories ORDER BY nom ASC");
        if (!$result)
            return [];
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Retourne toutes les catégories (actives et inactives) pour l'administration
    public function getAllArrayAdmin()
    {
        $result = $this->db->query("SELECT * FROM categories ORDER BY nom ASC");
        if (!$result)
            return [];
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Récupère les enfants directs d'un parent (null = racines)
    public function getChildren($parentId = null)
    {
        if ($parentId === null) {
            $sql = "SELECT * FROM categories WHERE parent_id IS NULL ORDER BY nom ASC";
            $res = $this->db->query($sql);
            return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
        }
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE parent_id = ? ORDER BY nom ASC");
        $stmt->bind_param("i", $parentId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Construit un arbre de catégories (tableau imbriqué) - seulement actives
    public function getTree()
    {
        $all = $this->getAllArray();
        $map = [];
        foreach ($all as $cat) {
            $cat['children'] = [];
            $map[$cat['id']] = $cat;
        }
        $tree = [];
        foreach ($map as $id => $cat) {
            if (isset($cat['parent_id']) && $cat['parent_id']) {
                $parentId = $cat['parent_id'];
                if (isset($map[$parentId])) {
                    $map[$parentId]['children'][] = &$map[$id];
                } else {
                    $tree[] = &$map[$id];
                }
            } else {
                $tree[] = &$map[$id];
            }
        }
        return $tree;
    }

    // Construit un arbre de catégories pour l'administration (inclut inactives)
    public function getTreeAdmin()
    {
        $all = $this->getAllArrayAdmin();
        $map = [];
        foreach ($all as $cat) {
            $cat['children'] = [];
            $map[$cat['id']] = $cat;
        }
        $tree = [];
        foreach ($map as $id => $cat) {
            if (isset($cat['parent_id']) && $cat['parent_id']) {
                $parentId = $cat['parent_id'];
                if (isset($map[$parentId])) {
                    $map[$parentId]['children'][] = &$map[$id];
                } else {
                    $tree[] = &$map[$id];
                }
            } else {
                $tree[] = &$map[$id];
            }
        }
        return $tree;
    }

    public function getFlatTree()
    {
        $tree = $this->getTree();
        $flat = [];
        $this->flattenTree($tree, $flat, 0);
        return $flat;
    }

    private function flattenTree($tree, &$flat, $level)
    {
        foreach ($tree as $node) {
            $node['level'] = $level;
            $flat[] = $node;
            if (!empty($node['children'])) {
                $this->flattenTree($node['children'], $flat, $level + 1);
            }
        }
    }

    // Retourne tous les id descendants (y compris l'id donné)
    public function getDescendantIds($id)
    {
        $all = $this->getAllArray();
        $childrenMap = [];
        foreach ($all as $c) {
            $parent = $c['parent_id'] ?? null;
            $childrenMap[$parent][] = $c['id'];
        }
        $ids = [];
        $stack = [$id];
        while (!empty($stack)) {
            $cur = array_pop($stack);
            $ids[] = $cur;
            if (!empty($childrenMap[$cur])) {
                foreach ($childrenMap[$cur] as $childId)
                    $stack[] = $childId;
            }
        }
        return $ids;
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Récupère une catégorie par son slug
     */
    public function getBySlug($slug)
    {
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE slug = ? AND actif = '1'");
        $stmt->bind_param("s", $slug);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Créer une catégorie
    public function create($data)
    {
        $nom = $data['nom'] ?? '';
        $description = $data['description'] ?? null;
        $parent_id = !empty($data['parent_id']) ? (int) $data['parent_id'] : null;

        // Empêcher parent = self est géré par le contrôleur
        $stmt = $this->db->prepare("INSERT INTO categories (nom, description, parent_id) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $nom, $description, $parent_id);
        if ($stmt->execute()) {
            // Générer et mettre à jour le slug
            $id = $this->db->insert_id;
            $slug = SlugHelper::generateSlug($nom, $id);
            $this->updateSlug($id, $slug);
            return true;
        }
        return false;
    }


    /**
     * Met à jour le slug d'une catégorie
     */
    private function updateSlug($id, $slug)
    {
        $stmt = $this->db->prepare("UPDATE categories SET slug = ? WHERE id = ?");
        $stmt->bind_param("si", $slug, $id);
        return $stmt->execute();
    }
    public function update($id, $data)
    {

        $nom = $data['nom'] ?? '';
        $description = $data['description'] ?? null;
        $parent_id = isset($data['parent_id']) && $data['parent_id'] !== '' ? (int) $data['parent_id'] : null;

        $stmt = $this->db->prepare("UPDATE categories SET nom = ?, description = ?, parent_id = ? WHERE id = ?");
        $stmt->bind_param("ssii", $nom, $description, $parent_id, $id);
        if ($stmt->execute()) {
            // Mettre à jour le slug
            $slug = SlugHelper::generateSlug($nom, $id);
            $this->updateSlug($id, $slug);
            return true;
        }
        return false;
    }

    // Supprimer une catégorie (désactiver si elle contient des produits ou sous-catégories, sinon supprimer)
    public function delete($id)
    {
        // Vérifier si la catégorie contient des produits actifs
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM produits WHERE categorie_id = ? AND actif = '1'");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        // Vérifier si la catégorie a des sous-catégories
        $children = $this->getChildren($id);
        $hasChildren = !empty($children);

        if ($result['count'] > 0 || $hasChildren) {
            // Désactiver la catégorie et ses descendants
            return $this->desactivateRecursive($id);
        } else {
            // Supprimer complètement la catégorie
            return $this->deleteCompletely($id);
        }
    }

    // Désactiver une catégorie et tous ses descendants
    public function desactivateRecursive($id)
    {
        // Désactiver la catégorie elle-même
        $stmt = $this->db->prepare("UPDATE categories SET actif = '0' WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        // Désactiver tous les descendants
        $descendants = $this->getDescendantIds($id);
        if (!empty($descendants)) {
            $placeholders = str_repeat('?,', count($descendants) - 1) . '?';
            $stmt = $this->db->prepare("UPDATE categories SET actif = '0' WHERE id IN ($placeholders)");
            $stmt->bind_param(str_repeat('i', count($descendants)), ...$descendants);
            $stmt->execute();
        }

        return true;
    }

    // Activer une catégorie et tous ses descendants
    public function activateRecursive($id)
    {
        // Activer la catégorie elle-même
        $stmt = $this->db->prepare("UPDATE categories SET actif = '1' WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        // Activer tous les descendants
        $descendants = $this->getDescendantIds($id);
        if (!empty($descendants)) {
            $placeholders = str_repeat('?,', count($descendants) - 1) . '?';
            $stmt = $this->db->prepare("UPDATE categories SET actif = '1' WHERE id IN ($placeholders)");
            $stmt->bind_param(str_repeat('i', count($descendants)), ...$descendants);
            $stmt->execute();
        }

        return true;
    }

    // Supprimer complètement une catégorie (privée)
    private function deleteCompletely($id)
    {
        // Mettre à NULL les parent_id des enfants
        $stmt = $this->db->prepare("UPDATE categories SET parent_id = NULL WHERE parent_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $stmt2 = $this->db->prepare("DELETE FROM categories WHERE id = ?");
        $stmt2->bind_param("i", $id);
        return $stmt2->execute();
    }

    function getCheminCategorie($categoryId, $db)
    {
        $breadcrumbs = [];

        // Tant qu'on a une catégorie valide
        while ($categoryId != null) {
            // On récupère les infos de la catégorie en BDD
            $query = $db->prepare("SELECT id, nom, parent_id FROM categories WHERE id = ?");
            $query->bind_param("i", $categoryId);
            $query->execute();
            $category = $query->get_result()->fetch_assoc();

            if ($category) {
                // On ajoute la catégorie au début du tableau
                array_unshift($breadcrumbs, $category);

                // On passe à l'ID du parent pour la prochaine boucle
                $categoryId = $category['parent_id'];
            } else {
                break;
            }
        }

        return $breadcrumbs; // Retourne un tableau de catégories du parent au plus petit enfant
    }
    public function getCategoriePopulaire($limit = 4)
    {
        $sql = "SELECT c.id, c.nom, SUM(p.vues) as total_vues
            FROM categories c
            INNER JOIN produits p ON p.categorie_id = c.id
            WHERE c.actif='1'
            GROUP BY c.id, c.nom
            ORDER BY total_vues DESC
            LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    // suggestions rapides pour recherche
    public function searchSuggestions($keyword, $limit = 5)
    {
        $like = '%' . $keyword . '%';
        $stmt = $this->db->prepare("SELECT id, nom, slug FROM categories WHERE nom LIKE ? AND actif='1' LIMIT ?");
        $stmt->bind_param('si', $like, $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>