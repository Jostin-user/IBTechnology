<?php

class AuthMiddleware {

    public static function verifierConnexion() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
    }
}
?>