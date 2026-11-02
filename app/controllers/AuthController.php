<?php
require_once __DIR__ . '/../models/utilisateur.php';

class AuthController
{

    public function login()
    {
        // session_start() ;
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                unset($_SESSION['csrf_token']);
                die("requete invalide !");
            }
            $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) ?? 'test@gail.com';
            $password = trim($_POST['mot_de_passe']);

            $userModel = new Utilisateur();
            $user = $userModel->trouverParEmail($email);

            if ($user && password_verify($password, $user['mot_de_passe'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_name'] = $user['nom_complet'];
                // Charger préférences utilisateur
                $_SESSION['theme'] = $user['theme'] ?? 'dark';
                $_SESSION['notif_email'] = $user['notif_email'] ?? 1;
                $_SESSION['notif_push'] = $user['notif_push'] ?? 1;

                if ($user['role'] == 'admin')
                    header('Location:' . BASE_URL . 'admin');
                elseif (isset($_SESSION['page_prochain'])) {
                    header("Location: " . $_SESSION['page_prochain']);
                } else
                    header('Location:' . BASE_URL);
                exit;
            } else {
                $_SESSION['erreur_con'] = "Email ou mot de passe incorrect";
            }
        }
        require_once __DIR__ . '/../views/auth/login.php';
    }

    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                unset($_SESSION['csrf_token']);
                die("requete invalide");
            }
            $nom = htmlspecialchars($_POST['nom_complet']);
            $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
            $password = trim($_POST['mot_de_passe']);
            $passwordr = trim($_POST['rmot_de_passe']);
            if (strlen($password) < 6) {
                $_SESSION['erreur_ins'] = "Le mot de passe est trop court. Utilisez au moins 6 carracteres";
            } else if ($password != $passwordr) {
                $_SESSION['erreur_ins'] = "Les mots de passe ne correspondent pas.";
            } else {

                $userModel = new utilisateur();

                if ($userModel->trouverParEmail($email)) {
                    $_SESSION['erreur_ins'] = "Cet email est déjà utilisé !";
                } else {
                    $pass = password_hash($password, PASSWORD_DEFAULT);
                    $userModel->creer($nom, $email, $pass);
                    $_SESSION['success'] = "Inscription réussie ! Connectez-vous.";
                    header('Location: ' . BASE_URL . 'login');
                    exit;
                }
            }
        }

        require_once __DIR__ . '/../views/auth/register.php';
    }

    public function logout()
    {
        session_destroy();
        header('Location: ' . BASE_URL);
    }

    public function forgotPassword()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email']);

            $userModel = new Utilisateur();
            $user = $userModel->trouverParEmail($email);

            if ($user) {
                // Générer un token unique
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

                // Stocker le token (supposons une table password_resets)
                // Pour simplifier, on peut utiliser une session ou une DB temporaire
                // Ici, on simule l'envoi d'email
                $_SESSION['reset_token'] = $token;
                $_SESSION['reset_email'] = $email;

                // Envoyer l'email (simulé)
                $resetLink = BASE_URL . "auth/reset_password.php?token=" . $token;
                $subject = "Réinitialisation de votre mot de passe";
                $message = "Cliquez sur ce lien pour réinitialiser votre mot de passe : " . $resetLink;
                // mail($email, $subject, $message); // Activer en production

                $_SESSION['success_forgot'] = "Un lien de réinitialisation a été envoyé à votre email.";
            } else {
                $_SESSION['erreur_forgot'] = "Aucun compte trouvé avec cet email.";
            }
        }

        require_once __DIR__ . '/../views/auth/forgot_password.php';
    }

    public function resetPassword()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = htmlspecialchars($_POST['token']);
            $newPassword = trim($_POST['new_password']);
            $confirmPassword = trim($_POST['confirm_password']);

            if ($newPassword !== $confirmPassword) {
                $_SESSION['erreur_reset'] = "Les mots de passe ne correspondent pas.";
            } elseif ($token !== ($_SESSION['reset_token'] ?? '')) {
                $_SESSION['erreur_reset'] = "Token invalide.";
            } else {
                $email = $_SESSION['reset_email'];
                $userModel = new Utilisateur();
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $userModel->updatePassword($email, $hashedPassword);

                unset($_SESSION['reset_token'], $_SESSION['reset_email']);
                $_SESSION['success'] = "Mot de passe réinitialisé avec succès.";
                header('Location: '.BASE_URL.'login');
                exit;
            }
        }

        require_once __DIR__ . '/../views/auth/reset_password.php';
    }
}