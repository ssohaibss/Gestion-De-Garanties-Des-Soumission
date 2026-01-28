<?php
require_once dirname(__DIR__) . '/database.php';
$pdo = getDBConnection();

$edit_data = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM pays WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_data = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<div class="content-header mb-4">
    <h2><i class="fas fa-globe me-2"></i>Gestion des Pays</h2>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header text-white fw-bold" style="background-color: #486a70;">
        <i class="fas <?= $edit_data ? 'fa-edit' : 'fa-plus-circle' ?> me-2"></i> 
        <span><?= $edit_data ? "Modifier le pays" : "Ajouter un nouveau pays" ?></span>
    </div>
    <div class="card-body">
        <form id="paysForm" novalidate>
            <input type="hidden" name="form_type" value="pays">
            <input type="hidden" name="id" id="paysId" value="<?= $edit_data['id'] ?? '' ?>">
            
            <div class="row">
                <div class="col-md-8 mb-3">
                    <label class="form-label fw-bold">Nom du Pays <span class="text-danger">*</span></label>
                    <input type="text" name="nom" id="nomInput" class="form-control intel-input" 
                           value="<?= $edit_data['nom'] ?? '' ?>" required
                           data-pattern="^[a-zA-ZÀ-ÿ\s\-']{2,50}$" 
                           data-msg="Veuillez entrer un nom valide (lettres uniquement).">
                    <div class="invalid-feedback"></div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Code ISO <span class="text-danger">*</span></label>
                    <input type="text" name="code_pays" id="codeInput" class="form-control intel-input text-uppercase" 
                           value="<?= $edit_data['code_pays'] ?? '' ?>" required maxlength="3"
                           data-pattern="^[A-Z]{2,3}$" 
                           data-msg="2 ou 3 lettres majuscules (ex: DZ).">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            
            <div class="d-flex gap-2 mt-3">
                <button type="submit" class="btn ajouter text-white shadow-sm" style="background-color: #486a70;">
                    <i class="fas <?= $edit_data ? 'fa-sync' : 'fa-save' ?> me-2"></i>
                    <?= $edit_data ? 'Mettre à jour' : 'Enregistrer' ?>
                </button>
                <a href="index.php?page=liste-pays" class="btn btn-secondary shadow-sm">Annuler</a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const nomInput = document.getElementById('nomInput');
    const codeInput = document.getElementById('codeInput');

    // NETTOYAGE TEMPS RÉEL
    nomInput.addEventListener('input', function() {
        let val = this.value.replace(/[0-9]/g, ''); // Pas de chiffres
        val = val.replace(/ {2,}/g, ' ');           // Pas de doubles espaces
        if (val.startsWith(' ')) val = val.trimStart();
        this.value = val;
    });

    codeInput.addEventListener('input', function() {
        this.value = this.value.toUpperCase().replace(/[^A-Z]/g, ''); // Lettres majuscules uniquement
    });

    // Validation Blur
    document.querySelectorAll('.intel-input').forEach(input => {
        input.addEventListener('blur', async function() {
            this.value = this.value.trim();
            const fb = this.closest('.mb-3').querySelector('.invalid-feedback');
            const pattern = new RegExp(this.dataset.pattern);
            const fieldName = this.name;
            const value = this.value;
            const idValue = document.getElementById('paysId').value;

            this.classList.remove('is-invalid', 'is-valid');
            if (value === "") return;

            if (!pattern.test(value)) {
                this.classList.add('is-invalid');
                if (fb) fb.textContent = this.dataset.msg; 
                return; 
            }

            try {
                const response = await fetch(`pages/unique_check.php?type=pays&field=${fieldName}&value=${encodeURIComponent(value)}&id=${idValue}`);
                const data = await response.json();
                if (data.exists) {
                    this.classList.add('is-invalid');
                    if (fb) fb.textContent = `Ce ${fieldName === 'nom' ? 'nom' : 'code'} est déjà enregistré.`;
                } else {
                    this.classList.add('is-valid');
                }
            } catch (e) { console.error(e); }
        });
    });

    document.getElementById('paysForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        if (this.querySelectorAll('.is-invalid').length > 0) return;

        try {
            const res = await fetch('process.php', { method: 'POST', body: new FormData(this) });
            const data = await res.json();
            if (data.ok) {
                await Swal.fire({ icon: 'success', title: 'Succès !', timer: 1500, showConfirmButton: false, timerProgressBar: true  });
                window.location.href = 'index.php?page=liste-pays';
            }
        } catch (err) { console.error(err); }
    });
});
</script>