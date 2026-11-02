<?php

class StockNotificationService
{
    private $db;
    private $stock;

    public function __construct($db)
    {
        $this->db = $db;
        require_once __DIR__ . '/../models/Stock.php';
        $this->stock = new Stock($db);
    }

    /**
     * Envoyer une notification d'alerte stock
     */
    public function notifierAlertesStock($admin_id = null)
    {
        $critiques = $this->stock->getProduitStockCritique();
        $ruptures = $this->stock->getProduitEnRupture();

        $notifications = [];

        // Ruptures de stock
        foreach ($ruptures as $produit) {
            $notification = $this->creerNotificationRupture($produit, $admin_id);
            if ($notification) {
                $notifications[] = $notification;
            }
        }

        // Stock critique
        foreach ($critiques as $produit) {
            if ($produit['stock'] > 0) { // Éviter les doublons avec ruptures
                $notification = $this->creerNotificationCritique($produit, $admin_id);
                if ($notification) {
                    $notifications[] = $notification;
                }
            }
        }

        return $notifications;
    }

    /**
     * Créer une notification de rupture
     */
    private function creerNotificationRupture($produit, $admin_id = null)
    {
        $titre = "⚠️ Rupture de stock: " . $produit['nom'];
        $message = "Le produit \"" . $produit['nom'] . "\" n'a plus de stock disponible.";

        return $this->sauvegarderNotification($titre, $message, $produit['id'], $admin_id);
    }

    /**
     * Créer une notification de stock critique
     */
    private function creerNotificationCritique($produit, $admin_id = null)
    {
        $titre = "⚡ Stock critique: " . $produit['nom'];
        $message = "Le produit \"" . $produit['nom'] . "\" a atteint le seuil critique. Stock: " . $produit['stock'] . " unités.";

        return $this->sauvegarderNotification($titre, $message, $produit['id'], $admin_id);
    }

    /**
     * Sauvegarder une notification
     */
    private function sauvegarderNotification($titre, $message, $produit_id, $admin_id = null)
    {
        // Récupérer les admins si admin_id non spécifié
        if (!$admin_id) {
            $stmt = $this->db->query("SELECT id FROM users WHERE role IN ('admin', 'super_admin')");
            $admins = $stmt->fetch_all(MYSQLI_ASSOC);

            foreach ($admins as $admin) {
                $this->enregistrerNotification($titre, $message, $admin['id']);
            }
        } else {
            $this->enregistrerNotification($titre, $message, $admin_id);
        }

        return [
            'titre' => $titre,
            'message' => $message,
            'produit_id' => $produit_id
        ];
    }

    /**
     * Enregistrer une notification en base
     */
    private function enregistrerNotification($titre, $message, $utilisateur_id)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO notifications (utilisateur_id, titre, message, lu)
             VALUES (?, ?, ?, 0)"
        );
        $stmt->bind_param("iss", $utilisateur_id, $titre, $message);
        return $stmt->execute();
    }

    /**
     * Obtenir les alertes non lues pour un utilisateur
     */
    public function getAlertes($utilisateur_id)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM notifications 
             WHERE utilisateur_id = ? AND lu = 0
             ORDER BY date_creation DESC"
        );
        $stmt->bind_param("i", $utilisateur_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Marquer une notification comme lue
     */
    public function marquerCommeLue($notification_id)
    {
        $stmt = $this->db->prepare("UPDATE notifications SET lu = 1 WHERE id = ?");
        $stmt->bind_param("i", $notification_id);
        return $stmt->execute();
    }

    /**
     * Vérifier les alertes automatiques
     */
    public function verifierAlertes()
    {
        // Vérifier chaque produit
        $stmt = $this->db->query("SELECT id FROM produits WHERE actif = 1");
        $produits = $stmt->fetch_all(MYSQLI_ASSOC);

        foreach ($produits as $p) {
            $this->stock->verifierAlertesStock($p['id']);
        }

        // Notifier les admins
        return $this->notifierAlertesStock();
    }
}
