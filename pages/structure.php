<?php
require_once dirname(__DIR__) . '/database.php';
$pdo = getDBConnection();

$edit_data = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM structure WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_data = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<div class="content-header mb-4">
    <h2><i class="fas fa-sitemap me-2"></i>Gestion des Structures</h2>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header text-white fw-bold" style="background-color: #486a70;">
        <i class="fas <?= $edit_data ? 'fa-edit' : 'fa-plus-circle' ?> me-2"></i> 
        <span><?= $edit_data ? "Modifier la structure : " . htmlspecialchars($edit_data['libelle']) : "Nouvelle Structure" ?></span>
    </div>
    <div class="card-body">
        <form id="structureForm" novalidate>
            <input type="hidden" name="form_type" value="structure">
            <input type="hidden" name="id" id="structureId" value="<?= $edit_data['id'] ?? '' ?>">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Libellé <span class="text-danger">*</span></label>
                    <input type="text" class="form-control intel-input" name="libelle" id="libelleInput"
                           value="<?= $edit_data['libelle'] ?? '' ?>" required
                           data-pattern="^[a-zA-ZÀ-ÿ\s\-']{3,}$"
                           data-msg="Lettres uniquement (min. 3).">
                    <div class="invalid-feedback"></div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Code (Acronyme) <span class="text-danger">*</span></label>
                    <input type="text" class="form-control intel-input text-uppercase" name="code" id="codeInput"
                           value="<?= $edit_data['code'] ?? '' ?>" required maxlength="6"
                           data-pattern="^[A-Z]{2,6}$"
                           data-msg="2 à 6 lettres majuscules.">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            
            <div class="d-flex gap-2 mt-2">
                <button type="submit" class="btn ajouter text-white shadow-sm" style="background-color: #486a70;">
                    <i class="fas <?= $edit_data ? 'fa-sync' : 'fa-save' ?> me-2"></i> 
                    <?= $edit_data ? 'Mettre à jour' : 'Enregistrer' ?>
                </button>
                <a href="index.php?page=liste-structure" class="btn btn-secondary shadow-sm">Annuler</a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('structureForm');
    const libelleInput = document.getElementById('libelleInput');
    const codeInput = document.getElementById('codeInput');

    // BLOCAGE TEMPS RÉEL : Libellé
    libelleInput.addEventListener('input', function() {
        let val = this.value;
        val = val.replace(/[0-9]/g, '');       // Supprime les chiffres
        val = val.replace(/ {2,}/g, ' ');      // Bloque le double espace instantanément
        if (val.startsWith(' ')) val = '';     // Empêche de commencer par un espace
        this.value = val;
    });

    // BLOCAGE TEMPS RÉEL : Code
    codeInput.addEventListener('input', function() {
        // Force majuscules et retire TOUT ce qui n'est pas une lettre A-Z (espaces inclus)
        this.value = this.value.toUpperCase().replace(/[^A-Z]/g, '');
    });

    // Validation Blur (Unicité)
    document.querySelectorAll('.intel-input').forEach(input => {
        input.addEventListener('blur', async function() {
            // Nettoyage final (enlève l'espace qui pourrait traîner à la fin)
            this.value = this.value.trim();

            const fb = this.nextElementSibling;
            const pattern = new RegExp(this.dataset.pattern);
            const val = this.value;
            const idVal = document.getElementById('structureId').value;

            this.classList.remove('is-invalid', 'is-valid');
            if (val === "") return;

            if (!pattern.test(val)) {
                this.classList.add('is-invalid');
                if (fb) fb.textContent = this.dataset.msg;
                return;
            }

            try {
                const res = await fetch(`pages/unique_check.php?type=structure&field=${this.name}&value=${encodeURIComponent(val)}&id=${idVal}`);
                const data = await res.json();
                if (data.exists) {
                    this.classList.add('is-invalid');
                    if (fb) fb.textContent = "Cette valeur existe déjà.";
                } else {
                    this.classList.add('is-valid');
                }
            } catch (e) { console.error(e); }
        });
    });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (form.querySelectorAll('.is-invalid').length > 0) return;

        try {
            const res = await fetch('process.php', { method: 'POST', body: new FormData(form) });
            const data = await res.json();
            if (data.ok) {
                await Swal.fire({ 
                    icon: 'success', title: 'Réussi', 
                    timer: 1500, showConfirmButton: false, timerProgressBar: true 
                });
                window.location.href = 'index.php?page=liste-structure';
            } else if (data.errors) {
                for (const [key, msg] of Object.entries(data.errors)) {
                    const el = form.querySelector(`[name="${key}"]`);
                    if (el) {
                        el.classList.add('is-invalid');
                        if (el.nextElementSibling) el.nextElementSibling.textContent = msg;
                    }
                }
            }
        } catch (err) { console.error(err); }
    });
});
</script>