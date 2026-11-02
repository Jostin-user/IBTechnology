<?php

class Notification
{
    private $db;
    public $lastError = '';

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Crée une nouvelle notification
     */
    public function creer($utilisateurId, $commandeId, $titre, $message)
    {
        $stmt = $this->db->prepare("
            INSERT INTO notifications (utilisateur_id, commande_id, titre, message, date_creation)
            VALUES (?, ?, ?, ?, NOW())
        ");

        if (!$stmt) {
            $this->lastError = $this->db->error;
            return false;
        }

        $stmt->bind_param("iiss", $utilisateurId, $commandeId, $titre, $message);
        $result = $stmt->execute();

        if (!$result) {
            $this->lastError = $stmt->error;
            return false;
        }

        return $this->db->insert_id;
    }

    /**
     * Récupère les notifications d'un utilisateur
     */
    public function getNotificationsUtilisateur($utilisateurId, $limit = 50, $offset = 0)
    {
        $stmt = $this->db->prepare("
            SELECT n.*, c.id as commande_id, c.statut as commande_statut
            FROM notifications n
            JOIN commandes c ON n.commande_id = c.id
            WHERE n.utilisateur_id = ?
            ORDER BY n.date_creation DESC
            LIMIT ? OFFSET ?
        ");

        $stmt->bind_param("iii", $utilisateurId, $limit, $offset);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Récupère les notifications non lues d'un utilisateur
     */
    public function getNotificationsNonLuesUtilisateur($utilisateurId)
    {
        $stmt = $this->db->prepare("
            SELECT n.*, c.id as commande_id, c.statut as commande_statut
            FROM notifications n
            JOIN commandes c ON n.commande_id = c.id
            WHERE n.utilisateur_id = ? AND n.lu = 0
            ORDER BY n.date_creation DESC
        ");

        $stmt->bind_param("i", $utilisateurId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Compte les notifications non lues d'un utilisateur
     */
    public function countNotificationsNonLues($utilisateurId)
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count
            FROM notifications
            WHERE utilisateur_id = ? AND lu = 0
        ");

        $stmt->bind_param("i", $utilisateurId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['count'] ?? 0;
    }

    /**
     * Marque une notification comme lue
     */
    public function marquerCommeLue($notificationId, $utilisateurId)
    {
        $stmt = $this->db->prepare("
            UPDATE notifications
            SET lu = 1
            WHERE id = ? AND utilisateur_id = ?
        ");

        $stmt->bind_param("ii", $notificationId, $utilisateurId);
        return $stmt->execute();
    }

    /**
     * Marque toutes les notifications comme lues
     */
    public function marquerToutCommeLu($utilisateurId)
    {
        $stmt = $this->db->prepare("
            UPDATE notifications
            SET lu = 1
            WHERE utilisateur_id = ? AND lu = 0
        ");

        $stmt->bind_param("i", $utilisateurId);
        return $stmt->execute();
    }

    /**
     * Récupère une notification par ID
     */
    public function getNotificationById($notificationId, $utilisateurId)
    {
        $stmt = $this->db->prepare("
            SELECT n.*
            FROM notifications n
            WHERE n.id = ? AND n.utilisateur_id = ?
        ");

        $stmt->bind_param("ii", $notificationId, $utilisateurId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Supprime une notification
     */
    public function supprimer($notificationId, $utilisateurId)
    {
        $stmt = $this->db->prepare("
            DELETE FROM notifications
            WHERE id = ? AND utilisateur_id = ?
        ");

        $stmt->bind_param("ii", $notificationId, $utilisateurId);
        return $stmt->execute();
    }

    /**
     * Supprime toutes les notifications d'un utilisateur
     */
    public function supprimerTout($utilisateurId)
    {
        $stmt = $this->db->prepare("
            DELETE FROM notifications
            WHERE utilisateur_id = ?
        ");

        $stmt->bind_param("i", $utilisateurId);
        return $stmt->execute();
    }

    /**
     * Récupère toutes les notifications (pour admin) avec info utilisateur
     */
    public function getAllForAdmin($limit = 100, $offset = 0)
    {
        $sql = "SELECT n.*, u.nom_complet AS utilisateur_nom, u.email AS utilisateur_email, c.id as commande_id, c.statut as commande_statut
                FROM notifications n
                LEFT JOIN users u ON n.utilisateur_id = u.id
                LEFT JOIN commandes c ON n.commande_id = c.id
                ORDER BY n.date_creation DESC
                LIMIT ? OFFSET ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Compte toutes les notifications (pour admin)
     */
    public function countAll()
    {
        $result = $this->db->query("SELECT COUNT(*) AS total FROM notifications");
        $row = $result->fetch_assoc();
        return $row['total'] ?? 0;
    }
}