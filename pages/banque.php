<?php
require_once dirname(__DIR__) . '/database.php';
$pdo = getDBConnection();

$edit_data = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM banque WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_data = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<div class="content-header mb-4">
    <h2><i class="fas fa-university me-2"></i>Gestion des Banques</h2>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header text-white fw-bold" style="background-color: #486a70;">
        <i class="fas fa-edit me-2"></i> 
        <span><?= $edit_data ? "Modifier la banque : " . htmlspecialchars($edit_data['nom_banque']) : "Ajouter une nouvelle banque" ?></span>
    </div>
    <div class="card-body">
        <form id="banqueForm" novalidate>
            <input type="hidden" name="form_type" value="banque">
            <input type="hidden" name="id" id="banqueId" value="<?= $edit_data['id'] ?? '' ?>">
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">
                        Code Banque <span class="text-danger" <?= $edit_data ? 'style="display:none;"' : '' ?>>*</span>
                    </label>
                    <input type="text" class="form-control intel-input" name="code" id="banqueCode" 
                           value="<?= $edit_data['code'] ?? '' ?>" required
                           data-pattern="^[A-Z]{3,5}$" 
                           data-msg="3 à 5 lettres uniquement (pas de chiffres).">
                    <div class="invalid-feedback"></div>
                    <?php if ($edit_data): ?>
                        <small class="text-muted d-block mt-1"><strong>Le code identifiant de la banque</strong></small>
                    <?php endif; ?>
                </div>
                <div class="col-md-8 mb-3">
                    <label class="form-label fw-bold">Nom de la Banque <span class="text-danger">*</span></label>
                    <input type="text" class="form-control intel-input" name="nom_banque" id="banqueNom" 
                           value="<?= $edit_data['nom_banque'] ?? '' ?>" required
                           data-pattern="^[a-zA-ZÀ-ÿ0-9\s\-\.']{3,}$" 
                           data-msg="Nom invalide (min. 3 car.).">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn ajouter shadow-sm">
                    <i class="fas <?= $edit_data ? 'fa-sync' : 'fa-save' ?> me-2"></i>
                    <?= $edit_data ? 'Mettre à jour la banque' : 'Enregistrer la banque' ?>
                </button>
                <a href="index.php?page=liste-banque" class="btn btn-secondary shadow-sm">Annuler / Retour</a>
            </div>
        </form>
    </div>
</div>

<script>
document.querySelectorAll('.intel-input').forEach(input => {
    input.addEventListener('input', function() {
        // Règle spécifique : Code en Lettres MAJUSCULES uniquement (pas de chiffres)
        if(this.id === 'banqueCode') {
            this.value = this.value.replace(/[^a-zA-Z]/g, '').toUpperCase();
        }
        
        const pattern = new RegExp(this.dataset.pattern);
        if (this.value !== "" && !pattern.test(this.value)) {
            this.classList.add('is-invalid');
            const feedback = this.closest('.mb-3').querySelector('.invalid-feedback');
            if (feedback) feedback.textContent = this.dataset.msg;
        } else {
            this.classList.remove('is-invalid');
        }
    });
});

document.getElementById('banqueForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    if (document.querySelectorAll('.is-invalid').length > 0) return;

    const res = await fetch('process.php', { method: 'POST', body: new FormData(e.target) });
    const data = await res.json();
    if (data.ok) {
        await Swal.fire({ 
            icon: 'success', title: 'Succès', 
            timer: 1500, showConfirmButton: false, timerProgressBar: true 
        });
        window.location.href = 'index.php?page=liste-banque';
    } else if (data.errors) {
        for (const [key, msg] of Object.entries(data.errors)) {
            const input = e.target.querySelector(`[name="${key}"]`);
            if (input) {
                input.classList.add('is-invalid');
                const feedback = input.closest('.mb-3').querySelector('.invalid-feedback');
                if (feedback) feedback.textContent = msg;
            }
        }
    }
});
</script>
