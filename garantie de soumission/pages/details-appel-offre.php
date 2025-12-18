<?php
require_once dirname(__DIR__) . '/database.php';

$id = $_GET['id'] ?? 0;

// Fetch appel d'offre details
$stmt = $pdo->prepare("SELECT * FROM appel_offre WHERE id = ?");
$stmt->execute([$id]);
$appel_offre = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$appel_offre) {
    $_SESSION['error'] = 'Appel d\'offre non trouvé';
    header('Location: index.php?page=liste-appels-offre');
    exit;
}

// Fetch all garanties related to this appel d'offre
$query = "SELECT 
    g.id,
    g.num_garantie,
    g.montant_garantie,
    g.date_emission,
    g.date_expiration,
    s.nom_entreprise,
    a.nom as agence_nom,
    b.nom_banque,
    d.code as devise_code,
    d.libelle as devise,
    st.libelle as statut,
    str.libelle as structure,
    DATEDIFF(g.date_expiration, CURDATE()) as jours_restants
FROM garantie_soumission g
LEFT JOIN soumissionnaire s ON g.soumissionnaireID = s.id
LEFT JOIN agence a ON g.agenceID = a.id
LEFT JOIN banque b ON a.banqueID = b.id
LEFT JOIN devise d ON g.deviseID = d.id
LEFT JOIN statut st ON g.statutID = st.id
LEFT JOIN structure str ON g.structureID = str.id
WHERE g.appel_offreID = ?
ORDER BY g.date_emission DESC";

$stmt_garanties = $pdo->prepare($query);
$stmt_garanties->execute([$id]);
$garanties = $stmt_garanties->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics
$total_garanties = count($garanties);
$montant_total = 0;
$garanties_actives = 0;
$garanties_expirees = 0;
$garanties_expiring_soon = 0;

foreach ($garanties as $g) {
    $montant_total += $g['montant_garantie'];
    if ($g['jours_restants'] < 0) {
        $garanties_expirees++;
    } elseif ($g['jours_restants'] <= 30) {
        $garanties_expiring_soon++;
        $garanties_actives++;
    } else {
        $garanties_actives++;
    }
}
?>

<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <h2><i class="fas fa-file-invoice"></i> Détails de l'Appel d'Offre: <?php echo htmlspecialchars($appel_offre['num_app_offre']); ?></h2>
        <a href="index.php?page=liste-appels-offre" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Total Garanties</h6>
                        <h2 class="mt-2 mb-0"><?php echo $total_garanties; ?></h2>
                    </div>
                    <i class="fas fa-shield-alt" style="font-size: 3rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Actives</h6>
                        <h2 class="mt-2 mb-0"><?php echo $garanties_actives; ?></h2>
                    </div>
                    <i class="fas fa-check-circle" style="font-size: 3rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Expire Bientôt</h6>
                        <h2 class="mt-2 mb-0"><?php echo $garanties_expiring_soon; ?></h2>
                    </div>
                    <i class="fas fa-exclamation-triangle" style="font-size: 3rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Montant Total</h6>
                        <h3 class="mt-2 mb-0"><?php echo number_format($montant_total, 0, ',', ' '); ?></h3>
                        <small>DZD</small>
                    </div>
                    <i class="fas fa-dollar-sign" style="font-size: 3rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Appel d'Offre Information Card -->
<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fas fa-info-circle"></i> Informations de l'Appel d'Offre</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <strong>Numéro d'Appel d'Offre:</strong><br>
                <span class="text-muted fs-5"><?php echo htmlspecialchars($appel_offre['num_app_offre']); ?></span>
            </div>
            <div class="col-md-6">
                <strong>ID:</strong><br>
                <span class="text-muted">#<?php echo htmlspecialchars($appel_offre['id']); ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Garanties Table -->
<div class="card">
    <div class="card-header bg-secondary text-white">
        <h5 class="mb-0"><i class="fas fa-shield-alt"></i> Garanties Liées à cet Appel d'Offre</h5>
    </div>
    <div class="card-body">
        <?php if ($total_garanties > 0): ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>N° Garantie</th>
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
                        <td><?php echo htmlspecialchars($row['nom_entreprise'] ?? 'N/A'); ?></td>
                        <td>
                            <?php echo htmlspecialchars($row['nom_banque'] ?? 'N/A'); ?><br>
                            <small class="text-muted"><?php echo htmlspecialchars($row['agence_nom'] ?? 'N/A'); ?></small>
                        </td>
                        <td><?php echo number_format($row['montant_garantie'], 2, ',', ' '); ?> <?php echo htmlspecialchars($row['devise_code'] ?? ''); ?></td>
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
                        <td>
                            <?php 
                            $statut = $row['statut'] ?? 'N/A';
                            $badgeClass = 'bg-secondary';
                            if (strpos(strtolower($statut), 'actif') !== false) {
                                $badgeClass = 'bg-success';
                            } elseif (strpos(strtolower($statut), 'expire') !== false) {
                                $badgeClass = 'bg-danger';
                            }
                            echo '<span class="badge ' . $badgeClass . '">' . htmlspecialchars($statut) . '</span>';
                            ?>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="index.php?page=details-garantie&id=<?php echo $row['id']; ?>" 
                                   class="btn btn-info" title="Détails">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="index.php?page=modifier-garantie&id=<?php echo $row['id']; ?>" 
                                   class="btn btn-warning" title="Modifier">
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
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="table-active">
                        <td colspan="3"><strong>Total</strong></td>
                        <td colspan="6"><strong><?php echo number_format($montant_total, 2, ',', ' '); ?> DZD</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <?php else: ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Aucune garantie n'est liée à cet appel d'offre.
            <a href="index.php?page=garantie" class="alert-link">Cliquez ici pour en ajouter une.</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Additional Actions -->
<div class="card mt-4">
    <div class="card-header bg-light">
        <h5 class="mb-0"><i class="fas fa-cogs"></i> Actions</h5>
    </div>
    <div class="card-body">
        <div class="d-flex gap-2">
            <a href="index.php?page=garantie&appel_offre=<?php echo $appel_offre['id']; ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Ajouter une Garantie pour cet AO
            </a>
            <?php if ($total_garanties == 0): ?>
            <a href="pages/delete-appel-offre.php?id=<?php echo $appel_offre['id']; ?>" 
               class="btn btn-danger"
               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet appel d\'offre ?');">
                <i class="fas fa-trash"></i> Supprimer l'Appel d'Offre
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php 

// Close the statement
$stmt_garanties = null;

// Close the database connection
$pdo = null; 
?>
