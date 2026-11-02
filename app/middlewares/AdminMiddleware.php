<?php
class AdminMiddleware {

    public static function verifierAdmin() {
        if (
            !isset($_SESSION['user_id']) ||
            $_SESSION['user_role'] != 'admin'
        ) {
            // Log security event
            if (file_exists(__DIR__.'/../helpers/Logger.php')) {
                require_once __DIR__.'/../helpers/Logger.php';
                try {
                    $l = new Logger(__DIR__ . '/../../storage/Logs');
                    $l->security('Accès admin refusé', ['session_user_id' => $_SESSION['user_id'] ?? null, 'role' => $_SESSION['user_role'] ?? null, 'uri' => $_SERVER['REQUEST_URI'] ?? '']);
                } catch (Exception $e) {}
            }
            require_once __DIR__.'/../views/404.php' ;
            exit;
        }
    }

    public static function verifierSuperAdmin() {
        if (
            !isset($_SESSION['user_id']) ||
            ($_SESSION['user_role'] ?? '') == 'superadmin'
        ) {
            require_once __DIR__.'/../views/404.php' ;
            exit;
        }
    }
}
?>