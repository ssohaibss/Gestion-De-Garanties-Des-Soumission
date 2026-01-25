<?php
require_once dirname(__DIR__) . '/database.php';
$pdo = getDBConnection();

$query = "SELECT * FROM devise ORDER BY libelle ASC";
$result = $pdo->query($query);
$currencies = $result->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="page-title"><i class="fas fa-money-bill me-2"></i>Liste des Devises</h1>
        <a href="index.php?page=devise" class="btn ajouter">
            <i class="fas fa-plus"></i> Ajouter une Devise
        </a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header">
        <i class="fas fa-list me-2"></i>Toutes les Devises
    </div>
    <div class="card-body">
        <?php if (count($currencies) > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nom de la Devise</th>
                        <th>Code ISO</th>
                        <th class="text-center" style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($currencies as $c): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($c['libelle']); ?></strong></td>
                            <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($c['code']); ?></span></td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <button class="btn btn-sm eye text-white edit-devise" 
                                            data-devise='<?= htmlspecialchars(json_encode($c), ENT_QUOTES, 'UTF-8') ?>'>
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-devise" 
                                            data-id="<?php echo $c['id']; ?>" 
                                            data-libelle="<?php echo htmlspecialchars($c['libelle']); ?>">
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
        <div class="text-center py-4"><p class="text-muted">Aucune devise enregistrée.</p></div>
        <?php endif; ?>
    </div>
</div>

<script>
document.querySelectorAll('.edit-devise').forEach(btn => {
    btn.addEventListener('click', function() {
        const c = JSON.parse(this.dataset.devise);
        window.location.href = 'index.php?page=devise&edit=' + c.id;
    });
});

document.querySelectorAll('.delete-devise').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        const libelle = this.dataset.libelle;

        Swal.fire({
            title: 'Êtes-vous sûr ?',
            text: `Supprimer la devise "${libelle}" ?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#486a70',
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler'
        }).then(async (result) => {
            if (result.isConfirmed) {
                const fd = new FormData();
                fd.append('form_type', 'delete_devise');
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
