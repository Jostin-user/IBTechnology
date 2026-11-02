<?php

require_once __DIR__ . '/../models/Avis.php';

class AvisController
{
    private $avisModel;
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
        $this->avisModel = new Avis($db);
    }

    /**
     * Récupérer tous les avis d'un produit (AJAX)
     */
    public function getAvis($produit_id)
    {
        header('Content-Type: application/json');

        $produit_id = filter_var($produit_id, FILTER_VALIDATE_INT);
        if (!$produit_id) {
            echo json_encode(['success' => false, 'message' => 'Produit invalide']);
            exit;
        }

        $page = $_GET['page'] ?? 1;
        $page = max(1, (int) $page);
        $limit = 5;
        $offset = ($page - 1) * $limit;

        $avis = $this->avisModel->getAvisProduit($produit_id, $limit, $offset);
        $total = $this->avisModel->compterAvisProduit($produit_id);
        $moyenne = $this->avisModel->getNoteMoyenne($produit_id);
        $distribution = $this->avisModel->getDistributionNotes($produit_id);

        echo json_encode([
            'success' => true,
            'avis' => $avis,
            'moyenne' => $moyenne,
            'total' => $total,
            'pages' => ceil($total / $limit),
            'distribution' => $distribution
        ]);
    }

    /**
     * Ajouter un avis (POST)
     */
    public function ajouterAvis()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            exit;
        }

        // Vérifier l'authentification
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Vous devez être connecté', 'redirect' => BASE_URL . 'login']);
            exit;
        }

        $produit_id = filter_var($_POST['produit_id'] ?? 0, FILTER_VALIDATE_INT);
        $note = filter_var($_POST['note'] ?? 0, FILTER_VALIDATE_INT);
        $titre = trim($_POST['titre'] ?? '');
        $commentaire = trim($_POST['commentaire'] ?? '');
        $utilisateur_id = $_SESSION['user_id'];

        // Validation
        if (!$produit_id || $note < 1 || $note > 5 || empty($titre)) {
            echo json_encode(['success' => false, 'message' => 'Données invalides']);
            exit;
        }

        // Limiter la longueur
        $titre = substr($titre, 0, 200);
        $commentaire = substr($commentaire, 0, 2000);

        // Vérifier que l'utilisateur a acheté ce produit
        if (!$this->avisModel->peutLaisserAvis($produit_id, $utilisateur_id)) {
            echo json_encode(['success' => false, 'message' => 'Vous devez avoir acheté ce produit pour laisser un avis']);
            exit;
        }

        if ($this->avisModel->ajouterAvis($produit_id, $utilisateur_id, $note, $titre, $commentaire)) {
            echo json_encode(['success' => true, 'message' => 'Avis ajouté avec succès']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'ajout de l\'avis']);
        }
        exit;
    }

    /**
     * Récupérer les statistiques d'avis (AJAX)
     */
    public function getStatistiques($produit_id)
    {
        header('Content-Type: application/json');

        $produit_id = filter_var($produit_id, FILTER_VALIDATE_INT);
        if (!$produit_id) {
            echo json_encode(['success' => false]);
            exit;
        }

        $moyenne = $this->avisModel->getNoteMoyenne($produit_id);
        $total = $this->avisModel->compterAvisProduit($produit_id);
        $distribution = $this->avisModel->getDistributionNotes($produit_id);

        echo json_encode([
            'success' => true,
            'moyenne' => $moyenne,
            'total' => $total,
            'distribution' => $distribution
        ]);
        exit;
    }

    /**
     * Vérifier si l'utilisateur peut laisser un avis
     */
    public function peutLaisserAvis($produit_id)
    {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['peut_laisser' => false, 'raison' => 'non_connecte']);
            exit;
        }

        $produit_id = filter_var($produit_id, FILTER_VALIDATE_INT);
        if (!$produit_id) {
            echo json_encode(['peut_laisser' => false, 'raison' => 'produit_invalide']);
            exit;
        }

        $utilisateur_id = $_SESSION['user_id'];
        $peut = $this->avisModel->peutLaisserAvis($produit_id, $utilisateur_id);
        $avis_existant = $peut ? $this->avisModel->getAvisUtilisateur($produit_id, $utilisateur_id) : null;

        echo json_encode([
            'peut_laisser' => $peut,
            'avis_existant' => $avis_existant
        ]);
        exit;
    }

    /**
     * Supprimer un avis (Admin)
     */
    public function supprimerAvis($avis_id)
    {
        // Vérifier admin
        if ($_SESSION['user_role'] !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Non autorisé']);
            exit;
        }

        $avis_id = filter_var($avis_id, FILTER_VALIDATE_INT);
        if ($this->avisModel->supprimerAvis($avis_id)) {
            echo json_encode(['success' => true, 'message' => 'Avis supprimé']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur de suppression']);
        }
        exit;
    }
}
