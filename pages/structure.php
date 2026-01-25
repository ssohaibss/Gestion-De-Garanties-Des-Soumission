<?php
require_once dirname(__DIR__) . '/database.php';
$pdo = getDBConnection();

$edit_data = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM structure WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_data = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<div class="content-header mb-4">
    <h2><i class="fas fa-sitemap me-2"></i>Gestion des Structures</h2>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header text-white fw-bold" style="background-color: #486a70;">
        <i class="fas fa-edit me-2"></i> 
        <span><?= $edit_data ? "Modifier la structure : " . htmlspecialchars($edit_data['libelle']) : "Nouvelle Structure" ?></span>
    </div>
    <div class="card-body">
        <form id="structureForm" novalidate>
            <input type="hidden" name="form_type" value="structure">
            <input type="hidden" name="id" value="<?= $edit_data['id'] ?? '' ?>">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Libellé <span class="text-danger">*</span></label>
                    <input type="text" class="form-control intel-input" id="libelleInput" name="libelle" 
                           value="<?= $edit_data['libelle'] ?? '' ?>" required
                           data-pattern="^[a-zA-ZÀ-ÿ\s\-]{3,}$"
                           data-msg="Libellé invalide (min. 3 lettres).">
                    <div class="invalid-feedback"></div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Code (Acronyme) <span class="text-danger">*</span></label>
                    <input type="text" class="form-control intel-input" id="codeInput" name="code" 
                           value="<?= $edit_data['code'] ?? '' ?>" required maxlength="6"
                           data-pattern="^[A-Z]{2,6}$"
                           data-msg="2 à 6 lettres uniquement (ex: ADP).">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            
            <div class="d-flex gap-2 mt-2">
                <button type="submit" class="btn ajouter shadow-sm">
                    <i class="fas <?= $edit_data ? 'fa-sync' : 'fa-save' ?> me-2"></i> 
                    <?= $edit_data ? 'Mettre à jour' : 'Enregistrer' ?>
                </button>
                <?php if ($edit_data): ?>
                    <a href="index.php?page=liste-structure" class="btn btn-secondary shadow-sm">Annuler</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<script>
const structureForm = document.getElementById('structureForm');

// Validation et Nettoyage en temps réel
document.querySelectorAll('.intel-input').forEach(input => {
    input.addEventListener('input', function() {

        if(this.id === 'codeInput') {
            this.value = this.value.toUpperCase().replace(/[^A-Z]/g, '');
        }
        
        if(this.id === 'libelleInput') {
            this.value = this.value.replace(/[^a-zA-ZÀ-ÿ\s\-]/g, '');
        }
        
        const pattern = new RegExp(this.dataset.pattern);
        if (this.value !== "" && !pattern.test(this.value)) {
            this.classList.add('is-invalid');
            this.nextElementSibling.textContent = this.dataset.msg;
        } else {
            this.classList.remove('is-invalid');
        }
    });
});

structureForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    structureForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

    if (structureForm.querySelectorAll('.is-invalid').length > 0) return;

    try {
        const res = await fetch('process.php', { method: 'POST', body: new FormData(structureForm) });
        const data = await res.json();
        
        if (data.ok) {
            await Swal.fire({ 
                icon: 'success', 
                title: 'Succès', 
                timer: 1500, 
                showConfirmButton: false, 
                timerProgressBar: true 
            });
            window.location.href = 'index.php?page=liste-structure';
        } else if (data.errors) {
            // Affichage des erreurs spécifique
            for (const [key, msg] of Object.entries(data.errors)) {
                const input = structureForm.querySelector(`[name="${key}"]`);
                if (input) {
                    input.classList.add('is-invalid');
                    const feedback = input.nextElementSibling;
                    if (feedback) feedback.textContent = msg;
                }
            }
        }
    } catch (err) { 
        Swal.fire({ icon: 'error', title: 'Erreur', text: 'Lien avec le serveur perdu.' }); 
    }
});
</script>