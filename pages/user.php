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
        <span id="formTitle"><?= $edit_data ? "Modifier : " . htmlspecialchars($edit_data['username']) : "Nouveau compte utilisateur" ?></span>
    </div>
    <div class="card-body">
        <form id="userForm" novalidate>
            <input type="hidden" name="form_type" value="user">
            <input type="hidden" name="id" id="userId" value="<?= $edit_data['id'] ?? '' ?>">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Nom <span class="text-danger">*</span></label>
                    <input type="text" class="form-control intel-input" name="nom" id="nomInput" 
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
                           data-pattern="^[a-zA-Z0-9._%+-]+@sonatrach\.com$" data-msg="Doit se terminer par @sonatrach.com">
                    <div class="invalid-feedback"></div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Rôle <span class="text-danger">*</span></label>
                    <select class="form-select" name="role" required>
                        <option value="">Sélectionner un rôle</option>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?= $role['id'] ?>" <?= (isset($edit_data['roleID']) && $edit_data['roleID'] == $role['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($role['libelle']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Login <span class="text-danger">*</span></label>
                    <input type="text" class="form-control intel-input" name="username" id="usernameInput"
                           value="<?= $edit_data['username'] ?? '' ?>" required
                           data-pattern="^[a-z0-9._\-]{4,}$" data-msg="Min. 4 car. (minuscules/chiffres).">
                    <div class="invalid-feedback"></div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">
                        Mot de passe <span id="passReq" class="text-danger" <?= $edit_data ? 'style="display:none;"' : '' ?>>*</span>
                    </label>
                    <div class="input-group">
                        <input type="password" class="form-control intel-input" name="password" id="passwordField"
                               data-pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$"
                               data-msg="8 car. min, 1 Maj, 1 Min, 1 Chiffre et 1 Symbole.">
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword"><i class="fas fa-eye"></i></button>
                        <div class="invalid-feedback"></div>
                    </div>
                    <small id="passHelp" class="text-muted d-block mt-1">
                        <?= $edit_data ? "<strong>Laissez vide pour garder l'ancien mot de passe</strong>" : "" ?>
                    </small>
                </div>
            </div>
            
            <div class="d-flex gap-2 mt-2">
                <button type="submit" class="btn ajouter shadow-sm">
                    <i class="fas <?= $edit_data ? 'fa-sync' : 'fa-save' ?> me-2"></i>
                    <?= $edit_data ? 'Mettre à jour l\'utilisateur' : 'Enregistrer l\'utilisateur' ?>
                </button>
                <a href="index.php?page=liste-user" class="btn btn-secondary shadow-sm">Annuler / Retour</a>
            </div>
        </form>
    </div>
</div>

<script>
// La logique intel-input avec data-pattern reste la même
document.querySelectorAll('.intel-input').forEach(input => {
    input.addEventListener('input', function() {
        if(this.id === 'nomInput') this.value = this.value.toUpperCase();
        if(this.id === 'usernameInput' || this.id === 'emailInput') this.value = this.value.toLowerCase();
        
        const isUpdate = document.getElementById('userId').value !== "";
        if (isUpdate && this.name === "password" && this.value === "") {
            this.classList.remove('is-invalid');
            return;
        }

        const pattern = new RegExp(this.dataset.pattern);
        if (this.value !== "" && !pattern.test(this.value)) {
            this.classList.add('is-invalid');
            const feedback = this.closest('.mb-3')?.querySelector('.invalid-feedback') || this.nextElementSibling;
            if (feedback) feedback.textContent = this.dataset.msg;
        } else {
            this.classList.remove('is-invalid');
        }
    });
});

document.getElementById('userForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    if (document.querySelectorAll('.is-invalid').length > 0) return;

    const res = await fetch('process.php', { method: 'POST', body: new FormData(e.target) });
    const data = await res.json();
    if (data.ok) {
        await Swal.fire({ icon: 'success', title: 'Succès', timer: 1500, showConfirmButton: false, timerProgressBar: true });
        window.location.href = 'index.php?page=liste-user';
    }
});

document.getElementById('togglePassword').addEventListener('click', function() {
    const f = document.getElementById('passwordField');
    const i = this.querySelector('i');
    f.type = (f.type === 'password') ? 'text' : 'password';
    i.classList.toggle('fa-eye'); i.classList.toggle('fa-eye-slash');
});
</script>
