<?php
require_once dirname(__DIR__) . '/database.php';
$pdo = getDBConnection();

$edit_data = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM pays WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_data = $stmt->fetch(PDO::FETCH_ASSOC);
}

$countries = $pdo->query("SELECT * FROM pays ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-header mb-4">
    <h2><i class="fas fa-globe me-2"></i>Gestion des Pays</h2>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header text-white" style="background-color: #486a70;">
        <i class="fas fa-plus-circle me-2"></i> 
        <span id="formTitle"><?= $edit_data ? "Modifier : " . htmlspecialchars($edit_data['nom']) : "Ajouter un Pays" ?></span>
    </div>
    <div class="card-body">
        <form id="paysForm" novalidate>
            <input type="hidden" name="form_type" value="pays">
            <input type="hidden" name="id" id="paysId" value="<?= $edit_data['id'] ?? '' ?>">

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Nom du pays <span class="text-danger">*</span></label>
                    <input type="text" name="nom" id="nomInput" class="form-control intel-input" 
                           value="<?= $edit_data['nom'] ?? '' ?>" required
                           data-pattern="^[a-zA-ZÀ-ÿ\s\-]{2,}$"
                           data-msg="Nom invalide (min. 2 lettres).">
                    <div class="invalid-feedback"></div>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Code pays (ISO) <span class="text-danger">*</span></label>
                    <input type="text" name="code_pays" id="codeInput" class="form-control intel-input" 
                           maxlength="3" value="<?= $edit_data['code_pays'] ?? '' ?>" required
                           data-pattern="^[A-Z]{2,3}$"
                           data-msg="2 ou 3 lettres majuscules requises.">
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <div class="d-flex gap-2 mt-2">
                <button type="submit" id="submitBtn" class="btn ajouter shadow-sm">
                    <i class="fas <?= $edit_data ? 'fa-sync' : 'fa-save' ?> me-2"></i>
                    <?= $edit_data ? 'Mettre à jour' : 'Enregistrer' ?>
                </button>
                <?php if ($edit_data): ?>
                    <a href="index.php?page=liste-pays" class="btn btn-secondary shadow-sm">Annuler</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<script>
const paysForm = document.getElementById('paysForm');

document.querySelectorAll('.intel-input').forEach(input => {
    input.addEventListener('input', function() {
        if(this.id === 'codeInput') this.value = this.value.toUpperCase().replace(/[^A-Z]/g, '');
        if(this.id === 'nomInput') this.value = this.value.replace(/[^a-zA-ZÀ-ÿ\s\-]/g, '');
        
        const pattern = new RegExp(this.dataset.pattern);
        if (this.value !== "" && !pattern.test(this.value)) {
            this.classList.add('is-invalid');
            this.nextElementSibling.textContent = this.dataset.msg;
        } else {
            this.classList.remove('is-invalid');
        }
    });
});

paysForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    // Reset des erreurs visuelles
    paysForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

    if (paysForm.querySelectorAll('.is-invalid').length > 0) return;

    try {
        const res = await fetch('process.php', { method: 'POST', body: new FormData(paysForm) });
        const data = await res.json();
        
        if (data.ok) {
            await Swal.fire({ 
                icon: 'success', title: 'Succès', 
                timer: 1500, showConfirmButton: false, timerProgressBar: true 
            });
            window.location.href = 'index.php?page=liste-pays';
        } else if (data.errors) {
            // Affichage des erreurs de doublons
            for (const [key, msg] of Object.entries(data.errors)) {
                const input = paysForm.querySelector(`[name="${key}"]`);
                if (input) {
                    input.classList.add('is-invalid');
                    const feedback = input.nextElementSibling;
                    if (feedback) feedback.textContent = msg;
                }
            }
        } else {
            Swal.fire({ icon: 'error', title: 'Erreur', text: data.message });
        }
    } catch (err) { }
});
</script>
