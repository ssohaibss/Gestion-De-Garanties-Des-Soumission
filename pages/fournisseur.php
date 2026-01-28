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
    <h2><i class="fas fa-truck me-2"></i>Gestion des Fournisseurs</h2>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header text-white fw-bold" style="background-color: #486a70;">
        <i class="fas <?= $edit_data ? 'fa-edit' : 'fa-plus-circle' ?> me-2"></i> 
        <span><?= $edit_data ? "Modifier le fournisseur" : "Nouveau fournisseur" ?></span>
    </div>
    <div class="card-body">
        <form id="fournisseurForm" novalidate>
            <input type="hidden" name="form_type" value="fournisseur">
            <input type="hidden" name="id" id="fournisseurId" value="<?= $edit_data['id'] ?? '' ?>">
            
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
                <a href="index.php?page=liste-fournisseur" class="btn btn-secondary shadow-sm">Annuler</a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('fournisseurForm');
    const telInput = document.getElementById('telInput');
    const nomInput = document.getElementById('nomInput');
    const emailInput = document.getElementById('emailInput');
    const adresseInput = document.getElementById('adresseInput');

    // BLOCAGE TEMPS RÉEL
    telInput.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    emailInput.addEventListener('input', function() {
        this.value = this.value.replace(/\s/g, '');
    });

    nomInput.addEventListener('input', function() {
        this.value = this.value.replace(/[0-9]/g, ''); // Optionnel: retire les chiffres si souhaité
        this.value = this.value.replace(/ {2,}/g, ' '); 
        if (this.value.startsWith(' ')) this.value = this.value.trimStart();
    });

    adresseInput.addEventListener('input', function() {
        this.value = this.value.replace(/ {2,}/g, ' '); 
        if (this.value.startsWith(' ')) this.value = this.value.trimStart();
    });

    // Validation Blur (Nettoyage final + Unicité)
    document.querySelectorAll('.intel-input').forEach(input => {
        input.addEventListener('blur', async function() {
            this.value = this.value.trim();

            const fb = this.closest('.mb-3, .col-md-6').querySelector('.invalid-feedback');
            const pattern = new RegExp(this.dataset.pattern);
            const fieldName = this.name;
            const value = this.value;
            const idValue = document.getElementById('fournisseurId').value;

            this.classList.remove('is-invalid', 'is-valid');
            if (value === "") return;

            if (!pattern.test(value)) {
                this.classList.add('is-invalid');
                if (fb) fb.textContent = this.dataset.msg; 
                return; 
            }

            const fieldsToCheck = ["nom", "email", "telephone"];
            if (fieldsToCheck.includes(fieldName)) {
                try {
                    // Note: on envoie la valeur avec le + pour le téléphone si c'est ce champ
                    const checkVal = (fieldName === 'telephone') ? '+' + value : value;
                    const response = await fetch(`pages/unique_check.php?type=fournisseur&field=${fieldName}&value=${encodeURIComponent(checkVal)}&id=${idValue}`);
                    const data = await response.json();
                    
                    if (data.exists) {
                        this.classList.add('is-invalid');
                        if (fb) fb.textContent = "Cette valeur est déjà utilisée.";
                    } else {
                        this.classList.add('is-valid');
                    }
                } catch (e) { console.error(e); }
            }
        });
    });

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        if (this.querySelectorAll('.is-invalid').length > 0) return;

        try {
            const res = await fetch('process.php', { method: 'POST', body: new FormData(this) });
            const data = await res.json();
            if (data.ok) {
                await Swal.fire({ icon: 'success', title: 'Succès !', timer: 1500, showConfirmButton: false, timerProgressBar: true  });
                window.location.href = 'index.php?page=liste-fournisseur';
            }
        } catch (err) { console.error(err); }
    });
});
</script>