<?php
require_once dirname(__DIR__) . '/database.php';
$pdo = getDBConnection();

$query = "SELECT * FROM structure ORDER BY libelle ASC";
$result = $pdo->query($query);
$structures = $result->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="page-title"><i class="fas fa-building me-2"></i>Liste des Structures</h1>
        <a href="index.php?page=structure" class="btn ajouter">
            <i class="fas fa-plus"></i> Ajouter une Structure
        </a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header">
        <i class="fas fa-list me-2"></i>Toutes les Structures
    </div>
    <div class="card-body">
        <?php if (count($structures) > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Libellé</th>
                        <th>Description</th>
                        <th class="text-center" style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($structures as $s): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($s['libelle']); ?></strong></td>
                            <td><?php echo htmlspecialchars($s['description'] ?? ''); ?></td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <button class="btn btn-sm eye text-white edit-structure" 
                                            data-structure='<?= htmlspecialchars(json_encode($s), ENT_QUOTES, 'UTF-8') ?>'>
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-structure" 
                                            data-id="<?php echo $s['id']; ?>" 
                                            data-libelle="<?php echo htmlspecialchars($s['libelle']); ?>">
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
        <div class="text-center py-4"><p class="text-muted">Aucune structure enregistrée.</p></div>
        <?php endif; ?>
    </div>
</div>

<script>
document.querySelectorAll('.edit-structure').forEach(btn => {
    btn.addEventListener('click', function() {
        const s = JSON.parse(this.dataset.structure);
        window.location.href = 'index.php?page=structure&edit=' + s.id;
    });
});

document.querySelectorAll('.delete-structure').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        const libelle = this.dataset.libelle;

        Swal.fire({
            title: 'Êtes-vous sûr ?',
            text: `Supprimer la structure "${libelle}" ?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#486a70',
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler'
        }).then(async (result) => {
            if (result.isConfirmed) {
                const fd = new FormData();
                fd.append('form_type', 'delete_structure');
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
