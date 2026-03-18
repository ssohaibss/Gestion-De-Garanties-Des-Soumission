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
    const form = document.getElementById('deviseForm');

    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    function validateField(input) {
        const val = input.value.trim();
        const fb = input.nextElementSibling;
        input.classList.remove('is-invalid', 'is-valid');

        if (input.hasAttribute('required') && val === "") {
            input.classList.add('is-invalid');
            if(fb) fb.textContent = "Requis.";
            return false;
        }

        const pattern = new RegExp(input.dataset.pattern);
        if (val !== "" && !pattern.test(val)) {
            input.classList.add('is-invalid');
            if(fb) fb.textContent = input.dataset.msg;
            return false;
        }
        if(val !== "") input.classList.add('is-valid');
        return true;
    }

    async function checkUniqueness(input) {
        if (!validateField(input)) return false;
        const val = input.value.trim();
        const idValue = document.getElementById('deviseId').value;
        const fb = input.nextElementSibling;

        try {
            const res = await fetch(`pages/unique_check.php?type=devise&field=${input.name}&value=${encodeURIComponent(val)}&id=${idValue}`);
            const data = await res.json();
            if (data.exists) {
                input.classList.remove('is-valid');
                input.classList.add('is-invalid');
                if (fb) fb.textContent = `Ce ${input.name === 'code' ? 'code' : 'nom'} existe déjà.`;
                return false;
            }
        } catch (e) {}
        return true;
    }

    const debouncedCheck = debounce((input) => checkUniqueness(input), 500);

    // Listeners
    document.getElementById('libelleInput').addEventListener('input', function() {
        let val = this.value.replace(/[0-9]/g, '').replace(/ {2,}/g, ' '); 
        if (val.startsWith(' ')) val = val.trimStart();
        this.value = val;
        validateField(this);
        if(this.value) debouncedCheck(this);
    });

    document.getElementById('codeInput').addEventListener('input', function() {
        this.value = this.value.toUpperCase().replace(/[^A-Z]/g, '');
        validateField(this);
        if(this.value) debouncedCheck(this);
    });

    document.querySelectorAll('.intel-input').forEach(i => i.addEventListener('blur', () => checkUniqueness(i)));

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        let isValid = true;
        this.querySelectorAll('.intel-input').forEach(i => { if(!validateField(i)) isValid = false; });
        
        const inputs = this.querySelectorAll('.intel-input');
        for(const i of inputs) { if(!await checkUniqueness(i)) isValid = false; }

        //Send
        const btn = this.querySelector('button[type="submit"]');
        const oldText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement...';
        btn.disabled = true;

        if (!isValid) return;

        const res = await fetch('process.php', { method: 'POST', body: new FormData(this) });
        const data = await res.json();
        if (data.ok) {
            await Swal.fire({ icon: 'success', title: 'Succès !', timer: 1500, showConfirmButton: false, timerProgressBar: true });
            window.location.href = 'index.php?page=liste-devise';
        }
    });
});
</script>