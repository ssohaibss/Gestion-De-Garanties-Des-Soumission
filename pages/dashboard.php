<?php
require_once 'database.php';
require_once 'includes/functions.php';

// 1. Define a single authoritative date
$today = new DateTimeImmutable('today', new DateTimeZone('UTC'));
$todayStr   = $today->format('Y-m-d');
$in30DaysStr = $today->modify('+30 days')->format('Y-m-d');

// 2. Total garanties (fully static, query() is fine)
$totalGaranties = (int) $pdo
    ->query("SELECT COUNT(*) FROM garantie_soumission")
    ->fetchColumn();

// 3. Active garanties
$stmt = $pdo->prepare(
    "SELECT COUNT(*) 
     FROM garantie_soumission
     WHERE date_expiration >= :today"
);
$stmt->execute(['today' => $todayStr]);
$activeGaranties = (int) $stmt->fetchColumn();

// 4. Expired garanties
$stmt = $pdo->prepare(
    "SELECT COUNT(*) 
     FROM garantie_soumission
     WHERE date_expiration < :today"
);
$stmt->execute(['today' => $todayStr]);
$expiredGaranties = (int) $stmt->fetchColumn();

// 5. Expiring soon (within 30 days)
$stmt = $pdo->prepare(
    "SELECT COUNT(*) 
     FROM garantie_soumission
     WHERE date_expiration BETWEEN :today AND :limit"
);
$stmt->execute([
    'today' => $todayStr,
    'limit' => $in30DaysStr,
]);
$expiringSoon = (int) $stmt->fetchColumn();

// 6. Total amount of active garanties
$stmt = $pdo->prepare(
    "SELECT SUM(montant_garantie) 
     FROM garantie_soumission
     WHERE date_expiration >= :today"
);
$stmt->execute(['today' => $todayStr]);
$totalAmount = (float) ($stmt->fetchColumn() ?? 0);

// 7. Main table query (requires :today for DATEDIFF)
$query = "
SELECT 
    g.id,
    g.num_garantie,
    g.montant_garantie,
    g.date_emission,
    g.date_expiration,
    s.nom_entreprise,
    a.nom AS agence_nom,
    b.nom_banque,
    d.libelle AS devise,
    st.libelle AS statut,
    ao.num_app_offre,
    DATEDIFF(g.date_expiration, :today) AS jours_restants
FROM garantie_soumission g
LEFT JOIN soumissionnaire s ON g.soumissionnaireID = s.id
LEFT JOIN agence a ON g.agenceID = a.id
LEFT JOIN banque b ON a.banqueID = b.id
LEFT JOIN devise d ON g.deviseID = d.id
LEFT JOIN appel_offre ao ON g.appel_offreID = ao.id
LEFT JOIN statut st ON g.statutID = st.id
ORDER BY g.date_emission DESC
LIMIT 20
";
$stmt = $pdo->prepare($query); 
$stmt->execute(['today' => $todayStr]);
$garanties = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="content-header">
    <h1><i class="bi bi-speedometer2 me-3"></i>Tableau de Bord</h1>
</div>

<!-- Updated stat cards with real-time counters from database -->
<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-secondary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Total Garanties</h6>
                        <h2 class="mt-2 mb-0"><?php echo $totalGaranties; ?></h2>
                    </div>
                    <i class="bi bi-shield-check" style="font-size: 3rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Garanties Actives</h6>
                        <h2 class="mt-2 mb-0"><?php echo $activeGaranties; ?></h2>
                    </div>
                    <i class="bi bi-check-circle" style="font-size: 3rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-danger">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Expire Bientôt</h6>
                        <h2 class="mt-2 mb-0"><?php echo $expiringSoon; ?></h2>
                        <small style="font-size: 0.75rem;">Dans 30 jours</small>
                    </div>
                    <i class="bi bi-exclamation-triangle" style="font-size: 3rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-dark">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Garanties Expirées</h6>
                        <h2 class="mt-2 mb-0"><?php echo $expiredGaranties; ?></h2>
                    </div>
                    <i class="bi bi-x-circle" style="font-size: 3rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Added garanties table with action controls -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>Garanties Récentes</h5>
                <a href="index.php?page=liste-garanties" class="btn nav-link">
                    <i class="bi bi-eye"></i> Voir toutes les garanties
                </a>
            </div>
            <div class="card-body"> 
                <?php if (!empty($garanties)): ?>
<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>N° Garantie</th>
                <th>Appel d'Offre</th>
                <th>Soumissionnaire</th>
                <th>Banque/Agence</th>
                <th>Montant</th>
                <th>Date Émission</th>
                <th>Date Expiration</th>
                <th>Jours Restants</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($garanties as $row): ?>
            <tr>
                <td><strong><?php echo htmlspecialchars($row['num_garantie']); ?></strong></td>
                <td><strong><?php echo htmlspecialchars($row['num_app_offre']); ?></strong></td>
                <td><?php echo htmlspecialchars($row['nom_entreprise'] ?? 'N/A'); ?></td>
                <td>
                    <?php echo htmlspecialchars($row['nom_banque'] ?? 'N/A'); ?><br>
                    <small class="text-muted"><?php echo htmlspecialchars($row['agence_nom'] ?? 'N/A'); ?></small>
                </td>
                <td><?php echo number_format($row['montant_garantie'], 2, ',', ' '); ?> <?php echo htmlspecialchars($row['devise'] ?? ''); ?></td>
                <td><?php echo date('d/m/Y', strtotime($row['date_emission'])); ?></td>
                <td><?php echo date('d/m/Y', strtotime($row['date_expiration'])); ?></td>
                <td>
                    <?php 
                    $jours = $row['jours_restants'];
                    if ($jours < 0) {
                        echo '<span class="badge bg-danger">Expirée</span>';
                    } elseif ($jours <= 30) {
                        echo '<span class="badge bg-warning text-dark">' . $jours . ' jours</span>';
                    } else {
                        echo '<span class="badge bg-success">' . $jours . ' jours</span>';
                    }
                    ?>
                </td>
                <td><?php echo htmlspecialchars($row['statut'] ?? 'N/A'); ?></td>
                <td>
                    <!-- Actions buttons go here -->
                     <div class="btn-group btn-group-sm" role="group">
                                <a href="index.php?page=details-garantie&id=<?php echo $row['id']; ?>" 
                                   class="btn eye" title="Détails">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="index.php?page=modifier-garantie&id=<?php echo $row['id']; ?>" 
                                   class="btn edit" title="Modifier">
                                    <i class="fas fa-pencil-alt"></i>
                                </a>
                                <a href="pages/delete-garantie.php?id=<?php echo $row['id']; ?>" 
                                   class="btn btn-danger" 
                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette garantie ?');"
                                   title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
<p>Aucune garantie trouvée.</p>
<?php endif; ?>

            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Actions Rapides</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="index.php?page=appel-offre" class="btn ajouter text-white">
                        <i class="bi bi-file-text me-2"></i>Ajouter Appel d'Offre
                    </a>
                    <a href="index.php?page=garantie" class="btn ajouter text-white">
                        <i class="bi bi-plus-circle me-2"></i>Ajouter une Garantie
                    </a>
                    <a href="index.php?page=liste-appels-offre" class="btn voir text-secondary-emphasis">
                        <i class="bi bi-file-text me-2"></i>Voir Tous les Appels d'Offre
                    </a>
                    <a href="index.php?page=liste-garanties" class="btn voir text-secondary-emphasis">
                        <i class="bi bi-file-text me-2"></i>Voir Toutes les Garanties
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php

?>

<!-- Added Bootstrap Icons CDN for icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">