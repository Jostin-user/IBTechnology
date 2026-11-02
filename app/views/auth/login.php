<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
if (empty(($_SESSION['csrf_token'])))
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$auth = new AuthController();
$auth->login();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - IB Technology</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/auth.css">
</head>

<body>

    <a href="<?= BASE_URL ?>" class="back-home">&larr; Retour à l'accueil</a>

    <div class="auth-container">
        <h2>Connexion à IB Technology</h2>
        <?php if (isset($_SESSION['success'])): ?>
            <p class="success"><?= $_SESSION['success'] ?></p>
            <?php unset($_SESSION['success']); 
        endif; ?>

        <?php if (isset($_SESSION['erreur_con'])): ?>
            <p class="error"><?= $_SESSION['erreur_con'] ?></p>
        <?php unset($_SESSION['erreur_con']);endif; ?>
        <?php if (isset($_SESSION['erreur_com'])): ?>
            <p style="color: greenyellow ;"><?= $_SESSION['erreur_com'] ?></p>
            <?php unset($_SESSION['erreur_com']);

        endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <label for="email">Email :</label>
            <input type="email" name="email" id="email" placeholder="Votre adresse email" required>

            <label for="mot_de_passe">Mot de passe :</label>
            <input type="password" name="mot_de_passe" id="mot_de_passe" placeholder="Votre mot de passe" required>

            <div class="form-options">
                <label>
                    <input type="checkbox" name="remember_me"> Se souvenir de moi
                </label>
                <a href="forgot_password" class="forgot-link">Mot de passe oublié ?</a>
            </div>

            <button type="submit">Se connecter</button>
        </form>

        <p>Pas encore inscrit ? <a href="register">Créer un compte</a></p>
    </div>

</body>

</html>