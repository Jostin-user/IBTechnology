<?php
require_once __DIR__ . '/../../../middlewares/AdminMiddleware.php';
require_once __DIR__ . '/../../../models/utilisateur.php';
require_once __DIR__ . '/../../../config/database.php';

AdminMiddleware::verifierAdmin();

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = (int) $_GET['id'];
$db = (new Database())->getConnexion();
$userModel = new utilisateur();

// // Simuler la récupération du client (à remplacer par une vraie requête)
$client = [
    'id' => $client['id'],
    'nom_complet' => $client['nom_complet'],
    'email' => $client['email'],
    'date_inscription' => $client['date_creation'],
    'statut' => $client['statut']=='1' ?'actif':'inactif',
    'telephone' => isset($client['telephone'])?$client['telephone']:'N/A',
    'adresse' => isset($client['adresse'])?$client['adresse']:'N/A',
    'dernier_connexion' => $client['dernier_connexion'],
    'commandes_count' => 5,
    'total_depenses' => 150000
];

// Simuler les commandes du client
// $commandes = [
//     [
//         'id' => 1,
//         'date' => '2024-01-25',
//         'montant' => 35000,
//         'statut' => 'Livrée',
//         'produits' => ['iPhone 15', 'AirPods']
//     ],
//     [
//         'id' => 2,
//         'date' => '2024-01-20',
//         'montant' => 25000,
//         'statut' => 'En cours',
//         'produits' => ['MacBook Pro']
//     ]
// ];

function getInitials($name)
{
    $parts = explode(' ', $name);
    return strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails Client - <?php echo htmlspecialchars($client['nom_complet']); ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin/admin.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin/detail-client.css">
</head>

<body>
    <?php require_once __DIR__ . '/../layouts/header_admin.php'; ?>

    <div class="client-details-container">
        <a href="<?=BASE_URL?>admin/clients" class="back-btn">
            <i class="fas fa-arrow-left"></i> Retour à la liste
        </a>

        <div class="client-profile">
            <div class="profile-header">
                <div class="profile-avatar">
                    <?php echo getInitials($client['nom_complet']); ?>
                </div>
                <div class="profile-info">
                    <h1><?php echo htmlspecialchars($client['nom_complet']); ?></h1>
                    <div class="profile-meta">
                        Client depuis le <?php echo date('d/m/Y', strtotime($client['date_inscription'])); ?> •
                        ID: <?php echo $client['id']; ?>
                    </div>
                </div>
            </div>

            <div class="profile-stats">
                <div class="stat-item">
                    <span class="stat-value"><?php echo $client['commandes_count']; ?></span>
                    <span class="stat-label">Commandes</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value"><?php echo number_format($client['total_depenses'], 0, ',', ' '); ?>
                        FCFA</span>
                    <span class="stat-label">Total dépensé</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value"><?php echo $client['statut'] == '1' ? 'Actif' : 'Inactif'; ?></span>
                    <span class="stat-label">Statut</span>
                </div>
                <div class="stat-item">
                    <span
                        class="stat-value"><?php echo $client['dernier_connexion'] ? date('d/m/Y', strtotime($client['dernier_connexion'])) : 'Jamais'; ?></span>
                    <span class="stat-label">Dernière connexion</span>
                </div>
            </div>

            <div class="action-buttons">
                <a href="#" class="btn btn-primary" onclick="alert('Fonctionnalité à implémenter')">
                    <i class="fas fa-edit"></i> Modifier
                </a>
                <a href="#" class="btn btn-danger"
                    onclick="return confirm('Êtes-vous sûr de vouloir désactiver ce client ?')">
                    <i class="fas fa-user-times"></i> Désactiver
                </a>
            </div>
        </div>

        <div class="info-section">
            <h2><i class="fas fa-info-circle"></i> Informations personnelles</h2>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Nom complet</span>
                    <span class="info-value"><?php echo htmlspecialchars($client['nom_complet']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Email</span>
                    <span class="info-value"><?php echo htmlspecialchars($client['email']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Téléphone</span>
                    <span class="info-value"><?php echo htmlspecialchars($client['telephone']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Adresse</span>
                    <span class="info-value"><?php echo htmlspecialchars($client['adresse']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Date d'inscription</span>
                    <span
                        class="info-value"><?php echo date('d/m/Y H:i', strtotime($client['date_inscription'])); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Dernière connexion</span>
                    <span
                        class="info-value"><?php echo $client['dernier_connexion'] ? date('d/m/Y H:i', strtotime($client['dernier_connexion'])) : 'Jamais connecté'; ?></span>
                </div>
            </div>
        </div>

        <div class="orders-section">
            <h2><i class="fas fa-shopping-cart"></i> Historique des commandes (<?php echo count($commandes); ?>)</h2>

            <?php if (!empty($commandes)): ?>
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>N° Commande</th>
                            <th>Date</th>
                            <th>Produits</th>
                            <th>Montant</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($commandes as $commande): ?>
                            <tr>
                                <td>#<?php echo str_pad($commande['id'], 4, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($commande['date'])); ?></td>
                                <td><?php echo htmlspecialchars(implode(', ', $commande['produits'])); ?></td>
                                <td><?php echo number_format($commande['montant'], 0, ',', ' '); ?> FCFA</td>
                                <td>
                                    <span class="order-status status-<?php echo strtolower($commande['statut']); ?>">
                                        <?php echo $commande['statut']; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="#" class="action-btn view" title="Voir détails"
                                        onclick="alert('Fonctionnalité à implémenter')">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div style="text-align: center; padding: 40px; color: #9ca3af;">
                    <i class="fas fa-shopping-cart" style="font-size: 3rem; margin-bottom: 20px;"></i>
                    <p>Aucune commande trouvée pour ce client.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>