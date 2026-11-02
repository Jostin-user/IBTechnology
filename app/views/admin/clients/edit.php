<?php
if (!isset($client)) {
    header('Location: '.BASE_URL.'admin/clients');
    exit;
}
require_once __DIR__ . '/../layouts/header_admin.php';
?>
<div class="clients-container">
    <h1>Modifier le client</h1>
    <form method="post" action="<?= BASE_URL ?>admin/clients/update">
        <input type="hidden" name="id" value="<?= $client['id'] ?>">
        <div class="form-row">
            <label>Nom complet</label>
            <input type="text" name="nom_complet" value="<?= htmlspecialchars($client['nom_complet']) ?>" required>
        </div>
        <div class="form-row">
            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($client['email']) ?>" required>
        </div>
        <div class="form-row">
            <label>Téléphone</label>
            <input type="text" name="telephone" value="<?= htmlspecialchars($client['telephone'] ?? '') ?>">
        </div>
        <div class="form-row">
            <label>Adresse</label>
            <input type="text" name="adresse" value="<?= htmlspecialchars($client['adresse'] ?? '') ?>">
        </div>
        <div class="form-row">
            <label>Statut</label>
            <select name="statut">
                <option value="active" <?= ($client['statut'] === 'active' || $client['statut'] === '1') ? 'selected' : '' ?>>Actif</option>
                <option value="inactive" <?= ($client['statut'] === 'inactive' || $client['statut'] === '0') ? 'selected' : '' ?>>Inactif</option>
            </select>
        </div>
        <div class="form-actions">
            <a href="<?= BASE_URL ?>admin/clients" class="btn btn-secondary">Annuler</a>
            <button class="btn btn-primary" type="submit">Enregistrer</button>
        </div>
    </form>
</div>
<?php require_once __DIR__ . '/../layouts/footer_admin.php'; ?>
