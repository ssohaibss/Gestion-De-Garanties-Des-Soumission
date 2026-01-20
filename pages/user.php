<?php
require_once dirname(__DIR__) . '/database.php';
$pdo = getDBConnection();

// --- SÉCURITÉ : VÉRIFICATION DE L'EXISTENCE DU COMPTE ---
// Si l'utilisateur a été supprimé par un autre admin, on le déconnecte immédiatement
if (isset($_SESSION['user_id'])) {
    $check_stmt = $pdo->prepare("SELECT id FROM utilisateur WHERE id = ?");
    $check_stmt->execute([$_SESSION['user_id']]);
    if (!$check_stmt->fetch()) {
        session_destroy();
        header("Location: login.php?error=account_deleted");
        exit();
    }
}

$roles_stmt = $pdo->query("SELECT id, libelle FROM role ORDER BY libelle");
$roles = $roles_stmt->fetchAll(PDO::FETCH_ASSOC);

$users_stmt = $pdo->query("
    SELECT u.*, r.libelle as role_nom 
    FROM utilisateur u 
    LEFT JOIN role r ON u.roleID = r.id 
    ORDER BY u.nom ASC
");
$users = $users_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-header">
    <h2><i class="fas fa-user"></i> Gestion des Utilisateurs</h2>
</div>

<div class="card">
    <div class="card-header">
        <i class="fas fa-user-edit"></i> <span id="formTitle">Nouveau compte utilisateur</span>
    </div>
    <div class="card-body">
        <form id="userForm" action="process.php" method="POST" novalidate>
            <input type="hidden" name="form_type" value="user">
            <input type="hidden" name="id" id="userId" value="">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nom <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nomInput" name="nom" required>
                    <div class="invalid-feedback"></div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Prénom <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="prenomInput" name="prenom" required>
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" name="email" placeholder="Ex: Xyw@sonatrach.com" required>
                    <div class="invalid-feedback"></div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Rôle <span class="text-danger">*</span></label>
                    <select class="form-select" name="role" required>
                        <option value="">Sélectionner un rôle</option>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?= $role['id'] ?>"><?= htmlspecialchars($role['libelle']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nom d'utilisateur (Login) <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="usernameInput" name="username" required>
                    <div class="invalid-feedback"></div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Mot de passe <span id="passReq" class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="password" class="form-control" name="password" id="passwordField" placeholder="Min. 8 car. (Maj, Min, Chiffre, Spécial), Ex: P@ssw0rd" >
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="fas fa-eye" id="eyeIcon"></i>
                        </button>
                        <div class="invalid-feedback"></div>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary ajouter" id="submitBtn">
                    <i class="fas fa-save"></i> Enregistrer
                </button>
                <button type="button" class="btn btn-secondary d-none" id="cancelEdit">Annuler</button>
            </div>
        </form>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header"><i class="fas fa-users"></i> Liste des Utilisateurs</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Login</th>
                        <th>Nom & Prénom</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($user['username']) ?></strong></td>
                        <td><?= htmlspecialchars($user['nom'] . ' ' . $user['prenom']) ?></td>
                        <td><strong><?= htmlspecialchars($user['email']) ?></strong></td>
                        <td><?= htmlspecialchars($user['role_nom']) ?></td>
                        <td>
                            <button class="btn btn-sm eye text-white edit-user" 
                                    data-user='<?= htmlspecialchars(json_encode($user), ENT_QUOTES, 'UTF-8') ?>'>
                                <i class="fas fa-edit"></i>
                            </button>
                            
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                <button class="btn btn-sm btn-danger delete-user" 
                                        data-id="<?= $user['id'] ?>" 
                                        data-login="<?= htmlspecialchars($user['username']) ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            <?php else: ?>
                                <span class="badge bg-light text-dark border">Vous</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
const userForm = document.getElementById('userForm');
const currentUserId = <?= (int)$_SESSION['user_id'] ?>;

document.getElementById('nomInput').addEventListener('input', function() {
    this.value = this.value.replace(/[^a-zA-ZÀ-ÿ\s\-]/g, '');
});

document.getElementById('prenomInput').addEventListener('input', function() {
    this.value = this.value.replace(/[^a-zA-ZÀ-ÿ\s\-]/g, '');
});
document.getElementById('usernameInput').addEventListener('input', function() {
    this.value = this.value.replace(/[^a-zA-Z0-9À-ÿ\-\-]/g, '');
});

document.getElementById('togglePassword').addEventListener('click', function() {
    const passwordField = document.getElementById('passwordField');
    const eyeIcon = document.getElementById('eyeIcon');
    passwordField.type = (passwordField.type === 'password') ? 'text' : 'password';
    eyeIcon.classList.toggle('fa-eye');
    eyeIcon.classList.toggle('fa-eye-slash');
});

userForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    userForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

    const emailField = userForm.querySelector('[name="email"]');
    const emailValue = emailField.value.trim().toLowerCase();
    
    if (emailValue !== "" && !emailValue.endsWith('@sonatrach.com')) {
        emailField.classList.add('is-invalid');
        const feedback = emailField.closest('.mb-3').querySelector('.invalid-feedback');
        if (feedback) feedback.textContent = "L'adresse doit être @sonatrach.com";
        return;
    }

    try {
        const response = await fetch('process.php', { 
            method: 'POST', 
            body: new FormData(userForm) 
        });
        const data = await response.json();
        
        if (data.ok) {
            Swal.fire({ icon: 'success', title: 'Succès', timer: 1500, showConfirmButton: false }).then(() => location.reload());
        } else if (data.errors) {
            for (const [key, msg] of Object.entries(data.errors)) {
                const input = userForm.querySelector(`[name="${key}"]`);
                if (input) {
                    input.classList.add('is-invalid');
                    const realFeedback = input.closest('.mb-3').querySelector('.invalid-feedback');
                    if (realFeedback) realFeedback.textContent = msg;
                }
            }
        } else {
            Swal.fire('Erreur', data.message, 'error');
        }
    } catch (err) { 
        Swal.fire('Erreur', 'Impossible de contacter le serveur.', 'error');
    }
});

