<?php
require_once dirname(__DIR__) . '/database.php';
if(!isset($pdo)) { $pdo = getDBConnection(); }

// 1. Récupération des données pour les selects
$devises = $pdo->query("SELECT Id, code FROM devise ORDER BY code ASC")->fetchAll();
$appels_offre = $pdo->query("SELECT Id, num_app_offre FROM appel_offre ORDER BY num_app_offre DESC")->fetchAll();
$soumissionnaire = $pdo->query("SELECT Id, nom_entreprise FROM soumissionnaire ORDER BY nom_entreprise ASC")->fetchAll();
$structures = $pdo->query("SELECT Id, libelle FROM structure ORDER BY libelle ASC")->fetchAll();
$banque = $pdo->query("SELECT Id, nom_banque FROM banque ORDER BY nom_banque ASC")->fetchAll();
$agences = $pdo->query("SELECT Id, nom, banqueID FROM agence ORDER BY nom ASC")->fetchAll();
$statuts = $pdo->query("SELECT Id, libelle FROM statut ORDER BY libelle ASC")->fetchAll(PDO::FETCH_ASSOC);

// 1.1. Détection Automatique des IDs de Statut
$status_active = ['id' => '', 'libelle' => 'Active'];
$status_expired = ['id' => '', 'libelle' => 'Expirée'];

foreach ($statuts as $s) {
    $lib = mb_strtolower($s['libelle'], 'UTF-8');
    if (strpos($lib, 'activ') !== false || strpos($lib, 'cours') !== false || strpos($lib, 'valid') !== false) {
        $status_active = ['id' => $s['Id'], 'libelle' => $s['libelle']];
    }
    if (strpos($lib, 'expir') !== false || strpos($lib, 'clôt') !== false || strpos($lib, 'inval') !== false) {
        $status_expired = ['id' => $s['Id'], 'libelle' => $s['libelle']];
    }
}

// 2. Logique AUTO-EDIT (Mise à jour pour inclure les totaux CALCULÉS)
$edit_data = null;
if (isset($_GET['edit'])) {
    $sql = "SELECT g.*, 
            (SELECT COUNT(*) FROM authentification a WHERE a.garantie_soumissionID = g.id) as auth_count,
            (g.montant_garantie + COALESCE((SELECT SUM(nouveau_montant) FROM amendement a LEFT JOIN type_amendement ta ON a.type_amendementID = ta.id WHERE a.garantie_soumissionID = g.id AND ta.code IN ('MONTANT', 'MIXTE')), 0)) as montant_actuel,
            COALESCE((SELECT nouvelle_date_expiration FROM amendement a LEFT JOIN type_amendement ta ON a.type_amendementID = ta.id WHERE a.garantie_soumissionID = g.id AND ta.code IN ('DATE', 'MIXTE') AND a.nouvelle_date_expiration IS NOT NULL ORDER BY a.date_amendement DESC, a.id DESC LIMIT 1), g.date_expiration) as date_expiration_actuelle,
            COALESCE((SELECT SUM(montant_libere) FROM liberation l WHERE l.garantie_soumissionID = g.id), 0) as total_libere
            FROM garantie_soumission g WHERE g.id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_GET['edit']]);
    $edit_data = $stmt->fetch(PDO::FETCH_ASSOC);
}

