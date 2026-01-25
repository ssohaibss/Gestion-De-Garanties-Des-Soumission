<?php
require_once dirname(__DIR__) . '/database.php';
$pdo = getDBConnection();

// Récupération des agences avec jointure pour afficher le nom de la banque
$query = "SELECT a.*, b.nom_banque, b.code as code_banque 
          FROM agence a 
          LEFT JOIN banque b ON a.banqueID = b.id 
          ORDER BY a.nom ASC";
$agencies = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <h2 class="page-title"><i class="fas fa-map-marked-alt me-2"></i>Liste des Agences</h2>
        <a href="index.php?page=agence" class="btn ajouter shadow-sm text-white" style="background-color: #486a70;">
            <i class="fas fa-plus"></i> Ajouter une Agence
        </a>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header text-white fw-bold" style="background-color: #486a70;">
        <i class="fas fa-list me-2"></i>Toutes les Agences
    </div>
    <div class="card-body p-0">
        <?php if (count($agencies) > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="color: #333;">Code</th>
                        <th style="color: #333;">Nom de l'Agence</th>
                        <th style="color: #333;">Banque rattachée</th>
                        <th style="color: #333;">Adresse</th>
                        <th class="text-center" style="width: 120px; color: #333;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($agencies as $a): ?>
                        <tr>
                            <td class="ps-3">
                                <span class="badge bg-light text-dark border"><?= htmlspecialchars($a['code']) ?></span>
                            </td>
                            <td><strong><?= htmlspecialchars($a['nom']) ?></strong></td>
                            <td>
                                <span class="text-muted"><?= htmlspecialchars($a['nom_banque'] ?? 'N/A') ?></span>
                                <small class="badge bg-secondary ms-1"><?= htmlspecialchars($a['code_banque'] ?? '---') ?></small>
                            </td>
                            <td><small class="text-muted"><?= htmlspecialchars($a['adresse']) ?></small></td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <a href="index.php?page=agence&edit=<?= $a['id'] ?>" class="btn btn-sm text-white" style="background-color: #486a70;">
                                        <i class="fas fa-pencil-alt"></i>
                                    </a>
                                    <button class="btn btn-sm btn-danger delete-agence" 
                                            data-id="<?= $a['id'] ?>" 
                                            data-nom="<?= htmlspecialchars($a['nom']) ?>">
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
            <p class="text-muted">Aucune agence enregistrée dans le système.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.querySelectorAll('.delete-agence').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        const nom = this.dataset.nom;

        Swal.fire({
            title: 'Supprimer ?',
            text: `Voulez-vous vraiment supprimer l'agence "${nom}" ?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#486a70',
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler',
            timerProgressBar: true
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
                    } else {
                        Swal.fire('Erreur', data.message || 'Suppression impossible', 'error');
                    }
                } catch (err) {
                    Swal.fire('Erreur', 'Lien avec le serveur rompu', 'error');
                }
            }
        });
    });
});
</script>