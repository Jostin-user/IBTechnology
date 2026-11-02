<?php
session_start();

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/models/Visite.php';
$visiteModel = new Visite($db);
# Simple logger integration
require_once __DIR__ . '/../app/helpers/Logger.php';
$logger = new Logger(__DIR__ . '/../storage/Logs');

// Log access
$logger->access('Request received', ['uri' => $_SERVER['REQUEST_URI'] ?? '', 'method' => $_SERVER['REQUEST_METHOD'] ?? '']);

// Error and exception handlers to log server errors
set_exception_handler(function($e) use ($logger) {
    $msg = sprintf("Uncaught exception: %s in %s on line %s", $e->getMessage(), $e->getFile(), $e->getLine());
    $logger->error($msg, ['trace' => $e->getTraceAsString()]);
    http_response_code(500);
    echo "Une erreur serveur est survenue.";
    exit;
});

set_error_handler(function($errno, $errstr, $errfile, $errline) use ($logger) {
    $logger->error("PHP Error: {$errstr} in {$errfile}:{$errline}", ['errno' => $errno]);
    return false; // proceed with PHP internal handler as well
});

register_shutdown_function(function() use ($logger) {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $logger->error('Fatal error: ' . ($err['message'] ?? ''), $err);
    }
});
if(!isset($_SESSION['visite_enregistree'])){
    $visiteModel->enregistrer();
    $_SESSION['visite_enregistree'] = true;
}
/*
|--------------------------------------------------------------------------
| Système de Routage du site
|--------------------------------------------------------------------------
*/

