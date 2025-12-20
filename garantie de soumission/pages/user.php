<?php
require_once dirname(__DIR__) . '/database.php';

// 1. Charger les rôles (Sécurité sur la casse de l'ID)
$stmt_r = $pdo->prepare("SELECT * FROM role ORDER BY libelle ASC");
$stmt_r->execute();
$roles = $stmt_r->fetchAll(PDO::FETCH_ASSOC);

$role_names = [];
foreach ($roles as $r) {
    // On teste ID ou id pour éviter l'erreur "Undefined array key"
    $r_id = $r['ID'] ?? $r['id'] ?? null;
    if ($r_id) {
        $role_names[$r_id] = $r['libelle'];
    }
}

// 2. Charger les utilisateurs
$stmt_u = $pdo->prepare("SELECT * FROM utilisateur ORDER BY nom ASC");
$stmt_u->execute();
$users = $stmt_u->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-header">
    <h2><i class="fas fa-user"></i> Gestion des Utilisateurs</h2>
</div>

<div class="card shadow-sm">
    <div class="card-header">
        <i class="fas fa-user-plus"></i> Nouveau compte
    </div>
    <div class="card-body">
        <form id="userForm" action="process.php" method="POST" novalidate autocomplete="off">
            <input type="hidden" name="form_type" value="user">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nom <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="nom" id="nom" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" name="email" id="email" required>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Mot de passe <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" name="password" id="password" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Rôle <span class="text-danger">*</span></label>
                    <select class="form-select" name="role" id="role" required>
                        <option value="">Choisir...</option>
                        <?php foreach($roles as $r): ?>
                            <?php $val_id = $r['ID'] ?? $r['id']; ?>
                            <option value="<?= $val_id ?>"><?= htmlspecialchars($r['libelle']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Enregistrer</button>
        </form>
    </div>
</div>

<div class="card mt-4 shadow-sm">
    <div class="card-header bg-light">
        <i class="fas fa-users me-2"></i>Liste des comptes
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Nom</th>
                        <th>Email</th>
                        <th>Rôle</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr><td colspan="3" class="text-center py-3">Aucun utilisateur trouvé.</td></tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td class="ps-3"><strong><?= htmlspecialchars($user['nom']) ?></strong></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td>
                                    <span class="badge bg-info text-dark">
                                        <?php 
                                            $u_role_id = $user['roleID'] ?? $user['id_role'] ?? null;
                                            echo htmlspecialchars($role_names[$u_role_id] ?? 'Non défini'); 
                                        ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('userForm');
    
    form.querySelectorAll('input, select').forEach(input => {
        input.addEventListener('input', () => input.classList.remove('is-invalid'));
    });

    form.addEventListener('submit', function(e) {
        let isValid = true;
        const nom = document.getElementById('nom');
        const email = document.getElementById('email');
        const password = document.getElementById('password');
        const role = document.getElementById('role');
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!nom.value.trim()) { nom.classList.add('is-invalid'); isValid = false; }
        if (!emailRegex.test(email.value.trim())) { email.classList.add('is-invalid'); isValid = false; }
        if (password.value.length < 6) { password.classList.add('is-invalid'); isValid = false; }
        if (role.value === "") { role.classList.add('is-invalid'); isValid = false; }

        if (!isValid) e.preventDefault();
    });
});
</script>