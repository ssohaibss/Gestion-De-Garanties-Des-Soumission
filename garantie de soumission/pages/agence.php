<?php
require_once dirname(__DIR__) . '/database.php';


// Fetch existing agencies for display
$agences = $pdo
    ->query("SELECT * FROM agence ORDER BY nom ASC")
    ->fetchAll(PDO::FETCH_ASSOC);

$banques = $pdo
    ->query("SELECT Id, nom_banque FROM banque ORDER BY nom_banque ASC")
    ->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="content-header">
    <h2><i class="fas fa-map-marked-alt"></i> Ajouter une Agence</h2>
</div>

<div class="card">
    <div class="card-header">
        <i class="fas fa-map-marked-alt"></i> Formulaire Agence
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
        
        <form action="process.php" method="POST">
            <input type="hidden" name="form_type" value="agence">
            
            <div class="mb-3">
                <label for="code" class="form-label">Code Agence <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="code" name="code" required>
            </div>
            
            <div class="mb-3">
                <label for="nom" class="form-label">Nom de l'Agence <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="nom" name="nom" required>
            </div>
            
            <div class="mb-3">
                <label for="banqueID" class="form-label">Banque</label>
                <select class="form-select" id="banqueID" name="banqueID">
                    <option value="">Sélectionnez une banque</option>
                    <?php foreach ($banques as $banque): ?>
                        <option value="<?php echo $banque['Id']; ?>"><?php echo htmlspecialchars($banque['nom_banque']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="adresse" class="form-label">Adresse</label>
                <input type="text" class="form-control" id="adresse" name="adresse">
            </div>
            
        
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Enregistrer
                </button>
                <button type="reset" class="btn btn-secondary">
                    <i class="fas fa-redo"></i> Réinitialiser
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <i class="fas fa-list"></i> Liste des Agences
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Code</th>
                        <th>Nom</th>
                        <th>Adresse</th>
                       
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($agences) > 0): ?>
                        <?php foreach ($agences as $agence): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($agence['id']); ?></td>
                                <td><?php echo htmlspecialchars($agence['code']); ?></td>
                                <td><?php echo htmlspecialchars($agence['nom']); ?></td>
                                <td><?php echo htmlspecialchars($agence['adresse'] ?? ''); ?></td>
                                
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">Aucune agence trouvée</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
