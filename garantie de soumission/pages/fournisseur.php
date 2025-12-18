<?php
require_once 'database.php';
$pdo = getDBConnection();
$pays_result = $pdo->query("SELECT id, nom FROM pays ORDER BY nom")->fetchAll();
?>

<div class="content-header">
    <h2><i class="fas fa-truck"></i> Ajouter un Fournisseur</h2>
</div>

<div class="card">
    <div class="card-header">
        <i class="fas fa-building"></i> Formulaire Fournisseur
    </div>
    <div class="card-body">
        <form id="fournisseurForm" action="process.php" method="POST">
            <input type="hidden" name="form_type" value="fournisseur">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nom" class="form-label">Nom de l'entreprise <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nom" name="nom" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="telephone" class="form-label">Téléphone <span class="text-danger">*</span></label>
                    <input type="tel" class="form-control" id="telephone" name="telephone" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="pays" class="form-label">Pays <span class="text-danger">*</span></label>
                    <select class="form-select" id="pays" name="pays" required>
                        <option value="">Sélectionner un pays</option>
                        <?php foreach ($pays_result as $pays): ?>
                            <option value="<?= $pays['id'] ?>"><?= htmlspecialchars($pays['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="adresse" class="form-label">Adresse <span class="text-danger">*</span></label>
                <textarea class="form-control" id="adresse" name="adresse" rows="3" required></textarea>
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

<?php // Removed mysqli close() call ?>