// 4. Types d'amendement
$types_amendement = $pdo->query("SELECT id, code, libelle FROM type_amendement ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
// 5. Types de libération
$types_liberation = $pdo->query("SELECT id, code, libelle FROM type_liberation ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <h2><i class="fas fa-shield-alt me-2"></i>Gestion des Garanties</h2>
        <div class="btn-group" role="group">
            <button type="button" id="btnAjouterAmendement" class="btn btn-warning text-white" style="display: none;">
                <i class="fas fa-file-signature me-2"></i>Ajouter Amendement
            </button>
            <button type="button" id="btnAjouterAuthentification" class="btn btn-primary" style="display: none;">
                <i class="fas fa-certificate me-2"></i>Ajouter Authentification
            </button>
            <button type="button" id="btnAjouterLiberation" class="btn btn-success" style="display: none;">
                <i class="fas fa-unlock-alt me-2"></i>Ajouter Libération
            </button>
        </div>
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
            <input type="hidden" name="statutID" id="statutInput">

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Numéro de Garantie <span class="text-danger">*</span></label>
                    <input type="text" name="num_garantie" id="numGarantieInput" class="form-control intel-input" 
                           placeholder="Chiffres uniquement" maxlength="20" required
                           data-pattern="^[0-9]+$" 
                           data-msg="Ce champ doit contenir uniquement des chiffres.">
                    <div class="invalid-feedback"></div>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Montant <span class="text-danger">*</span></label>
                    <input type="text" name="montant_garantie" id="montantInput" class="form-control intel-input" placeholder="0.00" required
                           data-pattern="^[0-9]+([.,][0-9]{1,2})?$"
                           data-msg="Numérique valide (ex: 1000.50).">
                    <div class="invalid-feedback"></div>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Devise <span class="text-danger">*</span></label>
                    <select name="deviseID" id="deviseSelect" class="form-select standard-input" required>
                        <option value="">Sélectionner...</option>
                        <?php foreach($devises as $d): ?>
                            <option value="<?= $d['Id'] ?>"><?= $d['code'] ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback"></div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Date d'Émission <span class="text-danger">*</span></label>
                    <input type="date" name="date_emission" id="dateEInput" class="form-control standard-input" required>
                    <div class="invalid-feedback"></div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Date d'Expiration <span class="text-danger">*</span></label>
                    <input type="date" name="date_expiration" id="dateXInput" class="form-control standard-input" required>
                    <div class="invalid-feedback"></div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Soumissionnaire <span class="text-danger">*</span></label>
                    <select name="soumissionnaireID" id="soumissionnaireSelect" class="form-select standard-input" required>
                        <option value="">Choisir un soumissionnaire...</option>
                        <?php foreach($soumissionnaire as $f): ?>
                            <option value="<?= $f['Id'] ?>"><?= htmlspecialchars($f['nom_entreprise']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback"></div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Appel d'Offre lié</label>
                    <select name="appel_offreID" id="aoSelect" class="form-select standard-input">
                        <option value="">Aucun</option>
                        <?php foreach($appels_offre as $ao): ?>
                            <option value="<?= $ao['Id'] ?>"><?= htmlspecialchars($ao['num_app_offre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback"></div>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Banque <span class="text-danger">*</span></label>
                    <select name="banqueID" id="banqueSelect" class="form-select standard-input" required>
                        <option value="">Sélectionner...</option>
                        <?php foreach($banque as $b): ?>
                            <option value="<?= $b['Id'] ?>"><?= htmlspecialchars($b['nom_banque']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback"></div>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Agence <span class="text-danger">*</span></label>
                    <select name="agenceID" id="agenceSelect" class="form-select standard-input" required>
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
                    <label class="form-label fw-bold">Structure <span class="text-danger">*</span></label>
                    <select name="structureID" id="structureSelect" class="form-select standard-input" required>
                        <option value="">Sélectionner...</option>
                        <?php foreach($structures as $s): ?>
                            <option value="<?= $s['Id'] ?>"><?= htmlspecialchars($s['libelle']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback"></div>
                </div>
            </div>

            <div class="card-body px-0">
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label fw-bold">Ajouter un document PDF <span class="text-danger">*</span></label>
                        <input type="file" name="pdf_files" id="pdfFilesInput" class="form-control standard-input" accept=".pdf" required>
                        <div class="invalid-feedback">Ce champ est requis pour une nouvelle garantie.</div> 
                        <small class="text-muted d-block mt-2">
                        <i class="fas fa-info-circle me-1"></i>Format PDF uniquement. Maximum 10 MB.
                        </small>
                    </div>
                    <div class="col-md-12" id="pdfPreview"></div>
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

<div class="modal fade" id="amendementModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header text-white" style="background-color: #e67e22;">
                <h5 class="modal-title"><i class="fas fa-file-signature me-2"></i>Nouvel Amendement</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="amendementForm" novalidate>
                <div class="modal-body">
                    <input type="hidden" name="garantie_soumissionID" id="amendementGarantieId">
                    <div class="alert alert-info mb-4">
                        <i class="fas fa-info-circle me-2"></i><strong id="amendementGarantieInfo"></strong>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Numéro d'Amendement <span class="text-danger">*</span></label>
                            <input type="text" name="num_amendement" class="form-control intel-input" 
                                   placeholder="Numéro unique" required data-pattern="^[0-9]+$" data-msg="Numérique uniquement.">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Date d'Amendement <span class="text-danger">*</span></label>
                            <input type="date" name="date_amendement" class="form-control standard-input" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Type d'Amendement <span class="text-danger">*</span></label>
                            <select name="type_amendementID" id="typeAmendementSelect" class="form-select standard-input" required>
                                <option value="">Sélectionner le type...</option>
                                <?php foreach ($types_amendement as $type): ?>
                                    <option value="<?php echo $type['id']; ?>" data-code="<?php echo $type['code']; ?>">
                                        <?php echo htmlspecialchars($type['libelle']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        
                        <div class="col-md-6" id="nouveauMontantGroup" style="display: none;">
                            <label class="form-label fw-bold">Nouveau Montant <span class="text-danger">*</span></label>
                            <input type="text" name="nouveau_montant" id="nouveauMontantInput" class="form-control intel-input" 
                                   placeholder="0.00" data-pattern="^[0-9]+([.,][0-9]{1,2})?$" data-msg="Montant invalide">
                            <small class="text-muted">Actuel : <span id="montantActuel"></span></small>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6" id="nouvelleDateGroup" style="display: none;">
                            <label class="form-label fw-bold">Nouvelle Date d'Expiration <span class="text-danger">*</span></label>
                            <input type="date" name="nouvelle_date_expiration" id="nouvelleDateInput" class="form-control standard-input" required>
                            <small class="text-muted">Actuelle : <span id="dateExpirationActuelle"></span></small>
                            <div class="invalid-feedback"></div>
                        </div>
                        
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Document PDF <span class="text-danger">*</span></label>
                            <input type="file" name="amendment_pdf" id="amendmentPdfInput" class="form-control standard-input" accept=".pdf" required>
                            <div class="invalid-feedback">Ce document est obligatoire.</div>
                            <div id="amendmentPdfPreview" class="mt-2"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-warning text-white"><i class="fas fa-save me-2"></i>Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="authentificationModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header text-white" style="background-color: #486a70;">
        <h5 class="modal-title"><i class="fas fa-certificate me-2"></i>Nouvelle Authentification</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form id="authentificationForm" novalidate>
        <div class="modal-body">
          <input type="hidden" name="garantie_soumissionID" id="authentificationGarantieId">
          <input type="hidden" name="form_type" value="authentification">
          <div class="alert alert-info mb-4">
            <i class="fas fa-info-circle me-2"></i><strong id="authentificationGarantieInfo"></strong>
          </div>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-bold">Numéro <span class="text-danger">*</span></label>
              <input type="text" name="num_authentification" class="form-control intel-input"
                placeholder="Numéro unique" required data-pattern="^[0-9A-Za-z\-]+$" data-msg="Alphanumérique uniquement.">
              <div class="invalid-feedback"></div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">Date <span class="text-danger">*</span></label>
              <input type="date" name="date_authentification" class="form-control standard-input" required>
              <div class="invalid-feedback"></div>
            </div>
            <div class="col-md-12">
              <label class="form-label fw-bold">Document PDF <span class="text-danger">*</span></label>
              <input type="file" name="authentification_pdf" id="authentificationPdfInput" class="form-control standard-input" accept=".pdf" required>
              <div class="invalid-feedback"></div>
              <div id="authentificationPdfPreview" class="mt-2"></div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Enregistrer</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="liberationModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header text-white" style="background-color: #27ae60;">
        <h5 class="modal-title"><i class="fas fa-unlock-alt me-2"></i>Nouvelle Libération</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form id="liberationForm" novalidate>
        <div class="modal-body">
          <input type="hidden" name="garantie_soumissionID" id="liberationGarantieId">
          <input type="hidden" name="form_type" value="liberation">
          <div class="alert alert-info mb-4">
            <i class="fas fa-info-circle me-2"></i><strong id="liberationGarantieInfo"></strong>
          </div>
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label fw-bold">Numéro <span class="text-danger">*</span></label>
              <input type="text" name="num_liberation" class="form-control intel-input"
                placeholder="Numéro unique" required data-pattern="^[0-9]+$" data-msg="Chiffres uniquement.">
              <div class="invalid-feedback"></div>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-bold">Date <span class="text-danger">*</span></label>
              <input type="date" name="date_liberation" class="form-control standard-input" required>
              <div class="invalid-feedback"></div>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-bold">Type de Libération <span class="text-danger">*</span></label>
              <select name="type_liberationID" id="typeLiberationSelect" class="form-select standard-input" required>
                  <option value="">Sélectionner...</option>
                  <?php foreach ($types_liberation as $type): ?>
                      <option value="<?php echo $type['id']; ?>" data-code="<?php echo $type['code']; ?>">
                          <?php echo htmlspecialchars($type['libelle']); ?>
                      </option>
                  <?php endforeach; ?>
              </select>
              <div class="invalid-feedback"></div>
            </div>
            
            <div class="col-md-12" id="montantLibereGroup" style="display: none;">
                <label class="form-label fw-bold">Montant Libéré <span class="text-danger">*</span></label>
                <input type="text" name="montant_libere" id="montantLibereInput" class="form-control intel-input" 
                       placeholder="0.00" data-pattern="^[0-9]+([.,][0-9]{1,2})?$" data-msg="Montant invalide">
                <small class="text-muted">Garantie Actuelle : <span id="montantMaxLibere"></span></small>
                <div class="invalid-feedback"></div>
            </div>

            <div class="col-md-12">
              <label class="form-label fw-bold">Document PDF <span class="text-danger">*</span></label>
              <input type="file" name="liberation_pdf" id="liberationPdfInput" class="form-control standard-input" accept=".pdf" required>
              <div class="invalid-feedback"></div>
              <div id="liberationPdfPreview" class="mt-2"></div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-success"><i class="fas fa-save me-2"></i>Enregistrer</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('garantieForm');
    
    // --- VARIABLES STATUT (Injection SÉCURISÉE) ---
    const STATUS_ACTIVE = <?= json_encode($status_active) ?>;
    const STATUS_EXPIRED = <?= json_encode($status_expired) ?>;

    // --- HELPER DATE LOCALE (YYYY-MM-DD) ---
    function getTodayLocal() {
        const now = new Date();
        return now.getFullYear() + '-' + 
               String(now.getMonth() + 1).padStart(2, '0') + '-' + 
               String(now.getDate()).padStart(2, '0');
    }

    // --- HELPER DEBOUNCE ---
    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            const context = this;
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(context, args), wait);
        };
    }

    // --- HELPER: FIND FEEDBACK (ROBUST) ---
    function getFeedbackElement(input) {
        if (input.parentElement) {
            const fb = input.parentElement.querySelector('.invalid-feedback');
            if (fb) return fb;
        }
        let sibling = input.nextElementSibling;
        while (sibling) {
            if (sibling.classList.contains('invalid-feedback')) return sibling;
            sibling = sibling.nextElementSibling;
        }
        return null;
    }

    // --- LOGIQUE BANQUE / AGENCE ---
    const banqueSelect = document.getElementById('banqueSelect');
    const agenceSelect = document.getElementById('agenceSelect');
    
    if (banqueSelect && agenceSelect) {
        const agenceOptions = agenceSelect.querySelectorAll('option');
        banqueSelect.addEventListener('change', function() {
            const selectedBanqueId = this.value;
            agenceSelect.value = "";
            let hasVisibleOptions = false;
            agenceOptions.forEach(option => {
                if (option.value === "") return;
                if (option.getAttribute('data-banque') === selectedBanqueId) {
                    option.style.display = 'block';
                    hasVisibleOptions = true;
                } else {
                    option.style.display = 'none';
                }
            });
            if (selectedBanqueId === "") agenceOptions[0].textContent = "Sélectionner une banque d'abord...";
            else if (!hasVisibleOptions) agenceOptions[0].textContent = "Aucune agence trouvée pour cette banque";
            else agenceOptions[0].textContent = "Sélectionner une agence...";
        });
    }

    // --- VALIDATION FORMAT ---
    function validateField(input) {
        if (input.offsetParent === null) return true; 

        const val = input.value.trim();
        const fb = getFeedbackElement(input);
        
        input.classList.remove('is-invalid', 'is-valid');

        if (input.hasAttribute('required') && val === "") {
            input.classList.add('is-invalid');
            if (fb) fb.textContent = "Ce champ est obligatoire.";
            return false;
        }

        if (val !== "" && input.dataset.pattern) {
            const pattern = new RegExp(input.dataset.pattern);
            if (!pattern.test(val)) {
                input.classList.add('is-invalid');
                if (fb) fb.textContent = input.dataset.msg || "Format invalide.";
                return false;
            }
        }
        
        if (val !== "") input.classList.add('is-valid');
        return true;
    }

    // --- CHECK UNICITÉ ---
    async function checkUniqueness(input) {
        if (!validateField(input)) return false; 
        const val = input.value.trim();
        if (val === "") return true;
        
        const fb = getFeedbackElement(input);
        let checkType = '';
        if (input.name === 'num_garantie') checkType = 'garantie';
        else if (input.name === 'num_amendement') checkType = 'amendement';
        else if (input.name === 'num_authentification') checkType = 'authentification';
        else if (input.name === 'num_liberation') checkType = 'liberation';
        else return true;
        
        const idValue = (checkType === 'garantie') ? (document.getElementById('garantieId').value || 0) : 0;
        try {
            const response = await fetch(`pages/unique_check.php?type=${checkType}&field=${input.name}&value=${encodeURIComponent(val)}&id=${idValue}`);
            if (!response.ok) return true;
            const data = await response.json(); 
            if (data.exists) {
                input.classList.remove('is-valid');
                input.classList.add('is-invalid');
                if (fb) fb.textContent = 'Cette valeur existe déjà.';
                return false;
            } else {
                if (!input.classList.contains('is-invalid')) input.classList.add('is-valid'); 
                return true;
            }
        } catch (e) { return true; }
    }
    const debouncedCheck = debounce((input) => checkUniqueness(input), 500);

   // --- CHECK DATE CONSTRAINTS ---
    function checkDateConstraints(input, type) {
        if(!currentEditingGarantie) return true;

        const val = input.value;
        const fb = getFeedbackElement(input);
        const today = getTodayLocal();

        if(!val) return true; 

        input.classList.remove('is-invalid', 'is-valid');
        
        if (type === 'amendement_date') {
            const min = currentEditingGarantie.dateEmission;
            const max = currentEditingGarantie.dateExpirationActuelle;
            
            if (val > today) {
                input.classList.add('is-invalid');
                if(fb) fb.textContent = "La date ne peut pas être dans le futur.";
                return false;
            }
            if (val < min) {
                input.classList.add('is-invalid');
                if(fb) fb.textContent = `La date ne peut pas être antérieure à l'émission (${new Date(min).toLocaleDateString('fr-FR')}).`;
                return false;
            }
            if (val > max) {
                input.classList.add('is-invalid');
                if(fb) fb.textContent = `La date ne peut pas être postérieure à l'expiration actuelle (${new Date(max).toLocaleDateString('fr-FR')}).`;
                return false;
            }
        } 
        else if (type === 'new_expiration') {
            const currentExp = currentEditingGarantie.dateExpirationActuelle;
            if (val <= currentExp) {
                input.classList.add('is-invalid');
                if(fb) fb.textContent = `La nouvelle date doit être postérieure à l'expiration actuelle (${new Date(currentExp).toLocaleDateString('fr-FR')}).`;
                return false;
            }
        }
        // --- NOUVEAU : LOGIQUE POUR L'AUTHENTIFICATION ---
        else if (type === 'authentification_date') {
            const min = currentEditingGarantie.dateEmission;
            
            if (val > today) {
                input.classList.add('is-invalid');
                if(fb) fb.textContent = "La date ne peut pas être dans le futur.";
                return false;
            }
            if (val < min) {
                input.classList.add('is-invalid');
                if(fb) fb.textContent = `La date ne peut pas être antérieure à l'émission (${new Date(min).toLocaleDateString('fr-FR')}).`;
                return false;
            }
        }

        input.classList.add('is-valid');
        return true;
    }

    // --- MAIN FORM DATES
    function checkMainDates() {
        const dateE = document.getElementById('dateEInput');
        const dateX = document.getElementById('dateXInput');
        const fbE = getFeedbackElement(dateE);
        const fbX = getFeedbackElement(dateX);
        const vE = dateE.value;
        const vX = dateX.value;
        const today = getTodayLocal();

        let isValid = true;
        if (vE) {
            if (vE > today) {
                dateE.classList.add('is-invalid');
                if(fbE) fbE.textContent = "Date futur impossible.";
                isValid = false;
            }
        }
        if (vE && vX && vX <= vE) {
            dateX.classList.add('is-invalid');
            if(fbX) fbX.textContent = "Expiration doit être après émission.";
            isValid = false;
        }
        return isValid;
    }

    form.querySelectorAll('.intel-input').forEach(i => {
        i.addEventListener('input', function() {
            if(this.name === 'num_garantie') this.value = this.value.replace(/[^0-9]/g, '');
            validateField(this);
            if(this.name === 'num_garantie' && this.value) debouncedCheck(this);
        });
    });

    form.querySelectorAll('.standard-input').forEach(el => {
        ['change', 'input'].forEach(evt => {
            el.addEventListener(evt, function() {
                validateField(this);
                if (this.type === 'date') checkMainDates();
            });
        });
    });

    // --- MAIN FORM SUBMISSION ---
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        // FIX STATUT : REMPLISSAGE AUTOMATIQUE
        const statutInput = document.getElementById('statutInput');
        const garantieId = document.getElementById('garantieId').value;
        if (!garantieId || garantieId === "") {
            statutInput.value = STATUS_ACTIVE.id;
        }

        let isValid = true;
        form.querySelectorAll('input, select').forEach(el => {
            if (el.type === 'file' && document.getElementById('garantieId').value !== "") return;
            if (!validateField(el)) isValid = false;
        });
        
        if (!checkMainDates()) isValid = false;
        const numG = document.getElementById('numGarantieInput');
        if(numG && !await checkUniqueness(numG)) isValid = false;

        if (form.querySelectorAll('.is-invalid').length > 0 || !isValid) {
            console.warn("Le formulaire contient des erreurs de validation.");
            return;
        }
        
        // LOADING STATE
        const btn = form.querySelector('button[type="submit"]');
        const oldText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement...';
        btn.disabled = true;

        const fd = new FormData(form);
        try {
            const res = await fetch('process.php', { method: 'POST', body: fd });
            const data = await res.json();
            
            if (data.ok) {
                location.href = 'index.php?page=liste-garanties';
            } else if (data.errors) {
                btn.innerHTML = oldText;
                btn.disabled = false;

                for (const [fieldName, errorMsg] of Object.entries(data.errors)) {
                    const input = form.querySelector(`[name="${fieldName}"]`);
                    if (input) {
                        input.classList.add('is-invalid');
                        const fb = getFeedbackElement(input);
                        if (fb) fb.textContent = errorMsg;
                    } else {
                        alert(errorMsg); 
                    }
                }
            } else if (data.message) {
                btn.innerHTML = oldText;
                btn.disabled = false;
                alert(data.message);
            }
        } catch(e) {
            btn.innerHTML = oldText;
            btn.disabled = false;
            console.error("Erreur serveur ou de parsing JSON :", e);
            alert("Une erreur technique s'est produite lors de l'envoi. Veuillez vérifier la console (F12).");
        }
    });

    // --- MODAL LOGIC (AMENDEMENT & AUTH) ---
    setupModalForm('amendementForm', 'amendementModal', 'amendement');
    setupModalForm('authentificationForm', 'authentificationModal', 'authentification');
    setupModalForm('liberationForm', 'liberationModal', 'liberation');

    function setupModalForm(formId, modalId, type) {
        const modalForm = document.getElementById(formId);
        if(!modalForm) return;
        
        const mPdfInput = modalForm.querySelector('input[type="file"]');
        const mPdfPrev = modalForm.querySelector('div[id$="Preview"]');
        if(mPdfInput && mPdfPrev) mPdfInput.addEventListener('change', function() { handlePdfPreview(this, mPdfPrev); });

        const numAmendInput = modalForm.querySelector('input[name="num_amendement"]');
        if(numAmendInput) {
            numAmendInput.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
                validateField(this);
                if(this.value) debouncedCheck(this);
            });
        }

        const dateAmendInput = modalForm.querySelector('input[name="date_amendement"]');
        if(type === 'amendement' && dateAmendInput) {
            dateAmendInput.addEventListener('input', function() {
                validateField(this);
                checkDateConstraints(this, 'amendement_date');
            });
        }

        const newExpInput = modalForm.querySelector('input[name="nouvelle_date_expiration"]');
        if(type === 'amendement' && newExpInput) {
            newExpInput.addEventListener('input', function() {
                validateField(this);
                checkDateConstraints(this, 'new_expiration');
            });
        }

        const dateAuthInput = modalForm.querySelector('input[name="date_authentification"]');
        if(type === 'authentification' && dateAuthInput) {
            dateAuthInput.addEventListener('input', function() {
                validateField(this);
                checkDateConstraints(this, 'authentification_date');
            });
        }
        modalForm.querySelectorAll('.intel-input').forEach(i => {
            if(i.name !== 'num_amendement') {
                i.addEventListener('input', function() {
                     if (this.name === 'num_authentification') this.value = this.value.replace(/[^0-9A-Za-z\-]/g, '');
                     if (this.name === 'nouveau_montant') this.value = this.value.replace(/[^0-9.,]/g, '').replace(',', '.');
                     validateField(this);
                     if (this.value !== "" && !this.classList.contains('is-invalid')) debouncedCheck(this);
                });
            }
            i.addEventListener('blur', function() { checkUniqueness(this); });
        });

        modalForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            let mValid = true;
            const mUniqueInputs = [];
            
            this.querySelectorAll('input, select').forEach(i => {
                if(!validateField(i)) mValid = false;
                if(i.name === 'date_amendement' && type === 'amendement') {
                    if(!checkDateConstraints(i, 'amendement_date')) mValid = false;
                }
                if(i.name === 'nouvelle_date_expiration' && type === 'amendement' && i.offsetParent !== null) {
                    if(!checkDateConstraints(i, 'new_expiration')) mValid = false;
                }
                
              if(i.name === 'date_authentification' && type === 'authentification') {
                    if(!checkDateConstraints(i, 'authentification_date')) mValid = false;
                }
                // ----------------------------------------------

                if(['num_amendement', 'num_authentification'].includes(i.name)) mUniqueInputs.push(i);
            });
            
            for(const inp of mUniqueInputs) {
                if(!inp.classList.contains('is-invalid')) {
                    if(!await checkUniqueness(inp)) mValid = false;
                } else {
                    mValid = false; 
                }
            }

            if(this.querySelectorAll('.is-invalid').length > 0 || !mValid) {
                console.warn("Validation du modal échouée.");
                return; 
            }
            
            // LOADING STATE
            const btn = this.querySelector('button[type="submit"]');
            const oldText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement...';
            btn.disabled = true;

            const fd = new FormData(this);
            fd.append('form_type', type);

            try {
                const res = await fetch('process.php', { method: 'POST', body: fd });
                const data = await res.json();
                
                if (data.ok) {
                    bootstrap.Modal.getOrCreateInstance(document.getElementById(modalId)).hide();
                    location.reload();
                } else if (data.errors) {
                    btn.innerHTML = oldText;
                    btn.disabled = false;

                    for (const [fieldName, errorMsg] of Object.entries(data.errors)) {
                        const input = modalForm.querySelector(`[name="${fieldName}"]`);
                        if (input) {
                            input.classList.add('is-invalid');
                            const fb = getFeedbackElement(input);
                            if (fb) fb.textContent = errorMsg;
                        } else {
                            Swal.fire('Erreur', errorMsg, 'error');
                        }
                    }
                } else if (data.message) {
                    btn.innerHTML = oldText;
                    btn.disabled = false;
                    Swal.fire('Erreur', data.message, 'error');
                }
            } catch(e) {
                btn.innerHTML = oldText;
                btn.disabled = false;
                console.error("Erreur de soumission modal:", e);
                Swal.fire('Erreur technique', 'Le serveur a renvoyé une erreur inattendue. Consultez la console (F12).', 'error');
            }
        });
    }

    const typeAmend = document.getElementById('typeAmendementSelect');
    if(typeAmend) {
        typeAmend.addEventListener('change', function() {
            const code = this.options[this.selectedIndex].dataset.code;
            const mGroup = document.getElementById('nouveauMontantGroup');
            const dGroup = document.getElementById('nouvelleDateGroup');
            const mInput = document.getElementById('nouveauMontantInput');
            const dInput = document.getElementById('nouvelleDateInput');
            
            mGroup.style.display = 'none';
            dGroup.style.display = 'none';
            mInput.removeAttribute('required');
            dInput.removeAttribute('required');
            mInput.classList.remove('is-invalid', 'is-valid');
            dInput.classList.remove('is-invalid', 'is-valid');
            
            if (code === 'MONTANT') {
                mGroup.style.display = 'block';
                mInput.setAttribute('required', 'required');
            } else if (code === 'DATE') {
                dGroup.style.display = 'block';
                dInput.setAttribute('required', 'required');
            } else if (code === 'MIXTE') {
                mGroup.style.display = 'block';
                dGroup.style.display = 'block';
                mInput.setAttribute('required', 'required');
                dInput.setAttribute('required', 'required');
            }
        });
    }

    const typeLibSelect = document.getElementById('typeLiberationSelect');
    if(typeLibSelect) {
        typeLibSelect.addEventListener('change', function() {
            const code = this.options[this.selectedIndex]?.dataset?.code;
            const mGroup = document.getElementById('montantLibereGroup');
            const mInput = document.getElementById('montantLibereInput');
            
            mGroup.style.display = 'none';
            mInput.removeAttribute('required');
            mInput.classList.remove('is-invalid', 'is-valid');
            mInput.removeAttribute('readonly');
            
            if (code === 'PARTIELLE') {
                mGroup.style.display = 'block';
                mInput.setAttribute('required', 'required');
                mInput.value = '';
            } else if (code === 'TOTALE') {
                mGroup.style.display = 'block';
                mInput.setAttribute('required', 'required');
                mInput.setAttribute('readonly', 'readonly');
                mInput.value = currentEditingGarantie ? currentEditingGarantie.resteALiberer : '';
            }
        });
    }

    const libDateInput = document.querySelector('#liberationForm input[name="date_liberation"]');
    if (libDateInput) {
        libDateInput.addEventListener('change', function() {
            const today = getTodayLocal();
            const val = this.value;
            const fb = getFeedbackElement(this);

            this.classList.remove('is-invalid', 'is-valid');

            if (val > today) {
                this.classList.add('is-invalid');
                if (fb) fb.textContent = "La date de libération ne peut pas être dans le futur.";
                this.value = "";
                return;
            }

            if (currentEditingGarantie && val < currentEditingGarantie.dateEmission) {
                this.classList.add('is-invalid');
                const emissionFormatted = new Date(currentEditingGarantie.dateEmission).toLocaleDateString('fr-FR');
                if (fb) fb.textContent = `La date ne peut pas être antérieure à la date d'émission (${emissionFormatted}).`;
                this.value = "";
                return;
            }

            this.classList.add('is-valid');
        });
    }

    function handlePdfPreview(input, container) {
        container.innerHTML = '';
        if (input.files.length === 0) return;
        const file = input.files[0];
        const previewDiv = document.createElement('div');
        if (file.type !== 'application/pdf') {
            previewDiv.className = 'alert alert-danger';
            previewDiv.innerHTML = `<i class="fas fa-exclamation-triangle me-2"></i>Format non accepté (PDF uniquement)`;
        } else {
            previewDiv.className = 'alert alert-success';
            previewDiv.innerHTML = `<i class="fas fa-file-pdf text-danger me-2"></i><strong>${file.name}</strong> (${(file.size / 1024).toFixed(2)} KB)`;
        }
        container.appendChild(previewDiv);
    }
});

