<?php
require_once dirname(__DIR__) . '/database.php';

$stmt = $pdo->prepare("SELECT Id, code, libelle FROM devise ORDER BY code ASC");
$stmt->execute();
$devises = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-header">
    <h2><i class="fas fa-file-invoice"></i> Ajouter un Appelle d'Offre</h2>
</div>

<div class="card">
    <div class="card-header">
        <i class="fas fa-file-contract"></i> Formulaire Appelle d'Offre
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
        
        <form id="appelleOffreForm" action="process.php" method="POST">
            <input type="hidden" name="form_type" value="appelle_offre">
            
            <div class="mb-3">
                <label for="numero_ao" class="form-label">Numéro d'Appelle d'Offre <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="numero_ao" name="numero_ao" required>
            </div>
            
           
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="date_lancement" class="form-label">Date de Lancement <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="date_lancement" name="date_lancement" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="date_limite" class="form-label">Date Limite de Soumission <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="date_limite" name="date_limite" required>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="montant_estime" class="form-label">Montant Estimé</label>
                    <input type="number" class="form-control" id="montant_estime" name="montant_estime" step="0.01">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="devise_ao" class="form-label">Devise</label>
                    <select class="form-select" id="devise_ao" name="devise_ao">
                        <option value="">Sélectionner une devise</option>
                        <?php foreach ($devises as $devise): ?>
                            <option value="<?php echo $devise['Id']; ?>">
                                <?php echo htmlspecialchars($devise['code'] . ' - ' . $devise['libelle']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
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
