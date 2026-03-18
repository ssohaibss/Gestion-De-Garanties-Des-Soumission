<?php
require_once dirname(__DIR__) . '/database.php';
$pdo = getDBConnection();

$edit_data = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM agence WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_data = $stmt->fetch(PDO::FETCH_ASSOC);
}
$banques = $pdo->query("SELECT * FROM banque ORDER BY nom_banque ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-header mb-4">
    <h2><i class="fas fa-map-marked-alt me-2"></i>Gestion des Agences</h2>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header text-white fw-bold" style="background-color: #486a70;">
        <i class="fas <?= $edit_data ? 'fa-edit' : 'fa-plus-circle' ?> me-2"></i> 
        <span><?= $edit_data ? "Modifier l'agence : " . htmlspecialchars($edit_data['nom']) : "Ajouter une nouvelle agence" ?></span>
    </div>
    <div class="card-body">
        <form id="agenceForm" novalidate>
            <input type="hidden" name="form_type" value="agence">
            <input type="hidden" name="id" id="agenceId" value="<?= $edit_data['id'] ?? '' ?>">
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Code Agence <span class="text-danger">*</span></label>
                    <input type="text" name="code" id="agenceCode" class="form-control intel-input text-uppercase" 
                           value="<?= $edit_data['code'] ?? '' ?>" required
                           data-pattern="^[A-Z0-9\-]{3,10}$" 
                           data-msg="3-10 car. (Lettres, chiffres, tirets).">
                    <div class="invalid-feedback"></div>
                </div>
                <div class="col-md-8 mb-3">
                    <label class="form-label fw-bold">Nom de l'Agence <span class="text-danger">*</span></label>
                    <input type="text" name="nom" id="agenceNom" class="form-control intel-input" 
                           value="<?= $edit_data['nom'] ?? '' ?>" required
                           data-pattern="^[a-zA-ZÀ-ÿ0-9\s\-\.']{3,}$" 
                           data-msg="Nom invalide (min. 3 car.).">
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Banque <span class="text-danger">*</span></label>
                    <select class="form-select" name="banqueID" id="banqueID" required>
                        <option value="">Sélectionner une banque</option>
                        <?php foreach ($banques as $b): ?>
                            <option value="<?= $b['id'] ?>" <?= (isset($edit_data['banqueID']) && $edit_data['banqueID'] == $b['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($b['nom_banque']) ?> (<?= htmlspecialchars($b['code']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback">Veuillez choisir une banque.</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Adresse <span class="text-danger">*</span></label>
                    <input type="text" name="adresse" id="agenceAdresse" class="form-control intel-input" 
                           value="<?= $edit_data['adresse'] ?? '' ?>" required
                           data-pattern=".{5,}" 
                           data-msg="Adresse trop courte (min 5 car.).">
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn ajouter text-white shadow-sm" style="background-color: #486a70;">
                    <i class="fas <?= $edit_data ? 'fa-sync' : 'fa-save' ?> me-2"></i>
                    <?= $edit_data ? 'Mettre à jour' : 'Enregistrer' ?>
                </button>
                <a href="index.php?page=liste-agence" class="btn btn-secondary shadow-sm">Annuler</a>
            </div>
        </form>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('agenceForm');

    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    function validateField(input) {
        const val = input.value.trim();
        const fb = input.parentElement.querySelector('.invalid-feedback');
        input.classList.remove('is-invalid', 'is-valid');

        if (input.hasAttribute('required') && val === "") {
            input.classList.add('is-invalid');
            if (fb) fb.textContent = "Ce champ est requis.";
            return false;
        }

        const pattern = new RegExp(input.dataset.pattern);
        if (val !== "" && !pattern.test(val)) {
            input.classList.add('is-invalid');
            if (fb) fb.textContent = input.dataset.msg;
            return false;
        }

        if (val !== "") input.classList.add('is-valid');
        return true;
    }

    async function checkUniqueness(input) {
        if (!validateField(input)) return false;
        
        const val = input.value.trim();
        const idVal = document.getElementById('agenceId').value;
        const fb = input.parentElement.querySelector('.invalid-feedback');

        if (input.name !== 'code' && input.name !== 'nom') return true;

        try {
            // CORRECTION: Ajout de banqueID et adresse pour le contexte
            let params = `type=agence&field=${input.name}&value=${encodeURIComponent(val)}&id=${idVal}`;
            
            // Si on check le NOM, on ajoute les infos contextuelles pour unique_check.php
            if(input.name === 'nom') {
                const banqueID = document.querySelector('select[name="banqueID"]').value;
                const adresse = document.getElementById('agenceAdresse').value;
                params += `&banqueID=${encodeURIComponent(banqueID)}&adresse=${encodeURIComponent(adresse)}`;
            }

            const res = await fetch(`pages/unique_check.php?${params}`);
            const data = await res.json();
            
            if (data.exists) {
                input.classList.remove('is-valid');
                input.classList.add('is-invalid');
                if (fb) fb.textContent = data.message || `Ce ${input.name} existe déjà.`;
                return false;
            } else {
                if(!input.classList.contains('is-invalid')) input.classList.add('is-valid');
                return true;
            }
        } catch (e) { return true; }
    }

    const debouncedCheck = debounce((input) => checkUniqueness(input), 500);

    // --- Listeners ---
    document.getElementById('agenceCode').addEventListener('input', function() {
        this.value = this.value.toUpperCase().replace(/\s/g, '').replace(/[^A-Z0-9\-]/g, '');
        validateField(this);
        if (this.value) debouncedCheck(this);
    });

    ['agenceNom', 'agenceAdresse'].forEach(id => {
        document.getElementById(id).addEventListener('input', function() {
            let val = this.value.replace(/ {2,}/g, ' '); 
            if (val.startsWith(' ')) val = val.trimStart();
            this.value = val;
            
            validateField(this);
            // On re-check le nom si l'adresse change (car unicité combinée)
            if (this.name === 'nom' && this.value) debouncedCheck(this);
            if (this.name === 'adresse') {
                const nomInput = document.getElementById('agenceNom');
                if(nomInput.value) debouncedCheck(nomInput);
            }
        });
    });

    // Re-check nom si la banque change
    const banqueSelect = document.querySelector('select[name="banqueID"]');
    if(banqueSelect) {
        banqueSelect.addEventListener('change', function() {
            const nomInput = document.getElementById('agenceNom');
            if(nomInput.value) debouncedCheck(nomInput);
        });
    }

    document.querySelectorAll('.intel-input').forEach(input => {
        input.addEventListener('blur', () => checkUniqueness(input));
    });

    // --- Submit ---
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        let isValid = true;
        const uniqueInputs = [];

        this.querySelectorAll('input, select').forEach(i => {
            if (!validateField(i)) isValid = false;
            if (i.name === 'code' || i.name === 'nom') uniqueInputs.push(i);
        });

        for (const input of uniqueInputs) {
            if (!await checkUniqueness(input)) isValid = false;
        }

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
            window.location.href = 'index.php?page=liste-agence';
        } else if (data.errors) {
            for (const [key, msg] of Object.entries(data.errors)) {
                const input = this.querySelector(`[name="${key}"]`);
                if (input) {
                    input.classList.add('is-invalid');
                    input.parentElement.querySelector('.invalid-feedback').textContent = msg;
                }
            }
        }
    });
});
</script>
