 <?php

require_once dirname(__DIR__) . '/database.php';

$pdo = getDBConnection();


//Récupération des données pour les selects

$devises = $pdo->query("SELECT Id, code FROM devise ORDER BY code ASC")->fetchAll();

$appels_offre = $pdo->query("SELECT Id, num_app_offre FROM appel_offre ORDER BY num_app_offre DESC")->fetchAll();

$fournisseurs = $pdo->query("SELECT Id, nom_entreprise FROM soumissionnaire ORDER BY nom_entreprise ASC")->fetchAll();

$structures = $pdo->query("SELECT Id, libelle FROM structure ORDER BY libelle ASC")->fetchAll();

$banque = $pdo->query("SELECT Id, nom_banque FROM banque ORDER BY nom_banque ASC")->fetchAll();

$agences = $pdo->query("SELECT Id, nom, banqueID FROM agence ORDER BY nom ASC")->fetchAll();

$statuts = $pdo->query("SELECT Id, libelle FROM statut ORDER BY libelle ASC")->fetchAll();


//Logique AUTO-EDIT (Si on vient de la liste avec ?edit=ID)

$edit_data = null;

if (isset($_GET['edit'])) {

    $stmt = $pdo->prepare("SELECT * FROM garantie_soumission WHERE id = ?");

    $stmt->execute([$_GET['edit']]);

    $edit_data = $stmt->fetch(PDO::FETCH_ASSOC);

}


//Liste des garanties pour le tableau

$query = "SELECT g.*, s.nom_entreprise, d.code as devise_code, st.libelle as statut_nom

          FROM garantie_soumission g

          LEFT JOIN soumissionnaire s ON g.soumissionnaireID = s.id

          LEFT JOIN devise d ON g.deviseID = d.id

          LEFT JOIN statut st ON g.statutID = st.id

          ORDER BY g.id DESC LIMIT 15";

$recent_garanties = $pdo->query($query)->fetchAll();

?>


<div class="content-header mb-4">

    <div class="d-flex justify-content-between align-items-center">

        <h2><i class="fas fa-shield-alt me-2"></i>Gestion des Garanties</h2>

    </div>

</div>


<div class="card shadow-sm mb-5">

    <div class="card-header text-white" style="background-color: #486a70;">

        <i class="fas fa-edit me-2"></i><span id="cardHeaderTitle">Enregistrer une nouvelle garantie</span>

    </div>

    <div class="card-body">

        <form id="garantieForm" novalidate>

            <input type="hidden" name="id" id="garantieId">

            <input type="hidden" name="form_type" id="formType" value="garantie">


            <div class="row g-3">

                <div class="col-md-4">

                    <label class="form-label fw-bold">Numéro de Garantie</label>

                    <input type="text" name="num_garantie" id="numGarantieInput" class="form-control" placeholder="Numérique uniquement" maxlength="20" required>

                    <div class="invalid-feedback"></div>

                </div>


                <div class="col-md-4">

                    <label class="form-label fw-bold">Montant</label>

                    <input type="text" name="montant_garantie" id="montantInput" class="form-control" placeholder="0.00" required>

                    <div class="invalid-feedback"></div>

                </div>


                <div class="col-md-4">

                    <label class="form-label fw-bold">Devise</label>

                    <select name="deviseID" id="deviseSelect" class="form-select" required>

                        <option value="">Sélectionner...</option>

                        <?php foreach($devises as $d): ?>

                            <option value="<?= $d['Id'] ?>"><?= $d['code'] ?></option>

                        <?php endforeach; ?>

                    </select>

                    <div class="invalid-feedback"></div>

                </div>


                <div class="col-md-6">

                    <label class="form-label fw-bold">Date d'Émission</label>

                    <input type="date" name="date_emission" id="dateEInput" class="form-control" required>

                    <div class="invalid-feedback"></div>

                </div>

                <div class="col-md-6">

                    <label class="form-label fw-bold">Date d'Expiration</label>

                    <input type="date" name="date_expiration" id="dateXInput" class="form-control" required>

                    <div class="invalid-feedback"></div>

                </div>


                <div class="col-md-6">

                    <label class="form-label fw-bold">Soumissionnaire (Fournisseur)</label>

                    <select name="soumissionnaireID" id="fournisseurSelect" class="form-select" required>

                        <option value="">Choisir un fournisseur...</option>

                        <?php foreach($fournisseurs as $f): ?>

                            <option value="<?= $f['Id'] ?>"><?= htmlspecialchars($f['nom_entreprise']) ?></option>

                        <?php endforeach; ?>

                    </select>

                    <div class="invalid-feedback"></div>

                </div>


                <div class="col-md-6">

                    <label class="form-label fw-bold">Appel d'Offre lié (Optionnel)</label>

                    <select name="appel_offreID" id="aoSelect" class="form-select">

                        <option value="">Aucun</option>

                        <?php foreach($appels_offre as $ao): ?>

                            <option value="<?= $ao['Id'] ?>"><?= htmlspecialchars($ao['num_app_offre']) ?></option>

                        <?php endforeach; ?>

                    </select>

                    <div class="invalid-feedback"></div>

                </div>


            <div class="col-md-4">

                    <label class="form-label fw-bold">Banque</label>

                    <select name="banqueID" id="banqueSelect" class="form-select" required>

                        <option value="">Sélectionner...</option>

                        <?php foreach($banque as $b): ?>
                            <option value="<?= $b['Id'] ?>"><?= htmlspecialchars($b['nom_banque']) ?></option>

                        <?php endforeach; ?>

                    </select>

                    <div class="invalid-feedback"></div>

                </div>



               <div class="col-md-4">

                    <label class="form-label fw-bold">Agence</label>

                    <select name="agenceID" id="agenceSelect" class="form-select" required>
                        
                        <option value="">Sélectionner une banque...</option>

                        <?php foreach($agences as $a): ?>
                            <option value="<?= $a['Id'] ?>" data-banque="<?= $a['banqueID'] ?>" style="display:none;">

                      <?= htmlspecialchars($a['nom']) ?>

                             </option>

                         <?php endforeach; ?>

                    </select>

                         <div class="invalid-feedback"></div>
                    </div>


                <div class="col-md-4">

                    <label class="form-label fw-bold">Structure</label>

                    <select name="structureID" id="structureSelect" class="form-select" required>

                        <option value="">Sélectionner...</option>

                        <?php foreach($structures as $s): ?>

                            <option value="<?= $s['Id'] ?>"><?= htmlspecialchars($s['libelle']) ?></option>

                        <?php endforeach; ?>

                    </select>

                    <div class="invalid-feedback"></div>

                </div>


                <div class="col-md-4">

                    <label class="form-label fw-bold">Statut</label>

                    <select name="statutID" id="statutSelect" class="form-select" required>

                        <option value="">Sélectionner...</option>

                        <?php foreach($statuts as $st): ?>

                            <option value="<?= $st['Id'] ?>"><?= htmlspecialchars($st['libelle']) ?></option>

                        <?php endforeach; ?>

                    </select>

                    <div class="invalid-feedback"></div>

                </div>

            </div>


            <div class="mt-4">

                <button type="submit" id="submitBtn" class="btn ajouter">

                    <i class="fas fa-save me-2"></i>Enregistrer la Garantie

                </button>

            </div>

        </form>

    </div>

