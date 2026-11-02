<?php

/**
 * Configuration du Système de Notifications
 * Personnalisez les messages et comportements ici
 */

// Définition des statuts et messages
const NOTIFICATION_CONFIG = [
    'statuts' => [
        'en_attente' => [
            'label' => 'En attente',
            'emoji' => '⏳',
            'titre' => '⏳ Commande en attente',
            'message' => 'Votre commande a été reçue et est en attente de validation. Nous traiterons votre commande dans les plus brefs délais.',
            'couleur' => '#FFC107',
            'next_statuts' => ['validee', 'annulee']
        ],
        'validee' => [
            'label' => 'Validée',
            'emoji' => '✓',
            'titre' => '✓ Commande validée',
            'message' => 'Votre commande a été validée avec succès ! Nous commençons la préparation de votre colis.',
            'couleur' => '#28A745',
            'next_statuts' => ['en_preparation', 'annulee']
        ],
        'en_preparation' => [
            'label' => 'En préparation',
            'emoji' => '📦',
            'titre' => '📦 Commande en préparation',
            'message' => 'Votre commande est actuellement en préparation dans notre entrepôt.',
            'couleur' => '#FFC107',
            'next_statuts' => ['expediee', 'annulee']
        ],
        'en_livraison' => [
            'label' => 'En livraison',
            'emoji' => '🚚',
            'titre' => '🚚 Commande en livraison',
            'message' => 'Votre commande a été remise au transporteur et est en cours de livraison.',
            'couleur' => '#17A2B8',
            'next_statuts' => ['livree']
        ],
        'expediee' => [
            'label' => 'Expédiée',
            'emoji' => '🚀',
            'titre' => '🚀 Colis expédié',
            'message' => 'Votre colis a été expédié et est en route vers vous !',
            'couleur' => '#17A2B8',
            'next_statuts' => ['livree']
        ],
        'livree' => [
            'label' => 'Livrée',
            'emoji' => '✅',
            'titre' => '✅ Commande livrée',
            'message' => 'Votre commande a été livrée avec succès. Merci de votre confiance !',
            'couleur' => '#28A745',
            'next_statuts' => []
        ],
        'annulee' => [
            'label' => 'Annulée',
            'emoji' => '❌',
            'titre' => '❌ Commande annulée',
            'message' => 'Votre commande a été annulée. Si vous pensez que c\'est une erreur, veuillez nous contacter.',
            'couleur' => '#DC3545',
            'next_statuts' => []
        ]
    ],

    // Paramètres globaux
    'pagination' => [
        'par_page' => 20,
        'max_recent' => 5  // Nombre de notifications dans la cloche
    ],

    // Mise à jour en temps réel
    'update_interval' => 30000, // 30 secondes en millisecondes

    // Emails (pour implémentation future)
    'email' => [
        'enabled' => false,
        'sender' => 'notifications@ibtechnologie.com',
        'send_on_status_change' => true
    ],

    // SMS (pour implémentation future)
    'sms' => [
        'enabled' => false,
        'send_on_status_change' => false
    ]
];

/**
 * Fonctions utilitaires pour accéder à la configuration
 */

function getStatusConfig($statut)
{
    if (isset(NOTIFICATION_CONFIG['statuts'][$statut])) {
        return NOTIFICATION_CONFIG['statuts'][$statut];
    }
    return null;
}

function getStatusLabel($statut)
{
    $config = getStatusConfig($statut);
    return $config['label'] ?? 'Inconnu';
}

function getStatusEmoji($statut)
{
    $config = getStatusConfig($statut);
    return $config['emoji'] ?? '📢';
}

function getStatusColor($statut)
{
    $config = getStatusConfig($statut);
    return $config['couleur'] ?? '#6C757D';
}

function getStatusTitle($statut)
{
    $config = getStatusConfig($statut);
    return $config['titre'] ?? 'Mise à jour de commande';
}

function getStatusMessage($statut)
{
    $config = getStatusConfig($statut);
    return $config['message'] ?? 'Votre commande a été mise à jour.';
}

function getNextStatuts($statut)
{
    $config = getStatusConfig($statut);
    return $config['next_statuts'] ?? [];
}

/**
 * Exemple d'utilisation dans NotificationService:
 * 
 * $titre = getStatusTitle($nouveauStatut);
 * $message = getStatusMessage($nouveauStatut);
 * $couleur = getStatusColor($nouveauStatut);
 */
