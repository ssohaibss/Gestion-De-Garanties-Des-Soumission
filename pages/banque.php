<?php
require_once dirname(__DIR__) . '/database.php';

// Récupération des données avec sécurité pour les clés 'id' et 'code'
$banques_stmt = $pdo->query("SELECT * FROM banque ORDER BY nom_banque ASC");
$banques = $banques_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-header">
    <h2><i class="fas fa-university"></i> Gestion des Banques</h2>
</div>

<div class="card">
    <div class="card-header">
        <i class="fas fa-edit"></i> <span id="formTitle">Ajouter une Banque</span>
    </div>
    <div class="card-body">
        <form id="banqueForm" action="process.php" method="POST" novalidate>
            <input type="hidden" name="form_type" value="banque">
            <input type="hidden" name="id" id="banqueId" value="">
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Code Banque <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="code" id="banqueCode" placeholder="Ex: BNA" required>
                    <div class="invalid-feedback"></div>
                </div>
                <div class="col-md-8 mb-3">
                    <label class="form-label">Nom de la Banque <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="nom_banque" id="banqueNom" placeholder="Ex: Banque Nationale d'Algérie" required>
                    <div class="invalid-feedback"></div>
                </div>
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
    <div class="card-header"><i class="fas fa-list"></i> Liste des Banques</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Nom de la Banque</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($banques)): ?>
                        <tr><td colspan="3" class="text-center py-3">Aucune banque enregistrée.</td></tr>
                    <?php else: ?>
                        <?php foreach ($banques as $b): 
                            $currentId = $b['id'] ?? $b['Id'] ?? 0;
                        ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($b['code'] ?? 'N/A') ?></strong></td>
                            <td><?= htmlspecialchars($b['nom_banque'] ?? 'Inconnu') ?></td>
                            <td>
                                <button class="btn btn-sm eye text-white edit-banque" 
                                        data-banque='<?= htmlspecialchars(json_encode($b), ENT_QUOTES, 'UTF-8') ?>'>
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger delete-banque" 
                                        data-id="<?= $currentId ?>" 
                                        data-nom="<?= htmlspecialchars($b['nom_banque'] ?? '') ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
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
const banqueForm = document.getElementById('banqueForm');
document.getElementById('banqueNom').addEventListener('input', function() {
    this.value = this.value.replace(/[^a-zA-ZÀ-ÿ\s\-]/g, '');
});
// --- AJOUT / MODIFICATION ---
banqueForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    banqueForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

    try {
        const res = await fetch('process.php', { method: 'POST', body: new FormData(banqueForm) });
        const data = await res.json();
        
        if (data.ok) {
            Swal.fire({ 
                icon: 'success', 
                title: 'Succès', 
                text: 'Données enregistrées', 
                timer: 1500,
                showConfirmButton: false,
                timerProgressBar: true
            }).then(() => location.reload());
        } else if (data.errors) {
            for (const [key, msg] of Object.entries(data.errors)) {
                const input = banqueForm.querySelector(`[name="${key}"]`);
                if (input) {
                    input.classList.add('is-invalid');
                    input.nextElementSibling.textContent = msg;
                }
            }
        } else {
            Swal.fire('Erreur', data.message, 'error');
        }
    } catch (err) { Swal.fire('Erreur', 'Erreur de connexion', 'error'); }
});

// --- REMPLIR FORMULAIRE ---
document.querySelectorAll('.edit-banque').forEach(btn => {
    btn.addEventListener('click', function() {
        const b = JSON.parse(this.dataset.banque);
        document.getElementById('formTitle').textContent = "Modifier : " + (b.nom_banque ?? '');
        document.getElementById('banqueId').value = b.id ?? b.Id;
        document.getElementById('banqueCode').value = b.code ?? '';
        document.getElementById('banqueNom').value = b.nom_banque ?? '';
        document.getElementById('submitBtn').innerHTML = '<i class="fas fa-sync"></i> Mettre à jour';
        document.getElementById('cancelEdit').classList.remove('d-none');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
});

document.getElementById('cancelEdit').addEventListener('click', () => location.reload());

// --- SUPPRESSION ---
document.querySelectorAll('.delete-banque').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        const nom = this.dataset.nom;

        Swal.fire({
            title: 'Confirmation',
            text: `Voulez-vous supprimer "${nom}" ?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Oui, supprimer'
        }).then(async (result) => {
            if (result.isConfirmed) {
                const fd = new FormData();
                fd.append('form_type', 'delete_banque');
                fd.append('id', id);

                const res = await fetch('process.php', { method: 'POST', body: fd });
                const data = await res.json();
                
                if (data.ok) {
                    Swal.fire({ 
                        title: 'Supprimé !', 
                        icon: 'success', 
                        timer: 1500,
                        showConfirmButton: false,
                        timerProgressBar: true
                    }).then(() => location.reload());
                } else {
                    Swal.fire('Impossible', data.message, 'error');
                }
            }
        });
    });
});
</script>