<?php
require_once __DIR__ . '/../config/database.php';


class Utilisateur
{

    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnexion();
    }

    public function trouverParEmail($email)
    {
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getById($id)
    {
        $sql = "SELECT * FROM users WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function creer($nom, $email, $pass)
    {
        $sql = "INSERT INTO users (nom_complet, email, mot_de_passe, role) VALUES (?, ?, ?, 'client')";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("sss", $nom, $email, $pass);
        return $stmt->execute();
    }

    public function updateProfile($id, $nom, $telephone, $adresse, $ville)
    {
        $sql = "UPDATE users SET nom_complet = ?, telephone = ?, adresse = ?, ville = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssssi", $nom, $telephone, $adresse, $ville, $id);
        return $stmt->execute();
    }

    public function updateSettings($id, $theme = 'dark', $notif_email = 1, $notif_push = 1)
    {
        $sql = "UPDATE users SET theme = ?, notif_email = ?, notif_push = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("siii", $theme, $notif_email, $notif_push, $id);
        return $stmt->execute();
    }

    public function changePassword($id, $hashedPassword)
    {
        $sql = "UPDATE users SET mot_de_passe = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("si", $hashedPassword, $id);
        return $stmt->execute();
    }

    public function updatePassword($email, $newPassword)
    {
        $sql = "UPDATE users SET mot_de_passe = ? WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ss", $newPassword, $email);
        return $stmt->execute();
    }
}
?>