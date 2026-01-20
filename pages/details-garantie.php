<?php
require_once dirname(__DIR__) . '/database.php';
$pdo = getDBConnection();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Requête mise à jour avec tes vrais noms de colonnes
$query = "SELECT 
    g.*,
    s.nom_entreprise,
    a.nom as agence_nom,
    b.nom_banque,
    d.code as devise_code,
    ao.num_app_offre,
    DATEDIFF(g.date_expiration, CURDATE()) as jours_restants
FROM garantie_soumission g
LEFT JOIN soumissionnaire s ON g.soumissionnaireID = s.id
LEFT JOIN agence a ON g.agenceID = a.id
LEFT JOIN banque b ON a.banqueID = b.id
LEFT JOIN devise d ON g.deviseID = d.id
LEFT JOIN appel_offre ao ON g.appel_offreID = ao.id
WHERE g.id = ?";

$stmt = $pdo->prepare($query);
$stmt->execute([$id]);
$garantie = $stmt->fetch();

if (!$garantie) {
    die("<div class='alert alert-danger m-3'>Garantie introuvable.</div>");
}
?>

<div class="content-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <h2><i class="fas fa-shield-alt me-2"></i>Détails Garantie : <?php echo htmlspecialchars($garantie['num_garantie']); ?></h2>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm mb-4 border-0">
            <div class="card-header text-white" style="background-color: #486a70;">
                <i class="fas fa-file-contract me-2"></i>Informations de la Garantie
            </div>
            <div class="card-body">
                <table class="table table-borderless fs-5">
                    <tr>
                        <th class="text-muted" style="width: 250px;">Numéro :</th>
                        <td class="fw-bold"><?php echo htmlspecialchars($garantie['num_garantie']); ?></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Montant :</th>
                        <td class="text-success fw-bold">
                            <?php echo number_format($garantie['montant_garantie'], 2, ',', ' '); ?> 
                            <small><?php echo $garantie['devise_code']; ?></small>
                        </td>
                    </tr>
                    <tr>
                        <th class="text-muted">Soumissionnaire :</th>
                        <td><i class="fas fa-building me-2 text-muted"></i><?php echo htmlspecialchars($garantie['nom_entreprise'] ?? 'N/A'); ?></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Liée à l'Appel d'Offre :</th>
                        <td><span class="badge bg-light text-dark border p-2"># <?php echo htmlspecialchars($garantie['num_app_offre'] ?? 'Aucun'); ?></span></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Dates :</th>
                        <td>
                            <div class="small">
                                Émission : <strong><?php echo date('d/m/Y', strtotime($garantie['date_emission'])); ?></strong><br>
                                Expiration : <strong class="text-danger"><?php echo date('d/m/Y', strtotime($garantie['date_expiration'])); ?></strong>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="card-footer bg-light d-flex gap-2">
                <a href="index.php?page=garantie&edit=<?php echo $garantie['id']; ?>" class="btn btn-primary ajouter">
                    <i class="fas fa-pencil-alt me-2"></i>Modifier
                </a>
                <button class="btn btn-danger" onclick="confirmDelete(<?php echo $garantie['id']; ?>, '<?php echo addslashes($garantie['num_garantie']); ?>')">
                    <i class="fas fa-trash me-2"></i>Supprimer
                </button>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-bold">
                <i class="fas fa-university me-2"></i>Détails Bancaires
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-6">
                        <label class="text-muted small uppercase">Banque</label>
                        <p class="fw-bold mb-0"><?php echo htmlspecialchars($garantie['nom_banque'] ?? 'Non précisée'); ?></p>
                    </div>
                    <div class="col-sm-6 border-start">
                        <label class="text-muted small uppercase">Agence</label>
                        <p class="fw-bold mb-0"><?php echo htmlspecialchars($garantie['agence_nom'] ?? 'Non précisée'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white fw-bold text-center">Validité</div>
            <div class="card-body text-center py-4">
                <?php 
                    $jours = $garantie['jours_restants'];
                    $colorClass = ($jours > 15) ? 'bg-success' : (($jours > 0) ? 'bg-warning text-dark' : 'bg-danger');
                ?>
                <div class="badge p-3 rounded-pill mb-3 <?php echo $colorClass; ?>" style="font-size: 1rem;">
                    <i class="fas fa-clock me-2"></i>
                    <?php echo ($jours > 0) ? $jours . " jours restants" : "Expirée"; ?>
                </div>
                <p class="text-muted small">ID Statut actuel : <strong><?php echo $garantie['statutID']; ?></strong></p>
            </div>
        </div>
    </div>
</div>

<script>
async function confirmDelete(id, num) {
    const result = await Swal.fire({
        title: 'Supprimer la garantie ?',
        text: `Numéro : ${num}`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Oui, supprimer',
        cancelButtonText: 'Annuler'
    });

    if (result.isConfirmed) {
        const fd = new FormData();
        fd.append('form_type', 'delete_garantie');
        fd.append('id', id);

        try {
            const res = await fetch('process.php', { method: 'POST', body: fd });
            const data = await res.json();
            if (data.ok) {
                await Swal.fire({ title: 'Supprimée !', icon: 'success', timer: 1500, showConfirmButton: false, timerProgressBar: true });
                window.location.href = 'index.php?page=liste-garanties';
            } else {
                Swal.fire('Erreur', data.message, 'error');
            }
        } catch (err) {
            Swal.fire('Erreur', 'Lien rompu', 'error');
        }
    }
}
</script>