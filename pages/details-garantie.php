<?php
require_once dirname(__DIR__) . '/database.php';
$pdo = getDBConnection();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Requête principale
$query = "SELECT 
    g.*,
    s.nom_entreprise,
    a.nom as agence_nom,
    b.nom_banque,
    d.code as devise_code,
    ao.num_app_offre,
    DATEDIFF(g.date_expiration, CURDATE()) as jours_restants_original
FROM garantie_soumission g
LEFT JOIN soumissionnaire s ON g.soumissionnaireID = s.id
LEFT JOIN agence a ON g.agenceID = a.id
LEFT JOIN banque b ON a.banqueID = b.id
LEFT JOIN devise d ON g.deviseID = d.id
LEFT JOIN appel_offre ao ON g.appel_offreID = ao.id
WHERE g.id = ?";

$stmt = $pdo->prepare($query);
$stmt->execute([$id]);
$garantie = $stmt->fetch();

if (!$garantie) {
    die("<div class='alert alert-danger m-3'>Garantie introuvable.</div>");
}

// Authentifications
$queryAuthentifications = "SELECT au.* FROM authentification au WHERE au.garantie_soumissionID = ? ORDER BY au.date_authentification DESC";
$stmtAuthentifications = $pdo->prepare($queryAuthentifications);
$stmtAuthentifications->execute([$id]);
$authentifications = $stmtAuthentifications->fetchAll(PDO::FETCH_ASSOC);

// Amendements (Trié par date décroissante)
$queryAmendements = "SELECT 
    a.*,
    ta.code as type_code,
    ta.libelle as type_libelle,
    u.username as utilisateur_nom
FROM amendement a
LEFT JOIN type_amendement ta ON a.type_amendementID = ta.id
LEFT JOIN utilisateur u ON a.utilisateurID = u.id
WHERE a.garantie_soumissionID = ?
ORDER BY a.date_amendement DESC, a.id DESC";

$stmtAmendements = $pdo->prepare($queryAmendements);
$stmtAmendements->execute([$id]);
$amendements = $stmtAmendements->fetchAll(PDO::FETCH_ASSOC);

// Documents
$queryDocs = "SELECT * FROM document WHERE garantie_soumissionID = ? AND type_documentID = 1 ORDER BY id DESC";
$stmtDocs = $pdo->prepare($queryDocs);
$stmtDocs->execute([$id]);
$garantie_docs = $stmtDocs->fetchAll(PDO::FETCH_ASSOC);

$queryAuthDocs = "SELECT d.*, au.num_authentification FROM document d 
                  LEFT JOIN document_authentification dau ON d.id = dau.documentID 
                  LEFT JOIN authentification au ON dau.authentificationID = au.id 
                  WHERE d.type_documentID = 3 AND au.garantie_soumissionID = ? 
                  ORDER BY au.date_authentification DESC";
$stmtAuthDocs = $pdo->prepare($queryAuthDocs);
$stmtAuthDocs->execute([$id]);
$authentification_docs = $stmtAuthDocs->fetchAll(PDO::FETCH_ASSOC);

$queryAmendDocs = "SELECT d.*, a.num_amendement FROM document d 
                   LEFT JOIN document_amendement da ON d.id = da.documentID 
                   LEFT JOIN amendement a ON da.amendementID = a.id 
                   WHERE d.type_documentID = 2 AND a.garantie_soumissionID = ? 
                   ORDER BY a.date_amendement DESC";
$stmtAmendDocs = $pdo->prepare($queryAmendDocs);
$stmtAmendDocs->execute([$id]);
$amendement_docs = $stmtAmendDocs->fetchAll(PDO::FETCH_ASSOC);
// --- RÉCUPÉRATION DES LIBÉRATIONS ---
$queryLibs = "SELECT l.*, tl.libelle as type_libelle, tl.code as type_code, u.username as user_nom 
              FROM liberation l 
              LEFT JOIN type_liberation tl ON l.type_liberationID = tl.id 
              LEFT JOIN utilisateur u ON l.utilisateurID = u.id
              WHERE l.garantie_soumissionID = ? 
              ORDER BY l.date_liberation DESC";
$stmtLibs = $pdo->prepare($queryLibs);
$stmtLibs->execute([$id]);
$liberations = $stmtLibs->fetchAll(PDO::FETCH_ASSOC);

