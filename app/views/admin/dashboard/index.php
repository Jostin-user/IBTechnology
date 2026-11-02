<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard admin</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin/admin.css">
</head>

<body>
    <?php
    require_once __DIR__ . '/../layouts/header_admin.php';
    ?>

    <h1>Tableau de bord administrateur</h1>

    <div class="dashboard-grid">

        <div class="card">
            <h3>Produits</h3>
            <p><?= $data['produits']; ?></p>
        </div>

        <div class="card warning">
            <h3>Stock bas</h3>
            <p><?= $data['stockBas']; ?></p>
        </div>

        <div class="card">
            <h3>Commandes</h3>
            <p><?= $data['commandes']; ?></p>
        </div>

        <div class="card">
            <h3>Commandes en attente</h3>
            <p><?= $data['commandesAttente']; ?></p>
        </div>

        <div class="card success">
            <h3>Chiffre d’affaires</h3>
            <p><?= number_format($data['chiffreAffaires']); ?> FCFA</p>
        </div>

        <div class="card">
            <h3>Clients</h3>
            <p><?= $data['clients']; ?></p>
        </div>
        <div class="card">
            <h3><?= $stats['visites_today'] ?></h3>
            <p>Visites aujourd'hui</p>
            <small>
                <?= $stats['visites_30'] ?> visites (30 derniers jours)
            </small>
        </div>

    </div>

    <?php require_once __DIR__ . '/../layouts/footer_admin.php'; ?>
</body>

</html>