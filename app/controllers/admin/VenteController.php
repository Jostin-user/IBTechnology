<?php

require_once __DIR__ . '/../../models/Vente.php';

class VenteController
{
    private $vente;
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
        $this->vente = new Vente($db);
    }

    /**
     * Historique des ventes (Admin)
     */
    public function index()
    {
        $parPage = 20;
        $page = isset($_GET['p']) ? max(1, (int) $_GET['p']) : 1;
        $offset = ($page - 1) * $parPage;

        $filtres = [
            'statut' => $_GET['statut'] ?? null,
            'date_debut' => $_GET['date_debut'] ?? null,
            'date_fin' => $_GET['date_fin'] ?? null,
            'search' => $_GET['search'] ?? null
        ];

        $ventes = $this->vente->getHistoriqueVentes($filtres, $parPage, $offset);
        $total = $this->vente->countVentes($filtres);
        $totalPages = ceil($total / $parPage);

        // Statistiques
        $stats = $this->vente->getStatistiques(
            $filtres['date_debut'],
            $filtres['date_fin']
        );

        require_once __DIR__ . '/../../views/admin/ventes/index.php';
    }

    /**
     * Détails d'une vente
     */
    public function details()
    {
        if (!isset($_GET['id'])) {
            header('Location: '.BASE_URL.'admin/ventes');
            exit;
        }

        $vente_id = (int) $_GET['id'];
        $vente = $this->vente->getVenteById($vente_id);

        if (!$vente) {
            http_response_code(404);
            die('Vente non trouvée');
        }

        $produits = $this->vente->getDetailsProduits($vente_id);

        require_once __DIR__ . '/../../views/admin/ventes/details.php';
    }

    /**
     * Confirmer la livraison
     */
    public function confirmerLivraison()
    {
        if (!isset($_GET['id'])) {
            header('Location: '.BASE_URL.'admin/ventes');
            exit;
        }

        $vente_id = (int) $_GET['id'];

        if ($this->vente->confirmerLivraison($vente_id)) {
            $_SESSION['success'] = 'Livraison confirmée';
        } else {
            $_SESSION['error'] = 'Erreur lors de la confirmation';
        }

        header('Location: '.BASE_URL.'admin/ventes/details?id=' . $vente_id);
        exit;
    }

    /**
     * Annuler une vente
     */
    public function annuler()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: '.BASE_URL.'admin/ventes');
            exit;
        }

        $vente_id = (int) ($_POST['vente_id'] ?? 0);
        $notes = $_POST['notes'] ?? '';

        if ($this->vente->annulerVente($vente_id, $notes)) {
            $_SESSION['success'] = 'Vente annulée';
        } else {
            $_SESSION['error'] = 'Erreur lors de l\'annulation';
        }

        header('Location: '.BASE_URL.'admin/ventes/details?id=' . $vente_id);
        exit;
    }

    /**
     * Rapport des ventes
     */
    public function rapport()
    {
        $date_debut = $_GET['date_debut'] ?? date('Y-01-01');
        $date_fin = $_GET['date_fin'] ?? date('Y-m-d');

        $stats = $this->vente->getStatistiques($date_debut, $date_fin);
        $ventes_par_jour = $this->vente->getVentesParJour($date_debut, $date_fin);
        $produits_populaires = $this->vente->getProduitsPopulaires($date_debut, $date_fin, 15);
        $clients_top = $this->vente->getClientsTopAcheteurs(10);

        require_once __DIR__ . '/../../views/admin/ventes/rapport.php';
    }

    /**
     * Mes ventes (Client)
     */
    public function mesVentes()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: '.BASE_URL.'/login');
            exit;
        }

        $utilisateur_id = $_SESSION['user_id'];
        $parPage = 10;
        $page = isset($_GET['p']) ? max(1, (int) $_GET['p']) : 1;
        $offset = ($page - 1) * $parPage;

        $ventes = $this->vente->getVentesUtilisateur($utilisateur_id, $parPage, $offset);
        $total = $this->vente->countVentesUtilisateur($utilisateur_id);
        $totalPages = ceil($total / $parPage);

        require_once __DIR__ . '/../../views/ventes/mesVentes.php';
    }

    /**
     * Détails d'une vente pour le client
     */
    public function mesVentesDetails()
    {
        if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
            header('Location: '.BASE_URL.'ventes/mes-ventes');
            exit;
        }

        $utilisateur_id = $_SESSION['user_id'];
        $vente_id = (int) $_GET['id'];

        $vente = $this->vente->getVenteById($vente_id);

        if (!$vente || $vente['utilisateur_id'] != $utilisateur_id) {
            http_response_code(403);
            die('Accès refusé');
        }

        $produits = $this->vente->getDetailsProduits($vente_id);

        require_once __DIR__ . '/../../views/ventes/ventesDetails.php';
    }

    /**
     * Demander un retour
     */
    public function demanderRetour()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
            header('Location: /ventes/mes-ventes');
            exit;
        }

        $vente_id = (int) ($_POST['vente_id'] ?? 0);
        $produit_id = (int) ($_POST['produit_id'] ?? 0);
        $quantite = (int) ($_POST['quantite'] ?? 0);
        $raison = $_POST['raison'] ?? '';

        if (!$vente_id || !$produit_id || !$quantite) {
            $_SESSION['error'] = 'Données invalides';
            header('Location: /ventes/mes-ventes/details?id=' . $vente_id);
            exit;
        }

        if ($this->vente->creerRetour($vente_id, $produit_id, $quantite, $raison)) {
            $_SESSION['success'] = 'Demande de retour créée';
        } else {
            $_SESSION['error'] = 'Erreur lors de la création du retour';
        }

        header('Location: /ventes/mes-ventes/details?id=' . $vente_id);
        exit;
    }

    /**
     * Gestion des retours (Admin)
     */
    public function retours()
    {
        $retours = $this->vente->getRetoursEnAttente();
        require_once __DIR__ . '/../../views/admin/ventes/retours.php';
    }

    /**
     * Approuver un retour
     */
    public function approuverRetour()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/ventes/retours');
            exit;
        }

        $retour_id = (int) ($_POST['retour_id'] ?? 0);

        if ($this->vente->approuverRetour($retour_id)) {
            $_SESSION['success'] = 'Retour approuvé';
        } else {
            $_SESSION['error'] = 'Erreur';
        }

        header('Location: /admin/ventes/retours');
        exit;
    }

    /**
     * Traiter un retour
     */
    public function traiterRetour()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/ventes/retours');
            exit;
        }

        $retour_id = (int) ($_POST['retour_id'] ?? 0);
        $notes = $_POST['notes'] ?? '';

        if ($this->vente->traiterRetour($retour_id, $notes)) {
            $_SESSION['success'] = 'Retour traité';
        } else {
            $_SESSION['error'] = 'Erreur';
        }

        header('Location: /admin/ventes/retours');
        exit;
    }

    /**
     * Export CSV des ventes
     */
    public function exporterCSV()
    {
        $date_debut = $_GET['date_debut'] ?? date('Y-01-01');
        $date_fin = $_GET['date_fin'] ?? date('Y-m-d');

        $filtres = [
            'date_debut' => $date_debut,
            'date_fin' => $date_fin
        ];

        $ventes = $this->vente->getHistoriqueVentes($filtres, -1, 0);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="ventes_' . date('Y-m-d_His') . '.csv"');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // En-têtes
        fputcsv($output, [
            'ID Vente',
            'Commande ID',
            'Client',
            'Email',
            'Montant Total',
            'Frais Livraison',
            'Articles',
            'Statut',
            'Date Vente',
            'Date Livraison'
        ]);

        // Données
        foreach ($ventes as $v) {
            fputcsv($output, [
                $v['id'],
                $v['commande_id'],
                $v['nom_complet'],
                $v['email'],
                $v['montant_total'],
                $v['frais_livraison'],
                $v['nombre_articles'],
                $v['statut'],
                date('d/m/Y H:i', strtotime($v['date_vente'])),
                $v['date_livraison'] ? date('d/m/Y H:i', strtotime($v['date_livraison'])) : '-'
            ]);
        }

        fclose($output);
        exit;
    }
}
