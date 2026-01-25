<?php
require_once dirname(__DIR__) . '/database.php';
$pdo = getDBConnection();

$query = "SELECT * FROM banque ORDER BY nom_banque ASC";
$result = $pdo->query($query);
$banks = $result->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="page-title"><i class="fas fa-university me-2"></i>Liste des Banques</h1>
        <a href="index.php?page=banque" class="btn ajouter">
            <i class="fas fa-plus"></i> Ajouter une Banque
        </a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header">
        <i class="fas fa-list me-2"></i>Toutes les Banques
    </div>
    <div class="card-body">
        <?php if (count($banks) > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Code</th>
                        <th>Nom de la Banque</th>
                        <th class="text-center" style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($banks as $b): ?>
                        <tr>
                            <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($b['code'] ?? 'N/A'); ?></span></td>
                            <td><strong><?php echo htmlspecialchars($b['nom_banque']); ?></strong></td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <button class="btn btn-sm eye text-white edit-banque" 
                                            data-banque='<?= htmlspecialchars(json_encode($b), ENT_QUOTES, 'UTF-8') ?>'>
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-banque" 
                                            data-id="<?php echo $b['id'] ?? $b['Id']; ?>" 
                                            data-nom="<?php echo htmlspecialchars($b['nom_banque']); ?>">
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
        <div class="text-center py-4"><p class="text-muted">Aucune banque enregistrée.</p></div>
        <?php endif; ?>
    </div>
</div>

<script>
document.querySelectorAll('.edit-banque').forEach(btn => {
    btn.addEventListener('click', function() {
        const b = JSON.parse(this.dataset.banque);
        window.location.href = 'index.php?page=banque&edit=' + (b.id ?? b.Id);
    });
});

document.querySelectorAll('.delete-banque').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        const nom = this.dataset.nom;

        Swal.fire({
            title: 'Êtes-vous sûr ?',
            text: `Supprimer la banque "${nom}" ?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#486a70',
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler'
        }).then(async (result) => {
            if (result.isConfirmed) {
                const fd = new FormData();
                fd.append('form_type', 'delete_banque');
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