// Définition des routes
$routes = [
    'GET' => [
        '/' => ['AccueilController', 'index'],
        '/accueil' => ['AccueilController', 'index'],
        '/login' => ['AuthController', 'login'],
        '/register' => ['AuthController', 'register'],
        '/logout' => ['AuthController', 'logout'],
        '/forgot_password' => ['AuthController', 'forgotPassword'],
        '/boutique' => ['ProduitController', 'index'],
        '/produits/{id}' => ['ProduitController', 'details'],
        '/produits/slug/{slug}' => ['ProduitController', 'detailsBySlug'],
        '/boutique/{slug}' => ['ProduitController', 'categorieBySlug'],
        '/panier' => ['PanierController', 'index'],
        '/panier/contenu' => ['PanierController', 'contenu'],
        '/panier/compter' => ['PanierController', 'compterPanier'],
        // recherche
        '/search/suggestions' => ['SearchController', 'suggestions'],
       // '/panier/compter' => ['PanierController', 'compterPanier'],
        // API Avis produits
        '/api/avis/get' => ['AvisController', 'getAvis'],
        '/api/avis/statistiques' => ['AvisController', 'getStatistiques'],
        '/api/avis/peut-laisser' => ['AvisController', 'peutLaisserAvis'],
        // Zones de livraison pour le formulaire de commande
        '/api/livraison/regions' => ['LivraisonController', 'regions'],
        '/api/livraison/villes' => ['LivraisonController', 'villes'],
        '/api/livraison/quartiers' => ['LivraisonController', 'quartiers'],
        //commande client
        '/commandes' => ['CommandeController', 'index'],
        '/commandes/valider' => ['CommandeController', 'validation'],
        '/commandes/details/{id}' => ['CommandeController', 'details'],
        '/commandes/annuler/{id}' => ['CommandeController', 'annuler'],
        '/commandes/noter/{id}' => ['CommandeController', 'noter'],
        // notifications clients et profils
        '/notifications' => ['NotificationController', 'index'],
        '/api/notifications/count' => ['NotificationController', 'countNonLues'],
        '/api/notifications/unread' => ['NotificationController', 'getNonLues'],
        '/profile' => ['ProfileController', 'index'],
        '/settings' => ['ProfileController', 'settings'],
        '/aide' => ['AccueilController', 'aide'],
        // Ventes (client)
        '/ventes/mes-ventes' => ['VenteController', 'mesVentes'],
        '/ventes/mes-ventes/details/{id}' => ['VenteController', 'mesVentesDetails'],
        '/ventes/demander-retour/{id}' => ['VenteController', 'demanderRetour'],
        //recherche admin
        '/admin/search/suggestions' => ['SearchController', 'suggestions'],
        //admin dashboard, produits et categories
        '/admin' => ['AdminController', 'dashboard'],
        '/commandes/telecharger-facture/{id}' => ['CommandeController', 'telechargerFacture'],
        '/admin/produits' => ['ProduitController', 'adminListe'],
        '/admin/produits/create' => ['ProduitController', 'ajouter'],
        '/admin/produits/edit' => ['ProduitController', 'modifier'],
        '/admin/categories' => ['AdminController', 'categories'],
        '/admin/categories/ajouter' => ['AdminController', 'ajoutCategorie'],
        //commandes admin
        '/admin/commandes' => ['CommandeController', 'adminIndex'],
        '/admin/commandes/details/{id}' => ['CommandeController', 'adminDetails'],
        '/admin/commandes/annuler/{id}' => ['CommandeController', 'annuler'],
        '/admin/commandes/telecharger-facture/{id}' => ['CommandeController', 'telechargerFacture'],
        '/admin/commandes/annulebr/{id}' => ['CommandeController', 'annuler'],
        '/admin/commandes/telechargerfacture/{id}' => ['CommandeController', 'telechargerFacture'],
        //livraison admin et gestion clients
        '/admin/livraison' => ['LivraisonController', 'index'],
        '/admin/livraison/regions' => ['LivraisonController', 'getRegions'],
        '/admin/livraison/villes' => ['LivraisonController', 'getVilles'],
        '/admin/livraison/quartiers' => ['LivraisonController', 'getQuartiers'],
        '/admin/livraison/edit-data' => ['LivraisonController', 'getZoneData'],
        '/admin/livraison/all-villes' => ['LivraisonController', 'getAllVilles'],
        '/admin/livraison/toggle-status' => ['LivraisonController', 'toggle'],
        '/admin/livraison/delete' => ['LivraisonController', 'delete'],
        '/admin/clients' => ['ClientController', 'index'],
        '/admin/clients/toggle-status' => ['ClientController', 'toggleStatus'],
        '/admin/clients/delete' => ['ClientController', 'delete'],
        '/admin/clients/details' => ['ClientController', 'details'],
        '/admin/administration' => ['AdministrateursController', 'index'],
        '/admin/administration/create' => ['AdministrateursController', 'create'],
        '/admin/administration/store' => ['AdministrateursController', 'store'],
        '/admin/administration/edit' => ['AdministrateursController', 'edit'],
        '/admin/administration/update' => ['AdministrateursController', 'update'],
        '/admin/administration/delete' => ['AdministrateursController', 'delete'],
        '/admin/administration/toggle-status' => ['AdministrateursController', 'toggleStatus'],
        // Stock management (admin)
        '/admin/stock' => ['StockController', 'index'],
        '/admin/stock/dashboard' => ['StockController', 'dashboard'],
        '/admin/stock/details' => ['StockController', 'details'],
        '/admin/stock/rapport' => ['StockController', 'rapport'],
        '/admin/stock/exporterCSV' => ['StockController', 'exporterCSV'],
        // Ventes (admin)
        '/admin/ventes' => ['VenteController', 'index'],
        '/admin/ventes/rapport' => ['VenteController', 'rapport'],
        '/admin/ventes/details' => ['VenteController', 'details'],
        '/admin/ventes/retours' => ['VenteController', 'retours'],
    ],
    'POST' => [
        '/login' => ['AuthController', 'login'],
        '/register' => ['AuthController', 'register'],
        '/logout' => ['AuthController', 'logout'],
        '/forgot_password' => ['AuthController', 'forgotPassword'],
        '/panier/ajouter' => ['PanierController', 'ajouter'],
        '/panier/augmenterquantite' => ['PanierController', 'augmenterProduit'],
        '/panier/diminuerquantite' => ['PanierController', 'diminuerProduit'],
        '/panier/supprimer' => ['PanierController', 'supprimerProduit'],
        '/commandes' => ['CommandeController', 'store'],
        '/commandes/soumettre-note/{id}' => ['CommandeController', 'soumettreNote'],
        // API Avis produits
        '/api/avis/ajouter' => ['AvisController', 'ajouterAvis'],
        '/api/avis/supprimer/{id}' => ['AvisController', 'supprimerAvis'],
        //notifications client et profils
        '/notifications/mark-read/{id}' => ['NotificationController', 'marquerCommeLue'],
        '/notifications/mark-all-read' => ['NotificationController', 'marquerToutCommeLu'],
        '/notifications/delete/{id}' => ['NotificationController', 'supprimer'],
        '/api/notifications/mark-all-read' => ['NotificationController', 'marquerToutCommeLu'],
        '/profile/update' => ['ProfileController', 'update'],
        '/profile/change-password' => ['ProfileController', 'changePassword'],
        '/settings/update' => ['ProfileController', 'updateSettings'],
        '/settings/toggle-theme' => ['ProfileController', 'toggleTheme'],
        // gestion admin - POST actions
        '/admin/categories' => ['AdminController', 'categories'],
        '/admin/categories/toggle-status' => ['AdminController', 'toggleCategoryStatus'],
        '/admin/produits/delete' => ['ProduitController', 'delete'],
        '/admin/produits/create' => ['ProduitController', 'ajouter'],
        '/admin/produits/edit' => ['ProduitController', 'modifier'],
        '/admin/categories/togglestatus' => ['AdminController', 'toggleCategoryStatus'],
        '/admin/livraison/store' => ['LivraisonController', 'storeAjax'],
        '/admin/livraison/update' => ['LivraisonController', 'update'],
        // '/admin/livraison/delete' => ['LivraisonController', 'delete'],
        // '/admin/livraison/toggle-status' => ['LivraisonController', 'toggle'],
        '/admin/commandes/updateStatus/{id}' => ['CommandeController', 'updateStatus'],
        // Stock management (admin) - POST actions
        '/admin/stock/ajouterMouvement' => ['StockController', 'ajouterMouvement'],
        '/admin/stock/ajuster' => ['StockController', 'ajuster'],
        '/admin/stock/definirSeuils' => ['StockController', 'definirSeuils'],
        '/admin/stock/exporter' => ['StockController', 'exporterCSV'],
        // Ventes (admin) - POST actions
        '/admin/ventes/confirmerLivraison/{id}' => ['VenteController', 'confirmerLivraison'],
        '/admin/ventes/annuler/{id}' => ['VenteController', 'annuler'],
        '/admin/ventes/exporter' => ['VenteController', 'exporterCSV'],
        '/admin/ventes/approuver-retour/{id}' => ['VenteController', 'approuverRetour'],
        '/admin/ventes/traiter-retour/{id}' => ['VenteController', 'traiterRetour'],
        //gestion clients par admin
        '/admin/clients/update' => ['ClientController', 'update'],
        // Ventes (client) - POST actions
        '/ventes/demander-retour/{id}' => ['VenteController', 'demanderRetour'],
    ],
];

