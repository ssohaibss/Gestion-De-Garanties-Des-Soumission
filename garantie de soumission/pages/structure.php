<?php

require_once dirname(__DIR__) . '/database.php';

// Fetch existing structures for display
$pdo = getDBConnection();
$stmt = $pdo->prepare("SELECT * FROM structure ORDER BY libelle ASC");
$stmt->execute();
$structures = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-header">
    <h2><i class="fas fa-sitemap"></i> Ajouter une Structure</h2>
</div>

<div class="card">
    <div class="card-header">
        <i class="fas fa-building"></i> Formulaire Structure
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
            <input type="hidden" name="form_type" value="structure">
            
            <div class="mb-3">
                <label for="code" class="form-label">Code Structure <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="code" name="code" required>
            </div>
            
            <div class="mb-3">
                <label for="libelle" class="form-label">Nom de la Structure <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="libelle" name="libelle" required>
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
        <i class="fas fa-list"></i> Liste des Structures
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                       
                        <th>Code</th>
                        <th>Libelle</th>
                        
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($structures as $structure): ?>
                        <tr>
                            
                            <td><?php echo htmlspecialchars($structure['code']); ?></td>
                            <td><?php echo htmlspecialchars($structure['libelle']); ?></td>
                            
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
