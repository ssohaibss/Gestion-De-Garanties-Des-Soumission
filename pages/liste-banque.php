<?php
require_once dirname(__DIR__) . '/database.php';
$pdo = getDBConnection();
$banks = $pdo->query("SELECT * FROM banque ORDER BY nom_banque ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <h2 class="page-title"><i class="fas fa-university me-2"></i>Liste des Banques</h2>
        <a href="index.php?page=banque" class="btn ajouter text-white shadow-sm" style="background-color: #486a70;">
            <i class="fas fa-plus me-2"></i>Ajouter une Banque
        </a>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header text-white fw-bold" style="background-color: #486a70;">
        <i class="fas fa-list me-2"></i>Répertoire des Institutions Bancaires
    </div>
    <div class="card-body p-0">
        <?php if (!empty($banks)): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width: 150px;">Code</th>
                        <th>Nom de la Banque</th>
                        <th class="text-center" style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($banks as $b): ?>
                        <tr>
                            <td class="ps-3"><span class="badge bg-light text-dark border fw-bold"><?= htmlspecialchars($b['code']) ?></span></td>
                            <td><strong><?= htmlspecialchars($b['nom_banque']) ?></strong></td>
                            <td class="text-center">
                                <div class="btn-group shadow-sm">
                                    <a href="index.php?page=banque&edit=<?= $b['id'] ?>" class="btn btn-sm text-white" style="background-color: #486a70;" title="Modifier">
                                        <i class="fas fa-pencil-alt"></i>
                                    </a>
                                    <button class="btn btn-sm btn-danger delete-btn" data-id="<?= $b['id'] ?>" data-nom="<?= htmlspecialchars($b['nom_banque']) ?>" title="Supprimer">
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
            <i class="fas fa-university fa-3x text-muted mb-3 opacity-25"></i>
            <p class="text-muted">Aucune banque n'est enregistrée dans le système.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', async function() {
        const id = this.dataset.id;
        const nom = this.dataset.nom;

        const fdCheck = new FormData();
        fdCheck.append('form_type', 'check_linked_garanties');
        fdCheck.append('type', 'banque');
        fdCheck.append('id', id);

        try {
            const resCheck = await fetch('process.php', { method: 'POST', body: fdCheck });
            const dataCheck = await resCheck.json();
            
            let htmlText = `<p>Voulez-vous supprimer la banque "<b>${nom}</b>" ?</p>`;
            
            if (dataCheck.ok && dataCheck.garanties.length > 0) {
                const list = dataCheck.garanties.map(g => `<li>${g}</li>`).join('');
                htmlText = `<div class="text-start">
                    <p class="text-danger fw-bold"><i class="fas fa-exclamation-triangle"></i> ATTENTION ! Vous allez supprimer cette banque ET toutes ses agences.</p>
                    <p>Cela supprimera également <b>TOUTES</b> ces garanties :</p>
                    <ul style="max-height: 120px; overflow-y: auto;" class="text-danger fw-bold">${list}</ul>
                </div>`;
            }

            Swal.fire({
                title: 'Confirmer la suppression',
                html: htmlText,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#486a70',
                confirmButtonText: 'Oui, tout supprimer',
                cancelButtonText: 'Annuler'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    const fd = new FormData();
                    fd.append('form_type', 'delete_banque');
                    fd.append('id', id);
                    const res = await fetch('process.php', { method: 'POST', body: fd });
                    const data = await res.json();
                    if (data.ok) {
                        await Swal.fire({ icon: 'success', title: 'Supprimée !', timer: 1500, showConfirmButton: false });
                        location.reload();
                    } else {
                        Swal.fire('Erreur', data.message, 'error');
                    }
                }
            });
        } catch (err) { Swal.fire('Erreur', 'Impossible de vérifier les liaisons.', 'error'); }
    });
});
</script>