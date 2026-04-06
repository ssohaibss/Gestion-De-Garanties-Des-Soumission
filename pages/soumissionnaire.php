<?php
require_once dirname(__DIR__) . '/database.php';
$pdo = getDBConnection();

$edit_data = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM soumissionnaire WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_data = $stmt->fetch(PDO::FETCH_ASSOC);
}

$pays_result = $pdo->query("SELECT id, nom FROM pays ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-header mb-4">
    <h2><i class="fas fa-truck me-2"></i>Gestion des Soumissionnaires</h2>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header text-white fw-bold" style="background-color: #486a70;">
        <i class="fas <?= $edit_data ? 'fa-edit' : 'fa-plus-circle' ?> me-2"></i> 
        <span><?= $edit_data ? "Modifier le soumissionaire" : "Nouveau soumissionnaire" ?></span>
    </div>
    <div class="card-body">
        <form id="soumissionnaireForm" novalidate>
            <input type="hidden" name="form_type" value="soumissionnaire">
            <input type="hidden" name="id" id="soumissionnaireId" value="<?= $edit_data['id'] ?? '' ?>">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Nom de l'entreprise <span class="text-danger">*</span></label>
                    <input type="text" name="nom" id="nomInput" class="form-control intel-input" 
                           value="<?= $edit_data['nom_entreprise'] ?? '' ?>" required
                           data-pattern="^[a-zA-ZÀ-ÿ\s\-\.']{3,}$" 
                           data-msg="Lettres uniquement (min. 3).">
                    <div class="invalid-feedback"></div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" id="emailInput" class="form-control intel-input" 
                           value="<?= $edit_data['email'] ?? '' ?>" required
                           data-pattern="^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$" 
                           data-msg="Format invalide (ex: nom@example.com).">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Téléphone <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">+</span>
                        <input type="tel" name="telephone" id="telInput" class="form-control intel-input" 
                               value="<?= isset($edit_data['telephone']) ? str_replace('+', '', $edit_data['telephone']) : '' ?>" required
                               data-pattern="^[0-9]{8,15}$" 
                               data-msg="8 à 15 chiffres requis.">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Pays <span class="text-danger">*</span></label>
                    <select class="form-select" name="pays" required>
                        <option value="">Sélectionner...</option>
                        <?php foreach ($pays_result as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= (isset($edit_data['paysID']) && $edit_data['paysID'] == $p['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback">Veuillez choisir un pays.</div>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-bold">Adresse <span class="text-danger">*</span></label>
                <textarea class="form-control intel-input" name="adresse" id="adresseInput" rows="2" required
                          data-pattern=".{5,}" data-msg="Veuillez entrer une adresse complète."><?= $edit_data['adresse'] ?? '' ?></textarea>
                <div class="invalid-feedback"></div>
            </div>
            
            <div class="d-flex gap-2 mt-3">
                <button type="submit" class="btn ajouter text-white shadow-sm" style="background-color: #486a70;">
                    <i class="fas <?= $edit_data ? 'fa-sync' : 'fa-save' ?> me-2"></i>
                    <?= $edit_data ? 'Mettre à jour' : 'Enregistrer' ?>
                </button>
                <a href="index.php?page=liste-soumissionnaire" class="btn btn-secondary shadow-sm">Annuler</a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('soumissionnaireForm');

    // --- 1. UTILS (Delay function) ---
    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    // --- 2. FIND FEEDBACK ELEMENT (Robust) ---
    function getFeedbackElement(input) {
        // Try next sibling
        let fb = input.nextElementSibling;
        if (fb && fb.classList.contains('invalid-feedback')) return fb;
        
        // Try parent's feedback (for input-groups or col-md)
        if (input.parentElement) {
            fb = input.parentElement.querySelector('.invalid-feedback');
            if (fb) return fb;
        }
        
        // Try closest container
        const container = input.closest('.mb-3, .col-md-6, .col-md-4, .col-md-12');
        if (container) return container.querySelector('.invalid-feedback');
        
        return null;
    }

    // --- 3. LOCAL VALIDATION (Format only) ---
    function validateField(input) {
        const val = input.value.trim();
        const fb = getFeedbackElement(input);
        
        input.classList.remove('is-invalid', 'is-valid');

        // Check Required
        if (input.hasAttribute('required') && val === "") {
            input.classList.add('is-invalid');
            if (fb) fb.textContent = "Ce champ est requis.";
            return false;
        }

        // Check Regex (if exists)
        if (val !== "" && input.dataset.pattern) {
            const pattern = new RegExp(input.dataset.pattern);
            if (!pattern.test(val)) {
                input.classList.add('is-invalid');
                if (fb) fb.textContent = input.dataset.msg || "Format invalide.";
                return false;
            }
        }
        
        // If basic format is OK, set Green (Unique check might override this later)
        if (val !== "") input.classList.add('is-valid');
        return true;
    }

  // --- 4. SERVER UNIQUE CHECK ---
    async function checkUniqueness(input) {
        // First, check basic format. If empty or invalid regex, don't check server.
        if (!validateField(input)) return false;
        
        const val = input.value.trim();
        if (!val) return true;

        const idElement = document.getElementById('soumissionnaireId');
        const idValue = idElement ? idElement.value : 0;
        const fb = getFeedbackElement(input);
        const fieldName = input.name;

        // Only check these fields
        if (!["nom", "email", "telephone"].includes(fieldName)) return true;

        // ---> THE FIX IS HERE <---
        // The database stores phone numbers with the '+', so we must append it 
        // to the value we are searching for, otherwise the DB won't find a match.
        let searchValue = val;
        if (fieldName === 'telephone') {
            searchValue = '+' + val;
        }

        try {
            // Send searchValue instead of the raw val
            const res = await fetch(`pages/unique_check.php?type=soumissionnaire&field=${fieldName}&value=${encodeURIComponent(searchValue)}&id=${idValue}`);
            if (!res.ok) return true;

            const data = await res.json();
            
            if (data.exists) {
                // FORCE RED ERROR LIVE
                input.classList.remove('is-valid'); // Remove Green
                input.classList.add('is-invalid');  // Add Red
                if (fb) fb.textContent = "Cette valeur est déjà utilisée."; // Update Text
                return false;
            } else {
                // Ensure Green if Unique
                if(!input.classList.contains('is-invalid')) input.classList.add('is-valid');
                return true;
            }
        } catch (e) { return true; }
    }

    // Create the debounced function (Waits 500ms after typing stops)
    const debouncedCheck = debounce((input) => checkUniqueness(input), 500);

    // --- 5. LISTENERS ---
    
    // TELEPHONE (Live Check + Clean)
    const telInput = document.getElementById('telInput');
    if(telInput) {
        telInput.addEventListener('input', function() {
            // 1. Strict Cleaning (Numbers Only)
            this.value = this.value.replace(/[^0-9]/g, ''); 
            // 2. Basic Validation (Green)
            validateField(this);
            // 3. Trigger Server Check (Will turn Red if exists)
            if(this.value.length > 0) debouncedCheck(this);
        });
    }

    // Email
    const emailInput = document.getElementById('emailInput');
    if(emailInput) {
        emailInput.addEventListener('input', function() {
            this.value = this.value.replace(/\s/g, '').toLowerCase();
            validateField(this);
            if(this.value) debouncedCheck(this);
        });
    }

    // Nom
    const nomInput = document.getElementById('nomInput');
    if(nomInput) {
        nomInput.addEventListener('input', function() {
            let val = this.value.replace(/[0-9]/g, '').replace(/ {2,}/g, ' '); 
            if (val.startsWith(' ')) this.value = this.value.trimStart();
            validateField(this);
            if(this.value) debouncedCheck(this);
        });
    }

    // --- NEW: ADRESSE LIVE CHECK ---
    const adrInput = document.getElementById('adresseInput');
    if(adrInput) {
        adrInput.addEventListener('input', function() {
            validateField(this);
        });
    }

    // General (Covers selects and other inputs not caught above)
    form.querySelectorAll('input, select, textarea').forEach(el => {
        if(el.tagName === 'SELECT') {
            el.addEventListener('change', () => validateField(el));
        }
        // General text inputs if ID doesn't match specific ones
        if((el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') && 
           !['telInput', 'emailInput', 'nomInput', 'adresseInput'].includes(el.id)) {
            el.addEventListener('input', () => validateField(el));
        }
    });

    // Blur Check (Extra safety when leaving field)
    document.querySelectorAll('.intel-input').forEach(i => i.addEventListener('blur', () => checkUniqueness(i)));

    // --- 6. SUBMIT ---
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        let isValid = true;
        const uniqueInputsToCheck = [];

        //Send
        const btn = this.querySelector('button[type="submit"]');
        const oldText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement...';
        btn.disabled = true;

        // Validate all fields visually first
        this.querySelectorAll('input, select, textarea').forEach(i => { 
            if(!validateField(i)) isValid = false; 
            if(["nom", "email", "telephone"].includes(i.name)) {
                uniqueInputsToCheck.push(i);
            }
        });
        
        // Wait for final server check
        for(const i of uniqueInputsToCheck) { 
            // Only check if not already marked invalid by live check
            if (!i.classList.contains('is-invalid')) {
                if(!await checkUniqueness(i)) isValid = false; 
            } else {
                isValid = false;
            }
        }

        if (!isValid) {
            const firstError = this.querySelector('.is-invalid');
            if(firstError) {
                firstError.scrollIntoView({behavior: 'smooth', block: 'center'});
                firstError.focus();
            }
            return;
        }

        try {
            const res = await fetch('process.php', { method: 'POST', body: new FormData(this) });
            const data = await res.json();
            
            if (data.ok) {
                await Swal.fire({ icon: 'success', title: 'Succès !', timer: 1500, showConfirmButton: false, timerProgressBar: true });
                window.location.href = 'index.php?page=liste-soumissionnaire';
            } else {
                if (data.errors) {
                    for (const [key, msg] of Object.entries(data.errors)) {
                        const input = form.querySelector(`[name="${key}"]`);
                        if (input) {
                            input.classList.add('is-invalid');
                            const fb = getFeedbackElement(input);
                            if(fb) fb.textContent = msg;
                        }
                    }
                    Swal.fire('Erreur', 'Veuillez corriger les erreurs.', 'error');
                } else {
                    Swal.fire('Erreur', data.message || 'Erreur inconnue.', 'error');
                }
            }
        } catch (err) {
            Swal.fire('Erreur', 'Impossible de contacter le serveur.', 'error');
        }
    });
});
</script>