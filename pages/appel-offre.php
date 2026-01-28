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
    const numInput = document.getElementById('numAOInput');
    const aoId = document.getElementById('aoId').value;

    // 1. Nettoyage en temps réel (pas d'espaces)
    numInput.addEventListener('input', function() {
        this.value = this.value.toUpperCase().replace(/\s/g, '').replace(/[^A-Z0-9\/\-]/g, '');
    });

    // 2. Validation au BLUR (quand on quitte le champ)
    document.querySelectorAll('.intel-input').forEach(input => {
        input.addEventListener('blur', async function() {
            const fieldName = this.name;
            const value = this.value.trim();
            const feedback = this.closest('.mb-3, .col-md-4').querySelector('.invalid-feedback');

            this.classList.remove('is-invalid', 'is-valid');
            if (value === "") return;

            // Règle de longueur minimum pour le numéro
            if (fieldName === 'numero_ao') {
                if (value.length < 3) {
                    this.classList.add('is-invalid');
                    if (feedback) feedback.textContent = "Le numéro est trop court (min. 3 caract.).";
                    return;
                }
                
                // Vérification d'unicité via AJAX
                try {
                    const res = await fetch(`pages/unique_check.php?type=appel_offre&field=numero_ao&value=${encodeURIComponent(value)}&id=${aoId}`);
                    const data = await res.json();
                    if (data.exists) {
                        this.classList.add('is-invalid');
                        if (feedback) feedback.textContent = "Ce numéro de dossier existe déjà.";
                    } else {
                        this.classList.add('is-valid');
                    }
                } catch (e) { console.error("Erreur unicité:", e); }
            } else {
                this.classList.add('is-valid');
            }
        });
    });

    // 3. Soumission du formulaire
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Reset des styles d'erreur
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

        try {
            const res = await fetch('process.php', { method: 'POST', body: new FormData(this) });
            const data = await res.json();

            if (data.ok) {
                await Swal.fire({ 
                    icon: 'success', 
                    title: 'Dossier enregistré !', 
                    timer: 1500, 
                    showConfirmButton: false,
                    timerProgressBar: true // Ton progrès conservé ici
                });
                window.location.href = 'index.php?page=liste-appels-offre';
            } else if (data.errors) {
                // Affichage des erreurs retournées par process.php (ex: date 0001)
                Object.entries(data.errors).forEach(([field, msg]) => {
                    const inp = form.querySelector(`[name="${field}"]`);
                    if (inp) {
                        inp.classList.add('is-invalid');
                        const fb = inp.closest('.mb-3, .col-md-4').querySelector('.invalid-feedback');
                        if (fb) fb.textContent = msg;
                    }
                });
            }
        } catch (err) {
            Swal.fire({ icon: 'error', title: 'Erreur', text: 'Connexion serveur impossible.' });
        }
    });
});
</script>