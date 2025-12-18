<?php
require_once dirname(__DIR__) . '/database.php';
$pdo = getDBConnection();

$id = $_GET['id'] ?? 0;

// Fetch garantie details
$query = "SELECT 
    g.*,
    s.nom_entreprise,
    s.email as soumissionnaire_email,
    s.telephone as soumissionnaire_tel,
    s.adresse as soumissionnaire_adresse,
    a.nom as agence_nom,
    a.code as agence_code,
    a.adresse as agence_adresse,
    b.nom_banque,
    b.code as banque_code,
    d.code as devise_code,
    d.libelle as devise,
    st.libelle as statut,
    ao.num_app_offre,
    str.libelle as structure,
    u.nom as utilisateur_nom,
    p.Nom as pays_nom,
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
LEFT JOIN pays p ON s.paysID = p.id
WHERE g.id = ?";

$stmt = $pdo->prepare($query);
$stmt->execute([$id]);
$garantie = $stmt->fetch();

if (!$garantie) {
    $_SESSION['error'] = 'Garantie non trouvée';
    header('Location: index.php?page=liste-garanties');
    exit;
}
?>

<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <h2><i class="fas fa-shield-alt"></i> Détails de la Garantie #<?php echo htmlspecialchars($garantie['num_garantie']); ?></h2>
        <div>
            <a href="index.php?page=modifier-garantie&id=<?php echo $garantie['id']; ?>" class="btn btn-warning">
                <i class="fas fa-edit"></i> Modifier
            </a>
            <a href="index.php?page=liste-garanties" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>
</div>

<div class="row">
    <!-- Main Information Card -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-info-circle"></i> Informations Générales</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Numéro de Garantie:</strong><br>
                        <span class="text-muted"><?php echo htmlspecialchars($garantie['num_garantie']); ?></span>
                    </div>
                    <div class="col-md-6">
                        <strong>Statut:</strong><br>
                        <?php 
                        $statut = $garantie['statut'] ?? 'N/A';
                        $badgeClass = 'bg-secondary';
                        if (strpos(strtolower($statut), 'actif') !== false) {
                            $badgeClass = 'bg-success';
                        } elseif (strpos(strtolower($statut), 'expire') !== false) {
                            $badgeClass = 'bg-danger';
                        }
                        echo '<span class="badge ' . $badgeClass . '">' . htmlspecialchars($statut) . '</span>';
                        ?>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Montant:</strong><br>
                        <span class="text-success fs-5">
                            <?php echo number_format($garantie['montant_garantie'], 2, ',', ' '); ?> 
                            <?php echo htmlspecialchars($garantie['devise_code']); ?>
                        </span>
                    </div>
                    <div class="col-md-6">
                        <strong>Devise:</strong><br>
                        <span class="text-muted"><?php echo htmlspecialchars($garantie['devise']); ?></span>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Date d'Émission:</strong><br>
                        <span class="text-muted"><?php echo date('d/m/Y', strtotime($garantie['date_emission'])); ?></span>
                    </div>
                    <div class="col-md-6">
                        <strong>Date d'Expiration:</strong><br>
                        <span class="text-muted"><?php echo date('d/m/Y', strtotime($garantie['date_expiration'])); ?></span>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-12">
                        <strong>Jours Restants:</strong><br>
                        <?php 
                        $jours = $garantie['jours_restants'];
                        if ($jours < 0) {
                            echo '<span class="badge bg-danger fs-6">Expirée (il y a ' . abs($jours) . ' jours)</span>';
                        } elseif ($jours <= 30) {
                            echo '<span class="badge bg-warning text-dark fs-6">' . $jours . ' jours restants</span>';
                        } else {
                            echo '<span class="badge bg-success fs-6">' . $jours . ' jours restants</span>';
                        }
                        ?>
                    </div>
                </div>

                <?php if ($garantie['structure']): ?>
                <div class="row mb-3">
                    <div class="col-12">
                        <strong>Structure:</strong><br>
                        <span class="text-muted"><?php echo htmlspecialchars($garantie['structure']); ?></span>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($garantie['num_app_offre']): ?>
                <div class="row">
                    <div class="col-12">
                        <strong>Appel d'Offre:</strong><br>
                        <span class="text-muted"><?php echo htmlspecialchars($garantie['num_app_offre']); ?></span>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Soumissionnaire Card -->
        <?php if ($garantie['nom_entreprise']): ?>
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-building"></i> Soumissionnaire</h5>
            </div>
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-12">
                        <strong>Entreprise:</strong><br>
                        <span class="text-muted"><?php echo htmlspecialchars($garantie['nom_entreprise']); ?></span>
                    </div>
                </div>
                <?php if ($garantie['soumissionnaire_email']): ?>
                <div class="row mb-2">
                    <div class="col-12">
                        <strong>Email:</strong><br>
                        <a href="mailto:<?php echo htmlspecialchars($garantie['soumissionnaire_email']); ?>">
                            <?php echo htmlspecialchars($garantie['soumissionnaire_email']); ?>
                        </a>
                    </div>
                </div>
                <?php endif; ?>
                <?php if ($garantie['soumissionnaire_tel']): ?>
                <div class="row mb-2">
                    <div class="col-12">
                        <strong>Téléphone:</strong><br>
                        <span class="text-muted"><?php echo htmlspecialchars($garantie['soumissionnaire_tel']); ?></span>
                    </div>
                </div>
                <?php endif; ?>
                <?php if ($garantie['soumissionnaire_adresse']): ?>
                <div class="row mb-2">
                    <div class="col-12">
                        <strong>Adresse:</strong><br>
                        <span class="text-muted"><?php echo htmlspecialchars($garantie['soumissionnaire_adresse']); ?></span>
                    </div>
                </div>
                <?php endif; ?>
                <?php if ($garantie['pays_nom']): ?>
                <div class="row">
                    <div class="col-12">
                        <strong>Pays:</strong><br>
                        <span class="text-muted"><?php echo htmlspecialchars($garantie['pays_nom']); ?></span>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Sidebar -->
    <div class="col-md-4">
        <!-- Bank Information Card -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-university"></i> Banque & Agence</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Banque:</strong><br>
                    <span class="text-muted"><?php echo htmlspecialchars($garantie['nom_banque'] ?? 'N/A'); ?></span><br>
                    <small class="text-muted">Code: <?php echo htmlspecialchars($garantie['banque_code'] ?? 'N/A'); ?></small>
                </div>
                <div>
                    <strong>Agence:</strong><br>
                    <span class="text-muted"><?php echo htmlspecialchars($garantie['agence_nom'] ?? 'N/A'); ?></span><br>
                    <small class="text-muted">Code: <?php echo htmlspecialchars($garantie['agence_code'] ?? 'N/A'); ?></small>
                    <?php if ($garantie['agence_adresse']): ?>
                    <br><small class="text-muted"><?php echo htmlspecialchars($garantie['agence_adresse']); ?></small>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Metadata Card -->
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="fas fa-user"></i> Informations Système</h5>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <strong>Créé par:</strong><br>
                    <span class="text-muted"><?php echo htmlspecialchars($garantie['utilisateur_nom'] ?? 'N/A'); ?></span>
                </div>
                <div>
                    <strong>ID Garantie:</strong><br>
                    <span class="text-muted">#<?php echo htmlspecialchars($garantie['id']); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php // Removed mysqli close() call ?>
