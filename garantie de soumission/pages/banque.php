<?php
require_once dirname(__DIR__) . '/database.php';

$stmt = $pdo->prepare("SELECT Id, nom FROM pays ORDER BY nom ASC");
$stmt->execute();
$pays = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="content-header">
    <h2><i class="fas fa-university"></i> Ajouter une Banque</h2>
</div>

<div class="card">
    <div class="card-header">
        <i class="fas fa-landmark"></i> Formulaire Banque
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
        
        <form id="banqueForm" action="process.php" method="POST">
            <input type="hidden" name="form_type" value="banque">
            
            <div class="mb-3">
                <label for="nom_banque" class="form-label">Nom de la Banque <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="nom_banque" name="nom_banque" required>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="code" class="form-label">Code Banque <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="code" name="code" required>
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
