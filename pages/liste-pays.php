<?php
require_once dirname(__DIR__) . '/database.php';
$pdo = getDBConnection();
$countries = $pdo->query("SELECT * FROM pays ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <h2 class="page-title"><i class="fas fa-map-marker-alt me-2"></i>Liste des Pays</h2>
        <a href="index.php?page=pays" class="btn ajouter text-white shadow-sm" style="background-color: #486a70;">
            <i class="fas fa-plus me-2"></i>Ajouter un Pays
        </a>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header text-white fw-bold" style="background-color: #486a70;">
        <i class="fas fa-list me-2"></i>Répertoire des Pays
    </div>
    <div class="card-body p-0">
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
                    <?php foreach ($countries as $c): ?>
                    <tr>
                        <td class="ps-3 fw-bold"><?= htmlspecialchars($c['nom']) ?></td>
                        <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($c['code_pays']) ?></span></td>
                        <td class="text-center">
                            <div class="btn-group shadow-sm">
                                <a href="index.php?page=pays&edit=<?= $c['id'] ?>" class="btn btn-sm text-white" style="background-color: #486a70;">
                                    <i class="fas fa-pencil-alt"></i>
                                </a>
                                <button class="btn btn-sm btn-danger delete-btn" data-id="<?= $c['id'] ?>" data-nom="<?= htmlspecialchars($c['nom']) ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        const nom = this.dataset.nom;

        Swal.fire({
            title: 'Supprimer ?',
            text: `Voulez-vous supprimer le pays "${nom}" ?`,
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
                        await Swal.fire({ icon: 'success', title: 'Pays supprimé !', timer: 1500, showConfirmButton: false, timerProgressBar: true });
                        location.reload();
                    } else {
                        Swal.fire('Attention', data.message, 'warning');
                    }
                } catch (e) { console.error(e); }
            }
        });
    });
});
</script>