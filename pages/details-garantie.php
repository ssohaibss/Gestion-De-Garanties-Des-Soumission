<?php
require_once dirname(__DIR__) . '/database.php';
$pdo = getDBConnection();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

//  Requête complète pour récupérer tous les détails liés
$query = "SELECT 
    g.*,
    s.nom_entreprise,
    a.nom as agence_nom,
    b.nom_banque,
    d.code as devise_code,
    st.libelle as statut_libelle,
    ao.num_app_offre,
    str.libelle as structure_libelle,
    u.nom as utilisateur_nom,
    DATEDIFF(g.date_expiration, CURDATE()) as jours_restants
FROM garantie_soumission g
LEFT JOIN soumissionnaire s ON g.soumissionnaireID = s.id
LEFT JOIN agence a ON g.agenceID = a.id
LEFT JOIN banque b ON a.banqueID = b.id
LEFT JOIN devise d ON g.deviseID = d.id
LEFT JOIN statut st ON g.statutID = st.id
LEFT JOIN appel_offre ao ON g.appel_offreID = ao.id
LEFT JOIN structure str ON g.structureID = str.id
LEFT JOIN utilisateur u ON g.utilisateurID = u.id
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
        
        <button type="button" onclick="window.history.back();" class="btn btn-primary ajouter">
            <i class="fas fa-arrow-left me-2"></i>Retourner à la page précédente
        </button>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header text-white" style="background-color: #486a70;">
                <i class="fas fa-file-contract me-2"></i>Informations de la Garantie
            </div>
            <div class="card-body">
                <table class="table table-borderless fs-5">
                    <tr><th class="text-muted" style="width: 250px;">Numéro :</th><td class="fw-bold"><?php echo htmlspecialchars($garantie['num_garantie']); ?></td></tr>
                    <tr><th class="text-muted">Montant :</th><td class="text-success fw-bold"><?php echo number_format($garantie['montant_garantie'], 2, ',', ' '); ?> <?php echo $garantie['devise_code']; ?></td></tr>
                    <tr><th class="text-muted">Date Émission :</th><td><?php echo date('d/m/Y', strtotime($garantie['date_emission'])); ?></td></tr>
                    <tr><th class="text-muted">Date Expiration :</th><td class="text-danger fw-bold"><?php echo date('d/m/Y', strtotime($garantie['date_expiration'])); ?></td></tr>
                    <tr><th class="text-muted">Soumissionnaire :</th><td><?php echo htmlspecialchars($garantie['nom_entreprise']); ?></td></tr>
                    <tr><th class="text-muted">Appel d'Offre :</th><td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($garantie['num_app_offre'] ?? 'N/A'); ?></span></td></tr>
                </table>
            </div>
            <div class="card-footer bg-light d-flex gap-2">
                <a href="index.php?page=garantie&edit=<?php echo $garantie['id']; ?>" class="btn btn-primary ajouter">
                    <i class="fas fa-pencil-alt me-2"></i>Modifier
                </a>
                <button class="btn btn-secondary" onclick="confirmDelete(<?php echo $garantie['id']; ?>, '<?php echo $garantie['num_garantie']; ?>')">
                    <i class="fas fa-trash me-2"></i>Supprimer
                </button>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white fw-bold">Statut & Validité</div>
            <div class="card-body text-center">
                <div class="badge p-3 rounded-pill mb-3 <?php echo ($garantie['jours_restants'] > 0) ? 'bg-success' : 'bg-danger'; ?>">
                    <?php echo htmlspecialchars($garantie['statut_libelle']); ?>
                </div>
                <p class="mb-0 text-muted">Jours restants : <strong><?php echo $garantie['jours_restants']; ?></strong></p>
            </div>
        </div>
    </div>
</div>

<script>
async function confirmDelete(id, num) {
    const result = await Swal.fire({
        title: 'Êtes-vous sûr ?',
        text: `Voulez-vous supprimer la garantie n° ${num} ?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#486a70',
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
                await Swal.fire({ 
                    title: 'Supprimée !', 
                    icon: 'success', 
                    timer: 1500, 
                    showConfirmButton: false, 
                    timerProgressBar: true 
                });
                window.location.href = 'index.php?page=liste-garanties';
            } else {
                Swal.fire('Erreur', data.message, 'error');
            }
        } catch (err) {
            Swal.fire('Erreur', 'Serveur injoignable', 'error');
        }
    }
}
</script>