<?php
require_once dirname(__DIR__) . '/database.php';
$pdo = getDBConnection();

$edit_data = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_data = $stmt->fetch(PDO::FETCH_ASSOC);
}

$roles = $pdo->query("SELECT id, libelle FROM role ORDER BY libelle")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-header mb-4">
    <h2><i class="fas fa-user-circle me-2"></i>Gestion des Utilisateurs</h2>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header text-white fw-bold" style="background-color: #486a70;">
        <i class="fas fa-user-edit me-2"></i> 
        <span><?= $edit_data ? "Modifier : " . htmlspecialchars($edit_data['username']) : "Nouveau compte utilisateur" ?></span>
    </div>
    <div class="card-body">
        <form id="userForm" novalidate>
            <input type="hidden" name="form_type" value="user">
            <input type="hidden" name="id" id="userId" value="<?= $edit_data['id'] ?? '' ?>">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Nom <span class="text-danger">*</span></label>
                    <input type="text" class="form-control intel-input text-uppercase" name="nom" id="nomInput" 
                           value="<?= $edit_data['nom'] ?? '' ?>" required
                           data-pattern="^[a-zA-ZÀ-ÿ\s\-]{3,}$" data-msg="Lettres uniquement (min. 3).">
                    <div class="invalid-feedback"></div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Prénom <span class="text-danger">*</span></label>
                    <input type="text" class="form-control intel-input" name="prenom" id="prenomInput"
                           value="<?= $edit_data['prenom'] ?? '' ?>" required
                           data-pattern="^[a-zA-ZÀ-ÿ\s\-]{3,}$" data-msg="Lettres uniquement (min. 3).">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Email Professionnel <span class="text-danger">*</span></label>
                    <input type="email" class="form-control intel-input" name="email" id="emailInput"
                           value="<?= $edit_data['email'] ?? '' ?>" required
                         data-pattern="^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$" data-msg="Veuillez entrer une adresse email valide.">
                    <div class="invalid-feedback"></div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Rôle <span class="text-danger">*</span></label>
                    <select class="form-select intel-input" name="role" required>
                        <option value="">Sélectionner un rôle</option>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?= $role['id'] ?>" <?= (isset($edit_data['roleID']) && $edit_data['roleID'] == $role['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($role['libelle']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Login (Nom d'utilisateur) <span class="text-danger">*</span></label>
                    <input type="text" class="form-control intel-input" name="username" id="usernameInput"
                           value="<?= $edit_data['username'] ?? '' ?>" required
                           data-pattern="^[a-zA-Z0-9._\-]{4,}$" data-msg="Min. 4 car. (lettres/chiffres, sans espace).">
                    <div class="invalid-feedback"></div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">
                        Mot de passe <?= $edit_data ? '' : '<span class="text-danger">*</span>' ?>
                    </label>
                    <div class="input-group">
                        <input type="password" class="form-control intel-input" name="password" id="passwordField"
                               data-pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&_\-])[A-Za-z\d@$!%*?&_\-]{8,}$"
                               data-msg="8 car. min, 1 Maj, 1 Min, 1 Chiffre et 1 Symbole.">
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword"><i class="fas fa-eye"></i></button>
                        <div class="invalid-feedback"></div>
                    </div>
                    <?php if ($edit_data): ?>
                        <small class="text-muted">Laissez vide pour conserver le mot de passe actuel.</small>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn ajouter text-white shadow-sm" style="background-color: #486a70;">
                    <i class="fas <?= $edit_data ? 'fa-sync' : 'fa-save' ?> me-2"></i>
                    <?= $edit_data ? 'Mettre à jour' : 'Enregistrer l\'utilisateur' ?>
                </button>
                <a href="index.php?page=liste-user" class="btn btn-secondary shadow-sm">Annuler</a>
            </div>
        </form>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('userForm');
    if (!form) return;

    // --- 1. UTILS ---
    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    // --- 2. FEEDBACK FINDER ---
    function getFeedbackElement(input) {
        let fb = input.nextElementSibling;
        if (fb && fb.classList.contains('invalid-feedback')) return fb;
        if (input.parentElement) {
            fb = input.parentElement.querySelector('.invalid-feedback');
            if (fb) return fb;
        }
        const container = input.closest('.mb-3');
        if (container) return container.querySelector('.invalid-feedback');
        return null;
    }

    // --- 3. LOCAL VALIDATION ---
    function validateField(input) {
        const val = input.value.trim();
        const fb = getFeedbackElement(input);
        
        input.classList.remove('is-invalid', 'is-valid');

        // SPECIAL: Password in Edit Mode
        if (input.id === 'passwordField' && document.getElementById('userId').value !== "") {
            if (val === "") {
                return true; 
            }
        }

        // Required Check
        if (input.hasAttribute('required') && val === "") {
            input.classList.add('is-invalid');
            if (fb) fb.textContent = "Ce champ est requis.";
            return false;
        }

        // Pattern Check
        if (val !== "" && input.hasAttribute('data-pattern')) {
            const pattern = new RegExp(input.getAttribute('data-pattern'));
            if (!pattern.test(val)) {
                input.classList.add('is-invalid');
                if (fb) fb.textContent = input.getAttribute('data-msg') || "Format invalide.";
                return false;
            }
        }

        // Green by default
        if (val !== "") input.classList.add('is-valid');
        return true;
    }

    // --- 4. SERVER UNIQUE CHECK ---
    async function checkUniqueness(input) {
        if (!validateField(input)) return false; 

        const val = input.value.trim();
        if (!val) return true;

        const id = document.getElementById('userId').value || 0;
        const field = input.name; 
        const fb = getFeedbackElement(input);

        if (!['username', 'email'].includes(field)) return true;

        try {
            const res = await fetch(`pages/unique_check.php?type=user&field=${field}&value=${encodeURIComponent(val)}&id=${id}`);
            if (!res.ok) return true; 

            const data = await res.json();
            
            if (data.exists) {
                input.classList.remove('is-valid');
                input.classList.add('is-invalid');
                if (fb) fb.textContent = `Ce ${field === 'email' ? 'email' : 'login'} est déjà pris.`;
                return false;
            } else {
                if(!input.classList.contains('is-invalid')) input.classList.add('is-valid');
                return true;
            }
        } catch (e) { return true; }
    }

    const debouncedCheck = debounce((input) => checkUniqueness(input), 500);

    // --- 5. LISTENERS ---
    const userIn = document.getElementById('usernameInput');
    if (userIn) {
        userIn.addEventListener('input', function() {
            this.value = this.value.replace(/\s/g, ''); 
            validateField(this);
            if (this.value.length > 0) debouncedCheck(this);
        });
    }

    const emailIn = document.getElementById('emailInput');
    if (emailIn) {
        emailIn.addEventListener('input', function() {
            this.value = this.value.replace(/\s/g, '').toLowerCase(); 
            validateField(this);
            if (this.value.length > 0) debouncedCheck(this);
        });
    }

    form.querySelectorAll('input, select').forEach(el => {
        if (el.id !== 'usernameInput' && el.id !== 'emailInput') {
            const evt = el.tagName === 'SELECT' ? 'change' : 'input';
            el.addEventListener(evt, () => validateField(el));
        }
    });

    document.getElementById('togglePassword')?.addEventListener('click', function() {
        const f = document.getElementById('passwordField');
        const i = this.querySelector('i');
        f.type = f.type === 'password' ? 'text' : 'password';
        i.classList.toggle('fa-eye'); i.classList.toggle('fa-eye-slash');
    });

    // --- 6. SUBMIT ---
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        let isValid = true;
        const uniqueInputsToCheck = [];

        this.querySelectorAll('input, select').forEach(i => {
            if (!validateField(i)) isValid = false;
            if (['username', 'email'].includes(i.name)) uniqueInputsToCheck.push(i);
        });

        for (const input of uniqueInputsToCheck) {
            if (!input.classList.contains('is-invalid')) {
                if (!await checkUniqueness(input)) isValid = false;
            } else {
                isValid = false;
            }
        }

        if (!isValid) {
            const firstError = this.querySelector('.is-invalid');
            if (firstError) firstError.focus();
            return;
        }

        const btn = this.querySelector('button[type="submit"]');
        const oldText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement...';
        btn.disabled = true;

        try {
            const res = await fetch('process.php', { method: 'POST', body: new FormData(this) });
            const text = await res.text(); // Capture raw text first to avoid crashing
            
            let data;
            try {
                data = JSON.parse(text);
            } catch (err) {
                console.error("Erreur serveur (JSON invalide):", text);
                Swal.fire('Erreur technique', 'Vérifiez la console (F12) pour voir l\'erreur PHP.', 'error');
                btn.innerHTML = oldText;
                btn.disabled = false;
                return;
            }
            
            if (data.ok) {
                await Swal.fire({ icon: 'success', title: 'Succès !', timer: 1500, showConfirmButton: false, timerProgressBar: true });
                window.location.href = 'index.php?page=liste-user';
            } else {
                // Safely extract the exact error message text 
                let errorMsg = data.message || 'Validation échouée.';
                
                if (data.errors && typeof data.errors === 'object') {
                    // This prevents [object Object] from happening again
                    errorMsg = Object.values(data.errors).join('<br><br>');
                }
                
                Swal.fire({
                    title: 'Erreur d\'enregistrement',
                    html: `<div style="text-align: left; font-size: 15px;">${errorMsg}</div>`,
                    icon: 'error'
                });
                
                btn.innerHTML = oldText;
                btn.disabled = false;
            }
        } catch (err) {
            console.error(err);
            Swal.fire('Erreur', 'Impossible de joindre le serveur.', 'error');
            btn.innerHTML = oldText;
            btn.disabled = false;
        }
    });
});
</script>