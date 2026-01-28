<?php
require_once dirname(__DIR__) . '/database.php';
$pdo = getDBConnection();

$edit_data = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM devise WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_data = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<div class="content-header mb-4">
    <h2><i class="fas fa-coins me-2"></i>Gestion des Devises</h2>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header text-white fw-bold" style="background-color: #486a70;">
        <i class="fas <?= $edit_data ? 'fa-edit' : 'fa-plus-circle' ?> me-2"></i> 
        <span><?= $edit_data ? "Modifier la devise" : "Ajouter une Devise" ?></span>
    </div>
    <div class="card-body">
        <form id="deviseForm" novalidate>
            <input type="hidden" name="form_type" value="devise">
            <input type="hidden" name="id" id="deviseId" value="<?= $edit_data['id'] ?? '' ?>">

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Nom de la devise <span class="text-danger">*</span></label>
                    <input type="text" name="libelle" id="libelleInput" class="form-control intel-input" 
                           value="<?= $edit_data['libelle'] ?? '' ?>" required
                           data-pattern="^[a-zA-ZÀ-ÿ\s\-']{3,}$"
                           data-msg="Lettres uniquement (min. 3).">
                    <div class="invalid-feedback"></div>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Code devise (ISO) <span class="text-danger">*</span></label>
                    <input type="text" name="code" id="codeInput" class="form-control intel-input text-uppercase" 
                           maxlength="3" value="<?= $edit_data['code'] ?? '' ?>" required
                           data-pattern="^[A-Z]{3}$"
                           data-msg="Exactement 3 lettres (ex: DZD).">
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <div class="d-flex gap-2 mt-3">
                <button type="submit" class="btn ajouter text-white shadow-sm" style="background-color: #486a70;">
                    <i class="fas <?= $edit_data ? 'fa-sync' : 'fa-save' ?> me-2"></i>
                    <?= $edit_data ? 'Mettre à jour' : 'Enregistrer' ?>
                </button>
                <a href="index.php?page=liste-devise" class="btn btn-secondary shadow-sm">Annuler</a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const libelleInput = document.getElementById('libelleInput');
    const codeInput = document.getElementById('codeInput');

    // NETTOYAGE TEMPS RÉEL
    libelleInput.addEventListener('input', function() {
        let val = this.value.replace(/[0-9]/g, ''); 
        val = val.replace(/ {2,}/g, ' '); 
        if (val.startsWith(' ')) val = val.trimStart();
        this.value = val;
    });

    codeInput.addEventListener('input', function() {
        this.value = this.value.toUpperCase().replace(/[^A-Z]/g, '');
    });

    // Validation Blur (Format + Unicité)
    document.querySelectorAll('.intel-input').forEach(input => {
        input.addEventListener('blur', async function() {
            this.value = this.value.trim();
            const fb = this.nextElementSibling;
            const pattern = new RegExp(this.dataset.pattern);
            const idValue = document.getElementById('deviseId').value;

            this.classList.remove('is-invalid', 'is-valid');
            if (this.value === "") return;

            if (!pattern.test(this.value)) {
                this.classList.add('is-invalid');
                if (fb) fb.textContent = this.dataset.msg; 
                return; 
            }

            try {
                const response = await fetch(`pages/unique_check.php?type=devise&field=${this.name}&value=${encodeURIComponent(this.value)}&id=${idValue}`);
                const data = await response.json();
                if (data.exists) {
                    this.classList.add('is-invalid');
                    if (fb) fb.textContent = `Ce ${this.name === 'code' ? 'code ISO' : 'nom'} existe déjà.`;
                } else {
                    this.classList.add('is-valid');
                }
            } catch (e) { console.error(e); }
        });
    });

    document.getElementById('deviseForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        if (this.querySelectorAll('.is-invalid').length > 0) return;

        try {
            const res = await fetch('process.php', { method: 'POST', body: new FormData(this) });
            const data = await res.json();
            if (data.ok) {
                await Swal.fire({ icon: 'success', title: 'Succès !', timer: 1500, showConfirmButton: false, timerProgressBar: true  });
                window.location.href = 'index.php?page=liste-devise';
            }
        } catch (err) { console.error(err); }
    });
});
</script>