</div>


<div class="card shadow-sm">

    <div class="card-header bg-white fw-bold">

        <i class="fas fa-list me-2"></i>Dernières garanties enregistrées

    </div>

    <div class="card-body p-0">

        <div class="table-responsive">

            <table class="table table-hover align-middle mb-0">

                <thead class="table-light">

                    <tr>

                        <th class="ps-3">N° Garantie</th>

                        <th>Fournisseur</th>

                        <th class="text-end">Montant</th>

                        <th class="text-center">Statut</th>

                        <th class="text-center">Actions</th>

                    </tr>

                </thead>

                <tbody>

                    <?php foreach ($recent_garanties as $g): ?>

                    <tr>

                        <td class="ps-3 fw-bold"><?= $g['num_garantie'] ?></td>

                        <td><?= htmlspecialchars($g['nom_entreprise']) ?></td>

                        <td class="text-end fw-bold"><?= number_format($g['montant_garantie'], 2) ?> <?= $g['devise_code'] ?></td>

                        <td class="text-center"><span class="badge bg-light text-dark border"><?= $g['statut_nom'] ?></span></td>

                        <td class="text-center">

                            <div class="btn-group">

                                <a href="index.php?page=details-garantie&id=<?= $g['id'] ?>" class="btn btn-sm eye text-white"><i class="fas fa-eye"></i></a>

                                <button class="btn btn-sm edit text-white edit-btn" data-garantie='<?= json_encode($g, JSON_HEX_APOS) ?>'><i class="fas fa-pencil-alt"></i></button>

                                <button class="btn btn-sm btn-danger delete-btn" data-id="<?= $g['id'] ?>" data-num="<?= $g['num_garantie'] ?>"><i class="fas fa-trash"></i></button>

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

const form = document.getElementById('garantieForm');

const numInput = document.getElementById('numGarantieInput');

const montantInput = document.getElementById('montantInput');


//Unicité AJAX en temps réel

numInput.addEventListener('blur', async function() {

    if(!this.value) return;

    const currentId = document.getElementById('garantieId').value || 0;

    const res = await fetch(`unique_check.php?type=garantie&field=num_garantie&value=${this.value}&current_id=${currentId}`);

    const data = await res.json();

    if(data.exists) {

        this.classList.add('is-invalid');

        this.nextElementSibling.textContent = "Ce numéro est déjà utilisé.";

    } else {

        this.classList.remove('is-invalid');

    }

});