// Documents de libération (Type 3)
$queryLibDocs = "SELECT d.*, l.num_liberation FROM document d 
                 LEFT JOIN document_liberation dl ON d.id = dl.documentID 
                 LEFT JOIN liberation l ON dl.liberationID = l.id 
                 WHERE d.type_documentID = 3 AND l.garantie_soumissionID = ?";
$stmtLibDocs = $pdo->prepare($queryLibDocs);
$stmtLibDocs->execute([$id]);
$liberation_docs = $stmtLibDocs->fetchAll(PDO::FETCH_ASSOC);

// --- CALCULS ---
$total_montant_amendments = 0;
$latest_expiration_date = $garantie['date_expiration'];

foreach ($amendements as $amend) {
    if (($amend['type_code'] === 'MONTANT' || $amend['type_code'] === 'MIXTE') && $amend['nouveau_montant']) {
        $total_montant_amendments += $amend['nouveau_montant'];
    }
}

foreach ($amendements as $amend) {
    if (($amend['type_code'] === 'DATE' || $amend['type_code'] === 'MIXTE') && $amend['nouvelle_date_expiration']) {
        $latest_expiration_date = $amend['nouvelle_date_expiration'];
        break; 
    }
}

$montant_total = $garantie['montant_garantie'] + $total_montant_amendments;

// Calcul Montant Total Garantie (Base + Amendements)
$montant_total_garantie = $garantie['montant_garantie'] + $total_montant_amendments;

// Calcul Total Libéré
$total_libere = 0;
$est_totalement_liberee = false;
foreach ($liberations as $lib) {
    $total_libere += $lib['montant_libere'];
    if ($lib['type_code'] === 'TOTALE') {
        $est_totalement_liberee = true;
    }
}

// Reste à libérer
$reste_a_liberer = $montant_total_garantie - $total_libere;
if ($reste_a_liberer < 0) $reste_a_liberer = 0; // Sécurité
?>

