<?php
require_once dirname(__DIR__) . '/database.php';
$pdo = getDBConnection();

$countries = $pdo->query("SELECT * FROM pays ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <h2 class="page-title"><i class="fas fa-globe me-2"></i>Liste des Pays</h2>
        <a href="index.php?page=pays" class="btn ajouter shadow-sm">
            <i class="fas fa-plus me-2"></i>Ajouter un Pays
        </a>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white fw-bold">
        <i class="fas fa-list me-2"></i>Tous les Pays
    </div>
    <div class="card-body p-0">
        <?php if (!empty($countries)): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Nom du Pays</th>
                        <th>Code ISO</th>
                        <th class="text-center" style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($countries as $country): ?>
                        <tr>
                            <td class="ps-3"><strong><?= htmlspecialchars($country['nom']); ?></strong></td>
                            <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($country['code_pays']); ?></span></td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <button class="btn btn-sm ajouter text-white edit-pays" 
                                            data-id="<?= $country['id']; ?>">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-pays" 
                                            data-id="<?= $country['id']; ?>" 
                                            data-nom="<?= htmlspecialchars($country['nom']); ?>">
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
        <div class="text-center py-4"><p class="text-muted mb-0">Aucun pays enregistré.</p></div>
        <?php endif; ?>
    </div>
</div>

<script>
document.querySelectorAll('.edit-pays').forEach(btn => {
    btn.addEventListener('click', function() {
        window.location.href = 'index.php?page=pays&edit=' + this.dataset.id;
    });
});

document.querySelectorAll('.delete-pays').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        const nom = this.dataset.nom;

        Swal.fire({
            title: 'Confirmer',
            text: `Supprimer le pays "${nom}" ?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#486a70',
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler'
        }).then(async (result) => {
            if (result.isConfirmed) {
                const fd = new FormData();
                fd.append('form_type', 'delete_pays');
                fd.append('id', id);
                try {
                    const res = await fetch('process.php', { method: 'POST', body: fd });
                    const data = await res.json();
                    if (data.ok) {
                        await Swal.fire({ 
                            icon: 'success', title: 'Supprimé !', 
                            timer: 1500, showConfirmButton: false, timerProgressBar: true 
                        });
                        location.reload();
                    }
                } catch (err) { }
            }
        });
    });
});
</script>