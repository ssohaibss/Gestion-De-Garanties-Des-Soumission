<?php
require_once dirname(__DIR__) . '/database.php';

// Récupération des pays
$stmt = $pdo->query("SELECT * FROM pays ORDER BY nom ASC");
$countries = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-header">
    <h2><i class="fas fa-globe"></i> Gestion des Pays</h2>
</div>

<div class="card">
    <div class="card-header">
        <i class="fas fa-plus-circle"></i> <span id="formTitle">Ajouter un Pays</span>
    </div>
    <div class="card-body">
        <form id="paysForm" action="process.php" method="POST" novalidate>
            <input type="hidden" name="form_type" value="pays">
            <input type="hidden" name="id" id="paysId" value="">

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nom du pays <span class="text-danger">*</span></label>
                    <input type="text" name="nom" id="nomInput" class="form-control" placeholder="Ex: Algérie" required>
                    <div class="invalid-feedback"></div>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Code pays (ISO) <span class="text-danger">*</span></label>
                    <input type="text" name="code_pays" id="codeInput" class="form-control" maxlength="3" placeholder="Ex: DZA ou DZ" required>
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" id="submitBtn" class="btn btn-primary ajouter">
                    <i class="fas fa-save"></i> Enregistrer
                </button>
                <button type="button" id="cancelEdit" class="btn btn-secondary d-none">Annuler</button>
            </div>
        </form>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <i class="fas fa-table"></i> Liste des Pays
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Nom du Pays</th>
                        <th>Code Pays</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($countries)): ?>
                        <tr><td colspan="3" class="text-center py-3">Aucun pays enregistré.</td></tr>
                    <?php else: ?>
                        <?php foreach ($countries as $country): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($country['nom']); ?></strong></td>
                                <td><strong><?= htmlspecialchars($country['code_pays']); ?></strong></td>
                                <td>
                                    <button class="btn btn-sm eye text-white edit-pays" 
                                            data-pays='<?= htmlspecialchars(json_encode($country), ENT_QUOTES, 'UTF-8') ?>'>
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-pays" 
                                            data-id="<?= $country['id'] ?>" 
                                            data-nom="<?= htmlspecialchars($country['nom']) ?>">
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
const paysForm = document.getElementById('paysForm');

// Restrictions de saisie (comme sur ton fournisseur)
document.getElementById('codeInput').addEventListener('input', function() {
    this.value = this.value.toUpperCase().replace(/[^A-Z]/g, '');
});
document.getElementById('nomInput').addEventListener('input', function() {
    this.value = this.value.replace(/[^a-zA-ZÀ-ÿ\s\-]/g, '');
});

// Soumission (Ajout / Modif) avec SweetAlert2 (Timer 1500)
paysForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    paysForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    
    try {
        const response = await fetch('process.php', { method: 'POST', body: new FormData(paysForm) });
        const data = await response.json();

        if (data.ok) {
            Swal.fire({
                icon: 'success',
                title: 'Succès',
                text: 'Données enregistrées avec succès',
                timer: 1500,
                showConfirmButton: false,
                timerProgressBar: true
            }).then(() => location.reload());
        } else if (data.errors) {
            for (const [key, msg] of Object.entries(data.errors)) {
                const input = paysForm.querySelector(`[name="${key}"]`);
                if (input) {
                    input.classList.add('is-invalid');
                    const feedback = input.nextElementSibling;
                    if(feedback) feedback.textContent = msg;
                }
            }
        } else {
            Swal.fire('Erreur', data.message, 'error');
        }
    } catch (err) { 
        Swal.fire('Erreur', 'Impossible de joindre le serveur.', 'error'); 
    }
});

// Mode Modification
document.querySelectorAll('.edit-pays').forEach(btn => {
    btn.addEventListener('click', function() {
        const p = JSON.parse(this.dataset.pays);
        document.getElementById('formTitle').textContent = "Modifier : " + p.nom;
        document.getElementById('paysId').value = p.id;
        
        paysForm.querySelector('[name="nom"]').value = p.nom;
        paysForm.querySelector('[name="code_pays"]').value = p.code_pays;

        document.getElementById('submitBtn').innerHTML = '<i class="fas fa-sync"></i> Mettre à jour';
        document.getElementById('cancelEdit').classList.remove('d-none');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
});

document.getElementById('cancelEdit').addEventListener('click', () => location.reload());

// Suppression avec SweetAlert2 (Harmonisée sur le Fournisseur)
document.querySelectorAll('.delete-pays').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        const nom = this.dataset.nom;

        Swal.fire({
            title: 'Êtes-vous sûr ?',
            text: `Supprimer le pays "${nom}" ?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Oui, supprimer !',
            cancelButtonText: 'Annuler'
        }).then(async (result) => {
            if (result.isConfirmed) {
                const fd = new FormData();
                fd.append('form_type', 'delete_pays');
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
                    Swal.fire('Erreur', data.message, 'error');
                }
            }
        });
    });
});
</script>