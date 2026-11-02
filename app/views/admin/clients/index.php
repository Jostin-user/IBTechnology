<?php

// Gestion des actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    switch ($_GET['action']) {
        case 'toggle_status':
            // Toggle user status (active/inactive)
            $user = $userModel->trouverParEmail(''); // Need to get user by ID, but model doesn't have it
            // For now, assume we have a method
            break;
        case 'delete':
            // Soft delete or deactivate user
            break;
    }
}

function getInitials($name)
{
    $parts = explode(' ', $name);
    return strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Clients - Administration</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin/admin.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin/clients.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin/clients-dark.css">

<body>
    <?php require_once __DIR__ . '/../layouts/header_admin.php'; ?>
    <div class="clients-container">
        <div class="clients-header">
            <h1><i class="fas fa-users"></i> Gestion des Clients</h1>
        </div>

        <!-- Statistiques -->
        <div class="stats-cards">
            <div class="stat-card">
                <h3>Total Clients</h3>
                <div class="stat-number"><?php echo $totalClients; ?></div>
            </div>
            <div class="stat-card">
                <h3>Clients Actifs</h3>
                <div class="stat-number"><?php echo $clientsActifs; ?></div>
            </div>
            <div class="stat-card">
                <h3>Nouveaux ce mois</h3>
                <div class="stat-number"><?php echo $nouveauxCeMois; ?></div>
            </div>
            <div class="stat-card">
                <h3>Taux d'activité</h3>
                <div class="stat-number">
                    <?php echo $totalClients > 0 ? round(($clientsActifs / $totalClients) * 100) : 0; ?>%
                </div>
            </div>
        </div>

        <div class="search-filter-section">
            <form class="search-box" onsubmit="return doSearch(event)">
                <input type="text" name="search" placeholder="Rechercher par nom ou email..."
                    value="<?php echo htmlspecialchars($search); ?>" id="searchInput">
                <button type="submit" class="search-btn"><i class="fas fa-search"></i></button>
            </form>
            <div class="filters">
                <a href="?filter=all<?php echo $search ? '&search=' . urlencode($search) : ''; ?>"
                    class="filter-btn <?php echo $filter === 'all' ? 'active' : ''; ?>">
                    Tous (<?php echo $totalClients; ?>)
                </a>
                <a href="?filter=active<?php echo $search ? '&search=' . urlencode($search) : ''; ?>"
                    class="filter-btn <?php echo $filter === 'active' ? 'active' : ''; ?>">
                    Actifs
                </a>
                <a href="?filter=inactive<?php echo $search ? '&search=' . urlencode($search) : ''; ?>"
                    class="filter-btn <?php echo $filter === 'inactive' ? 'active' : ''; ?>">
                    Inactifs
                </a>
            </div>
        </div>

        <!-- Messages -->
        <?php if (isset($_GET['message'])): ?>
            <div style="background: #10b981; color: white; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_GET['message']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div style="background: #ef4444; color: white; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Table des clients -->
        <?php if (!empty($clients)): ?>
            <div class="clients-table-container">
                <table class="clients-table">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Email</th>
                            <th>Date d'inscription</th>
                            <th>Commandes</th>
                            <th>Dernière connexion</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clients as $client): ?>
                            <?php
                            if ($client): ?>
                                <tr>
                                    <td>
                                        <div class="client-info">
                                            <div class="client-avatar">
                                                <?php echo getInitials($client['nom_complet']); ?>
                                            </div>
                                            <div class="client-details">
                                                <h4><?php echo htmlspecialchars($client['nom_complet']); ?></h4>
                                                <p>ID: <?php echo $client['id']; ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($client['email']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($client['date_creation'])); ?></td>
                                    <td><?php echo $client['commandes_count']; ?> commande(s)</td>
                                    <td><?php echo $client['dernier_connexion'] ? date('d/m/Y', strtotime($client['dernier_connexion'])) : 'Jamais'; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $isActive = ($client['statut'] === '1' || strtolower($client['statut']) === 'active');
                                        ?>
                                        <span class="status-badge <?php echo $isActive ? 'status-active' : 'status-inactive'; ?>">
                                            <?php echo $isActive ? 'Actif' : 'Inactif'; ?>
                                        </span>
                                    </td>
                                    <td class="actions-column">
                                        <a href="clients/details?id=<?php echo $client['id']; ?>" class="action-btn view"
                                            title="Voir détails">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="#" class="action-btn edit" title="Modifier"
                                            data-id="<?php echo $client['id']; ?>"
                                            data-name="<?php echo htmlspecialchars($client['nom_complet'], ENT_QUOTES); ?>"
                                            data-email="<?php echo htmlspecialchars($client['email'], ENT_QUOTES); ?>"
                                            data-phone="<?php echo htmlspecialchars($client['telephone'] ?? '', ENT_QUOTES); ?>"
                                            data-address="<?php echo htmlspecialchars($client['adresse'] ?? '', ENT_QUOTES); ?>"
                                            data-statut="<?php echo htmlspecialchars($client['statut']); ?>"
                                            onclick="openEditModal(this); return false;">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>admin/clients/delete?id=<?php echo $client['id']; ?>" class="action-btn delete" title="Supprimer/Désactiver"
                                            onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce client ? Cette action est définitive si le client n\'a pas de commandes.');">
                                            <i class="fas fa-user-times"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php
                            endif;
                        endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&filter=<?php echo $filter; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>"
                            class="pagination-btn">
                            <i class="fas fa-chevron-left"></i> Précédent
                        </a>
                    <?php endif; ?>

                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <a href="?page=<?php echo $page + 1; ?>filter=<?php echo $filter; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>"
                            class="pagination-btn <?php echo $i === $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&filter=<?php echo $filter; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>"
                            class="pagination-btn">
                            Suivant <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="no-clients">
                <i class="fas fa-users" style="font-size: 3rem; color: #9ca3af; margin-bottom: 20px;"></i>
                <h3>Aucun client trouvé</h3>
                <p>Il n'y a pas de clients correspondant à vos critères de recherche.</p>
                <a href="?filter=all" class="filter-btn active" style="margin-top: 15px;">Voir tous les clients</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Edit client modal -->
    <div id="editModale" class="modal" style="display:none;">
        <div class="modal-content">
            <button class="modal-close" onclick="closeEditModale()">&times;</button>
            <h2>Modifier le client</h2>
            <form id="editForm" method="post" action="<?= BASE_URL ?>admin/clients/update">
                <input type="hidden" name="id" id="clientId">
                <div class="form-row">
                    <label>Nom complet</label>
                    <input type="text" name="nom_complet" id="clientName" required>
                </div>
                <div class="form-row">
                    <label>Email</label>
                    <input type="email" name="email" id="clientEmail" required>
                </div>
                <div class="form-row">
                    <label>Téléphone</label>
                    <input type="text" name="telephone" id="clientPhone">
                </div>
                <div class="form-row">
                    <label>Adresse</label>
                    <input type="text" name="adresse" id="clientAddress">
                </div>
                <div class="form-row">
                    <label>Statut</label>
                    <select name="statut" id="clientStatus">
                        <option value="active">Actif</option>
                        <option value="inactive">Inactif</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModale()">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function doSearch(e) {
            e.preventDefault();
            const input = document.getElementById('searchInput');
            const searchTerm = input.value.trim();
            const currentUrl = new URL(window.location);
            if (searchTerm) {
                currentUrl.searchParams.set('search', searchTerm);
            } else {
                currentUrl.searchParams.delete('search');
            }
            currentUrl.searchParams.set('page', '1');
            window.location.href = currentUrl.toString();
            return false;
        }

        function openEditModal(el) {
            const id = el.getAttribute('data-id');
            const name = el.getAttribute('data-name');
            const email = el.getAttribute('data-email');
            const phone = el.getAttribute('data-phone');
            const address = el.getAttribute('data-address');
            const statut = el.getAttribute('data-statut');

            document.getElementById('clientId').value = id;
            document.getElementById('clientName').value = name;
            document.getElementById('clientEmail').value = email;
            document.getElementById('clientPhone').value = phone;
            document.getElementById('clientAddress').value = address;
            document.getElementById('clientStatus').value = (statut === '1') ? 'active' : (statut === 'active' ? 'active' : 'inactive');

            document.getElementById('editModale').style.display = 'block';
        }

        function closeEditModale() {
             document.getElementById('editModale').style.display = 'none';
        }

        // Close modal on outside click
        window.addEventListener('click', function (e) {
            const modal = document.getElementById('editModal');
            if (e.target === modal) closeEditModal();
        });
    </script>
    <?php require_once __DIR__.'/../layouts/footer_admin.php' ;?>
</body>

</html>