//Mode Edition

function activateEditMode(g) {

    document.getElementById('cardHeaderTitle').textContent = "Modifier la Garantie n° " + g.num_garantie;

    document.getElementById('formType').value = 'update_garantie';

    document.getElementById('garantieId').value = g.id;

    document.getElementById('numGarantieInput').value = g.num_garantie;

    document.getElementById('montantInput').value = g.montant_garantie;

    document.getElementById('dateEInput').value = g.date_emission;

    document.getElementById('dateXInput').value = g.date_expiration;

    document.getElementById('fournisseurSelect').value = g.soumissionnaireID;

    document.getElementById('deviseSelect').value = g.deviseID;

    document.getElementById('aoSelect').value = g.appel_offreID;

    document.getElementById('agenceSelect').value = g.agenceID;

    document.getElementById('structureSelect').value = g.structureID;

    document.getElementById('statutSelect').value = g.statutID;

   

    document.getElementById('submitBtn').innerHTML = '<i class="fas fa-sync me-2"></i>Mettre à jour la garantie';

    window.scrollTo({ top: 0, behavior: 'smooth' });

}


window.addEventListener('DOMContentLoaded', () => {

    <?php if ($edit_data): ?> activateEditMode(<?= json_encode($edit_data) ?>); <?php endif; ?>

});


//Envoi AJAX avec gestion des erreurs par champ

form.addEventListener('submit', async (e) => {

    e.preventDefault();

    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));


    const fd = new FormData(form);

    try {

        const res = await fetch('process.php', { method: 'POST', body: fd });

        const data = await res.json();


        if (data.ok) {

            await Swal.fire({ icon: 'success', title: 'Réussi', timer: 1500, timerProgressBar: true, showConfirmButton: false });

            window.location.href = 'index.php?page=liste-garanties';

        } else if (data.errors) {

            for (const [key, msg] of Object.entries(data.errors)) {

                const input = form.querySelector(`[name="${key}"]`);

                if (input) {

                    input.classList.add('is-invalid');

                    const feedback = input.nextElementSibling;

                    if (feedback && feedback.classList.contains('invalid-feedback')) feedback.textContent = msg;

                }

            }

            Swal.fire({ icon: 'error', title: 'Erreur', text: 'Veuillez corriger les champs en rouge.' });

        }

    } catch(e) { Swal.fire('Erreur', 'Lien rompu', 'error'); }

});


// Actions tableau

document.querySelectorAll('.edit-btn').forEach(btn => {

    btn.addEventListener('click', function() { activateEditMode(JSON.parse(this.dataset.garantie)); });

});


document.querySelectorAll('.delete-btn').forEach(btn => {

    btn.addEventListener('click', async function() {

        const id = this.dataset.id;

        const result = await Swal.fire({

            title: 'Supprimer ?',

            text: `Garantie n° ${this.dataset.num}`,

            icon: 'warning',

            showCancelButton: true,

            confirmButtonColor: '#d33',

            cancelButtonColor: '#486a70'

        });

        if(result.isConfirmed) {

            const fd = new FormData(); fd.append('form_type', 'delete_garantie'); fd.append('id', id);

            const res = await fetch('process.php', { method: 'POST', body: fd });

            const data = await res.json();

            if(data.ok) {

                await Swal.fire({ icon: 'success', title: 'Supprimée', timer: 1500, timerProgressBar: true, showConfirmButton: false });

                location.reload();

            }

        }

    });

});


// Nettoyage forcé

montantInput.addEventListener('input', function() { this.value = this.value.replace(/[^0-9.]/g, ''); });

numInput.addEventListener('input', function() { this.value = this.value.replace(/[^0-9]/g, ''); });


// banque/agence
document.addEventListener('DOMContentLoaded', function() {
    const banqueSelect = document.getElementById('banqueSelect');
    const agenceSelect = document.getElementById('agenceSelect');
    const agenceOptions = agenceSelect.querySelectorAll('option');

    banqueSelect.addEventListener('change', function() {
        const selectedBanqueId = this.value;
        
        // Reset agence selection
        agenceSelect.value = "";
        
        let hasVisibleOptions = false;

        agenceOptions.forEach(option => {
            // Skip the placeholder option
            if (option.value === "") return;

            // Check if option belongs to selected bank
            if (option.getAttribute('data-banque') === selectedBanqueId) {
                option.style.display = 'block';
                hasVisibleOptions = true;
            } else {
                option.style.display = 'none';
            }
        });

        // Update placeholder text based on results
        if (selectedBanqueId === "") {
            agenceOptions[0].textContent = "Sélectionner une banque d'abord...";
        } else if (!hasVisibleOptions) {
            agenceOptions[0].textContent = "Aucune agence trouvée pour cette banque";
        } else {
            agenceOptions[0].textContent = "Sélectionner une agence...";
        }
    });
});

</script> 