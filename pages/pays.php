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
    const form = document.getElementById('paysForm');

    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    function validateField(input) {
        const val = input.value.trim();
        const fb = input.closest('.mb-3').querySelector('.invalid-feedback');
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
        const idValue = document.getElementById('paysId').value;
        const fb = input.closest('.mb-3').querySelector('.invalid-feedback');

        try {
            const res = await fetch(`pages/unique_check.php?type=pays&field=${input.name}&value=${encodeURIComponent(val)}&id=${idValue}`);
            const data = await res.json();
            if (data.exists) {
                input.classList.remove('is-valid');
                input.classList.add('is-invalid');
                if(fb) fb.textContent = `Ce ${input.name === 'nom' ? 'nom' : 'code'} est déjà utilisé.`;
                return false;
            }
        } catch (e) {}
        return true;
    }

    const debouncedCheck = debounce((input) => checkUniqueness(input), 500);

    document.getElementById('nomInput').addEventListener('input', function() {
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

        if (!isValid) return;

        //Send
        const btn = this.querySelector('button[type="submit"]');
        const oldText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement...';
        btn.disabled = true;

        const res = await fetch('process.php', { method: 'POST', body: new FormData(this) });
        const data = await res.json();
        if (data.ok) {
            await Swal.fire({ icon: 'success', title: 'Succès !', timer: 1500, showConfirmButton: false, timerProgressBar: true });
            window.location.href = 'index.php?page=liste-pays';
        }
    });
});
</script>
