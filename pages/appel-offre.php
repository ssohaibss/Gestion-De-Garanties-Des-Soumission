<?php
require_once dirname(__DIR__) . '/database.php';
$pdo = getDBConnection();

$edit_data = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM appel_offre WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_data = $stmt->fetch(PDO::FETCH_ASSOC);
}

$devises = $pdo->query("SELECT Id, code FROM devise ORDER BY code")->fetchAll();
?>

<div class="content-header mb-4">
    <h2><i class="fas fa-file-invoice me-2"></i>Gestion des Appels d'Offres</h2>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header text-white fw-bold" style="background-color: #486a70;">
        <i class="fas <?= $edit_data ? 'fa-edit' : 'fa-plus-circle' ?> me-2"></i> 
        <span><?= $edit_data ? "Modifier l'appel d'offre" : "Nouvel appel d'offre" ?></span>
    </div>
    <div class="card-body">
        <form id="aoForm" novalidate>
            <input type="hidden" name="form_type" value="<?= $edit_data ? 'update_appel_offre' : 'appel_offre' ?>">
            <input type="hidden" name="id" id="aoId" value="<?= $edit_data['id'] ?? '' ?>">
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Numéro d'Appel d'Offre <span class="text-danger">*</span></label>
                    <input type="text" name="numero_ao" id="numAOInput" class="form-control text-uppercase intel-input" 
                           value="<?= $edit_data['num_app_offre'] ?? '' ?>" required>
                    <div class="invalid-feedback"></div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Date d'Émission <span class="text-danger">*</span></label>
                    <input type="date" name="date_emission" id="dateInput" class="form-control intel-input" 
                           value="<?= $edit_data['date_emission'] ?? '' ?>" required>
                    <div class="invalid-feedback"></div>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Devise du dossier <span class="text-danger">*</span></label>
                    <select class="form-select intel-input" name="deviseID" id="deviseSelect" required>
                        <option value="">Sélectionner une devise...</option>
                        <?php foreach ($devises as $d): ?>
                            <option value="<?= $d['Id'] ?>" <?= (isset($edit_data['deviseID']) && $edit_data['deviseID'] == $d['Id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($d['code']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback">Veuillez choisir une devise.</div>
                </div>
            </div>
            
            <div class="d-flex gap-2 mt-3">
                <button type="submit" id="btnSubmit" class="btn ajouter text-white shadow-sm" style="background-color: #486a70;">
                    <i class="fas <?= $edit_data ? 'fa-sync' : 'fa-save' ?> me-2"></i>
                    <?= $edit_data ? 'Mettre à jour' : 'Enregistrer' ?>
                </button>
                <a href="index.php?page=liste-appels-offre" class="btn btn-secondary shadow-sm">Annuler</a>
            </div>
        </form>
    </div>
</div>



<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('aoForm');
    if (!form) return;

    // --- 1. UTILS & HELPERS ---
    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    function getFeedbackElement(input) {
        let fb = input.nextElementSibling;
        if (fb && fb.classList.contains('invalid-feedback')) return fb;
        if (input.parentElement) {
            fb = input.parentElement.querySelector('.invalid-feedback');
            if (fb) return fb;
        }
        const col = input.closest('.col-md-4, .col-md-6, .mb-3');
        if (col) return col.querySelector('.invalid-feedback');
        return null;
    }

    function showError(input, msg) {
        input.classList.remove('is-valid');
        input.classList.add('is-invalid');
        const fb = getFeedbackElement(input);
        if (fb) fb.textContent = msg;
    }

    function showSuccess(input) {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
    }

    //           VALIDATION
    function validateFormat(input) {
        const val = input.value.trim();
        
        // Required Check
        if (input.hasAttribute('required') && val === "") {
            showError(input, "Ce champ est requis.");
            return false;
        }

        // Length Check for AO
        if (input.name === 'numero_ao' && val.length < 3) {
            showError(input, "Minimum 3 caractères.");
            return false;
        }

        // Note: We do NOT set Green here for AO Number yet, 
        if (input.name !== 'numero_ao') {
            showSuccess(input);
        }
        return true;
    }

    // --- 3. SERVER CHECK ---
    async function checkUniqueAO(input) {
       
        if (!validateFormat(input)) return false;

        const val = input.value.trim();
        const id = document.getElementById('aoId').value || 0;

        try {
            const res = await fetch(`pages/unique_check.php?type=appel_offre&field=numero_ao&value=${encodeURIComponent(val)}&id=${id}`);
            const data = await res.json();
            
            if (data.exists) {
                showError(input, "Ce numéro existe déjà.");
                return false;
            } else {
                showSuccess(input);
                return true;
            }
        } catch (e) { return true; } 
    }

    const debouncedCheck = debounce((input) => checkUniqueAO(input), 500);

    //           LISTENERS 
    const numInput = document.getElementById('numAOInput');
    
    // AO Input Listener
    if (numInput) {
        numInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase().replace(/\s/g, '').replace(/[^A-Z0-9\/\-]/g, '');
            // Check format first
            if(validateFormat(this)) {
                // If format OK, debounce server check
                debouncedCheck(this);
            }
        });
    }

    // Standard Inputs Listener
    form.querySelectorAll('input, select, textarea').forEach(el => {
        if (el.id !== 'numAOInput') {
            const evt = el.tagName === 'SELECT' ? 'change' : 'input';
            el.addEventListener(evt, () => validateFormat(el));
        }
    });

    //              SUBMIT 
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        let isValid = true;

        // A. Validate Standard Fields
        const standardInputs = Array.from(this.querySelectorAll('input, select, textarea'))
                                    .filter(el => el.id !== 'numAOInput');
        
        standardInputs.forEach(el => {
            if (!validateFormat(el)) isValid = false;
        });

        // B. Validate AO Number (Format AND Uniqueness) explicitly
        if (numInput) {
            // 1. Check Format
            if (!validateFormat(numInput)) {
                isValid = false;
            } else {
                // 2. Check Uniqueness (Await result immediately)
                const isUnique = await checkUniqueAO(numInput);
                if (!isUnique) {
                    isValid = false; // Mark invalid if duplicate
                }
            }
        }

        // C. Stop if any errors found
        if (!isValid) {
            const err = this.querySelector('.is-invalid');
            if (err) err.focus();
            return;
        }

        // D. Send
        const btn = this.querySelector('button[type="submit"]');
        const oldText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement...';
        btn.disabled = true;

        try {
            const res = await fetch('process.php', { method: 'POST', body: new FormData(this) });
            const data = await res.json();
            
            if (data.ok) {
                await Swal.fire({ icon: 'success', title: 'Succès !', timer: 1500, showConfirmButton: false, timerProgressBar: true });
                window.location.href = 'index.php?page=liste-appels-offre';
            } else {
                Swal.fire('Erreur', data.message || 'Erreur inconnue', 'error');
                btn.innerHTML = oldText;
                btn.disabled = false;
            }
        } catch (err) {
            Swal.fire('Erreur', 'Erreur serveur', 'error');
            btn.innerHTML = oldText;
            btn.disabled = false;
        }
    });
});
</script>