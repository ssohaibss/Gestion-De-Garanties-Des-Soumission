<?php
require_once dirname(__DIR__) . '/database.php';

//  Récupération des devises
$stmt = $pdo->query("SELECT * FROM devise ORDER BY libelle ASC");
$currencies = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-header">
    <h2><i class="fas fa-money-bill"></i> Gestion des Devises</h2>
</div>

<div class="card">
    <div class="card-header">
        <i class="fas fa-dollar-sign"></i> <span id="formTitle">Ajouter une Devise</span>
    </div>
    <div class="card-body">
        <form id="deviseForm" action="process.php" method="POST" novalidate>
            <input type="hidden" name="form_type" value="devise">
            <input type="hidden" name="id" id="deviseId" value="">

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nom de la devise <span class="text-danger">*</span></label>
                    <input type="text" name="libelle" id="libelleInput" class="form-control" placeholder="Ex: Dinar Algérien" required>
                    <div class="invalid-feedback"></div>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Code devise (ISO) <span class="text-danger">*</span></label>
                    <input type="text" name="code" id="codeInput" class="form-control" maxlength="3" placeholder="Ex: DZD" required>
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
        <i class="fas fa-list"></i> Liste des Devises
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Nom de la Devise</th>
                        <th>Code ISO</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($currencies)): ?>
                        <tr><td colspan="3" class="text-center py-3">Aucune devise enregistrée.</td></tr>
                    <?php else: ?>
                        <?php foreach ($currencies as $c): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($c['libelle']); ?></strong></td>
                                <td><strong><?= htmlspecialchars($c['code']); ?></strong></td>
                                <td>
                                    <button class="btn btn-sm eye text-white edit-devise" 
                                            data-devise='<?= htmlspecialchars(json_encode($c), ENT_QUOTES, 'UTF-8') ?>'>
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-devise" 
                                            data-id="<?= $c['id'] ?>" 
                                            data-libelle="<?= htmlspecialchars($c['libelle']) ?>">
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
const deviseForm = document.getElementById('deviseForm');

// Restrictions de saisie
document.getElementById('libelleInput').addEventListener('input', function() {
    this.value = this.value.replace(/[^a-zA-ZÀ-ÿ\s\-]/g, '');
});
document.getElementById('codeInput').addEventListener('input', function() {
    this.value = this.value.replace(/[^a-zA-Z]/g, '').toUpperCase();
});

// Soumission avec SweetAlert2
deviseForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    deviseForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    
    try {
        const response = await fetch('process.php', { method: 'POST', body: new FormData(deviseForm) });
        const data = await response.json();

        if (data.ok) {
            Swal.fire({
                icon: 'success',
                title: 'Succès',
                text: 'Devise enregistrée',
                timer: 1500,
                showConfirmButton: false,
                timerProgressBar: true
            }).then(() => location.reload());
        } else if (data.errors) {
            for (const [key, msg] of Object.entries(data.errors)) {
                const input = deviseForm.querySelector(`[name="${key}"]`);
                if (input) {
                    input.classList.add('is-invalid');
                    input.nextElementSibling.textContent = msg;
                }
            }
        } else {
            Swal.fire('Erreur', data.message, 'error');
        }
    } catch (err) { Swal.fire('Erreur', 'Erreur serveur.', 'error'); }
});

// Mode Modification
document.querySelectorAll('.edit-devise').forEach(btn => {
    btn.addEventListener('click', function() {
        const c = JSON.parse(this.dataset.devise);
        document.getElementById('formTitle').textContent = "Modifier la devise : " + c.libelle;
        document.getElementById('deviseId').value = c.id;
        deviseForm.querySelector('[name="libelle"]').value = c.libelle;
        deviseForm.querySelector('[name="code"]').value = c.code;

        document.getElementById('submitBtn').innerHTML = '<i class="fas fa-sync"></i> Mettre à jour';
        document.getElementById('cancelEdit').classList.remove('d-none');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
});

document.getElementById('cancelEdit').addEventListener('click', () => location.reload());

// Suppression avec SweetAlert2
document.querySelectorAll('.delete-devise').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        const libelle = this.dataset.libelle;

        Swal.fire({
            title: 'Êtes-vous sûr ?',
            text: `Voulez-vous supprimer la devise "${libelle}" ?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Oui, supprimer !',
            cancelButtonText: 'Annuler'
        }).then(async (result) => {
            if (result.isConfirmed) {
                const fd = new FormData();
                fd.append('form_type', 'delete_devise');
                fd.append('id', id);

                const res = await fetch('process.php', { method: 'POST', body: fd });
                const data = await res.json();
                if (data.ok) {
                    Swal.fire({ title: 'Supprimé !', icon: 'success', timer: 1500, showConfirmButton: false, timerProgressBar: true })
                    .then(() => location.reload());
                } else {
                    Swal.fire('Erreur', data.message, 'error');
                }
            }
        });
    });
});
</script>