// Fonction pour matcher les routes
function matchRoute($routes, $method, $uri)
{
    if (!isset($routes[$method])) {
        return false;
    }

    foreach ($routes[$method] as $pattern => $handler) {
        // Convertir le pattern en regex
        $regex = preg_replace('/\{([^}]+)\}/', '([^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';

        if (preg_match($regex, $uri, $matches)) {
            array_shift($matches); // Retirer la correspondance complète
            return ['handler' => $handler, 'params' => $matches];
        }
    }

    return false;
}

// Obtenir la méthode HTTP et l'URI
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
// Retirer le préfixe /IbTechnologie/public si nécessaire
$basePath = '/IbTechnologie/public';
if (strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath));
    
}
if ($uri === '') {
    $uri = '/';
}

// Matcher la route
$match = matchRoute($routes, $method, $uri);

if ($match) {
    $handler = $match['handler'];
    $params = $match['params'];


    // Charger et instancier le contrôleur
    $controllerName = $handler[0];
    $methodName = $handler[1];
    // Vérifier les middlewares pour les routes admin
    if (strpos($uri, '/admin') === 0) {
        require_once __DIR__ . '/../app/middlewares/AdminMiddleware.php';
        AdminMiddleware::verifierAdmin();
        require_once __DIR__ . '/../app/controllers/admin/' . $controllerName . '.php';
    } else {
        require_once __DIR__ . '/../app/controllers/' . $controllerName . '.php';
    }
    $controller = new $controllerName($db);

    // Appeler la méthode avec les paramètres
    call_user_func_array([$controller, $methodName], $params);
} else {
    // Erreur 404 - page non trouvée
    // Log 404 with context
    if (isset($logger) && is_object($logger)) {
        $context = [
            'uri' => $_SERVER['REQUEST_URI'] ?? '',
            'method' => $_SERVER['REQUEST_METHOD'] ?? '',
            'query' => $_SERVER['QUERY_STRING'] ?? '',
            'referer' => $_SERVER['HTTP_REFERER'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'user_id' => $_SESSION['user_id'] ?? null
        ];
        $logger->access('404 Not Found', $context);
    }

    // Afficher la page 404
    require_once  __DIR__.'/../app/views/404.php' ;
    //echo "<h1 style='color:rgb(89, 100, 123);text-align:center'>404 - Page introuvable sur Ibtechnology</h1>";
}
?>