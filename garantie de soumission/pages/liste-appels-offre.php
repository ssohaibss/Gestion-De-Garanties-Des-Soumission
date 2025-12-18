<?php
require_once dirname(__DIR__) . '/database.php';
$pdo = getDBConnection();

// Fetch all appels d'offre with related garanties count
$query = "SELECT 
    ao.id,
    ao.num_app_offre,
    COUNT(g.id) as nb_garanties,
    SUM(g.montant_garantie) as montant_total
FROM appel_offre ao
LEFT JOIN garantie_soumission g ON ao.id = g.appel_offreID
GROUP BY ao.id
ORDER BY ao.num_app_offre DESC";

$result = $pdo->query($query);
$appels_offre = $result->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <h2><i class="fas fa-file-invoice"></i> Liste des Appels d'Offre</h2>
        <a href="index.php?page=appelle-offre" class="btn btn-primary">
            <i class="fas fa-plus"></i> Ajouter un Appel d'Offre
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <i class="fas fa-list"></i> Tous les Appels d'Offre
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

        <?php if (count($appels_offre) > 0): ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Numéro d'Appel d'Offre</th>
                        <th>Nombre de Garanties</th>
                        <th>Montant Total</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appels_offre as $ao): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($ao['id']); ?></td>
                            <td><strong><?php echo htmlspecialchars($ao['num_app_offre']); ?></strong></td>
                            <td>
                                <span class="badge bg-info">
                                    <?php echo $ao['nb_garanties']; ?> garantie(s)
                                </span>
                            </td>
                            <td>
                                <?php 
                                if ($ao['montant_total']) {
                                    echo number_format($ao['montant_total'], 2, ',', ' ') . ' DZD';
                                } else {
                                    echo '<span class="text-muted">-</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="index.php?page=details-appel-offre&id=<?php echo $ao['id']; ?>" 
                                       class="btn btn-info" title="Détails">
                                        <i class="fas fa-eye"></i> Détails
                                    </a>
                                    <a href="pages/delete-appel-offre.php?id=<?php echo $ao['id']; ?>" 
                                       class="btn btn-danger" 
                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet appel d\'offre ?');"
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
            <i class="fas fa-info-circle"></i> Aucun appel d'offre trouvé. 
            <a href="index.php?page=appelle-offre" class="alert-link">Cliquez ici pour en ajouter un.</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php $pdo = null; ?>
