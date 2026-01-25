<?php
require_once dirname(__DIR__) . '/database.php';
$pdo = getDBConnection();

$banks = $pdo->query("SELECT * FROM banque ORDER BY nom_banque ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <h2 class="page-title"><i class="fas fa-university me-2"></i>Liste des Banques</h2>
        <a href="index.php?page=banque" class="btn ajouter shadow-sm">
            <i class="fas fa-plus"></i> Ajouter une Banque
        </a>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header text-white fw-bold" style="background-color: #486a70;">
        <i class="fas fa-list me-2"></i>Toutes les Banques
    </div>
    <div class="card-body p-0">
        <?php if (count($banks) > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="color: #333;">Code</th>
                        <th style="color: #333;">Nom de la Banque</th>
                        <th class="text-center" style="width: 120px; color: #333;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($banks as $b): ?>
                        <tr>
                            <td class="ps-3"><span class="badge bg-light text-dark border"><?= htmlspecialchars($b['code']) ?></span></td>
                            <td><strong><?= htmlspecialchars($b['nom_banque']) ?></strong></td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <a href="index.php?page=banque&edit=<?= $b['id'] ?>" class="btn btn-sm ajouter text-white">
                                        <i class="fas fa-pencil-alt"></i>
                                    </a>
                                    <button class="btn btn-sm btn-danger delete-banque" 
                                            data-id="<?= $b['id'] ?>" 
                                            data-nom="<?= htmlspecialchars($b['nom_banque']) ?>">
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
        <div class="text-center py-5">
            <p class="text-muted">Aucune banque enregistrée.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.querySelectorAll('.delete-banque').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        const nom = this.dataset.nom;

        Swal.fire({
            title: 'Supprimer ?',
            text: `Voulez-vous supprimer la banque "${nom}" ?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#486a70',
            confirmButtonText: 'Oui, supprimer',
            timerProgressBar: true
        }).then(async (result) => {
            if (result.isConfirmed) {
                const fd = new FormData();
                fd.append('form_type', 'delete_banque');
                fd.append('id', id);
                try {
                    const res = await fetch('process.php', { method: 'POST', body: fd });
                    const data = await res.json();
                    if (data.ok) {
                        await Swal.fire({ icon: 'success', title: 'Supprimé !', timer: 1500, showConfirmButton: false, timerProgressBar: true });
                        location.reload();
                    }
                } catch (err) { Swal.fire('Erreur', 'Lien rompu', 'error'); }
            }
        });
    });
});
</script>