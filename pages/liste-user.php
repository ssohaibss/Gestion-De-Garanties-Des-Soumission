<?php
require_once dirname(__DIR__) . '/database.php';
$pdo = getDBConnection();

$query = "SELECT * FROM utilisateur ORDER BY nom ASC";
$result = $pdo->query($query);
$users = $result->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="page-title"><i class="fas fa-users me-2"></i>Liste des Utilisateurs</h1>
        <a href="index.php?page=user" class="btn ajouter">
            <i class="fas fa-plus"></i> Ajouter un Utilisateur
        </a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header">
        <i class="fas fa-list me-2"></i>Tous les Utilisateurs
    </div>
    <div class="card-body">
        <?php if (count($users) > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nom Complet</th>
                        <th>Nom d'Utilisateur</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th class="text-center" style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($u['nom'] . ' ' . $u['prenom']); ?> </strong></td>
                            <td><?php echo htmlspecialchars($u['username']); ?></td>
                            <td><?php echo htmlspecialchars($u['email'] ?? ''); ?></td>
                            <td><span class="badge bg-info"><?php echo htmlspecialchars($u['role'] ?? 'user'); ?></span></td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <button class="btn btn-sm eye text-white edit-user" 
                                            data-user='<?= htmlspecialchars(json_encode($u), ENT_QUOTES, 'UTF-8') ?>'>
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-user" 
                                            data-id="<?php echo $u['id']; ?>" 
                                            data-nom="<?php echo htmlspecialchars($u['nom']); ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="text-center py-4"><p class="text-muted">Aucun utilisateur enregistré.</p></div>
        <?php endif; ?>
    </div>
</div>

<script>
document.querySelectorAll('.edit-user').forEach(btn => {
    btn.addEventListener('click', function() {
        const u = JSON.parse(this.dataset.user);
        window.location.href = 'index.php?page=user&edit=' + u.id;
    });
});

document.querySelectorAll('.delete-user').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        const nom = this.dataset.nom;

        Swal.fire({
            title: 'Êtes-vous sûr ?',
            text: `Supprimer l'utilisateur "${nom}" ?`,
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
                    }
                } catch (err) { Swal.fire('Erreur', 'Lien rompu', 'error'); }
            }
        });
    });
});
</script>
