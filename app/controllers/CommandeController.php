<?php
require_once __DIR__.'/../middlewares/AuthMiddleware.php';
require_once __DIR__.'/../models/Commande.php';
require_once __DIR__.'/../models/Avis.php';
require_once  __DIR__."/../models/Categorie.php" ;

class CommandeController
{
    private $db;
    private $commandeModel;
    private $avisModel;

    public function __construct()
    {
        require_once __DIR__.'/../config/database.php';
        $this->db = (new Database())->getConnexion();
        $this->commandeModel = new Commande($this->db);
        $this->avisModel = new Avis($this->db);
    }

    public function index()
    {
        AuthMiddleware::verifierConnexion();

        // Récupérer l'utilisateur connecté
        $utilisateurId = $_SESSION['user_id']??0;

        // Récupérer les commandes de l'utilisateur
        $commandes = $this->commandeModel->getCommandesByUtilisateur($utilisateurId);

        // Pour chaque commande, récupérer les produits
        foreach ($commandes as &$commande) {
            $commande['produits'] = $this->commandeModel->getProduitsCommande($commande['id']);
            $commande['status_class'] = $this->getStatusClass($commande['statut']);
            $commande['status_label'] = $this->getStatusLabel($commande['statut']);
            $commande['tracking_steps'] = $this->getTrackingSteps($commande['statut']);
        }

        require_once '../app/views/commandes/index.php';
    }

    public function details($id)
    {
        AuthMiddleware::verifierConnexion();

        $utilisateurId = $_SESSION['user_id'];
        $commande = $this->commandeModel->getCommandeById($id, $utilisateurId);

        if (!$commande) {
            header('Location: '.BASE_URL.'commandes');
            exit;
        }

        $commande['produits'] = $this->commandeModel->getProduitsCommande($id);
        $commande['status_class'] = $this->getStatusClass($commande['statut']);
        $commande['status_label'] = $this->getStatusLabel($commande['statut']);
        $commande['tracking_steps'] = $this->getTrackingSteps($commande['statut']);

        require_once '../app/views/commandes/details.php';
    }

