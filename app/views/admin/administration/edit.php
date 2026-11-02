<?php
if (!isset($admin)) {
    header('Location: '.BASE_URL.'admin/administration');
    exit;
}
require_once __DIR__ . '/../layouts/header_admin.php';
?>
<div class="clients-container">
    <h2>Modifier administrateur</h2>
    <form method="post" action="<?= BASE_URL ?>admin/administration/update">
        <input type="hidden" name="id" value="<?= $admin['id'] ?>">
        <div class="form-row">
            <label>Nom complet</label>
            <input type="text" name="nom_complet" value="<?= htmlspecialchars($admin['nom_complet']) ?>" required>
        </div>
        <div class="form-row">
            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($admin['email']) ?>" required>
        </div>
        <div class="form-row">
            <label>Mot de passe (laisser vide pour ne pas changer)</label>
            <input type="password" name="mot_de_passe">
        </div>
        <div class="form-row">
            <label>Role</label>
            <select name="role">
                <option value="admin" <?= ($admin['role'] === 'admin') ? 'selected' : '' ?>>Admin</option>
                <option value="superadmin" <?= ($admin['role'] === 'superadmin') ? 'selected' : '' ?>>Superadmin</option>
            </select>
        </div>
        <div class="form-row">
            <label>Statut</label>
            <select name="statut">
                <option value="active" <?= ($admin['statut'] === 'active' || $admin['statut'] === '1') ? 'selected' : '' ?>>Actif</option>
                <option value="inactive" <?= ($admin['statut'] === 'inactive' || $admin['statut'] === '0') ? 'selected' : '' ?>>Inactif</option>
            </select>
        </div>
        <div class="form-actions">
            <a href="<?= BASE_URL ?>admin/administration" class="btn btn-secondary">Annuler</a>
            <button class="btn btn-primary" type="submit">Enregistrer</button>
        </div>
    </form>
</div>
<?php require_once __DIR__ . '/../layouts/footer_admin.php'; ?>