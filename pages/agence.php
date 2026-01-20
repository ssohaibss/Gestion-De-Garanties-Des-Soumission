<?php
require_once dirname(__DIR__) . '/database.php';
$pdo = getDBConnection();

// 1. Récupération des agences avec jointure et gestion du code banque
$query = "SELECT a.*, b.nom_banque, b.code as code_banque 
          FROM agence a 
          LEFT JOIN banque b ON a.banqueID = b.id OR a.banqueID = b.Id 
          ORDER BY a.nom ASC";
$agences = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);

// 2. Récupération des banques avec le code pour le menu déroulant
$banques = $pdo->query("SELECT * FROM banque ORDER BY nom_banque ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-header">
    <h2><i class="fas fa-map-marked-alt"></i> Gestion des Agences</h2>
</div>

<div class="card">
    <div class="card-header">
        <i class="fas fa-user-edit"></i> <span id="formTitle">Nouvelle Agence</span>
    </div>
    <div class="card-body">
        <form id="agenceForm" action="process.php" method="POST" novalidate>
            <input type="hidden" name="form_type" value="agence">
            <input type="hidden" name="id" id="agenceId" value="">

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Code Agence <span class="text-danger">*</span></label>
                    <input type="text" name="code" class="form-control" placeholder="Ex: AG001" required>
                    <div class="invalid-feedback"></div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nom de l'Agence <span class="text-danger">*</span></label>
                    <input type="text" id="nomInput" name="nom" class="form-control" placeholder="Ex: Agence Alger Centre" required>
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Banque <span class="text-danger">*</span></label>
                    <select class="form-select" name="banqueID" required>
    <option value="">Sélectionner une banque</option>
    <?php foreach ($banques as $b): ?>
        <option value="<?= $b['id'] ?? $b['Id'] ?>">
            <?= htmlspecialchars($b['nom_banque']) ?> (<?= htmlspecialchars($b['code'] ?? '---') ?>)
        </option>
    <?php endforeach; ?>
</select>
                    <div class="invalid-feedback"></div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Adresse <span class="text-danger">*</span></label>
                    <input type="text" name="adresse" id="adresseInput" class="form-control" placeholder="Adresse complète" required>
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
    <div class="card-header"><i class="fas fa-list"></i> Liste des Agences</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Nom</th>
                        <th>Banque</th>
                        <th>Adresse</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($agences as $a): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($a['code']) ?></strong></td>
                        <td><strong><?= htmlspecialchars($a['nom']) ?></strong></td>
                        <td><?= htmlspecialchars($a['nom_banque'] ?? 'N/A') ?> (<?= htmlspecialchars($a['code_banque'] ?? '---') ?>)</td>
                        <td><?= htmlspecialchars($a['adresse']) ?></td>
                        <td>
                            <button class="btn btn-sm eye text-white edit-agence" 
                                    data-agence='<?= htmlspecialchars(json_encode($a), ENT_QUOTES, 'UTF-8') ?>'>
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger delete-agence" 
                                    data-id="<?= $a['id'] ?? $a['Id'] ?>" 
                                    data-nom="<?= htmlspecialchars($a['nom']) ?>">
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
const agenceForm = document.getElementById('agenceForm');
document.getElementById('nomInput').addEventListener('input', function() {
    this.value = this.value.replace(/[^a-zA-ZÀ-ÿ\s\-']/g, '');
});

document.getElementById('adresseInput').addEventListener('input', function() {
    this.value = this.value.replace(/[^a-zA-ZÀ-ÿ\s\-',]/g, '');
});
// --- SOUMISSION ---
agenceForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    agenceForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

    try {
        const response = await fetch('process.php', { method: 'POST', body: new FormData(agenceForm) });
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
                const input = agenceForm.querySelector(`[name="${key}"]`);
                if (input) {
                    input.classList.add('is-invalid');
                    const feedback = input.closest('.mb-3')?.querySelector('.invalid-feedback');
                    if (feedback) feedback.textContent = msg;
                }
            }
        }
    } catch (err) { Swal.fire('Erreur', 'Lien serveur rompu.', 'error'); }
});

// --- MODIFICATION ---
document.querySelectorAll('.edit-agence').forEach(btn => {
    btn.addEventListener('click', function() {
        const a = JSON.parse(this.dataset.agence);
        document.getElementById('formTitle').textContent = "Modifier l'agence : " + a.nom;
        document.getElementById('agenceId').value = a.id ?? a.Id;
        
        agenceForm.querySelector('[name="code"]').value = a.code;
        agenceForm.querySelector('[name="nom"]').value = a.nom;
        agenceForm.querySelector('[name="banqueID"]').value = a.banqueID;
        agenceForm.querySelector('[name="adresse"]').value = a.adresse;

        document.getElementById('submitBtn').innerHTML = '<i class="fas fa-sync"></i> Mettre à jour';
        document.getElementById('cancelEdit').classList.remove('d-none');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
});

document.getElementById('cancelEdit').addEventListener('click', () => location.reload());

// --- SUPPRESSION ---
document.querySelectorAll('.delete-agence').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        const nom = this.dataset.nom;

        Swal.fire({
            title: 'Êtes-vous sûr ?',
            text: `Supprimer l'agence "${nom}" ?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Oui, supprimer !',
            cancelButtonText: 'Annuler'
        }).then(async (result) => {
            if (result.isConfirmed) {
                const fd = new FormData();
                fd.append('form_type', 'delete_agence');
                fd.append('id', id);
                const res = await fetch('process.php', { method: 'POST', body: fd });
                const data = await res.json();
                if (data.ok) {
                    Swal.fire({ title: 'Supprimé !', icon: 'success', timer: 1500, showConfirmButton: false })
                    .then(() => location.reload());
                }
            }
        });
    });
});
</script>