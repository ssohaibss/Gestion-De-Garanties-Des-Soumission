<?php
require_once dirname(__DIR__) . '/database.php';
$pdo = getDBConnection();

$query = "SELECT ao.id, ao.num_app_offre, COUNT(g.id) as nb_garanties
          FROM appel_offre ao
          LEFT JOIN garantie_soumission g ON ao.id = g.appel_offreID
          GROUP BY ao.id ORDER BY ao.num_app_offre DESC";

$appels_offre = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="page-title"><i class="fas fa-file-invoice me-2"></i>Liste des Appels d'Offre</h1>
        <a href="index.php?page=appel-offre" class="btn ajouter">
            <i class="fas fa-plus"></i> Ajouter un Appel d'Offre
        </a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header"><i class="fas fa-list me-2"></i>Tous les Appels d'Offre</div>
    <div class="card-body">
        <?php if (count($appels_offre) > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                       
                        <th>Numéro d'Appel d'Offre</th>
                        <th class="text-center">Nombre de Garanties</th>
                        <th class="text-center" style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appels_offre as $ao): ?>
                       <tr>
    <td><strong><?= htmlspecialchars($ao['num_app_offre']); ?></strong></td>
    <td class="text-center"><?= $ao['nb_garanties']; ?> dossier(s)</td>
    <td class="text-center">
        <div class="btn-group shadow-sm">
            <a href="index.php?page=details-appel-offre&id=<?= $ao['id']; ?>" 
               class="btn btn-sm text-white" 
               style="background-color: #486a70;" 
               title="Voir les détails">
               <i class="fas fa-eye"></i>
            </a>

            <a href="index.php?page=appel-offre&edit=<?= $ao['id']; ?>" 
               class="btn btn-sm text-white" 
               style="background-color: #486a70; border-left: 1px solid rgba(255,255,255,0.3);" 
               title="Modifier">
               <i class="fas fa-pencil-alt"></i>
            </a>

            <button class="btn btn-sm btn-danger delete-ao" 
                    data-id="<?= $ao['id']; ?>" 
                    data-num="<?= htmlspecialchars($ao['num_app_offre']); ?>" 
                    title="Supprimer">
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
        <div class="text-center py-4 text-muted">Aucun appel d'offre enregistré.</div>
        <?php endif; ?>
    </div>
</div>

<script>
document.querySelectorAll('.delete-ao').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        const num = this.dataset.num;

        Swal.fire({
            title: 'Êtes-vous sûr ?',
            text: `Supprimer l'appel d'offre : ${num}`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#486a70',
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler'
        }).then(async (result) => {
            if (result.isConfirmed) {
                const fd = new FormData();
                fd.append('form_type', 'delete_appel_offre');
                fd.append('id', id);
                try {
                    const res = await fetch('process.php', { method: 'POST', body: fd });
                    const data = await res.json();
                    if (data.ok) {
                        await Swal.fire({ icon: 'success', title: 'Appel d"offre supprimée !', timer: 1500, showConfirmButton: false, timerProgressBar: true });
                        location.reload();
                    } else { Swal.fire('Erreur', data.message, 'error'); }
                } catch (err) { Swal.fire('Erreur', 'Lien rompu', 'error'); }
            }
        });
    });
});
</script>