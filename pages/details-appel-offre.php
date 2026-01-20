<?php
require_once dirname(__DIR__) . '/database.php';
$pdo = getDBConnection();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

//  Récupération des détails avec jointure pour la devise
$query = "SELECT ao.*, d.code as devise_code 
          FROM appel_offre ao 
          LEFT JOIN devise d ON ao.deviseID = d.Id 
          WHERE ao.id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$id]);
$ao = $stmt->fetch();

if (!$ao) {
    die("<div class='alert alert-danger m-3'>Dossier introuvable.</div>");
}
?>

<div class="content-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <h2><i class="fas fa-info-circle me-2"></i>Détails du Dossier : <?php echo htmlspecialchars($ao['num_app_offre']); ?></h2>
        
        <a href="index.php?page=liste-appels-offre" class="btn btn-primary ajouter">
            <i class="fas fa-arrow-left me-2"></i>Retourner à la page précédente
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-header text-white" style="background-color: #486a70;">
                <i class="fas fa-file-alt me-2"></i>Informations Générales
            </div>
            <div class="card-body">
                <table class="table table-borderless fs-5">
                    <tr>
                        <th class="text-muted" style="width: 250px;">Numéro de dossier :</th>
                        <td class="fw-bold text-dark"><?php echo htmlspecialchars($ao['num_app_offre']); ?></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Date d'émission :</th>
                        <td><?php echo date('d/m/Y', strtotime($ao['date_emission'])); ?></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Montant estimé :</th>
                        <td class="text-success fw-bold">
                            <?php echo number_format($ao['montant'], 2, ',', ' '); ?> 
                            <small class="text-muted"><?php echo $ao['devise_code']; ?></small>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="card-footer bg-light d-flex gap-2">
                <a href="index.php?page=appel-offre&edit=<?php echo $ao['id']; ?>" class="btn btn-primary ajouter">
                    <i class="fas fa-pencil-alt me-2"></i>Modifier ce dossier
                </a>
                
                <button class="btn btn-secondary" onclick="confirmDelete(<?php echo $ao['id']; ?>)">
                    <i class="fas fa-trash me-2"></i>Supprimer
                </button>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-bold">
                <i class="fas fa-history me-2"></i>Statut
            </div>
            <div class="card-body text-center py-4">
                <div class="badge bg-success p-3 rounded-pill mb-3" style="font-size: 0.9rem;">
                    Dossier Actif
                </div>
                <p class="text-muted small">Consulté le : <br><strong><?php echo date('d/m/Y à H:i'); ?></strong></p>
            </div>
        </div>
    </div>
</div>

<script>
async function confirmDelete(id) {
    const result = await Swal.fire({
        title: 'Êtes-vous sûr ?',
        text: "Ce dossier sera définitivement supprimé de la base de données.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Oui, supprimer',
        cancelButtonText: 'Annuler'
    });

    if (result.isConfirmed) {
        const fd = new FormData();
        fd.append('form_type', 'delete_appel_offre');
        fd.append('id', id);

        try {
            const res = await fetch('process.php', { 
                method: 'POST', 
                body: fd 
            });

            if (!res.ok) throw new Error('Erreur réseau');
            
            const data = await res.json();
            
            if (data.ok) {
                // Succès avec barre de progression
                await Swal.fire({ 
                    title: 'Supprimé !', 
                    text: 'Le dossier a été supprimé avec succès.',
                    icon: 'success', 
                    timer: 1500, 
                    showConfirmButton: false,
                    timerProgressBar: true
                });
                
                // Redirection vers la liste
                window.location.href = 'index.php?page=liste-appels-offre';
            } else {
                Swal.fire('Erreur', data.message || 'La suppression a échoué', 'error');
            }
        } catch (err) {
            console.error(err);
            Swal.fire('Erreur', 'Impossible de communiquer avec process.php', 'error');
        }
    }
}
</script>