    public function telechargerFacture($id)
    {
        AuthMiddleware::verifierConnexion();

        $utilisateurId = $_SESSION['user_id'];
        $commande = $this->commandeModel->getCommandeById((int)$id?? 0, $utilisateurId);

        if (!$commande) {
            header('Location: '.BASE_URL.'commandes');
            exit;
        }

        $commande['produits'] = $this->commandeModel->getProduitsCommande($id);

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
                <p><strong>Client:</strong> ' . htmlspecialchars($_SESSION['user_name']) . '</p>
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

    // Traitement du formulaire de validation (création de commande)
    public function store()
    {
        AuthMiddleware::verifierConnexion();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: '.BASE_URL.'panier');
            exit;
        }

        $utilisateurId = $_SESSION['user_id'];

        // Récupérer et valider les champs nécessaires
        $nom = trim($_POST['nom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telephone = trim($_POST['telephone'] ?? '');
        $adresse = trim($_POST['adresse'] ?? '');
        $ville = trim($_POST['ville'] ?? '');
        $quartier= trim($_POST['quartier'] ?? '');
        $note_livraison = trim($_POST['note_livraison'] ?? '');
        $shipping_cost = floatval($_POST['shipping_cost'] ?? 0);
        $subtotal = floatval($_POST['subtotal'] ?? 0);

        $panier = $_SESSION['panier'] ?? [];
        if (empty($panier)) {
            $_SESSION['error'] = 'Votre panier est vide.';
            header('Location: '.BASE_URL.'panier');
            exit;
        }
        $adresseArr = [
            'adresse' => $adresse,
            'ville' => $ville,
            'quartier' => $quartier,
            'telephone' => $telephone,
            'note_livraison' => $note_livraison
        ];

        $commandeId = $this->commandeModel->creerDepuisPanierSession($utilisateurId, $panier, $shipping_cost, $subtotal, $adresseArr);

        if ($commandeId) {
            // Vider le panier
            unset($_SESSION['panier']);
            $_SESSION['success'] = 'Commande créée avec succès.';
            header('Location: '.BASE_URL.'commandes/details/' . $commandeId);
            exit;
        } else {
            // si le modèle a défini un message d'erreur, l'afficher
            $err = $this->commandeModel->lastError ?? '';
            if (!empty($err)) {
                $_SESSION['error'] = $err;
            } else {
                $_SESSION['error'] = 'Impossible de créer la commande. Veuillez réessayer.';
            }
            header('Location: panier');
            exit;
        }
    }

    public function ajouterAuPanier($id)
    {
        AuthMiddleware::verifierConnexion();

        $utilisateurId = $_SESSION['user_id'];
        $commande = $this->commandeModel->getCommandeById(filter_var($id, FILTER_VALIDATE_INT) ?: 0, $utilisateurId);

        if (!$commande) {
            $_SESSION['error'] = 'Commande introuvable.';
            header('Location: '.BASE_URL.'commandes');
            exit;
        }

        $produits = $this->commandeModel->getProduitsCommande($id);

        // Ajouter chaque produit au panier (session)
        foreach ($produits as $produit) {
            $produitId = $produit['produit_id'];
            if (isset($_SESSION['panier'][$produitId])) {
                $_SESSION['panier'][$produitId]['quantite'] += $produit['quantite'];
            } else {
                $_SESSION['panier'][$produitId] = [
                    'id' => $produitId,
                    'nom' => $produit['nom'],
                    'prix' => $produit['prix'],
                    'image' => $produit['image'],
                    'quantite' => $produit['quantite']
                ];
            }
        }

        $_SESSION['success'] = 'Produits ajoutés au panier avec succès.';
        header('Location: '.BASE_URL.'panier');
        exit;
    }

    public function noter($id)
    {
        AuthMiddleware::verifierConnexion();

        $utilisateurId = $_SESSION['user_id'];
        $commande = $this->commandeModel->getCommandeById(filter_var($id, FILTER_VALIDATE_INT) ?: 0, $utilisateurId);

        if (!$commande || $commande['statut'] !== 'livree') {
            header('Location: '.BASE_URL.'commandes');
            exit;
        }

        $commande['produits'] = $this->commandeModel->getProduitsCommande($id);

        require_once __DIR__.'/../views/commandes/noter.php';
    }

    public function soumettreNote($id)
    {
        AuthMiddleware::verifierConnexion();

        $utilisateurId = $_SESSION['user_id'];
        $commande = $this->commandeModel->getCommandeById(filter_var($id, FILTER_VALIDATE_INT) ?: 0, $utilisateurId);

        if (!$commande || $commande['statut'] !== 'livree') {
            header('Location: '.BASE_URL.'commandes');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ratings = $_POST['rating'] ?? [];
            $comments = $_POST['comment'] ?? [];

            $notes_enregistrees = 0;
            $erreurs = 0;

            // Enregistrer chaque note dans la base de données
            foreach ($ratings as $produit_id => $note) {
                $produit_id = filter_var($produit_id, FILTER_VALIDATE_INT);
                $note = filter_var($note, FILTER_VALIDATE_INT);
                $commentaire = trim($comments[$produit_id] ?? '');

                if (!$produit_id || $note < 1 || $note > 5) {
                    $erreurs++;
                    continue;
                }

                // Limiter la longueur du commentaire
                $titre = "Avis suite à l'achat";
                $commentaire = substr($commentaire, 0, 2000);

                // Ajouter l'avis via le modèle
                if ($this->avisModel->ajouterAvis($produit_id, $utilisateurId, $note, $titre, $commentaire)) {
                    $notes_enregistrees++;
                }
            }

            if ($notes_enregistrees > 0) {
                $_SESSION['success'] = "Merci ! $notes_enregistrees note(s) ont été enregistrée(s).";
            }
            if ($erreurs > 0) {
                $_SESSION['warning'] = "$erreurs note(s) n'ont pas pu être enregistrée(s).";
            }

            header('Location: '.BASE_URL.'commandes/details/' . $id);
            exit;
        }

        header('Location: '.BASE_URL.'commandes');
        exit;
    }

    public function annuler($id)
    {
        AuthMiddleware::verifierConnexion();

        $utilisateurId = $_SESSION['user_id'];

        if ($this->commandeModel->annulerCommande(filter_var($id, FILTER_VALIDATE_INT) ?: 0, $utilisateurId)) {
            $_SESSION['success'] = 'Commande annulée avec succès.';
        } else {
            $_SESSION['error'] = 'Impossible d\'annuler cette commande.';
        }

        header('Location: '.BASE_URL.'commandes');
        exit;
    }

    private function getStatusClass($statut)
    {
        $classes = [
            'en_attente' => 'processing',
            'validee' => 'processing',
            'en_livraison' => 'shipped',
            'livree' => 'delivered',
            'annulee' => 'cancelled'
        ];
        return $classes[$statut] ?? 'processing';
    }

    private function getStatusLabel($statut)
    {
        $labels = [
            'en_attente' => 'En attente',
            'validee' => 'Validée',
            'preparation' => 'En preparation',
            'expediee' => 'Expediée',
            'livree' => 'Livrée',
            'annulee' => 'Annulée'
        ];
        return $labels[$statut] ?? 'En attente';
    }

    private function getTrackingSteps($statut)
    {
        $allSteps = [
            ['label' => 'Confirmée', 'status' => 'pending'],
            ['label' => 'Préparation', 'status' => 'pending'],
            ['label' => 'Expédiée', 'status' => 'pending'],
            ['label' => 'Livrée', 'status' => 'pending']
        ];

        $statusOrder = [
            'en_attente' => 0,
            'validee' => 1,
            'en_livraison' => 2,
            'livree' => 3,
            'annulee' => -1
        ];

        $currentStep = $statusOrder[$statut] ?? 0;

        foreach ($allSteps as $index => &$step) {
            if ($index < $currentStep) {
                $step['status'] = 'completed';
            } elseif ($index === $currentStep && $statut !== 'annulee') {
                $step['status'] = 'active';
            } else {
                $step['status'] = 'pending';
            }
        }

        return $allSteps;
    }
    public function validation(){
        if(!isset($_SESSION['user_id'])){
            $_SESSION['page_prochain']= BASE_URL."commandes/valider" ;
            $_SESSION['erreur_com']= "Connectez vous pour passer votre commande" ;
            header("Location: ".BASE_URL."login") ;
            exit() ;
        }

        require_once  __DIR__."/../models/Categorie.php" ;
        require_once  __DIR__."/../views/commandes/validation.php" ;
        exit ;
    }

}

?>