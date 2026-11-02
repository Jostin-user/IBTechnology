<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Paramètres - IB Technologie</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/header.css?v=20261102-cart">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/profile.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/dark-theme.css">
</head>

<body class="theme-<?= htmlspecialchars($_SESSION['theme'] ?? 'dark') ?>">
    <?php require_once __DIR__ . '/../../layouts/header.php'; ?>

    <section class="container profile-page">
        <h1>Paramètres du compte</h1>
        <?php if (!empty($_SESSION['success_profile'])): ?>
            <div class="alert success"><?= $_SESSION['success_profile'];
            unset($_SESSION['success_profile']); ?></div>
        <?php endif; ?>

        <div class="card">
            <h3>Apparence</h3>
            <form action="<?= BASE_URL ?>settings/update" method="post">
                <label>Thème</label>
                <div class="theme-options">
                    <label><input type="radio" name="theme" value="dark" <?= $user['theme'] === 'dark' ? 'checked' : '' ?>>
                        Sombre</label>
                    <label><input type="radio" name="theme" value="light" <?= $user['theme'] === 'light' ? 'checked' : '' ?>> Clair</label>
                </div>

                <h3>Notifications</h3>
                <label><input type="checkbox" name="notif_email" <?= $user['notif_email'] ? 'checked' : '' ?>>
                    Notifications par email</label>
                <label><input type="checkbox" name="notif_push" <?= $user['notif_push'] ? 'checked' : '' ?>>
                    Notifications push</label>

                <div style="margin-top:12px;">
                    <button class="btn primary" type="submit">Enregistrer les paramètres</button>
                </div>
            </form>
        </div>

        <div class="card">
            <h3>Thème rapide</h3>
            <button id="toggleTheme" class="btn">Basculer le thème</button>
        </div>

    </section>

    <?php include __DIR__ . '/../../layouts/footer.php'; ?>

    <script>
        document.getElementById('toggleTheme').addEventListener('click', function () {
            fetch('<?= BASE_URL ?>settings/toggle-theme', { method: 'POST' })
                .then(r => r.json())
                .then(data => {
                    document.body.className = 'theme-' + data.theme;
                });
        });
    </script>
</body>

</html>