document.querySelectorAll('.edit-user').forEach(btn => {
    btn.addEventListener('click', function() {
        const user = JSON.parse(this.dataset.user);
        document.getElementById('formTitle').textContent = "Modifier : " + user.username;
        document.getElementById('userId').value = user.id;
        userForm.querySelector('[name="nom"]').value = user.nom;
        userForm.querySelector('[name="prenom"]').value = user.prenom;
        userForm.querySelector('[name="email"]').value = user.email;
        userForm.querySelector('[name="username"]').value = user.username;
        userForm.querySelector('[name="role"]').value = user.roleID;
        document.getElementById('passReq').classList.add('d-none');
        document.getElementById('passHelp').innerHTML = "<strong>Laissez vide pour conserver le mot de passe actuel</strong>";
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.innerHTML = '<i class="fas fa-sync"></i> Mettre à jour';
        submitBtn.classList.replace('btn-primary', 'btn-warning');
        document.getElementById('cancelEdit').classList.remove('d-none');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
});

document.getElementById('cancelEdit').addEventListener('click', () => location.reload());

document.querySelectorAll('.delete-user').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = parseInt(this.dataset.id);
        const login = this.dataset.login;

        if (id === currentUserId) {
            Swal.fire('Action impossible', "Vous ne pouvez pas supprimer votre propre compte.", 'error');
            return;
        }

        Swal.fire({
            title: 'Êtes-vous sûr ?',
            text: `Supprimer définitivement l'utilisateur "${login}" ?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler'
        }).then(async (result) => {
            if (result.isConfirmed) {
                const fd = new FormData();
                fd.append('form_type', 'delete_user');
                fd.append('id', id);

                const res = await fetch('process.php', { method: 'POST', body: fd });
                const data = await res.json();
                
                if (data.ok) {
                    Swal.fire({ title: 'Supprimé !', icon: 'success', timer: 1500, showConfirmButton: false }).then(() => location.reload());
                } else {
                    Swal.fire('Erreur', data.message, 'error');
                }
            }
        });
    });
});
</script>