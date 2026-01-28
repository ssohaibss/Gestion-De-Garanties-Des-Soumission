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
    // 1. Nettoyage strict des inputs
    document.getElementById('agenceCode').addEventListener('input', function() {
        this.value = this.value.toUpperCase().replace(/\s/g, '').replace(/[^A-Z0-9\-]/g, '');
    });

    ['agenceNom', 'agenceAdresse'].forEach(id => {
        document.getElementById(id).addEventListener('input', function() {
            let val = this.value.replace(/ {2,}/g, ' '); 
            if (val.startsWith(' ')) val = val.trimStart();
            this.value = val;
        });
    });

    // 2. Validation Blur + Unique Check
    document.querySelectorAll('.intel-input').forEach(input => {
        input.addEventListener('blur', async function() {
            const val = this.value.trim();
            const idVal = document.getElementById('agenceId').value;
            const fb = this.parentElement.querySelector('.invalid-feedback');
            
            this.classList.remove('is-invalid', 'is-valid');
            if (val === "") return;

            const pattern = new RegExp(this.dataset.pattern);
            if (!pattern.test(val)) {
                this.classList.add('is-invalid');
                if (fb) fb.textContent = this.dataset.msg;
                return;
            }

            if (this.name === 'code' || this.name === 'nom') {
                try {
                    const res = await fetch(`pages/unique_check.php?type=agence&field=${this.name}&value=${encodeURIComponent(val)}&id=${idVal}`);
                    const data = await res.json();
                    if (data.exists) {
                        this.classList.add('is-invalid');
                        if (fb) fb.textContent = `Ce ${this.name} existe déjà.`;
                    } else {
                        this.classList.add('is-valid');
                    }
                } catch (e) { console.error(e); }
            } else {
                this.classList.add('is-valid');
            }
        });
    });

    // 3. Submit
    document.getElementById('agenceForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        this.querySelectorAll('.intel-input').forEach(i => i.dispatchEvent(new Event('blur')));

        if (this.querySelectorAll('.is-invalid').length > 0) return;

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
        }
    });
});
</script>