<?php
require_once dirname(__DIR__) . '/database.php';

// Récupération des banques existantes
$banques = $pdo->query("SELECT * FROM banque ORDER BY nom_banque ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-header">
    <h2><i class="fas fa-university"></i> Gestion des Banques</h2>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
        <i class="fas fa-plus-circle me-2"></i>Formulaire Banque
    </div>
    <div class="card-body">
        <form id="banqueForm" action="process.php" method="POST" autocomplete="off">
            <input type="hidden" name="form_type" value="banque">
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="code" class="form-label">Code Banque <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="code" name="code" placeholder="Ex: BNA" required>
                </div>
                <div class="col-md-8 mb-3">
                    <label for="nom_banque" class="form-label">Nom de la Banque <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nom_banque" name="nom_banque" placeholder="Ex: Banque Nationale d'Algérie" required>
                </div>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Enregistrer
                </button>
                <button type="reset" class="btn btn-secondary">
                    <i class="fas fa-redo me-2"></i>Réinitialiser
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card mt-4 shadow-sm">
    <div class="card-header bg-light">
        <i class="fas fa-list me-2"></i>Liste des Banques
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width: 150px;">Code</th>
                        <th>Nom de la Banque</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($banques) > 0): ?>
                        <?php foreach ($banques as $b): ?>
                            <tr>
                                <td class="ps-3">
                                    <span class="badge bg-info text-dark">
                                        <?= htmlspecialchars($b['code']) ?>
                                    </span>
                                </td>
                                <td class="fw-bold text-secondary">
                                    <?= htmlspecialchars($b['nom_banque']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="2" class="text-center py-4 text-muted">
                                <i class="fas fa-info-circle me-2"></i>Aucune banque enregistrée.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>