// --- EDIT MODE HANDLERS ---
let currentEditingGarantie = null;

function activateEditMode(g) {
    const totalLibere = g.total_libere ? parseFloat(g.total_libere) : 0;
    const montantAct = g.montant_actuel ? parseFloat(g.montant_actuel) : parseFloat(g.montant_garantie);

    currentEditingGarantie = {
        id: g.id,
        numGarantie: g.num_garantie,
        montant: g.montant_garantie,
        deviseCode: g.devise_code || '', 
        dateExpiration: g.date_expiration,
        dateEmission: g.date_emission,
        authCount: g.auth_count,
        montantActuel: montantAct,
        dateExpirationActuelle: g.date_expiration_actuelle || g.date_expiration,
        totalLibere: totalLibere,
        resteALiberer: montantAct - totalLibere
    };

    document.getElementById('cardHeaderTitle').textContent = "Modifier la Garantie n° " + g.num_garantie;
    document.getElementById('formType').value = 'update_garantie';
    document.getElementById('garantieId').value = g.id;

    // FIX STATUT : RÉCUPÉRATION DU STATUT EXISTANT
    document.getElementById('statutInput').value = g.statutID;

    document.getElementById('numGarantieInput').value = g.num_garantie;
    document.getElementById('montantInput').value = g.montant_garantie;
    document.getElementById('dateEInput').value = g.date_emission;
    document.getElementById('dateXInput').value = g.date_expiration;
    document.getElementById('soumissionnaireSelect').value = g.soumissionnaireID;
    document.getElementById('deviseSelect').value = g.deviseID;
    document.getElementById('aoSelect').value = g.appel_offreID;
    document.getElementById('structureSelect').value = g.structureID;

    document.getElementById('pdfFilesInput').removeAttribute('required');
    
    document.getElementById('btnAjouterLiberation').style.display = 'inline-block';

    const agenceSelect = document.getElementById('agenceSelect');
    const banqueSelect = document.getElementById('banqueSelect');
    const agenceOption = agenceSelect.querySelector(`option[value="${g.agenceID}"]`);
    if (agenceOption) {
        banqueSelect.value = agenceOption.getAttribute('data-banque');
        banqueSelect.dispatchEvent(new Event('change'));
        agenceSelect.value = g.agenceID;
    }
    
    document.querySelectorAll('.is-invalid, .is-valid').forEach(el => el.classList.remove('is-invalid', 'is-valid'));

    document.getElementById('btnAjouterAmendement').style.display = 'inline-block';
    const btnAuth = document.getElementById('btnAjouterAuthentification');
    btnAuth.style.display = (g.auth_count > 0) ? 'none' : 'inline-block';

    document.getElementById('submitBtn').innerHTML = '<i class="fas fa-sync me-2"></i>Mettre à jour';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

<?php if ($edit_data): ?> 
document.addEventListener('DOMContentLoaded', () => activateEditMode(<?= json_encode($edit_data) ?>)); 
<?php endif; ?>

document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', function() { activateEditMode(JSON.parse(this.dataset.garantie)); });
});

