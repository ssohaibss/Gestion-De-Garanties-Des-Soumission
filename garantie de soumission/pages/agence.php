<?php
require_once dirname(__DIR__) . '/database.php';

// On récupère les agences avec le nom de la banque associée
$agences = $pdo->query("
    SELECT a.*, b.nom_banque 
    FROM agence a 
    LEFT JOIN banque b ON a.banqueID = b.id 
    ORDER BY a.nom ASC
")->fetchAll(PDO::FETCH_ASSOC);

// On récupère les banques pour le select (Notez le 'id' en minuscule ici)
$banques = $pdo->query("SELECT id, nom_banque FROM banque ORDER BY nom_banque ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-header">
    <h2><i class="fas fa-map-marked-alt"></i> Gestion des Agences</h2>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
        <i class="fas fa-plus-circle"></i> Formulaire Agence
    </div>
    <div class="card-body">
        <form action="process.php" method="POST">
            <input type="hidden" name="form_type" value="agence">
            
            <div class="mb-3">
                <label for="code" class="form-label">Code Agence <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="code" name="code" required>
            </div>
            
            <div class="mb-3">
                <label for="nom" class="form-label">Nom de l'Agence <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="nom" name="nom" required>
            </div>
            
            <div class="mb-3">
                <label for="banqueID" class="form-label">Banque</label>
                <select class="form-select" id="banqueID" name="banqueID" required>
                    <option value="">Sélectionnez une banque</option>
                    <?php foreach ($banques as $banque): ?>
                        <option value="<?= $banque['id']; ?>"><?= htmlspecialchars($banque['nom_banque']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="adresse" class="form-label">Adresse</label>
                <input type="text" class="form-control" id="adresse" name="adresse">
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
                <button type="reset" class="btn btn-secondary"><i class="fas fa-redo"></i> Réinitialiser</button>
            </div>
        </form>
    </div>
</div>

<div class="card mt-4 shadow-sm">
    <div class="card-header bg-light"><i class="fas fa-list"></i> Liste des Agences</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Code</th>
                        <th>Nom</th>
                        <th>Banque</th>
                        <th>Adresse</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($agences) > 0): ?>
                        <?php foreach ($agences as $agence): ?>
                            <tr>
                                <td class="ps-3"><code><?= htmlspecialchars($agence['code']); ?></code></td>
                                <td class="fw-bold"><?= htmlspecialchars($agence['nom']); ?></td>
                                <td><span class="badge bg-info text-dark"><?= htmlspecialchars($agence['nom_banque'] ?? 'N/A'); ?></span></td>
                                <td><?= htmlspecialchars($agence['adresse']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center py-3">Aucune agence trouvée</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>