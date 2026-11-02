<?php
require_once __DIR__ . '/../models/utilisateur.php';

class ProfileController {

    private $userModel;

    public function __construct() {
        $this->userModel = new Utilisateur();
    }

    private function requireAuth() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
    }

    public function index() {
        $this->requireAuth();
        $user = $this->userModel->getById($_SESSION['user_id']);
        require_once __DIR__ . '/../views/profile/index.php';
    }

    public function update() {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = htmlspecialchars($_POST['nom_complet']);
            $telephone = htmlspecialchars($_POST['telephone'] ?? '');
            $adresse = htmlspecialchars($_POST['adresse'] ?? '');
            $ville = htmlspecialchars($_POST['ville'] ?? '');

            $this->userModel->updateProfile($_SESSION['user_id'], $nom, $telephone, $adresse, $ville);
            $_SESSION['success_profile'] = 'Profil mis à jour avec succès.';
        }
        header('Location: ' . BASE_URL . 'profile');
    }

    public function changePassword() {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $current = $_POST['current_password'] ?? '';
            $new = $_POST['new_password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';

            $user = $this->userModel->getById($_SESSION['user_id']);

            if (!password_verify($current, $user['mot_de_passe'])) {
                $_SESSION['error_profile'] = 'Mot de passe actuel incorrect.';
            } elseif ($new !== $confirm) {
                $_SESSION['error_profile'] = 'Les nouveaux mots de passe ne correspondent pas.';
            } else {
                $this->userModel->changePassword($_SESSION['user_id'], password_hash($new, PASSWORD_DEFAULT));
                $_SESSION['success_profile'] = 'Mot de passe modifié avec succès.';
            }
        }
        header('Location: ' . BASE_URL . 'profile');
    }

    public function settings() {
        $this->requireAuth();
        $user = $this->userModel->getById($_SESSION['user_id']);
        require_once __DIR__ . '/../views/profile/settings.php';
    }

    public function updateSettings() {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $theme = in_array($_POST['theme'] ?? 'dark', ['dark','light']) ? $_POST['theme'] : 'dark';
            $notif_email = isset($_POST['notif_email']) ? 1 : 0;
            $notif_push = isset($_POST['notif_push']) ? 1 : 0;

            $this->userModel->updateSettings($_SESSION['user_id'], $theme, $notif_email, $notif_push);

            // Update session theme for immediate effect
            $_SESSION['theme'] = $theme;

            $_SESSION['success_profile'] = 'Paramètres enregistrés.';
        }
        header('Location: ' . BASE_URL . 'settings');
    }

    public function toggleTheme() {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user = $this->userModel->getById($_SESSION['user_id']);
            $newTheme = ($user['theme'] === 'dark') ? 'light' : 'dark';
            $this->userModel->updateSettings($_SESSION['user_id'], $newTheme, $user['notif_email'], $user['notif_push']);
            $_SESSION['theme'] = $newTheme;
            header('Content-Type: application/json');
            echo json_encode(['theme' => $newTheme]);
            return;
        }
        http_response_code(405);
    }
}
