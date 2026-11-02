<?php
if (empty(($_SESSION['csrf_token'])))
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
$auth = new AuthController();
$auth->register();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - IB Technology</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/auth.css">
</head>

<body>

    <a href="<?= BASE_URL ?>" class="back-home">&larr; Retour à l'accueil</a>

    <div class="auth-container">
        <h2>Créer un compte</h2>

        <?php if (isset($_SESSION['erreur_ins'])): ?>
            <p class="error"><?= $_SESSION['erreur_ins'] ?></p>
        <?php unset($_SESSION['erreur_ins']);endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?=$_SESSION['csrf_token']?>">
            <label for="nom">Nom complet :</label>
            <input type="text" name="nom_complet" id="nom" placeholder="Votre nom complet" required>

            <label for="email">Email :</label>
            <input type="email" name="email" id="email" placeholder="Votre adresse email" required>

            <label for="mot_de_passe">Mot de passe :</label>
            <input type="password" name="mot_de_passe" id="mot_de_passe" placeholder="Votre mot de passe" required>

            <label for="rmot_de_passe">Confirmer le mot de passe :</label>
            <input type="password" name="rmot_de_passe" id="rmot_de_passe" placeholder="Confirmez votre mot de passe"
                required>

            <button type="submit">S'inscrire</button>
        </form>

        <p>Déjà inscrit ? <a href="login">Se connecter</a></p>
    </div>

</body>

</html>