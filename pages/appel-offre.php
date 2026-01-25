<?php
require_once dirname(__DIR__) . '/database.php';
$pdo = getDBConnection();

// 1. Récupération des devises pour le select
$devises = $pdo->query("SELECT Id, code FROM devise ORDER BY code")->fetchAll();

// 2. Logique AUTO-EDIT : Si l'ID est présent dans l'URL, on récupère les infos
$edit_data = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT ao.*, d.code as devise_code FROM appel_offre ao LEFT JOIN devise d ON ao.deviseID = d.Id WHERE ao.id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_data = $stmt->fetch(PDO::FETCH_ASSOC);
}

// 3. Récupération de la liste complète pour le tableau en bas
$query = "SELECT ao.*, d.code as devise_code 
          FROM appel_offre ao 
          LEFT JOIN devise d ON ao.deviseID = d.Id 
          ORDER BY ao.id DESC";
$appels_offre = $pdo->query($query)->fetchAll();
?>

<div class="content-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <h2><i class="fas fa-file-invoice me-2"></i>Gestion des Appels d'Offres</h2>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header text-white" style="background-color: #486a70;">
        <i class="fas fa-user-edit me-2"></i><span id="cardHeaderTitle">Nouvel Appel d'Offre</span>
    </div>
    <div class="card-body">
        <form id="appelOffreForm" novalidate>
            <input type="hidden" name="id" id="aoId">
            <input type="hidden" name="form_type" id="formType" value="appel_offre">

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Numéro d'Appel d'Offre</label>
                    <input type="text" name="numero_ao" id="numeroAoInput" class="form-control" placeholder="Ex: AO2025001" required>
                    <div class="invalid-feedback"></div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Date d'Émission</label>
                    <input type="date" name="date_emission" id="dateEmissionInput" class="form-control" required>
                    <div class="invalid-feedback"></div>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Montant estimé</label>
                    <input type="text" name="montant" id="montantInput" class="form-control" placeholder="0.00">
                    <div class="invalid-feedback"></div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Devise</label>
                    <select name="deviseID" id="deviseIDSelect" class="form-select">
                        <option value="">Sélectionner</option>
                        <?php foreach ($devises as $d): ?>
                            <option value="<?php echo $d['Id']; ?>"><?php echo $d['code']; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <div class="mt-2">
                <button type="submit" id="submitBtn" class="btn ajouter">
                    <i class="fas fa-save me-2"></i>Enregistrer le dossier
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white fw-bold">
        <i class="fas fa-list me-2"></i>Liste des dossiers récents
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width: 80px;">ID</th>
                        <th>Numéro Appel d'Offre</th>
                        <th>Date</th>
                        <th class="text-end">Montant</th>
                        <th class="text-center" style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appels_offre as $ao): ?>
                    <tr>
                        <td class="ps-3 text-muted">#<?php echo $ao['id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($ao['num_app_offre']); ?></strong></td>
                        <td><?php echo date('d/m/Y', strtotime($ao['date_emission'])); ?></td>
                        <td class="text-end fw-bold">
                            <?php echo number_format($ao['montant'], 2, ',', ' '); ?> <small><?php echo $ao['devise_code']; ?></small>
                        </td>
                        <td class="text-center">
                            <div class="btn-group">
                                <button class="btn btn-sm edit text-white edit-ao" data-ao='<?php echo json_encode($ao, JSON_HEX_APOS); ?>' title="Modifier">
                                    <i class="fas fa-pencil-alt"></i>
                                </button>
                                <button class="btn btn-sm btn-danger delete-ao" data-id="<?php echo $ao['id']; ?>" title="Supprimer">
            
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
const aoForm = document.getElementById('appelOffreForm');
const submitBtn = document.getElementById('submitBtn');
const cardHeaderTitle = document.getElementById('cardHeaderTitle');

// --- FONCTION POUR ACTIVER LE MODE ÉDITION ---
function activateEditMode(ao) {
    if(!ao) return;
    
    cardHeaderTitle.textContent = `Modifier l'appel d'offre : ${ao.num_app_offre}`;
    
    document.getElementById('formType').value = 'update_appel_offre';
    document.getElementById('aoId').value = ao.id;
    document.getElementById('numeroAoInput').value = ao.num_app_offre;
    document.getElementById('dateEmissionInput').value = ao.date_emission;
    document.getElementById('montantInput').value = ao.montant;
    document.getElementById('deviseIDSelect').value = ao.deviseID;
    
    submitBtn.innerHTML = '<i class="fas fa-sync me-2"></i>Mettre à jour le dossier';
    
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Initialisation au chargement
window.addEventListener('DOMContentLoaded', () => {
    <?php if ($edit_data): ?>
        const dataFromUrl = <?php echo json_encode($edit_data); ?>;
        activateEditMode(dataFromUrl);
    <?php endif; ?>
});

// Envoi AJAX Formulaire (Ajout ou Modification)
aoForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    aoForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    
    try {
        const res = await fetch('process.php', { method: 'POST', body: new FormData(aoForm) });
        const data = await res.json();
        
        if (data.ok) {
            await Swal.fire({ 
                icon: 'success', 
                title: 'Opération réussie', 
                timer: 1500, 
                showConfirmButton: false, 
                timerProgressBar: true
            });
            window.location.href = 'index.php?page=appel-offre';
        } else if (data.errors) {
            for (const [key, msg] of Object.entries(data.errors)) {
                const input = aoForm.querySelector(`[name="${key}"]`);
                if (input) {
                    input.classList.add('is-invalid');
                    const feedback = input.nextElementSibling;
                    if (feedback && feedback.classList.contains('invalid-feedback')) feedback.textContent = msg;
                }
            }
        }
    } catch (err) { Swal.fire('Erreur', 'Lien rompu avec le serveur', 'error'); }
});

// Clic sur bouton modifier du tableau
document.querySelectorAll('.edit-ao').forEach(btn => {
    btn.addEventListener('click', function() {
        const ao = JSON.parse(this.dataset.ao);
        activateEditMode(ao);
    });
});

// Suppression AJAX
document.querySelectorAll('.delete-ao').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        const numAo = this.closest('tr').querySelector('td strong').textContent;

        Swal.fire({
            title: 'Êtes-vous sûr ?',
            text: `Supprimer l'appel d'offre n° ${numAo} ?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#486a70',
            confirmButtonText: 'Oui, supprimer !',
            cancelButtonText: 'Annuler'
        }).then(async (result) => {
            if (result.isConfirmed) {
                const fd = new FormData();
                fd.append('form_type', 'delete_appel_offre');
                fd.append('id', id);

                try {
                    const res = await fetch('process.php', { method: 'POST', body: fd });
                    const data = await res.json();
                    
                    if (data.ok) {
                        await Swal.fire({ 
                            title: 'Supprimé !', 
                            icon: 'success', 
                            timer: 1500, 
                            showConfirmButton: false, 
                            timerProgressBar: true 
                        });
                        location.reload();
                    }
                } catch (err) { Swal.fire('Erreur', 'Lien rompu', 'error'); }
            }
        });
    });
});

// Nettoyage des inputs (Majuscules et Chiffres uniquement)
document.getElementById('numeroAoInput').addEventListener('input', function() {
    this.value = this.value.toUpperCase().replace(/[^A-Z0-9\/ \-]/g, '');
});
document.getElementById('montantInput').addEventListener('input', function() {
    this.value = this.value.replace(/[^0-9.]/g, '');
});
</script>