<?php
require_once dirname(__DIR__) . '/database.php';
$pdo = getDBConnection();

function updateExpiredGuarantees($pdo) {
    try {
        $today = date('Y-m-d');
        $sql = "UPDATE garantie_soumission 
                SET statutID = 2 
                WHERE statutID = 1 
                AND date_expiration < ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$today]);
        return $stmt->rowCount();
    } catch (Exception $e) {
        error_log("Auto-update expired guarantees failed: " . $e->getMessage());
        return 0;
    }
}
$updated = updateExpiredGuarantees($pdo);
if ($updated > 0) {
    error_log("✅ {$updated} garanties expirées mises à jour automatiquement");
}
// Requête intégrant la logique d'authentification EXACTEMENT comme l'amendement
$query = "SELECT 
    g.id,
    g.num_garantie,
    g.montant_garantie,
    g.date_emission,
    g.date_expiration,
    s.nom_entreprise,
    a.nom as agence_nom,
    d.code as devise_code,
    st.libelle as statut_libelle,
    ao.num_app_offre,
    DATEDIFF(g.date_expiration, CURDATE()) as jours_restants,
    -- Calcul Amendements (existant)
    COALESCE(SUM(DISTINCT CASE WHEN a_inner.type_amendementID IN (SELECT id FROM type_amendement WHERE code IN ('MONTANT', 'MIXTE')) 
                      THEN a_inner.nouveau_montant ELSE 0 END), 0) as total_amendments_montant,
    -- Calcul Authentifications (Nouveau - EXACTEMENT comme amendement)
    COUNT(DISTINCT auth.id) as nb_auth
FROM garantie_soumission g
LEFT JOIN soumissionnaire s ON g.soumissionnaireID = s.id
LEFT JOIN agence a ON g.agenceID = a.id
LEFT JOIN devise d ON g.deviseID = d.id
LEFT JOIN statut st ON g.statutID = st.id
LEFT JOIN appel_offre ao ON g.appel_offreID = ao.id
LEFT JOIN amendement a_inner ON g.id = a_inner.garantie_soumissionID
LEFT JOIN authentification auth ON g.id = auth.garantie_soumissionID
GROUP BY g.id, g.num_garantie, g.montant_garantie, g.date_emission, g.date_expiration, s.nom_entreprise, a.nom, d.code, st.libelle, ao.num_app_offre
ORDER BY g.date_emission DESC";

$result = $pdo->query($query);
$garanties = $result->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="page-title"><i class="fas fa-shield-alt me-2"></i>Liste des Garanties</h1>
        <a href="index.php?page=garantie" class="btn ajouter">
            <i class="fas fa-plus me-2"></i>Ajouter une Garantie
        </a>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white fw-bold">
        <i class="fas fa-list me-2"></i>Toutes les garanties enregistrées
    </div>
    <div class="card-body p-0">
        <?php if (count($garanties) > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                     <tr>
                        <th class="ps-3">N° Garantie</th>
                        <th>Soumissionnaire</th>
                        <th>Appel d'Offre</th>
                        <th>Statut</th> <th>Expiration</th>
                        <th class="text-end">Montant</th>
                        <th class="text-center">Actions</th>
                     </tr>
            </thead>
            <tbody>
    <?php foreach ($garanties as $row): ?>
    <tr>
        <td class="ps-3">
            <span class="fw-bold text-dark"><?php echo htmlspecialchars($row['num_garantie']); ?></span>
            <?php if ($row['nb_auth'] > 0): ?>
                <i class="fas fa-check-circle text-primary ms-1" title="Authentifiée"></i>
            <?php endif; ?>
            <br>
            <small class="text-muted"><?php echo htmlspecialchars($row['agence_nom']); ?></small>
        </td>
        <td><?php echo htmlspecialchars($row['nom_entreprise']); ?></td>
        <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($row['num_app_offre'] ?? 'N/A'); ?></span></td>
        
        <td>
            <?php 
            $stLib = $row['statut_libelle'];
            $stClass = 'bg-secondary';
            if(stripos($stLib, 'Activ') !== false) $stClass = 'bg-success';
            elseif(stripos($stLib, 'Expir') !== false) $stClass = 'bg-danger';
            elseif(stripos($stLib, 'Libér') !== false) $stClass = 'bg-info text-dark';
            ?>
            <span class="badge <?php echo $stClass; ?>"><?php echo htmlspecialchars($stLib); ?></span>
        </td>

        <td>
            <?php 
                $badgeClass = ($row['jours_restants'] > 0) ? 'bg-success' : 'bg-danger';
                $dateExp = date('d/m/Y', strtotime($row['date_expiration']));
            ?>
            <span class="badge <?php echo $badgeClass; ?>"><?php echo $dateExp; ?></span>
        </td>
        <td class="text-end">
            <?php $montant_total = $row['montant_garantie'] + $row['total_amendments_montant']; ?>
            <div class="fw-bold text-success">
                <?php echo number_format($montant_total, 2, ',', ' '); ?> 
                <small><?php echo $row['devise_code']; ?></small>
            </div>
        </td>
        <td class="text-center">
            <div class="btn-group shadow-sm">
                <a href="index.php?page=details-garantie&id=<?php echo $row['id']; ?>" class="btn btn-sm text-white" style="background-color: #486a70;"><i class="fas fa-eye"></i></a>
                <a href="index.php?page=garantie&edit=<?php echo $row['id']; ?>" class="btn btn-sm text-white" style="background-color: #486a70; border-left: 1px solid rgba(255,255,255,0.3);"><i class="fas fa-pencil-alt"></i></a>
                <button type="button" class="btn btn-sm btn-danger delete-garantie" data-id="<?php echo $row['id']; ?>" data-num="<?php echo htmlspecialchars($row['num_garantie']); ?>"><i class="fas fa-trash"></i></button>
            </div>
        </td>
    </tr>
    <?php endforeach; ?>
</tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
            <p class="text-muted">Aucune garantie trouvée dans la base de données.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Suppression garantie
    document.querySelectorAll('.delete-garantie').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const num = this.dataset.num;

            Swal.fire({
                title: 'Êtes-vous sûr ?',
                text: `Supprimer la garantie n° : ${num}`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#486a70',
                confirmButtonText: 'Oui, supprimer',
                cancelButtonText: 'Annuler'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    const fd = new FormData();
                    fd.append('form_type', 'delete_garantie');
                    fd.append('id', id);

                    try {
                        const res = await fetch('process.php', { method: 'POST', body: fd });
                        const data = await res.json();
                        
                        if (data.ok) {
                            await Swal.fire({ 
                                icon: 'success', 
                                title: 'Garantie supprimée !', 
                                timer: 1500, 
                                showConfirmButton: false,
                                timerProgressBar: true 
                            });
                            location.reload();
                        } else {
                            Swal.fire('Erreur', data.message || 'La suppression a échoué', 'error');
                        }
                    } catch (err) {
                        Swal.fire('Erreur', 'Lien avec le serveur rompu', 'error');
                    }
                }
            });
        });
    });
});
</script>