<div class="content-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <h2><i class="fas fa-shield-alt me-2"></i>Détails Garantie : <?php echo htmlspecialchars($garantie['num_garantie']); ?></h2>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm mb-4 border-0">
            <div class="card-header text-white" style="background-color: #486a70;">
                <i class="fas fa-file-contract me-2"></i>Informations de la Garantie
            </div>
            <div class="card-body">
                <table class="table table-borderless fs-5">
                    <tr>
                        <th class="text-muted" style="width: 250px;">Numéro :</th>
                        <td class="fw-bold"><?php echo htmlspecialchars($garantie['num_garantie']); ?></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Montant :</th>
                        <td>
                            <div class="small">
                                <div class="text-success fw-bold">
                                    Original : <strong><?php echo number_format($garantie['montant_garantie'], 2, ',', ' '); ?> <?php echo $garantie['devise_code']; ?></strong>
                                </div>
                                <?php if ($total_montant_amendments > 0): ?>
                                    <div class="text-info">
                                        Amendements : <strong>+<?php echo number_format($total_montant_amendments, 2, ',', ' '); ?> <?php echo $garantie['devise_code']; ?></strong>
                                    </div>
                                    <div class="border-top mt-2 pt-2 text-success fw-bold">
                                        Total : <strong><?php echo number_format($montant_total, 2, ',', ' '); ?> <?php echo $garantie['devise_code']; ?></strong>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th class="text-muted">Soumissionnaire :</th>
                        <td><i class="fas fa-building me-2 text-muted"></i><?php echo htmlspecialchars($garantie['nom_entreprise'] ?? 'N/A'); ?></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Liée à l'Appel d'Offre :</th>
                        <td><span class="badge bg-light text-dark border p-2"># <?php echo htmlspecialchars($garantie['num_app_offre'] ?? 'Aucun'); ?></span></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Dates :</th>
                        <td>
                            <div class="small">
                                Émission : <strong><?php echo date('d/m/Y', strtotime($garantie['date_emission'])); ?></strong><br>
                                Expiration (Original) : <strong class="text-danger"><?php echo date('d/m/Y', strtotime($garantie['date_expiration'])); ?></strong>
                                <?php if ($latest_expiration_date !== $garantie['date_expiration']): ?>
                                    <br>Expiration (Actuelle) : <strong class="text-success"><?php echo date('d/m/Y', strtotime($latest_expiration_date)); ?></strong>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th class="text-muted">Document PDF :</th>
                        <td>
                            <?php if (count($garantie_docs) > 0): ?>
                                <?php $doc = $garantie_docs[0]; ?>
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-file-pdf text-danger"></i>
                                    <span><?php echo htmlspecialchars($doc['nom_document']); ?></span>
                                    <?php if (file_exists($doc['chemin_access'])): ?>
                                        <a href="<?php echo htmlspecialchars($doc['chemin_access']); ?>" class="btn btn-sm btn-outline-primary" download>
                                            <i class="fas fa-download me-1"></i>Télécharger
                                        </a>
                                    <?php else: ?>
                                        <span class="text-danger small">Fichier manquant</span>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <span class="text-muted">Aucun document</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="card-footer bg-light d-flex gap-2">
                <a href="index.php?page=garantie&edit=<?php echo $garantie['id']; ?>" class="btn btn-primary ajouter">
                    <i class="fas fa-pencil-alt me-2"></i>Modifier
                </a>
                <button class="btn btn-danger" onclick="confirmDelete(<?php echo $garantie['id']; ?>, '<?php echo addslashes($garantie['num_garantie']); ?>')">
                    <i class="fas fa-trash me-2"></i>Supprimer
                </button>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white fw-bold">
                <i class="fas fa-university me-2"></i>Détails Bancaires
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-6">
                        <label class="text-muted small uppercase">Banque</label>
                        <p class="fw-bold mb-0"><?php echo htmlspecialchars($garantie['nom_banque'] ?? 'Non précisée'); ?></p>
                    </div>
                    <div class="col-sm-6 border-start">
                        <label class="text-muted small uppercase">Agence</label>
                        <p class="fw-bold mb-0"><?php echo htmlspecialchars($garantie['agence_nom'] ?? 'Non précisée'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header text-white" style="background-color: #486a70;">
                <i class="fas fa-certificate me-2"></i>Authentification
            </div>
            <div class="card-body p-0">
                <?php if (count($authentifications) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Date d'Authentification</th>
                                <th class="text-center">Document</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($authentifications as $auth): ?>
                            <tr>
                                <td class="ps-3 fw-bold"><?php echo date('d/m/Y', strtotime($auth['date_authentification'])); ?></td>
                                <td class="text-center">
                                    <?php 
                                    $auth_doc = null;
                                    foreach ($authentification_docs as $doc) {
                                        if ($doc['num_authentification'] == $auth['num_authentification']) {
                                            $auth_doc = $doc;
                                            break;
                                        }
                                    }
                                    if ($auth_doc && file_exists($auth_doc['chemin_access'])): ?>
                                        <a href="<?php echo htmlspecialchars($auth_doc['chemin_access']); ?>" class="btn btn-sm btn-outline-primary" download title="<?php echo htmlspecialchars($auth_doc['nom_document']); ?>">
                                            <i class="fas fa-download me-1"></i> Télécharger
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small text-danger"><i class="fas fa-exclamation-circle"></i> Introuvable</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-danger delete-authentification" 
                                            data-id="<?php echo $auth['id']; ?>" 
                                            data-num="<?php echo htmlspecialchars($auth['num_authentification']); ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-certificate fa-2x text-muted mb-2"></i>
                    <p class="text-muted mb-0">Aucune authentification enregistrée.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="card shadow-sm border-0 mb-4">
    <div class="card-header text-white d-flex justify-content-between align-items-center" style="background-color: #27ae60;">
        <span><i class="fas fa-unlock-alt me-2"></i>Suivi des Libérations</span>
        <?php if($est_totalement_liberee): ?>
            <span class="badge bg-white text-success fw-bold">LIBÉRATION TOTALE</span>
        <?php elseif($total_libere > 0): ?>
            <span class="badge bg-white text-success fw-bold">LIBÉRATION PARTIELLE</span>
        <?php endif; ?>
    </div>
    <div class="card-body">
        
        <?php if ($total_libere > 0): ?>
            <div class="row mb-4 align-items-center">
                <div class="col-md-6 border-end">
                    <p class="text-muted mb-1 small uppercase">Montant Total Libéré</p>
                    <h3 class="text-success fw-bold mb-0">
                        <?php echo number_format($total_libere, 2, ',', ' '); ?> 
                        <small class="fs-6 text-muted"><?php echo $garantie['devise_code']; ?></small>
                    </h3>
                </div>
                <div class="col-md-6 ps-4">
                    <p class="text-muted mb-1 small uppercase">Reste à Libérer</p>
                    <h3 class="text-danger fw-bold mb-0">
                        <?php echo number_format($reste_a_liberer, 2, ',', ' '); ?>
                        <small class="fs-6 text-muted"><?php echo $garantie['devise_code']; ?></small>
                    </h3>
                </div>
                <div class="col-12 mt-3">
                    <?php 
                        $percent = ($montant_total_garantie > 0) ? ($total_libere / $montant_total_garantie) * 100 : 0;
                        if($percent > 100) $percent = 100;
                    ?>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $percent; ?>%"></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (count($liberations) > 0): ?>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>N° Lib</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th class="text-end">Montant</th>
                            <th class="text-center">Doc</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($liberations as $lib): ?>
                        <tr>
                            <td class="fw-bold text-muted">#<?php echo $lib['num_liberation']; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($lib['date_liberation'])); ?></td>
                            <td>
                                <span class="badge <?php echo ($lib['type_code'] === 'TOTALE') ? 'bg-success' : 'bg-warning text-dark'; ?>">
                                    <?php echo htmlspecialchars($lib['type_libelle']); ?>
                                </span>
                            </td>
                            <td class="text-end fw-bold text-success">
                                <?php echo number_format($lib['montant_libere'], 2, ',', ' '); ?>
                            </td>
                            <td class="text-center">
                                <?php 
                                $l_doc = null;
                                foreach ($liberation_docs as $d) {
                                    if ($d['num_liberation'] == $lib['num_liberation']) { $l_doc = $d; break; }
                                }
                                if ($l_doc && file_exists($l_doc['chemin_access'])): ?>
                                    <a href="<?php echo htmlspecialchars($l_doc['chemin_access']); ?>" class="text-danger" download>
                                        <i class="fas fa-file-pdf fa-lg"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-danger delete-liberation" 
                                        data-id="<?php echo $lib['id']; ?>"
                                        title="Supprimer cette libération">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center text-muted py-3">
                <small>Aucune libération enregistrée.</small>
            </div>
        <?php endif; ?>
    </div>
</div>
        <div class="card shadow-sm border-0">
            <div class="card-header text-white d-flex justify-content-between align-items-center" style="background-color: #e67e22;">
                <span><i class="fas fa-file-signature me-2"></i>Historique des Amendements</span>
                <span class="badge bg-white text-dark"><?php echo count($amendements); ?> amendement(s)</span>
            </div>
            <div class="card-body p-0">
                <?php if (count($amendements) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">N° Amendement</th>
                                <th>Date</th>
                                <th>Type</th>
                                <th class="text-end">Montant</th>
                                <th>Date d'Expiration</th>
                                <th class="text-center">Document</th>
                                <th>Par</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($amendements as $amend): ?>
                            <tr>
                                <td class="ps-3 fw-bold"><?php echo $amend['num_amendement']; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($amend['date_amendement'])); ?></td>
                                <td>
                                    <?php 
                                    $badgeColor = 'bg-secondary';
                                    if ($amend['type_code'] === 'MONTANT') $badgeColor = 'bg-success';
                                    elseif ($amend['type_code'] === 'DATE') $badgeColor = 'bg-info';
                                    elseif ($amend['type_code'] === 'MIXTE') $badgeColor = 'bg-warning text-dark';
                                    ?>
                                    <span class="badge <?php echo $badgeColor; ?>"><?php echo htmlspecialchars($amend['type_libelle']); ?></span>
                                </td>
                                <td class="text-end">
                                    <?php if ($amend['type_code'] === 'MONTANT' || $amend['type_code'] === 'MIXTE'): ?>
                                        <strong class="text-info"><?php echo number_format($amend['nouveau_montant'], 2, ',', ' '); ?></strong>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($amend['type_code'] === 'DATE' || $amend['type_code'] === 'MIXTE'): ?>
                                        <strong class="text-success"><?php echo date('d/m/Y', strtotime($amend['nouvelle_date_expiration'])); ?></strong>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php 
                                    $amd_doc = null;
                                    foreach ($amendement_docs as $doc) {
                                        if ($doc['num_amendement'] == $amend['num_amendement']) {
                                            $amd_doc = $doc;
                                            break;
                                        }
                                    }
                                    if ($amd_doc && file_exists($amd_doc['chemin_access'])): ?>
                                        <a href="<?php echo htmlspecialchars($amd_doc['chemin_access']); ?>" class="btn btn-sm btn-outline-danger" download title="<?php echo htmlspecialchars($amd_doc['nom_document']); ?>">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><small class="text-muted"><?php echo htmlspecialchars($amend['utilisateur_nom'] ?? 'N/A'); ?></small></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-danger delete-amendement" 
                                            data-id="<?php echo $amend['id']; ?>" 
                                            data-num="<?php echo $amend['num_amendement']; ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-file-signature fa-2x text-muted mb-2"></i>
                    <p class="text-muted mb-0">Aucun amendement enregistré pour cette garantie.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white fw-bold text-center">Validité</div>
            <div class="card-body text-center py-4">
                <?php 
                    $date_actuelle = new DateTime();
                    $date_expiration_finale = new DateTime($latest_expiration_date);
                    
                    $date_actuelle->setTime(0, 0, 0);
                    $date_expiration_finale->setTime(0, 0, 0);

                    $interval = $date_actuelle->diff($date_expiration_finale);
                    $jours = (int)$interval->format('%r%a');

                    $colorClass = ($jours > 15) ? 'bg-success' : (($jours > 0) ? 'bg-warning text-dark' : 'bg-danger');
                ?>
                <div class="badge p-3 rounded-pill mb-3 <?php echo $colorClass; ?>" style="font-size: 1rem;">
                    <i class="fas fa-clock me-2"></i>
                    <?php echo ($jours >= 0) ? $jours . " jours restants" : "Expirée"; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
async function confirmDelete(id, num) {
    const result = await Swal.fire({
        title: 'Supprimer la garantie ?',
        text: `Numéro : ${num}`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Oui, supprimer',
        cancelButtonText: 'Annuler'
    });

if (result.isConfirmed) {
        const fd = new FormData();
        fd.append('form_type', 'delete_garantie');
        fd.append('id', id);

        try {
            const res = await fetch('process.php', { method: 'POST', body: fd });
            const data = await res.json();
            if (data.ok) {
                await Swal.fire({ title: 'Supprimée !', icon: 'success', timer: 1500, showConfirmButton: false, timerProgressBar: true });
                window.location.href = 'index.php?page=liste-garanties';
            } else {
                Swal.fire('Erreur', data.message, 'error');
            }
        } catch (err) {
            Swal.fire('Erreur', 'Lien rompu', 'error');
        }
    }
}

document.querySelectorAll('.delete-authentification').forEach(btn => {
    btn.addEventListener('click', async function() {
        const id = this.dataset.id;
        const num = this.dataset.num;

        const result = await Swal.fire({
            title: 'Supprimer l\'authentification ?',
            text: `Suppression définitive.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#486a70',
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler'
        });

        if (result.isConfirmed) {
            const fd = new FormData();
            fd.append('form_type', 'delete_authentification');
            fd.append('id', id);

            try {
                const res = await fetch('process.php', { method: 'POST', body: fd });
                const data = await res.json();
                if (data.ok) {
                    await Swal.fire({ 
                        title: 'Supprimée !', 
                        icon: 'success', 
                        timer: 1500, 
                        showConfirmButton: false, 
                        timerProgressBar: true 
                    });
                    location.reload();
                } else {
                    Swal.fire('Erreur', data.message || 'La suppression a échoué', 'error');
                }
            } catch (err) {
                Swal.fire('Erreur', 'Lien rompu', 'error');
            }
        }
    });
});

document.querySelectorAll('.delete-amendement').forEach(btn => {
    btn.addEventListener('click', async function() {
        const id = this.dataset.id;
        const num = this.dataset.num;

        const result = await Swal.fire({
            title: 'Supprimer l\'amendement ?',
            text: `Amendement n° ${num}`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#486a70',
            confirmButtonText: 'Oui, supprimer',
            cancelButtonText: 'Annuler'
        });

        if (result.isConfirmed) {
            const fd = new FormData();
            fd.append('form_type', 'delete_amendement');
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
                } else {
                    Swal.fire('Erreur', data.message || 'La suppression a échoué', 'error');
                }
            } catch (err) {
                Swal.fire('Erreur', 'Lien rompu', 'error');
            }
        }
    });
});

document.querySelectorAll('.delete-liberation').forEach(btn => {
    btn.addEventListener('click', async function() {
        if(!confirm("Supprimer cette libération ?")) return;
        const id = this.dataset.id;
        const fd = new FormData();
        fd.append('form_type', 'delete_liberation');
        fd.append('id', id);
        
        try {
            const res = await fetch('process.php', {method: 'POST', body: fd});
            const data = await res.json();
            if(data.ok) location.reload();
            else alert(data.message || "Erreur");
        } catch(e) { alert("Erreur serveur"); }
    });
});
</script>