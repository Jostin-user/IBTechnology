<?php

class NotificationService
{
    private $notification;
    private $db;

    public function __construct($db)
    {
        require_once __DIR__ . '/../models/Notification.php';
        $this->notification = new Notification($db);
        $this->db = $db;
    }

    /**
     * Envoie une notification lors du changement de statut de commande
     */
    public function notifierChangementStatut($commandeId, $ancienStatut, $nouveauStatut, $utilisateurId)
    {
        $titre = $this->getTitreNotification($nouveauStatut);
        $message = $this->getMessageNotification($ancienStatut, $nouveauStatut, $commandeId);

        return $this->notification->creer($utilisateurId, $commandeId, $titre, $message);
    }

    /**
     * Retourne le titre selon le statut
     */
    private function getTitreNotification($statut)
    {
        $titres = [
            'en_attente' => '⏳ Commande en attente',
            'validee' => '✓ Commande validée',
            'en_preparation' => '📦 Commande en préparation',
            'en_livraison' => '🚚 Commande en livraison',
            'expediee' => '🚀 Colis expédié',
            'livree' => '✅ Commande livrée',
            'annulee' => '❌ Commande annulée'
        ];

        return $titres[$statut] ?? 'Mise à jour de commande';
    }

    /**
     * Retourne le message selon le statut
     */
    private function getMessageNotification($ancienStatut, $nouveauStatut, $commandeId)
    {
        $messages = [
            'en_attente' => 'Votre commande a été reçue et est en attente de validation. Nous traiterons votre commande dans les plus brefs délais.',
            'validee' => 'Votre commande a été validée avec succès ! Nous commençons la préparation de votre colis.',
            'en_preparation' => 'Votre commande est actuellement en préparation dans notre entrepôt.',
            'en_livraison' => 'Votre commande a été remise au transporteur et est en cours de livraison.',
            'expediee' => 'Votre colis a été expédié et est en route vers vous !',
            'livree' => 'Votre commande a été livrée avec succès. Merci de votre confiance !',
            'annulee' => 'Votre commande a été annulée. Si vous pensez que c\'est une erreur, veuillez nous contacter.'
        ];

        return $messages[$nouveauStatut] ?? 'Votre commande a été mise à jour.';
    }

    /**
     * Récupère les notifications non lues
     */
    public function getNotificationsNonLues($utilisateurId)
    {
        return $this->notification->getNotificationsNonLuesUtilisateur($utilisateurId);
    }

    /**
     * Compte les notifications non lues
     */
    public function countNonLues($utilisateurId)
    {
        return $this->notification->countNotificationsNonLues($utilisateurId);
    }

    /**
     * Récupère toutes les notifications
     */
    public function getAllNotifications($utilisateurId, $limit = 50, $offset = 0)
    {
        return $this->notification->getNotificationsUtilisateur($utilisateurId, $limit, $offset);
    }

    /**
     * Marque une notification comme lue
     */
    public function marquerCommeLue($notificationId, $utilisateurId)
    {
        return $this->notification->marquerCommeLue($notificationId, $utilisateurId);
    }

    /**
     * Marque toutes les notifications comme lues
     */
    public function marquerToutCommeLu($utilisateurId)
    {
        return $this->notification->marquerToutCommeLu($utilisateurId);
    }

    /**
     * Supprime une notification
     */
    public function supprimer($notificationId, $utilisateurId)
    {
        return $this->notification->supprimer($notificationId, $utilisateurId);
    }

    /**
     * Récupère une notification
     */
    public function getNotification($notificationId, $utilisateurId)
    {
        return $this->notification->getNotificationById($notificationId, $utilisateurId);
    }
}
