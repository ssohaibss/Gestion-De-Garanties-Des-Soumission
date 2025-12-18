<?php
require_once dirname(__DIR__) . '/database.php';
$pdo = getDBConnection();

$id = $_GET['id'] ?? 0;

// Fetch garantie data
$stmt = $pdo->prepare("SELECT * FROM garantie_soumission WHERE id = ?");
$stmt->execute([$id]);
$garantie = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$garantie) {
    $_SESSION['error'] = 'Garantie non trouvée';
    header('Location: index.php?page=liste-garanties');
    exit;
}

// Fetch dropdown data
$stmt_banques = $pdo->prepare("SELECT Id, nom_banque FROM banque ORDER BY nom_banque ASC");
$stmt_banques->execute();
$banques = $stmt_banques->fetchAll(PDO::FETCH_ASSOC);

$stmt_devises = $pdo->prepare("SELECT Id, code, libelle FROM devise ORDER BY code ASC");
$stmt_devises->execute();
$devises = $stmt_devises->fetchAll(PDO::FETCH_ASSOC);

$stmt_ao = $pdo->prepare("SELECT Id, num_app_offre FROM appel_offre ORDER BY num_app_offre DESC");
$stmt_ao->execute();
$appels_offre = $stmt_ao->fetchAll(PDO::FETCH_ASSOC);

$stmt_fournisseurs = $pdo->prepare("SELECT Id, nom_entreprise FROM soumissionnaire ORDER BY nom_entreprise ASC");
$stmt_fournisseurs->execute();
$fournisseurs = $stmt_fournisseurs->fetchAll(PDO::FETCH_ASSOC);

$stmt_structure = $pdo->prepare("SELECT Id, libelle FROM structure ORDER BY libelle ASC");
$stmt_structure->execute();
$structures = $stmt_structure->fetchAll(PDO::FETCH_ASSOC);

$stmt_agences = $pdo->prepare("SELECT Id, nom, banqueID FROM agence ORDER BY nom ASC");
$stmt_agences->execute();
$agences = $stmt_agences->fetchAll(PDO::FETCH_ASSOC);

$stmt_statut = $pdo->prepare("SELECT Id, libelle FROM statut ORDER BY id ASC");
$stmt_statut->execute();
$statuts = $stmt_statut->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <h2><i class="fas fa-edit"></i> Modifier la Garantie #<?php echo htmlspecialchars($garantie['num_garantie']); ?></h2>
        <a href="index.php?page=details-garantie&id=<?php echo $garantie['id']; ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <i class="fas fa-certificate"></i> Formulaire de Modification
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
        
        <form action="pages/update-garantie.php" method="POST">
            <input type="hidden" name="id" value="<?php echo $garantie['id']; ?>">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="num_garantie" class="form-label">Numéro de Garantie <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="num_garantie" name="num_garantie" 
                           value="<?php echo htmlspecialchars($garantie['num_garantie']); ?>" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="agenceID" class="form-label">Agence <span class="text-danger">*</span></label>
                    <select class="form-select" id="agenceID" name="agenceID" required>
                        <option value="">Sélectionner une agence</option>
                        <?php foreach ($agences as $agence): ?>
                            <option value="<?php echo $agence['Id']; ?>" 
                                    <?php echo $agence['Id'] == $garantie['agenceID'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($agence['nom']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="montant_garantie" class="form-label">Montant <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="montant_garantie" name="montant_garantie" 
                           step="0.01" value="<?php echo htmlspecialchars($garantie['montant_garantie']); ?>" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="deviseID" class="form-label">Devise <span class="text-danger">*</span></label>
                    <select class="form-select" id="deviseID" name="deviseID" required>
                        <option value="">Sélectionner une devise</option>
                        <?php foreach ($devises as $devise): ?>
                            <option value="<?php echo $devise['Id']; ?>"
                                    <?php echo $devise['Id'] == $garantie['deviseID'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($devise['code'] . ' - ' . $devise['libelle']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="date_emission" class="form-label">Date d'Émission <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="date_emission" name="date_emission" 
                           value="<?php echo htmlspecialchars($garantie['date_emission']); ?>" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="date_expiration" class="form-label">Date d'Expiration <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="date_expiration" name="date_expiration" 
                           value="<?php echo htmlspecialchars($garantie['date_expiration']); ?>" required>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="appel_offreID" class="form-label">Appel d'Offre de Référence</label>
                <select class="form-select" id="appel_offreID" name="appel_offreID">
                    <option value="">Sélectionner un AO</option>
                    <?php foreach ($appels_offre as $ao): ?>
                        <option value="<?php echo $ao['Id']; ?>"
                                <?php echo $ao['Id'] == $garantie['appel_offreID'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($ao['num_app_offre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="structureID" class="form-label">Structure</label>
                <select class="form-select" id="structureID" name="structureID">
                    <option value="">Sélectionner une structure</option>
                    <?php foreach ($structures as $structure): ?>
                        <option value="<?php echo $structure['Id']; ?>"
                                <?php echo $structure['Id'] == $garantie['structureID'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($structure['libelle']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="soumissionnaireID" class="form-label">Fournisseur</label>
                <select class="form-select" id="soumissionnaireID" name="soumissionnaireID">
                    <option value="">Sélectionner un fournisseur</option>
                    <?php foreach ($fournisseurs as $fournisseur): ?>
                        <option value="<?php echo $fournisseur['Id']; ?>"
                                <?php echo $fournisseur['Id'] == $garantie['soumissionnaireID'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($fournisseur['nom_entreprise']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="statutID" class="form-label">Statut <span class="text-danger">*</span></label>
                <select class="form-select" id="statutID" name="statutID" required>
                    <option value="">Sélectionner un statut</option>
                    <?php foreach ($statuts as $statut): ?>
                        <option value="<?php echo $statut['Id']; ?>"
                                <?php echo $statut['Id'] == $garantie['statutID'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($statut['libelle']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Enregistrer les modifications
                </button>
                <a href="index.php?page=details-garantie&id=<?php echo $garantie['id']; ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Annuler
                </a>
            </div>
        </form>
    </div>
</div>
<!-- Add this script at the very end of modifier-garantie.php -->
<!-- Place it right before the final <?php  ?> line -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dateEmission = document.getElementById('date_emission');
    const dateExpiration = document.getElementById('date_expiration');
    const form = document.querySelector('form');

    function validateDates() {
        if (dateEmission.value && dateExpiration.value) {
            const emission = new Date(dateEmission.value);
            const expiration = new Date(dateExpiration.value);

            if (expiration < emission) {
                dateExpiration.setCustomValidity('La date d\'expiration ne peut pas être antérieure à la date d\'émission');
                return false;
            } else {
                dateExpiration.setCustomValidity('');
                return true;
            }
        }
        return true;
    }

    // Validate on change
    dateEmission.addEventListener('change', validateDates);
    dateExpiration.addEventListener('change', validateDates);

    // Validate on form submit
    form.addEventListener('submit', function(e) {
        if (!validateDates()) {
            e.preventDefault();
            alert('Erreur: La date d\'expiration ne peut pas être antérieure à la date d\'émission');
            return false;
        }
    });
});
</script>

<?php ?>
