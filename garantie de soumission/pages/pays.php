<?php

require_once dirname(__DIR__) . '/database.php';

// Fetch existing countries for display
$stmt = $pdo->prepare("SELECT * FROM pays ORDER BY nom ASC");
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
        
        <form action="process.php" method="POST">
            <input type="hidden" name="form_type" value="pays">
            
            <div class="mb-3">
                <label for="nom" class="form-label">Nom du Pays <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="nom" name="nom" required>
            </div>
            
            <div class="mb-3">
                <label for="code_pays" class="form-label">Code Pays (ISO) <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="code_pays" name="code_pays" maxlength="3" required>
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
                    <?php foreach ($countries as $country): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($country['Nom']); ?></td>
                            <td><?php echo htmlspecialchars($country['code_pays']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>


// form restriction 
const inputs = document.querySelectorAll('#nom, #code_pays')
inputs.forEach(input => {
    input.addEventListener('input', function (e) {
        this.value = this.value.replace(/[^a-zA-ZÀ-ÿ\s\-]/g, '');
    });
});

//unique check
document.addEventListener('DOMContentLoaded', function() {
    const fields = ['nom', 'code_pays'];
    const submitBtn = document.querySelector('button[type="submit"]');

    fields.forEach(fieldId => {
        const input = document.getElementById(fieldId);
        
        input.addEventListener('blur', function() { // Fires when user clicks away
            const value = this.value;
            if (value.length < 2) return;

            fetch('unique_check.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `field=${fieldId}&value=${encodeURIComponent(value)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.exists) {
                    this.classList.add('is-invalid');
                    alert(`Ce ${fieldId === 'nom' ? 'nom' : 'code'} existe déjà !`);
                    submitBtn.disabled = true;
                } else {
                    this.classList.remove('is-invalid');
                    submitBtn.disabled = false;
                }
            });
        });
    });
});


</script>


