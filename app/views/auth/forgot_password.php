<?php
    require_once __DIR__.'/../../config/database.php';
    require_once __DIR__ . '/../../controllers/AuthController.php';
    $auth = new AuthController();
    $auth->forgotPassword();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié - IB Technology</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/auth.css">
</head>
<body>

<a href="<?= BASE_URL ?>" class="back-home">&larr; Retour à l'accueil</a>

<div class="auth-container">
    <h2>Mot de passe oublié</h2>

    <?php if (isset($_SESSION['erreur_forgot'])): ?>
        <p class="error"><?= $_SESSION['erreur_forgot'] ?></p>
    <?php endif; ?>

    <?php if (isset($_SESSION['success_forgot'])): ?>
        <p class="success"><?= $_SESSION['success_forgot'] ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="email">Email :</label><br>
        <input type="email" name="email" id="email" placeholder="Votre adresse email" required><br>

        <button type="submit">Envoyer le lien de réinitialisation</button>
    </form>

    <p><a href="login">Retour à la connexion</a></p>
</div>

</body>
</html>