document.getElementById('btnAjouterAmendement')?.addEventListener('click', function() {
    if (!currentEditingGarantie) return;
    document.getElementById('amendementGarantieId').value = currentEditingGarantie.id;
    document.getElementById('amendementGarantieInfo').textContent = `Garantie n° ${currentEditingGarantie.numGarantie}`;
    document.getElementById('montantActuel').textContent = `${Number(currentEditingGarantie.montantActuel).toLocaleString('fr-FR')} ${currentEditingGarantie.deviseCode}`;
    document.getElementById('dateExpirationActuelle').textContent = new Date(currentEditingGarantie.dateExpirationActuelle).toLocaleDateString('fr-FR');
    
    const form = document.getElementById('amendementForm');
    form.reset();
    form.querySelectorAll('.is-invalid, .is-valid').forEach(e => e.classList.remove('is-invalid','is-valid'));
    document.getElementById('typeAmendementSelect').dispatchEvent(new Event('change'));
    bootstrap.Modal.getOrCreateInstance(document.getElementById('amendementModal')).show();
});

document.getElementById('btnAjouterAuthentification')?.addEventListener('click', function() {
    if (!currentEditingGarantie) return;
    document.getElementById('authentificationGarantieId').value = currentEditingGarantie.id;
    document.getElementById('authentificationGarantieInfo').textContent = `Garantie n° ${currentEditingGarantie.numGarantie}`;
    const form = document.getElementById('authentificationForm');
    form.reset();
    form.querySelectorAll('.is-invalid, .is-valid').forEach(e => e.classList.remove('is-invalid','is-valid'));
    bootstrap.Modal.getOrCreateInstance(document.getElementById('authentificationModal')).show();
});

document.getElementById('btnAjouterLiberation')?.addEventListener('click', function() {
    if (!currentEditingGarantie) return;
    document.getElementById('liberationGarantieId').value = currentEditingGarantie.id;
    document.getElementById('liberationGarantieInfo').textContent = `Garantie n° ${currentEditingGarantie.numGarantie}`;
    document.getElementById('montantMaxLibere').textContent = `${Number(currentEditingGarantie.resteALiberer).toLocaleString('fr-FR')} ${currentEditingGarantie.deviseCode} (Reste à libérer)`;
    const form = document.getElementById('liberationForm');
    form.reset();
    form.querySelectorAll('.is-invalid, .is-valid').forEach(e => e.classList.remove('is-invalid','is-valid'));
    document.getElementById('typeLiberationSelect').dispatchEvent(new Event('change'));
    bootstrap.Modal.getOrCreateInstance(document.getElementById('liberationModal')).show();
});
</script>