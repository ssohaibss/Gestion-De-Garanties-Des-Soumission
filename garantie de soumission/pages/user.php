<?php
require_once 'database.php';

$roles_result = $pdo->query("SELECT id, libelle FROM role ORDER BY libelle");
?>

<div class="content-header">
    <h2><i class="fas fa-user"></i> Ajouter un Utilisateur</h2>
</div>

<div class="card">
    <div class="card-header">
        <i class="fas fa-user-plus"></i> Formulaire Utilisateur
    </div>
    <div class="card-body">
        <form id="userForm" action="process.php" method="POST">
            <input type="hidden" name="form_type" value="user">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nom" name="nom" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label">Mot de passe <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="role" class="form-label">Rôle <span class="text-danger">*</span></label>
                    <select class="form-select" id="role" name="role" required>
                        <option value="">Sélectionner un rôle</option>
                        <?php while ($role = $roles_result->fetch(PDO::FETCH_ASSOC)): ?>
                            <option value="<?= $role['id'] ?>"><?= htmlspecialchars($role['libelle']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Enregistrer
                </button>
                <button type="reset" class="btn btn-secondary">
                    <i class="fas fa-redo"></i> Réinitialiser
                </button>
            </div>
        </form>
    </div>
</div>
