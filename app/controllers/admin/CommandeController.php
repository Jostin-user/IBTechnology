<?php
require_once __DIR__.'/../../middlewares/AdminMiddleware.php';
require_once __DIR__.'/../../models/Commande.php';
require_once __DIR__.'/../../models/Client.php';
require_once __DIR__.'/../../helpers/NotificationService.php';

class CommandeController
{
    private $db;
    private $commandeModel;
    private $clientModel;

    public function __construct()
    {
        require_once __DIR__.'/../../config/database.php';
        $this->db = (new Database())->getConnexion();
        $this->commandeModel = new Commande($this->db);
        $this->clientModel = new Client($this->db);
    }

    public function adminIndex()
    {
        AdminMiddleware::verifierAdmin();

        // Pagination
        $parPage = 10;
        $pageCourante = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
        $offset = ($pageCourante - 1) * $parPage;

        // Filtres
        $filtres = [
            'q' => $_GET['q'] ?? null,
            'statut' => $_GET['statut'] ?? null,
            'date_debut' => $_GET['date_debut'] ?? null,
            'date_fin' => $_GET['date_fin'] ?? null,
            'client' => $_GET['client'] ?? null
        ];

        // Récupérer toutes les commandes avec filtres et pagination
        $commandes = $this->commandeModel->getAllCommandesAdmin($filtres, $parPage, $offset);
        $totalCommandes = $this->commandeModel->countCommandesAdmin($filtres);
        $totalPages = ceil($totalCommandes / $parPage);

        // Pour chaque commande, ajouter les infos client et produits
        foreach ($commandes as &$commande) {
            $commande['client'] = $this->clientModel->getById($commande['utilisateur_id']);
            $commande['produits_count'] = count($this->commandeModel->getProduitsCommande($commande['id']));
            $commande['status_class'] = $this->getStatusClass($commande['statut']);
            $commande['status_label'] = $this->getStatusLabel($commande['statut']);
        }

        require_once __DIR__ . '/../../views/admin/commandes/index.php';
    }

    public function adminDetails($id)
    {
        AdminMiddleware::verifierAdmin();

        $commande = $this->commandeModel->getCommandeById($id);

        if (!$commande) {
            header('Location: '.BASE_URL.'admin/commandes');
            exit;
        }

        $commande['produits'] = $this->commandeModel->getProduitsCommande($id);
        $client= $this->clientModel->getById($commande['utilisateur_id']);
        $commande['status_class'] = $this->getStatusClass($commande['statut']);
        $commande['status_label'] = $this->getStatusLabel($commande['statut']);
        $commande['tracking_steps'] = $this->getTrackingSteps($commande['statut']);

        require_once __DIR__ . '/../../views/admin/commandes/details.php';
    }

    public function updateStatus($id)
    {
        AdminMiddleware::verifierAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: '.BASE_URL.'admin/commandes');
            exit;
        }

        $nouveauStatut = $_POST['statut'] ?? '';
        $commentaire = $_POST['commentaire'] ?? '';

        $statutsValides = ['en_attente', 'validee', 'en_preparation', 'expediee', 'livree', 'annulee'];

        if (!in_array($nouveauStatut, $statutsValides)) {
            $_SESSION['error'] = 'Statut invalidehv.'.$nouveauStatut;
            header('Location: '.BASE_URL.'admin/commandes/details/' . $id);
            exit;
        }

        // Vérifier que la commande existe
        $commande = $this->commandeModel->getCommandeById($id);
        if (!$commande) {
            $_SESSION['error'] = 'Commande introuvable.';
            header('Location: '.BASE_URL.'admin/commandes');
            exit;
        }

        // Récupérer l'ancien statut
        $ancienStatut = $commande['statut'];

        // Mettre à jour le statut
        if ($this->commandeModel->updateStatut($id, $nouveauStatut)) {
            // Si la commande passe au statut "livree", créer une vente
            if ($nouveauStatut === 'livree' && $ancienStatut !== 'livree') {
                require_once __DIR__ . '/../../helpers/CommandeVenteHelper.php';
                $venteHelper = new CommandeVenteHelper($this->db);
                $venteHelper->creerVenteDepuisCommande($id, $commande['utilisateur_id']);
            }
            
            // Créer une notification pour le client
            $notificationService = new NotificationService($this->db);
            $notificationService->notifierChangementStatut(
                $id,
                $ancienStatut,
                $nouveauStatut,
                $commande['utilisateur_id']
            );
            
            $_SESSION['success'] = 'Statut de la commande mis à jour avec succès. Le client a été notifié.';
        } else {
            $_SESSION['error'] = 'Erreur lors de la mise à jour du statut.';
        }

