<?php
require_once dirname(__DIR__) . '/database.php';
$pdo = getDBConnection();
// Fetch existing currencies for display
$stmt = $pdo->prepare(query: "SELECT * FROM devise ORDER BY code ASC");
$stmt->execute();
$currencies = $stmt->fetchAll();
?>


<div class="content-header">
    <h2><i class="fas fa-money-bill"></i> Ajouter une Devise</h2>
</div>

<div class="card">
    <div class="card-header">
        <i class="fas fa-dollar-sign"></i> Formulaire Devise
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
            <input type="hidden" name="form_type" value="devise">
            
            <div class="mb-3">
                <label for="libelle" class="form-label">Nom de la Devise <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="libelle" name="libelle" required>
                <small class="form-text text-muted">Ex: Dinar Algérien, Euro, Dollar Américain</small>
            </div>
            
            <div class="mb-3">
                <label for="code" class="form-label">Code Devise (ISO) <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="code" name="code" maxlength="3" required>
                <small class="form-text text-muted">Ex: DZD, EUR, USD</small>
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
        <i class="fas fa-list"></i> Liste des Devises
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Code</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($currencies as $currency): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($currency['libelle']); ?></td>
                            <td><?php echo htmlspecialchars($currency['code']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
