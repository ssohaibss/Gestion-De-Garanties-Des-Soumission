<?php
require_once dirname(__DIR__) . '/database.php';

// Fetch existing countries for display
$stmt = $pdo->prepare("SELECT Nom, code_pays FROM pays ORDER BY Nom ASC");
$stmt->execute();
$countries = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-header">
    <h2><i class="fas fa-globe"></i> Ajouter un Pays</h2>
</div>

<div class="card">
    <div class="card-header">
        <i class="fas fa-flag"></i> Formulaire Pays
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
        
        <form id="paysForm" action="process.php" method="POST" novalidate>
            <input type="hidden" name="form_type" value="pays">
            
            <div class="mb-3">
                <label for="nom" class="form-label">Nom du Pays <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="nom" name="nom">
                <div class="invalid-feedback">Le nom du pays est obligatoire.</div>
            </div>
            
            <div class="mb-3">
                <label for="code_pays" class="form-label">Code Pays (ISO) <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="code_pays" name="code_pays" maxlength="3">
                <div class="invalid-feedback">Le code pays est obligatoire.</div>
                <small class="form-text text-muted">Ex: DZ pour Algérie, US pour État-Unis</small>
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
        <i class="fas fa-list"></i> Liste des Pays
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Nom du Pays</th>
                        <th>Code Pays</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($countries)): ?>
                        <tr><td colspan="2" class="text-center">Aucun pays trouvé</td></tr>
                    <?php else: ?>
                        <?php foreach ($countries as $country): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($country['Nom']); ?></td>
                                <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($country['code_pays']); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('paysForm');
    const nomInput = document.getElementById('nom');
    const codeInput = document.getElementById('code_pays');

    // 1. Restriction: Allow only letters, spaces, and hyphens
    [nomInput, codeInput].forEach(input => {
        input.addEventListener('input', function() {
            this.value = this.value.replace(/[^a-zA-ZÀ-ÿ\s\-]/g, '');
            this.classList.remove('is-invalid');
        });
    });

    // 2. Modern Form Submission Check
    form.addEventListener('submit', function(e) {
        let isValid = true;

        [nomInput, codeInput].forEach(input => input.classList.remove('is-invalid'));

        if (nomInput.value.trim() === "") {
            nomInput.classList.add('is-invalid');
            isValid = false;
        }

        if (codeInput.value.trim() === "") {
            codeInput.classList.add('is-invalid');
            isValid = false;
        }

        if (!isValid) {
            e.preventDefault();
        }
    });
});
</script>