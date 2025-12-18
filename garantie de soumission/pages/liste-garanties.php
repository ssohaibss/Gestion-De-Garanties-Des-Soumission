<?php
require_once dirname(__DIR__) . '/database.php';
require_once 'includes/functions.php';
$pdo = getDBConnection();

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
    ao.num_app_offre,
    str.libelle as structure,
    DATEDIFF(g.date_expiration, CURDATE()) as jours_restants
FROM garantie_soumission g
LEFT JOIN soumissionnaire s ON g.soumissionnaireID = s.id
LEFT JOIN agence a ON g.agenceID = a.id
LEFT JOIN banque b ON a.banqueID = b.id
LEFT JOIN devise d ON g.deviseID = d.id
LEFT JOIN statut st ON g.statutID = st.id
LEFT JOIN appel_offre ao ON g.appel_offreID = ao.id
LEFT JOIN structure str ON g.structureID = str.id
ORDER BY g.date_emission DESC";

$result = $pdo->query($query);
$garanties = $result->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <h2><i class="fas fa-shield-alt"></i> Liste des Garanties</h2>
        <a href="index.php?page=garantie" class="btn btn-primary">
            <i class="fas fa-plus"></i> Ajouter une Garantie
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <i class="fas fa-list"></i> Toutes les Garanties
    </div>
    <div class="card-body">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (count($garanties) > 0): ?>
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
                            $badgeClass = getStatusBadgeClass($statut);
                            echo '<span class="badge ' . $badgeClass . '">' . htmlspecialchars($statut) . '</span>';
                            ?>
                        </td>
                        <td>
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
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Aucune garantie trouvée. 
            <a href="index.php?page=garantie" class="alert-link">Cliquez ici pour en ajouter une.</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php $pdo = null; ?>
