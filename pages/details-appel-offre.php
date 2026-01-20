<?php
require_once dirname(__DIR__) . '/database.php';
$pdo = getDBConnection();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 1. Récupération des détails de l'Appel d'Offre
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

// 2. Récupération des garanties avec le bon nom : nom_entreprise
$stmtG = $pdo->prepare("SELECT g.*, s.nom_entreprise 
                        FROM garantie_soumission g 
                        LEFT JOIN soumissionnaire s ON g.soumissionnaireID = s.id 
                        WHERE g.appel_offreID = ?");
$stmtG->execute([$id]);
$garanties = $stmtG->fetchAll();
?>

<div class="content-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <h2><i class="fas fa-info-circle me-2"></i>Détails du Dossier : <?php echo htmlspecialchars($ao['num_app_offre']); ?></h2>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm border-0 mb-4">
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
                    <i class="fas fa-pencil-alt me-2"></i>Modifier
                </a>
                <button class="btn btn-danger" onclick="confirmDelete(<?php echo $ao['id']; ?>, '<?php echo addslashes($ao['num_app_offre']); ?>')">
                    <i class="fas fa-trash me-2"></i>Supprimer
                </button>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-bold">
                <i class="fas fa-shield-alt me-2 text-primary"></i>Garanties Déposées (<?php echo count($garanties); ?>)
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">N° Garantie</th>
                                <th>Entreprise (Soumissionnaire)</th>
                                <th class="text-end">Montant</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($garanties) > 0): ?>
                                <?php foreach($garanties as $g): ?>
                                <tr>
                                    <td class="ps-3"><strong><?php echo htmlspecialchars($g['num_garantie']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($g['nom_entreprise'] ?? 'Non assigné'); ?></td>
                                    <td class="text-end fw-bold"><?php echo number_format($g['montant_garantie'], 2, ',', ' '); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center py-3 text-muted">Aucune garantie enregistrée pour cet appel d'offre.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-bold">
                <i class="fas fa-history me-2"></i>État
            </div>
            <div class="card-body text-center py-4">
                <div class="badge bg-success p-3 rounded-pill mb-3" style="font-size: 0.9rem;">Dossier Actif</div>
                <hr>
                <p class="text-muted small">Dernière consultation : <br><strong><?php echo date('d/m/Y à H:i'); ?></strong></p>
            </div>
        </div>
    </div>
</div>

<script>
async function confirmDelete(id, numAo) {
    const result = await Swal.fire({
        title: 'Confirmer la suppression',
        text: `Voulez-vous supprimer l'appel d'offre n° ${numAo} ?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Oui, supprimer',
        cancelButtonText: 'Annuler'
    });

    if (result.isConfirmed) {
        const fd = new FormData();
        fd.append('form_type', 'delete_appel_offre');
        fd.append('id', id);

        try {
            const res = await fetch('process.php', { method: 'POST', body: fd });
            const data = await res.json();
            if (data.ok) {
                await Swal.fire({ title: 'Supprimé !', icon: 'success', timer: 1500, showConfirmButton: false, timerProgressBar: true });
                window.location.href = 'index.php?page=liste-appel-offre';
            } else {
                Swal.fire('Erreur', data.message, 'error');
            }
        } catch (err) {
            Swal.fire('Erreur', 'Serveur injoignable', 'error');
        }
    }
}
</script>