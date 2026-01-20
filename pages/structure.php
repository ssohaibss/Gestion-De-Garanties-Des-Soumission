<?php
require_once dirname(__DIR__) . '/database.php';

// Récupération des structures selon tes colonnes (id, code, libelle)
$stmt = $pdo->query("SELECT * FROM structure ORDER BY libelle ASC");
$structures = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<div class="content-header">
    <h2><i class="fas fa-sitemap"></i> Gestion des Structures</h2>
</div>

<div class="card">
    <div class="card-header">
        <i class="fas fa-user-edit"></i> <span id="formTitle">Nouvelle structure</span>
    </div>
    <div class="card-body">
        <form id="structureForm" action="process.php" method="POST" novalidate>
            <input type="hidden" name="form_type" value="structure">
            <input type="hidden" name="id" id="structureId" value="">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Libellé <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="libelleInput" name="libelle" placeholder="Ex: Administration du Personnel"required>
                    <div class="invalid-feedback"></div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Code <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="code" placeholder="Ex: ADP"required>
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
    <div class="card-header"><i class="fas fa-list"></i> Liste des Structures</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Libellé</th>
                        <th>Code</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($structures as $s): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($s['libelle']) ?></strong></td>
                        <td><strong><?= htmlspecialchars($s['code']) ?></strong></td>
                        <td>
                            <button class="btn btn-sm eye text-white edit-structure" 
                                    data-structure='<?= htmlspecialchars(json_encode($s), ENT_QUOTES, 'UTF-8') ?>'>
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger delete-structure" 
                                    data-id="<?= $s['id'] ?>" 
                                    data-nom="<?= htmlspecialchars($s['libelle']) ?>">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
const structureForm = document.getElementById('structureForm');
const codeInput = structureForm.querySelector('[name="code"]');

document.getElementById('libelleInput').addEventListener('input', function() {
    this.value = this.value.replace(/[^a-zA-ZÀ-ÿ\s\-]/g, '');
});
// --- FORCE MAJUSCULES ET INTERDIT ESPACES/SPECIAUX ---
if (codeInput) {
    codeInput.addEventListener('input', function() {
        // Supprime tout ce qui n'est pas lettre ou chiffre et met en majuscules
        this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
    });
}

// --- SOUMISSION ---
structureForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    structureForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

    try {
        const response = await fetch('process.php', { method: 'POST', body: new FormData(structureForm) });
        const data = await response.json();
        
        if (data.ok) {
            Swal.fire({
                icon: 'success',
                title: 'Succès',
                text: 'Opération réussie !',
                timer: 1500,
                showConfirmButton: false,
                timerProgressBar: true
            }).then(() => location.reload());
        } else if (data.errors) {
            for (const [key, msg] of Object.entries(data.errors)) {
                const input = structureForm.querySelector(`[name="${key}"]`);
                if (input) {
                    input.classList.add('is-invalid');
                    const feedback = input.closest('.mb-3')?.querySelector('.invalid-feedback');
                    if (feedback) feedback.textContent = msg;
                }
            }
        }
    } catch (err) { Swal.fire('Erreur', 'Lien avec le serveur perdu.', 'error'); }
});

// --- MODIFICATION ---
document.querySelectorAll('.edit-structure').forEach(btn => {
    btn.addEventListener('click', function() {
        const s = JSON.parse(this.dataset.structure);
        document.getElementById('formTitle').textContent = "Modifier la structure : " + s.libelle;
        document.getElementById('structureId').value = s.id;
        structureForm.querySelector('[name="libelle"]').value = s.libelle;
        structureForm.querySelector('[name="code"]').value = s.code;
        
        document.getElementById('submitBtn').innerHTML = '<i class="fas fa-sync"></i> Mettre à jour';
        document.getElementById('cancelEdit').classList.remove('d-none');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
});

document.getElementById('cancelEdit').addEventListener('click', () => location.reload());

// --- SUPPRESSION ---
document.querySelectorAll('.delete-structure').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        const nom = this.dataset.nom;

        Swal.fire({
            title: 'Êtes-vous sûr ?',
            text: `Supprimer la structure "${nom}" ?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Oui, supprimer !',
            cancelButtonText: 'Annuler'
        }).then(async (result) => {
            if (result.isConfirmed) {
                const fd = new FormData();
                fd.append('form_type', 'delete_structure');
                fd.append('id', id);
                const res = await fetch('process.php', { method: 'POST', body: fd });
                const data = await res.json();
                if (data.ok) {
                    Swal.fire({ title: 'Supprimé !', icon: 'success', timer: 1500, showConfirmButton: false, timerProgressBar: true })
                    .then(() => location.reload());
                }
            }
        });
    });
});
</script>