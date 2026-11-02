<?php
    require_once __DIR__.'/../../config/database.php';
    require_once __DIR__ . '/../../controllers/AuthController.php';
    $auth = new AuthController();
    $auth->resetPassword();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialiser mot de passe - IB Technology</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/auth.css">
</head>
<body>

<a href="<?= BASE_URL ?>" class="back-home">&larr; Retour à l'accueil</a>

<div class="auth-container">
    <h2>Réinitialiser le mot de passe</h2>

    <?php if (isset($_SESSION['erreur_reset'])): ?>
        <p class="error"><?= $_SESSION['erreur_reset'] ?></p>
    <?php endif; ?>

    <?php if (isset($_SESSION['success_reset'])): ?>
        <p class="success"><?= $_SESSION['success_reset'] ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token'] ?? '') ?>">
        
        <label for="new_password">Nouveau mot de passe :</label><br>
        <input type="password" name="new_password" id="new_password" placeholder="Votre nouveau mot de passe" required><br>
        
        <label for="confirm_password">Confirmer le mot de passe :</label><br>
        <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirmez le mot de passe" required><br>
        
        <button type="submit">Réinitialiser</button>
    </form>

    <p><a href="login">Retour à la connexion</a></p>
</div>

</body>
</html>