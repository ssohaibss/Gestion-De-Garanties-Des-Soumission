<?php
require_once dirname(__DIR__) . '/database.php';
$pdo = getDBConnection();

$query = "SELECT a.*, b.nom_banque, b.code as code_banque 
          FROM agence a 
          LEFT JOIN banque b ON a.banqueID = b.id OR a.banqueID = b.Id 
          ORDER BY a.nom ASC";
$result = $pdo->query($query);
$agencies = $result->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="page-title"><i class="fas fa-map-marked-alt me-2"></i>Liste des Agences</h1>
        <a href="index.php?page=agence" class="btn ajouter">
            <i class="fas fa-plus"></i> Ajouter une Agence
        </a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header">
        <i class="fas fa-list me-2"></i>Toutes les Agences
    </div>
    <div class="card-body">
        <?php if (count($agencies) > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Code</th>
                        <th>Nom</th>
                        <th>Banque</th>
                        <th>Adresse</th>
                        <th class="text-center" style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($agencies as $a): ?>
                        <tr>
                            <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($a['code']); ?></span></td>
                            <td><strong><?php echo htmlspecialchars($a['nom']); ?></strong></td>
                            <td><?php echo htmlspecialchars($a['nom_banque'] ?? 'N/A'); ?> (<?php echo htmlspecialchars($a['code_banque'] ?? '---'); ?>)</td>
                            <td><?php echo htmlspecialchars($a['adresse']); ?></td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <a href="index.php?page=agence&edit=<?php echo $a['id'] ?? $a['Id']; ?>" class="btn btn-sm eye text-white">
                                        <i class="fas fa-pencil-alt"></i>
                                    </a>
                                    <button class="btn btn-sm btn-danger delete-agence" 
                                            data-id="<?php echo $a['id'] ?? $a['Id']; ?>" 
                                            data-nom="<?php echo htmlspecialchars($a['nom']); ?>">
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
        <div class="text-center py-4"><p class="text-muted">Aucune agence enregistrée.</p></div>
        <?php endif; ?>
    </div>
</div>

<script>
document.querySelectorAll('.delete-agence').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        const nom = this.dataset.nom;

        Swal.fire({
            title: 'Êtes-vous sûr ?',
            text: `Supprimer l'agence "${nom}" ?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#486a70',
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler'
        }).then(async (result) => {
            if (result.isConfirmed) {
                const fd = new FormData();
                fd.append('form_type', 'delete_agence');
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
