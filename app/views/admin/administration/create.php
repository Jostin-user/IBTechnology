<?php require_once __DIR__ . '/../layouts/header_admin.php'; ?>
<div class="clients-container">
    <h2>Ajouter un administrateur</h2>
    <form method="post" action="<?= BASE_URL ?>admin/administration/store">
        <div class="form-row">
            <label>Nom complet</label>
            <input type="text" name="nom_complet" required>
        </div>
        <div class="form-row">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>
        <div class="form-row">
            <label>Mot de passe</label>
            <input type="password" name="mot_de_passe" required>
        </div>
        <div class="form-row">
            <label>Role</label>
            <select name="role">
                <option value="admin">Admin</option>
                <option value="superadmin">Superadmin</option>
            </select>
        </div>
        <div class="form-row">
            <label>Statut</label>
            <select name="statut">
                <option value="active">Actif</option>
                <option value="inactive">Inactif</option>
            </select>
        </div>
        <div class="form-actions">
            <a href="<?= BASE_URL ?>admin/administration" class="btn btn-secondary">Annuler</a>
            <button class="btn btn-primary" type="submit">Créer</button>
        </div>
    </form>
</div>
<?php require_once __DIR__ . '/../layouts/footer_admin.php'; ?>