        header('Location: '.BASE_URL.'admin/commandes/details/' . $id);
        exit;
    }

    public function annuler($id)
    {
        AdminMiddleware::verifierAdmin();

        $commande = $this->commandeModel->getCommandeById($id);
        if (!$commande) {
            header('Location: '.BASE_URL.'admin/commandes');
            exit;
        }

        // Récupérer l'ancien statut
        $ancienStatut = $commande['statut'];

        // Annuler la commande
        if ($this->commandeModel->updateStatut($id, 'annulee')) {
            // Créer une notification pour le client
            $notificationService = new NotificationService($this->db);
            $notificationService->notifierChangementStatut(
                $id,
                $ancienStatut,
                'annulee',
                $commande['utilisateur_id']
            );
            
            $_SESSION['success'] = 'Commande annulée avec succès. Le client a été notifié.';
        } else {
            $_SESSION['error'] = 'Erreur lors de l\'annulation.';
        }

        header('Location: '.BASE_URL.'admin/commandes');
        exit;
    }

    public function telechargerFacture($id)
    {
        AdminMiddleware::verifierAdmin();

        $commande = $this->commandeModel->getCommandeById($id);

        if (!$commande) {
            header('Location: '.BASE_URL.'admin/commandes');
            exit;
        }

        $commande['produits'] = $this->commandeModel->getProduitsCommande($id);
        $commande['client'] = $this->clientModel->getById($commande['utilisateur_id']);

        // Générer un PDF simple (pour l'instant, on affiche en HTML)
        header('Content-Type: text/html');
        header('Content-Disposition: attachment; filename="facture-commande-' . $commande['id'] . '.html"');

        // Template de facture simple
        echo '<!DOCTYPE html>
        <html>
        <head>
            <title>Facture - Commande #' . $commande['id'] . '</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; }
                .details { margin: 20px 0; }
                table { width: 100%; border-collapse: collapse; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                .total { font-weight: bold; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>IB Technology</h1>
                <p>Facture de commande</p>
            </div>
            <div class="details">
                <p><strong>Numéro de commande:</strong> #' . str_pad($commande['id'], 4, '0', STR_PAD_LEFT) . '</p>
                <p><strong>Date:</strong> ' . date('d/m/Y', strtotime($commande['date_commande'])) . '</p>
                <p><strong>Client:</strong> ' . htmlspecialchars($commande['client']['nom_complet'] ?? 'N/A') . '</p>
                <p><strong>Montant total:</strong> ' . number_format($commande['montant_total'], 0, ',', ' ') . ' FCFA</p>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Quantité</th>
                        <th>Prix unitaire</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>';

        foreach ($commande['produits'] as $produit) {
            echo '<tr>
                <td>' . htmlspecialchars($produit['nom']) . '</td>
                <td>' . $produit['quantite'] . '</td>
                <td>' . number_format($produit['prix'], 0, ',', ' ') . ' FCFA</td>
                <td>' . number_format($produit['prix'] * $produit['quantite'], 0, ',', ' ') . ' FCFA</td>
            </tr>';
        }

        echo '</tbody>
            </table>
        </body>
        </html>';
        exit;
    }

    // Méthodes helper pour les statuts
    private function getStatusClass($statut)
    {
        $classes = [
            'en_attente' => 'pending',
            'validee' => 'confirmed',
            'en_preparation' => 'preparing',
            'expediee' => 'shipped',
            'livree' => 'delivered',
            'annulee' => 'cancelled'
        ];
        return $classes[$statut] ?? 'unknown';
    }

    private function getStatusLabel($statut)
    {
        $labels = [
            'en_attente' => 'En attente',
            'validee' => 'Validée',
            'en_preparation' => 'En préparation',
            'expediee' => 'Expédiée',
            'livree' => 'Livrée',
            'annulee' => 'Annulée'
        ];
        return $labels[$statut] ?? 'Inconnu';
    }

    private function getTrackingSteps($statut)
    {
        $steps = [
            ['label' => 'Commande reçue', 'status' => in_array($statut, ['en_attente', 'validee', 'en_preparation', 'expediee', 'livree']) ? 'completed' : 'pending'],
            ['label' => 'Validation', 'status' => in_array($statut, ['validee', 'en_preparation', 'expediee', 'livree']) ? 'completed' : ($statut === 'en_attente' ? 'active' : 'pending')],
            ['label' => 'Préparation', 'status' => in_array($statut, ['en_preparation', 'expediee', 'livree']) ? 'completed' : ($statut === 'validee' ? 'active' : 'pending')],
            ['label' => 'Expédition', 'status' => in_array($statut, ['expediee', 'livree']) ? 'completed' : ($statut === 'en_preparation' ? 'active' : 'pending')],
            ['label' => 'Livraison', 'status' => $statut === 'livree' ? 'completed' : ($statut === 'expediee' ? 'active' : 'pending')]
        ];
        return $steps;
    }
}

?>