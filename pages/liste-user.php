<?php
require_once dirname(__DIR__) . '/database.php';
$pdo = getDBConnection();

// On récupère les utilisateurs avec le libellé de leur rôle
$query = "SELECT u.*, r.libelle as role_nom 
          FROM utilisateur u 
          LEFT JOIN role r ON u.roleID = r.id 
          ORDER BY u.nom ASC";
$result = $pdo->query($query);
$users = $result->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="page-title"><i class="fas fa-users me-2"></i>Liste des Utilisateurs</h1>
        <a href="index.php?page=user" class="btn ajouter shadow-sm">
            <i class="fas fa-plus"></i> Ajouter un Utilisateur
        </a>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header text-white fw-bold" style="background-color: #486a70;">
        <i class="fas fa-list me-2"></i>Tous les Utilisateurs
    </div>
    <div class="card-body p-0">
        <?php if (count($users) > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="color: #333;">Nom Complet</th>
                        <th style="color: #333;">Nom d'Utilisateur</th>
                        <th style="color: #333;">Email</th>
                        <th style="color: #333;">Rôle</th>
                        <th class="text-center" style="width: 120px; color: #333;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td class="ps-3"><strong><?php echo htmlspecialchars($u['nom'] . ' ' . $u['prenom']); ?> </strong></td>
                            <td><?php echo htmlspecialchars($u['username']); ?></td>
                            <td><?php echo htmlspecialchars($u['email'] ?? ''); ?></td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <?php echo htmlspecialchars($u['role_nom'] ?? 'Utilisateur'); ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <a href="index.php?page=user&edit=<?php echo $u['id']; ?>" class="btn btn-sm ajouter text-white">
                                        <i class="fas fa-pencil-alt"></i>
                                    </a>
                                    
                                    <?php if (isset($_SESSION['user_id']) && $u['id'] != $_SESSION['user_id']): ?>
                                    <button class="btn btn-sm btn-danger delete-user" 
                                            data-id="<?php echo $u['id']; ?>" 
                                            data-nom="<?php echo htmlspecialchars($u['username']); ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-user-slash fa-3x text-muted mb-3"></i>
            <p class="text-muted">Aucun utilisateur enregistré.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.querySelectorAll('.delete-user').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        const nom = this.dataset.nom;

        Swal.fire({
            title: 'Supprimer ?',
            text: `Voulez-vous vraiment supprimer l'utilisateur "${nom}" ?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#486a70',
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler'
        }).then(async (result) => {
            if (result.isConfirmed) {
                const fd = new FormData();
                fd.append('form_type', 'delete_user');
                fd.append('id', id);
                try {
                    const res = await fetch('process.php', { method: 'POST', body: fd });
                    const data = await res.json();
                    if (data.ok) {
                        await Swal.fire({ 
                            icon: 'success', 
                            title: 'Supprimé !', 
                            timer: 1500, 
                            showConfirmButton: false,
                            timerProgressBar: true 
                        });
                        location.reload();
                    } else {
                        Swal.fire('Erreur', data.message || 'Impossible de supprimer', 'error');
                    }
                } catch (err) { 
                    Swal.fire('Erreur', 'Lien rompu avec le serveur.', 'error'); 
                }
            }
        });
    });
});
</script>
