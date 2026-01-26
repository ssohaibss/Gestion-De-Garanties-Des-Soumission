<?php
require_once dirname(__DIR__) . '/database.php';
$pdo = getDBConnection();

// Récupération des données
$structures = $pdo->query("SELECT * FROM structure ORDER BY libelle ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <h2 class="page-title"><i class="fas fa-sitemap me-2"></i>Gestion des Structures</h2>
        <a href="index.php?page=structure" class="btn ajouter shadow-sm">
            <i class="fas fa-plus me-2"></i>Ajouter une Structure
        </a>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header text-white fw-bold" style="background-color: #486a70;">
        <i class="fas fa-list me-2"></i>Toutes les Structures
    </div>
    
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="color: #333;">Libellé</th>
                        <th style="color: #333;">Code</th>
                        <th class="text-center" style="width: 120px; color: #333;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($structures)): ?>
                        <?php foreach ($structures as $s): ?>
                            <tr>
                                <td class="ps-3"><strong><?= htmlspecialchars($s['libelle']); ?></strong></td>
                                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($s['code']); ?></span></td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="index.php?page=structure&edit=<?= $s['id']; ?>" class="btn btn-sm ajouter text-white">
                                            <i class="fas fa-pencil-alt"></i>
                                        </a>
                                        <button class="btn btn-sm btn-danger delete-structure" 
                                                data-id="<?= $s['id']; ?>" 
                                                data-nom="<?= htmlspecialchars($s['libelle']); ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-center py-4 text-muted">
                                Aucune structure enregistrée.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.delete-structure').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        const nom = this.dataset.nom;

        Swal.fire({
            title: 'Confirmer la suppression',
            text: `Voulez-vous vraiment supprimer la structure "${nom}" ?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#486a70',
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler'
        }).then(async (result) => {
            if (result.isConfirmed) {
                const fd = new FormData();
                fd.append('form_type', 'delete_structure');
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
                            timerProgressBar: true // Ajouté comme demandé
                        });
                        location.reload();
                    } else {
                        Swal.fire('Erreur', data.message || 'Erreur lors de la suppression', 'error');
                    }
                } catch (err) {
                    Swal.fire('Erreur', 'Impossible de contacter le serveur.', 'error');
                }
            }
        });
    });
});
</script>
