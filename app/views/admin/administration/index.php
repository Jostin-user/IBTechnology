<?php
// $admins expected from controller
if (!isset($admins)) $admins = [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Gestion des administrateurs</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin/admin.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin/clients-dark.css">
</head>
<body>
<?php require_once __DIR__ . '/../layouts/header_admin.php'; ?>
<div class="clients-container">
    <div class="clients-header">
        <h1>Gestion des administrateurs</h1>
        <a href="<?= BASE_URL ?>admin/administration/create" class="btn btn-primary">Ajouter un administrateur</a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="stat-card" style="border-left:4px solid var(--success);margin-bottom:12px;padding:10px;">
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="stat-card" style="border-left:4px solid var(--danger);margin-bottom:12px;padding:10px;">
            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <?php if (count($admins) === 0): ?>
        <div class="no-clients">
            <i class="fas fa-user-shield" style="font-size:2.5rem;color:var(--muted)"></i>
            <h3>Aucun administrateur trouvé</h3>
        </div>
    <?php else: ?>
        <div class="clients-table-container">
            <table class="clients-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Date création</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($admins as $a): ?>
                        <tr>
                            <td><?= htmlspecialchars($a['nom_complet']); ?></td>
                            <td><?= htmlspecialchars($a['email']); ?></td>
                            <td><?= htmlspecialchars($a['role']); ?></td>
                            <td><?= date('d/m/Y', strtotime($a['date_creation'] ?? 'now')); ?></td>
                            <td><span class="status-badge <?= ($a['statut']==='active' || $a['statut']=='1') ? 'status-active' : 'status-inactive' ?>"><?= ($a['statut']==='active' || $a['statut']=='1') ? 'Actif' : 'Inactif' ?></span></td>
                            <td>
                                <a href="<?= BASE_URL ?>admin/administration/edit?id=<?= $a['id'] ?>" class="action-btn edit" title="Modifier"><i class="fas fa-edit"></i></a>
                                <a href="<?= BASE_URL ?>admin/administration/toggle-status?id=<?= $a['id'] ?>" class="action-btn" onclick="return confirm('Changer le statut ?')"><i class="fas fa-toggle-on"></i></a>
                                <a href="<?= BASE_URL ?>admin/administration/delete?id=<?= $a['id'] ?>" class="action-btn delete" onclick="return confirm('Supprimer cet administrateur ?')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../layouts/footer_admin.php'; ?>
</body>
</html>
