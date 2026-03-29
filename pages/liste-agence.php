<?php
require_once dirname(__DIR__) . '/database.php';
$pdo = getDBConnection();

$query = "SELECT a.*, b.nom_banque, b.code as code_banque 
          FROM agence a 
          LEFT JOIN banque b ON a.banqueID = b.id 
          ORDER BY a.nom ASC";
$agencies = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <h2 class="page-title"><i class="fas fa-map-marked-alt me-2"></i>Liste des Agences</h2>
        <a href="index.php?page=agence" class="btn ajouter text-white shadow-sm" style="background-color: #486a70;">
            <i class="fas fa-plus me-2"></i>Ajouter une Agence
        </a>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header text-white fw-bold" style="background-color: #486a70;">
        <i class="fas fa-list me-2"></i>Répertoire des Agences
    </div>
    <div class="card-body p-0">
        <?php if (count($agencies) > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Code</th>
                        <th>Nom de l'Agence</th>
                        <th>Banque rattachée</th>
                        <th>Adresse</th>
                        <th class="text-center" style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($agencies as $a): ?>
                        <tr>
                            <td class="ps-3"><span class="badge bg-light text-dark border"><?= htmlspecialchars($a['code']) ?></span></td>
                            <td><strong><?= htmlspecialchars($a['nom']) ?></strong></td>
                            <td>
                                <span class="text-muted"><?= htmlspecialchars($a['nom_banque'] ?? 'N/A') ?></span>
                                <small class="badge bg-secondary ms-1 opacity-75"><?= htmlspecialchars($a['code_banque'] ?? '---') ?></small>
                            </td>
                            <td><small class="text-muted"><?= htmlspecialchars($a['adresse']) ?></small></td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <a href="index.php?page=agence&edit=<?= $a['id'] ?>" class="btn btn-sm text-white" style="background-color: #486a70;">
                                        <i class="fas fa-pencil-alt"></i>
                                    </a>
                                    <button class="btn btn-sm btn-danger delete-agence" data-id="<?= $a['id'] ?>" data-nom="<?= htmlspecialchars($a['nom']) ?>">
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
        <div class="text-center py-5"><p class="text-muted">Aucune agence enregistrée.</p></div>
        <?php endif; ?>
    </div>
</div>

<script>
document.querySelectorAll('.delete-agence').forEach(btn => {
    btn.addEventListener('click', async function() {
        const id = this.dataset.id;
        const nom = this.dataset.nom;

        const fdCheck = new FormData();
        fdCheck.append('form_type', 'check_linked_garanties');
        fdCheck.append('type', 'agence');
        fdCheck.append('id', id);

        try {
            const resCheck = await fetch('process.php', { method: 'POST', body: fdCheck });
            const dataCheck = await resCheck.json();
            
            let htmlText = `<p>Voulez-vous supprimer l'agence "<b>${nom}</b>" ?</p>`;
            
            if (dataCheck.ok && dataCheck.garanties.length > 0) {
                const list = dataCheck.garanties.map(g => `<li>${g}</li>`).join('');
                htmlText = `<div class="text-start">
                    <p class="text-danger fw-bold"><i class="fas fa-exclamation-triangle"></i> ATTENTION ! Cette action supprimera également <b>TOUTES</b> les garanties suivantes :</p>
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
                    fd.append('form_type', 'delete_agence');
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