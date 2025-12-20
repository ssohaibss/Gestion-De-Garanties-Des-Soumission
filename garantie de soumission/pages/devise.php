<?php
require_once dirname(__DIR__) . '/database.php';

// Fetch existing currencies for display
$stmt = $pdo->prepare("SELECT * FROM devise ORDER BY code ASC");
$stmt->execute();
$currencies = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        
        <form id="deviseForm" action="process.php" method="POST" novalidate>
            <input type="hidden" name="form_type" value="devise">
            
            <div class="mb-3">
                <label for="libelle" class="form-label">Nom de la Devise <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="libelle" name="libelle">
                <div class="invalid-feedback">Veuillez saisir le nom de la devise.</div>
                <small class="form-text text-muted">Ex: Dinar Algérien, Euro, Dollar Américain</small>
            </div>
            
            <div class="mb-3">
                <label for="code" class="form-label">Code Devise (ISO) <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="code" name="code" maxlength="3">
                <div class="invalid-feedback">Le code ISO (3 caractères) est obligatoire.</div>
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
                    <?php if (empty($currencies)): ?>
                        <tr><td colspan="2" class="text-center">Aucune devise trouvée</td></tr>
                    <?php else: ?>
                        <?php foreach ($currencies as $currency): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($currency['libelle']); ?></td>
                                <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($currency['code']); ?></span></td>
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
    const form = document.getElementById('deviseForm');
    const libelleInput = document.getElementById('libelle');
    const codeInput = document.getElementById('code');

    // 1. Restriction: Letters and spaces only
    [libelleInput, codeInput].forEach(input => {
        input.addEventListener('input', function() {
            this.value = this.value.replace(/[^a-zA-ZÀ-ÿ\s\-]/g, '');
            this.classList.remove('is-invalid'); // Clean red border when typing
        });
    });

    // 2. Modern Form Submission Check
    form.addEventListener('submit', function(e) {
        let isValid = true;

        // Reset borders
        [libelleInput, codeInput].forEach(el => el.classList.remove('is-invalid'));

        // Validate Libelle
        if (libelleInput.value.trim() === "") {
            libelleInput.classList.add('is-invalid');
            isValid = false;
        }

        // Validate Code
        if (codeInput.value.trim() === "") {
            codeInput.classList.add('is-invalid');
            isValid = false;
        }

        if (!isValid) {
            e.preventDefault(); // Stop form submission
        }
    });
});
</script>