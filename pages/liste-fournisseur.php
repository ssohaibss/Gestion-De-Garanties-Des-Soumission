<?php
require_once dirname(__DIR__) . '/database.php';
$pdo = getDBConnection();

$query = "SELECT * FROM soumissionnaire ORDER BY nom_entreprise ASC";
$result = $pdo->query($query);
$suppliers = $result->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="page-title"><i class="fas fa-industry me-2"></i>Liste des Fournisseurs</h1>
        <a href="index.php?page=fournisseur" class="btn ajouter">
            <i class="fas fa-plus"></i> Ajouter un Fournisseur
        </a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header">
        <i class="fas fa-list me-2"></i>Tous les Fournisseurs
    </div>
    <div class="card-body">
        <?php if (count($suppliers) > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nom Entreprise</th>
                        <th>Contact</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th class="text-center" style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($suppliers as $s): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($s['nom_entreprise']); ?></strong></td>
                            <td><?php echo htmlspecialchars($s['contact_principal'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($s['email'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($s['telephone'] ?? ''); ?></td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <a href="index.php?page=fournisseur&edit=<?php echo $s['id']; ?>" class="btn btn-sm eye text-white">
                                        <i class="fas fa-pencil-alt"></i>
                                    </a>
                                    <button class="btn btn-sm btn-danger delete-supplier" 
                                            data-id="<?php echo $s['id']; ?>" 
                                            data-nom="<?php echo htmlspecialchars($s['nom_entreprise']); ?>">
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
        <div class="text-center py-4"><p class="text-muted">Aucun fournisseur enregistré.</p></div>
        <?php endif; ?>
    </div>
</div>

<script>
document.querySelectorAll('.delete-supplier').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        const nom = this.dataset.nom;

        Swal.fire({
            title: 'Êtes-vous sûr ?',
            text: `Supprimer le fournisseur "${nom}" ?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#486a70',
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler'
        }).then(async (result) => {
            if (result.isConfirmed) {
                const fd = new FormData();
                fd.append('form_type', 'delete_fournisseur');
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
