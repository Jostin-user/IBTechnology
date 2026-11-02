<?php
class Client
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getAll(){
        $sql = "SELECT id, nom_complet, email, date_creation, telephone, adresse, statut,
                       NULL AS dernier_connexion
                FROM users
                WHERE role = 'client'
                ORDER BY nom_complet ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function getById($id){
        $sql = "SELECT id, nom_complet, email, telephone, adresse, date_creation, statut,
                       NULL AS dernier_connexion
                FROM users
                WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public function toggleStatus($id) {
        $sql = "UPDATE users SET statut = CASE WHEN statut = 'active' THEN 'inactive' ELSE 'active' END WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function delete($id) {
        $sql = "DELETE FROM users WHERE id = ? AND role = 'client'";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function countNouveauxCeMois() {
        $sql = "SELECT COUNT(*) as count FROM users
                WHERE role = 'client'
                AND MONTH(date_creation) = MONTH(CURRENT_DATE())
                AND YEAR(date_creation) = YEAR(CURRENT_DATE())";
        $result = $this->db->query($sql);
        $row = $result->fetch_assoc();
        return $row['count'];
    }

    public function search($query) {
        $sql = "SELECT id, nom_complet, email, date_creation, statut,
                       NULL AS dernier_connexion
                FROM users
                WHERE role = 'client'
                AND (nom_complet LIKE ? OR email LIKE ?)
                ORDER BY nom_complet ASC";
        $stmt = $this->db->prepare($sql);
        $searchTerm = "%$query%";
        $stmt->bind_param("ss", $searchTerm, $searchTerm);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function update($id, $data) {
        $sql = "UPDATE users SET nom_complet = ?, email = ?, telephone = ?, adresse = ?, statut = ? WHERE id = ? AND role = 'client'";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param(
            "sssssi",
            $data['nom_complet'],
            $data['email'],
            $data['telephone'],
            $data['adresse'],
            $data['statut'],
            $id
        );
        return $stmt->execute();
    }
}
?>