<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mon profil - IB Technologie</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/header.css?v=20261102-cart">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/profile.css">
    <!-- <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/dark-theme.css"> -->
</head>

<body >
    <?php require_once __DIR__ . '/../../layouts/header.php'; ?>

    <section class="container profile-page">
        <h1>Mon profil</h1>
        <?php if (!empty($_SESSION['success_profile'])): ?>
            <div class="alert success"><?= $_SESSION['success_profile'];
            unset($_SESSION['success_profile']); ?></div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['error_profile'])): ?>
            <div class="alert error"><?= $_SESSION['error_profile'];
            unset($_SESSION['error_profile']); ?></div>
        <?php endif; ?>

        <div class="profile-grid">
            <div class="card">
                <h3>Informations personnelles</h3>
                <form action="<?= BASE_URL ?>profile/update" method="post">
                    <label>Nom complet</label>
                    <input type="text" name="nom_complet" value="<?= htmlspecialchars($user['nom_complet']) ?>">
                    <label>Email (non modifiable)</label>
                    <input type="email" disabled value="<?= htmlspecialchars($user['email']) ?>">
                    <label>Téléphone</label>
                    <input type="text" name="telephone" value="<?= htmlspecialchars($user['telephone']) ?>">
                    <label>Adresse</label>
                    <textarea name="adresse"><?= htmlspecialchars($user['adresse']) ?></textarea>
                    <label>Ville</label>
                    <input type="text" name="ville" value="<?= htmlspecialchars($user['ville']) ?>">
                    <button class="btn primary" type="submit">Enregistrer</button>
                </form>
            </div>

            <div class="card">
                <h3>Sécurité</h3>
                <form action="<?= BASE_URL ?>profile/change-password" method="post">
                    <label>Mot de passe actuel</label>
                    <input type="password" name="current_password">
                    <label>Nouveau mot de passe</label>
                    <input type="password" name="new_password">
                    <label>Confirmer le mot de passe</label>
                    <input type="password" name="confirm_password">
                    <button class="btn secondary" type="submit">Changer le mot de passe</button>
                </form>
            </div>
        </div>
    </section>

    <?php include __DIR__ . '/../../layouts/footer.php'; ?>

    <script>
        // Optional: simple client side validation
    </script>
</body>

</html>