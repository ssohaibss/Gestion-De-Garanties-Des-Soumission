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
                            data-pattern="^[A-Z0-9]+-[0-9]{1,4}$" 
                            data-msg="Format : CODEBANQUE-XXXX (Chiffres uniquement après le tiret).">
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
                                <option value="" data-code="">Sélectionner une banque</option>
                        <?php foreach ($banques as $b): ?>
                     <option value="<?= $b['id'] ?>" data-code="<?= htmlspecialchars($b['code']) ?>" <?= (isset($edit_data['banqueID']) && $edit_data['banqueID'] == $b['id']) ? 'selected' : '' ?>>
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
            let params = `type=agence&field=${input.name}&value=${encodeURIComponent(val)}&id=${idVal}`;
            
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
    const banqueSelect = document.getElementById('banqueID');
    const agenceCodeInput = document.getElementById('agenceCode');
    const nomInput = document.getElementById('agenceNom');

    if (banqueSelect && agenceCodeInput) {
        // Fonction pour bloquer/débloquer le champ Code Agence
        const toggleCodeInput = () => {
            if (banqueSelect.value === "") {
                agenceCodeInput.setAttribute('readonly', 'readonly');
                agenceCodeInput.placeholder = "Sélectionnez une banque d'abord";
            } else {
                agenceCodeInput.removeAttribute('readonly');
                agenceCodeInput.placeholder = "EX: BNA-012";
                // Retire le message d'erreur si la banque vient d'être sélectionnée
                if(agenceCodeInput.classList.contains('is-invalid') && agenceCodeInput.value === "") {
                    agenceCodeInput.classList.remove('is-invalid');
                }
            }
        };

        // Appliquer l'état initial (bloqué par défaut, sauf en mode édition)
        toggleCodeInput();

        // Événement : Changement de la banque
        banqueSelect.addEventListener('change', function() {
            toggleCodeInput(); // Met à jour l'état (bloqué/débloqué)
            
            const selectedOption = this.options[this.selectedIndex];
            const bankCode = selectedOption.getAttribute('data-code');
            
            if (bankCode) {
                let currentVal = agenceCodeInput.value;
                let suffix = '';
                // Conserver la partie après le tiret si elle existe déjà
                if (currentVal.includes('-')) {
                    suffix = currentVal.substring(currentVal.indexOf('-') + 1);
                }
                agenceCodeInput.value = bankCode + '-' + suffix;
            } else {
                agenceCodeInput.value = '';
                agenceCodeInput.classList.remove('is-valid', 'is-invalid');
            }
            
            if (agenceCodeInput.value) validateField(agenceCodeInput);
            if (nomInput && nomInput.value) debouncedCheck(nomInput);
        });

        // Événement : Clic sur le champ Code Agence sans avoir choisi de banque
        agenceCodeInput.addEventListener('click', function() {
            if (banqueSelect.value === "") {
                this.classList.add('is-invalid');
                const fb = this.parentElement.querySelector('.invalid-feedback');
                if (fb) fb.textContent = "Veuillez d'abord sélectionner une banque de la liste.";
            }
        });

       // Événement : Saisie dans le champ Code Agence
        // Événement : Saisie dans le champ Code Agence
        agenceCodeInput.addEventListener('input', function() {
            // Sécurité supplémentaire au cas où
            if (banqueSelect.value === "") {
                this.value = '';
                return;
            }
            
            const selectedOption = banqueSelect.options[banqueSelect.selectedIndex];
            const bankCode = selectedOption ? selectedOption.getAttribute('data-code') : '';
            
            if (bankCode) {
                const prefix = bankCode + '-';
                
                // On récupère la valeur saisie sans les espaces, tout en majuscules
                let rawValue = this.value.toUpperCase().replace(/\s/g, '');

                // 1. On s'assure que le préfixe est toujours présent
                if (!rawValue.startsWith(prefix)) {
                    let suffix = rawValue.replace(new RegExp('^' + bankCode.replace(/[^A-Z0-9]/g, '')), '');
                    suffix = suffix.replace(/^-/, ''); 
                    rawValue = prefix + suffix;
                }

                // 2. On isole la partie après le tiret
                let currentSuffix = rawValue.substring(prefix.length);
                
                // 3. NOUVEAU : On force le suffixe à n'être QUE des chiffres
                currentSuffix = currentSuffix.replace(/[^0-9]/g, '');

                // 4. On limite à 4 chiffres maximum
                if (currentSuffix.length > 4) {
                    currentSuffix = currentSuffix.substring(0, 4);
                }

                // On recompose la valeur finale
                this.value = prefix + currentSuffix;
            } else {
                // Fallback générique si pas de code banque (ne devrait pas arriver avec le readonly)
                this.value = this.value.toUpperCase().replace(/\s/g, '').replace(/[^A-Z0-9\-]/g, '');
            }

            validateField(this);
            if (this.value) debouncedCheck(this);
        });
    }

    ['agenceNom', 'agenceAdresse'].forEach(id => {
        const inputEl = document.getElementById(id);
        if (inputEl) {
            inputEl.addEventListener('input', function() {
                let val = this.value.replace(/ {2,}/g, ' '); 
                if (val.startsWith(' ')) val = val.trimStart();
                this.value = val;
                
                validateField(this);
                if (this.name === 'nom' && this.value) debouncedCheck(this);
                if (this.name === 'adresse') {
                    if(nomInput && nomInput.value) debouncedCheck(nomInput);
                }
            });
        }
    });

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

        if (!isValid) {
            const firstError = this.querySelector('.is-invalid');
            if (firstError) firstError.focus();
            return;
        }

        // Send
        const btn = this.querySelector('button[type="submit"]');
        const oldText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement...';
        btn.disabled = true;

        try {
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
                        const fb = input.parentElement.querySelector('.invalid-feedback');
                        if (fb) fb.textContent = msg;
                    }
                }
                btn.innerHTML = oldText;
                btn.disabled = false;
            }
        } catch (error) {
            console.error("Erreur de soumission:", error);
            btn.innerHTML = oldText;
            btn.disabled = false;
        }
    });
});
</script>
