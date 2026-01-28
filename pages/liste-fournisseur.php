<?php
require_once dirname(__DIR__) . '/database.php';
$pdo = getDBConnection();

$query = "SELECT s.*, p.nom as pays_nom 
          FROM soumissionnaire s 
          LEFT JOIN pays p ON s.paysID = p.id 
          ORDER BY s.nom_entreprise ASC";
$suppliers = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <h2 class="page-title"><i class="fas fa-truck me-2"></i>Liste des Fournisseurs</h2>
        <a href="index.php?page=fournisseur" class="btn ajouter text-white shadow-sm" style="background-color: #486a70;">
            <i class="fas fa-plus me-2"></i>Ajouter un Fournisseur
        </a>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header text-white fw-bold" style="background-color: #486a70;">
        <i class="fas fa-list me-2"></i>Tous les Fournisseurs
    </div>
    <div class="card-body p-0">
        <?php if (count($suppliers) > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Entreprise</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Pays</th>
                        <th class="text-center" style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($suppliers as $s): ?>
                        <tr>
                            <td class="ps-3"><strong><?= htmlspecialchars($s['nom_entreprise']) ?></strong></td>
                            <td><span class="text-muted"><?= htmlspecialchars($s['email']) ?></span></td>
                            <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($s['telephone']) ?></span></td>
                            <td><?= htmlspecialchars($s['pays_nom'] ?? 'N/A') ?></td>
                            <td class="text-center">
                                <div class="btn-group shadow-sm">
                                    <a href="index.php?page=fournisseur&edit=<?= $s['id'] ?>" class="btn btn-sm text-white" style="background-color: #486a70;">
                                        <i class="fas fa-pencil-alt"></i>
                                    </a>
                                    <button class="btn btn-sm btn-danger delete-supplier" 
                                            data-id="<?= $s['id'] ?>" 
                                            data-nom="<?= htmlspecialchars($s['nom_entreprise']) ?>">
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
        <div class="text-center py-5"><p class="text-muted">Aucun fournisseur enregistré.</p></div>
        <?php endif; ?>
    </div>
</div>

<script>
document.querySelectorAll('.delete-supplier').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        const nom = this.dataset.nom;

        Swal.fire({
            title: 'Supprimer ?',
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
                        await Swal.fire({ icon: 'success', title: 'Supprimé !', timer: 1500, showConfirmButton: false, timerProgressBar: true  });
                        location.reload();
                    }
                } catch (err) { Swal.fire('Erreur', 'Lien rompu', 'error'); }
            }
        });
    });
});
</script>