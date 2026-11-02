<?php
// Wrapper pour permettre l'accès aux méthodes de VenteController depuis les routes non-admin
require_once __DIR__ . '/admin/VenteController.php';

// La classe `VenteController` est définie dans le fichier admin/VenteController.php
// Nous ne redéfinissons rien ici, ce fichier existe juste pour que le routeur puisse
// charger `VenteController` depuis `app/controllers` lors des routes publiques.
?>