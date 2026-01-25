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
        <i class="fas fa-edit me-2"></i> 
        <span><?= $edit_data ? "Modifier l'agence : " . htmlspecialchars($edit_data['nom']) : "Ajouter une nouvelle agence" ?></span>
    </div>
    <div class="card-body">
        <form id="agenceForm" novalidate>
            <input type="hidden" name="form_type" value="agence">
            <input type="hidden" name="id" id="agenceId" value="<?= $edit_data['id'] ?? '' ?>">
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">
                        Code Agence <span class="text-danger" <?= $edit_data ? 'style="display:none;"' : '' ?>>*</span>
                    </label>
                    <input type="text" name="code" id="agenceCode" class="form-control intel-input" 
                           value="<?= $edit_data['code'] ?? '' ?>" required
                           data-pattern="^[A-Z0-9\-]{3,10}$" 
                           data-msg="3-10 Lettres (Lettres suivi d'un tiret et chiffres uniquement).">
                    <div class="invalid-feedback"></div>
                    <?php if ($edit_data): ?>
                        <small class="text-muted d-block mt-1"><strong>L'identifiant unique de l'agence</strong></small>
                    <?php endif; ?>
                </div>
                <div class="col-md-8 mb-3">
                    <label class="form-label fw-bold">Nom de l'Agence <span class="text-danger">*</span></label>
                    <input type="text" name="nom" class="form-control intel-input" 
                           value="<?= $edit_data['nom'] ?? '' ?>" required
                           data-pattern="^[a-zA-ZÀ-ÿ0-9\s\-\.']{3,}$" 
                           data-msg="Nom invalide (min. 3 car.).">
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Banque <span class="text-danger">*</span></label>
                    <select class="form-select" name="banqueID" required>
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
                    <input type="text" name="adresse" class="form-control intel-input" 
                           value="<?= $edit_data['adresse'] ?? '' ?>" required
                           data-pattern=".{5,}" 
                           data-msg="L'adresse doit être plus précise (min 5 car.).">
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn ajouter shadow-sm text-white" style="background-color: #486a70;">
                    <i class="fas <?= $edit_data ? 'fa-sync' : 'fa-save' ?> me-2"></i>
                    <?= $edit_data ? 'Mettre à jour l\'agence' : 'Enregistrer l\'agence' ?>
                </button>
                <a href="index.php?page=liste-agence" class="btn btn-secondary shadow-sm">Annuler / Retour</a>
            </div>
        </form>
    </div>
</div>

<script>
document.querySelectorAll('.intel-input').forEach(input => {
    input.addEventListener('input', function() {
        // Transformation en temps réel pour le Code : Majuscules + suppression espaces
        if(this.id === 'agenceCode') {
            this.value = this.value.toUpperCase().replace(/\s/g, '').replace(/[^A-Z0-9\-]/g, '');
        }

        const pattern = new RegExp(this.dataset.pattern);
        if (this.value !== "" && !pattern.test(this.value)) {
            this.classList.add('is-invalid');
            const feedback = this.parentElement.querySelector('.invalid-feedback');
            if (feedback) feedback.textContent = this.dataset.msg;
        } else {
            this.classList.remove('is-invalid');
        }
    });
});

document.getElementById('agenceForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    if (this.querySelectorAll('.is-invalid').length > 0) return;

    try {
        const res = await fetch('process.php', { method: 'POST', body: new FormData(this) });
        const data = await res.json();
        if (data.ok) {
            await Swal.fire({ icon: 'success', title: 'Opération réussie', timer: 1500, showConfirmButton: false, timerProgressBar: true });
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
    } catch (err) { Swal.fire('Erreur', 'Lien rompu', 'error'); }
});
</script>