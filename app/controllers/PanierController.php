<?php
require_once __DIR__ . "/../models/Produit.php";
class PanierController
{
    private $produitModel;

    public function __construct($db)
    {
        // session_start();
        $this->produitModel = new Produit($db);
        if (!isset($_SESSION['panier'])) {
            $_SESSION['panier'] = [];
        }
    }

    public function index()
    {
        $panier = $_SESSION['panier'] ?? [];
        $produits = [];
        $total = 0;

        foreach ($panier as $item) {
            $produit = $this->produitModel->getProduitById($item['id']);
            if ($produit) {
                $item['details'] = $produit;
                $produits[] = $item;
                $total += $item['prix'] * $item['quantite'];
            }
        }

        require_once __DIR__ . '/../views/panier/index.php';
    }

    public function compterPanier()
    {
        header('Content-Type: application/json');
        $total = array_sum(array_column($_SESSION['panier'] ?? [], 'quantite'));
        echo json_encode(['total' => $total]);
        exit;
    }

    public function ajouter()
    {
        header('Content-Type: application/json');
        // Gérer à la fois les données JSON et les données de formulaire
        if ($_SERVER['CONTENT_TYPE'] === 'application/json' || strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
            $data = json_decode(file_get_contents("php://input"), true);
        } else {
            $data = $_POST;
        }
        $id = (int) ($data['id'] ?? 0);
        $quantite = (int) ($data['quantite'] ?? 1);

        $produit = $this->produitModel->getProduitById($id);
        if (!$produit) {
            echo json_encode(['success' => false, 'message' => 'Produit non trouvé']);
            exit;
        }

        if ($quantite < 1 || $quantite > $produit['stock']) {
            echo json_encode(['success' => false, 'message' => 'Quantité invalide']);
            exit;
        }

        if (isset($_SESSION['panier'][$id])) {
            $_SESSION['panier'][$id]['quantite'] += $quantite;
        } else {
            $_SESSION['panier'][$id] = [
                'id' => $produit['id'],
                'nom' => $produit['nom'],
                'prix' => $produit['prix'],
                'image' => $produit['image'],
                'quantite' => $quantite
            ];
        }

        echo json_encode([
            'success' => true,
            'total' => array_sum(array_column($_SESSION['panier'], 'quantite')),
            'message' => 'Produit ajouté au panier'
        ]);
        exit;
    }

    public function contenu()
    {
        header('Content-Type: application/json');
        $panier = $_SESSION['panier'] ?? [];
        $produits = [];
        foreach ($panier as $p) {
            $produits[] = $p;
        }

        //echo "0o" ;
        //header("Location: ../layouts/headerej.php ");
        echo json_encode($produits);
        exit;
    }


    public function augmenterProduit()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $id = (int) $data['produit_id'];

        $_SESSION['panier'][$id]['quantite'] = ($_SESSION['panier'][$id]['quantite'] ?? 0) + 1;

        echo json_encode(['success' => true]);
        exit;
    }
    public function diminuerProduit()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $id = (int) $data['produit_id'];

        if (isset($_SESSION['panier'][$id])) {
            $_SESSION['panier'][$id]['quantite']--;

            if ($_SESSION['panier'][$id]['quantite'] <= 0) {
                $_SESSION['panier'][$id]['quantite'] = 1;
            }
        }

        echo json_encode(['success' => true]);
        exit;
    }
    public function supprimerProduit()
    {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents("php://input"), true);
        $id = (int) $data["produit_id"];
        if (isset($_SESSION['panier'][$id])) {
            unset($_SESSION['panier'][$id]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
        exit;
    }
}
?>