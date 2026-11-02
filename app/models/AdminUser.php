<?php
class AdminUser
{
    private $db;

    public function __construct($db = null)
    {
        if ($db === null) {
            require_once __DIR__ . '/../config/database.php';
            $this->db = (new Database())->getConnexion();
        } else {
            $this->db = $db;
        }
    }

    public function getAll()
    {
        $sql = "SELECT id, nom_complet, email, date_creation, statut, role FROM users WHERE role IN ('admin','superadmin') ORDER BY date_creation DESC";
        $res = $this->db->query($sql);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getById($id)
    {
        $sql = "SELECT * FROM users WHERE id = ? AND role IN ('admin','superadmin')";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function create($data)
    {
        $sql = "INSERT INTO users (nom_complet, email, mot_de_passe, role, statut, date_creation) VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $this->db->prepare($sql);
        $hashed = password_hash($data['mot_de_passe'], PASSWORD_DEFAULT);
        $role = $data['role'] ?? 'admin';
        $statut = isset($data['statut']) && $data['statut'] === 'active' ? 'active' : 'inactive';
        $stmt->bind_param("sssss", $data['nom_complet'], $data['email'], $hashed, $role, $statut);
        return $stmt->execute();
    }

    public function update($id, $data)
    {
        // update nom, email, telephone, adresse, role, statut. password optional
        if (!empty($data['mot_de_passe'])) {
            $sql = "UPDATE users SET nom_complet = ?, email = ?, telephone = ?, adresse = ?, role = ?, statut = ?, mot_de_passe = ? WHERE id = ? AND role IN ('admin','superadmin')";
            $stmt = $this->db->prepare($sql);
            $hashed = password_hash($data['mot_de_passe'], PASSWORD_DEFAULT);
            $stmt->bind_param("sssssssi", $data['nom_complet'], $data['email'], $data['telephone'], $data['adresse'], $data['role'], $data['statut'], $hashed, $id);
        } else {
            $sql = "UPDATE users SET nom_complet = ?, email = ?, telephone = ?, adresse = ?, role = ?, statut = ? WHERE id = ? AND role IN ('admin','superadmin')";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("ssssssi", $data['nom_complet'], $data['email'], $data['telephone'], $data['adresse'], $data['role'], $data['statut'], $id);
        }
        return $stmt->execute();
    }

    public function delete($id)
    {
        $sql = "DELETE FROM users WHERE id = ? AND role IN ('admin','superadmin')";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function toggleStatus($id)
    {
        // toggle between 'active' and 'inactive'
        $user = $this->getById($id);
        if (!$user) return false;
        $new = ($user['statut'] === 'active' || $user['statut'] === '1') ? 'inactive' : 'active';
        $sql = "UPDATE users SET statut = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("si", $new, $id);
        return $stmt->execute();
    }

    public function search($q, $limit = 5)
    {
        $like = '%' . $q . '%';
        $stmt = $this->db->prepare("SELECT id, nom_complet, email, role, statut FROM users WHERE (nom_complet LIKE ? OR email LIKE ?) AND role IN ('admin','superadmin') LIMIT ?");
        $stmt->bind_param('ssi', $like, $like, $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>