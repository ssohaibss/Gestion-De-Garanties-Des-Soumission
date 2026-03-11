<?php
require_once 'database.php';
require_once 'includes/functions.php';

// 1. Define a single authoritative date
$today = new DateTimeImmutable('today', new DateTimeZone('UTC'));
$todayStr   = $today->format('Y-m-d');
$in30DaysStr = $today->modify('+30 days')->format('Y-m-d');

// --- KPI CARDS DATA ---
$totalGaranties = (int) $pdo->query("SELECT COUNT(*) FROM garantie_soumission")->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM garantie_soumission WHERE date_expiration >= :today AND statutID = 1");
$stmt->execute(['today' => $todayStr]);
$activeGaranties = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM garantie_soumission WHERE date_expiration BETWEEN :today AND :limit AND statutID = 1");
$stmt->execute(['today' => $todayStr, 'limit' => $in30DaysStr]);
$expiringSoon = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM garantie_soumission WHERE date_expiration < :today OR statutID = 2");
$stmt->execute(['today' => $todayStr]);
$expiredGaranties = (int) $stmt->fetchColumn();

// --- NOUVELLES STATISTIQUES ---

// 1. Évolution des Garanties (Filtres)
// Par Jour (30 derniers jours)
$dataJour = $pdo->query("
    SELECT DATE_FORMAT(date_emission, '%d/%m') as date_val, COUNT(id) as count 
    FROM garantie_soumission 
    WHERE date_emission >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) 
    GROUP BY date_val 
    ORDER BY MIN(date_emission)
")->fetchAll(PDO::FETCH_ASSOC);

// Par Mois (12 derniers mois)
$dataMois = $pdo->query("
    SELECT DATE_FORMAT(date_emission, '%m/%Y') as date_val, COUNT(id) as count 
    FROM garantie_soumission 
    WHERE date_emission >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY date_val 
    ORDER BY MIN(date_emission)
")->fetchAll(PDO::FETCH_ASSOC);

// Par Année
$dataAnnee = $pdo->query("
    SELECT YEAR(date_emission) as date_val, COUNT(id) as count 
    FROM garantie_soumission 
    GROUP BY date_val 
    ORDER BY date_val
")->fetchAll(PDO::FETCH_ASSOC);


// 2. Statistiques Banques
$totalBanques = (int) $pdo->query("SELECT COUNT(*) FROM banque")->fetchColumn();
$banqueData = $pdo->query("
    SELECT b.code as nom_banque, COUNT(g.id) as count 
    FROM banque b 
    LEFT JOIN agence a ON b.id = a.banqueID 
    LEFT JOIN garantie_soumission g ON a.id = g.agenceID 
    GROUP BY b.id 
    ORDER BY count DESC LIMIT 8
")->fetchAll(PDO::FETCH_ASSOC);


// 3. Statistiques Appel d'Offres (AO)
$totalAO = (int) $pdo->query("SELECT COUNT(*) FROM appel_offre")->fetchColumn();
$aoData = $pdo->query("
    SELECT ao.num_app_offre, COUNT(g.id) as count 
    FROM appel_offre ao 
    LEFT JOIN garantie_soumission g ON ao.id = g.appel_offreID 
    GROUP BY ao.id 
    ORDER BY count DESC LIMIT 8
")->fetchAll(PDO::FETCH_ASSOC);


// Main table query (Dernières Garanties)
$query = "
SELECT 
    g.id, g.num_garantie, g.montant_garantie, g.date_emission, g.date_expiration,
    s.nom_entreprise, a.nom AS agence_nom, b.nom_banque, d.libelle AS devise,
    st.libelle AS statut, ao.num_app_offre, DATEDIFF(g.date_expiration, :today) AS jours_restants
FROM garantie_soumission g
LEFT JOIN soumissionnaire s ON g.soumissionnaireID = s.id
LEFT JOIN agence a ON g.agenceID = a.id
LEFT JOIN banque b ON a.banqueID = b.id
LEFT JOIN devise d ON g.deviseID = d.id
LEFT JOIN appel_offre ao ON g.appel_offreID = ao.id
LEFT JOIN statut st ON g.statutID = st.id
ORDER BY g.date_emission DESC LIMIT 10
";
$stmt = $pdo->prepare($query); 
$stmt->execute(['today' => $todayStr]);
$garanties = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-header mb-4">
    <h2 class="fw-bold" style="color: #486a70;"><i class="fas fa-chart-line me-2"></i>Tableau de Bord</h2>
</div>

<div class="row mb-4">
    <div class="col-md-3 mb-3 mb-md-0">
        <div class="card text-white shadow-sm border-0 h-100" style="background: linear-gradient(135deg, #6c757d, #495057);">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-uppercase fw-bold mb-1" style="font-size: 0.8rem; letter-spacing: 1px;">Total Garanties</h6>
                        <h2 class="mb-0 fw-bold"><?php echo $totalGaranties; ?></h2>
                    </div>
                    <div class="bg-white bg-opacity-25 rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <i class="fas fa-shield-alt fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3 mb-md-0">
        <div class="card text-white shadow-sm border-0 h-100" style="background: linear-gradient(135deg, #198754, #146c43);">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-uppercase fw-bold mb-1" style="font-size: 0.8rem; letter-spacing: 1px;">Garanties Actives</h6>
                        <h2 class="mb-0 fw-bold"><?php echo $activeGaranties; ?></h2>
                    </div>
                    <div class="bg-white bg-opacity-25 rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3 mb-md-0">
        <div class="card text-dark shadow-sm border-0 h-100" style="background: linear-gradient(135deg, #ffc107, #ffb300);">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-uppercase fw-bold mb-1" style="font-size: 0.8rem; letter-spacing: 1px;">Expire Bientôt</h6>
                        <h2 class="mb-0 fw-bold"><?php echo $expiringSoon; ?></h2>
                    </div>
                    <div class="bg-dark bg-opacity-10 rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3 mb-md-0">
        <div class="card text-white shadow-sm border-0 h-100" style="background: linear-gradient(135deg, #dc3545, #b02a37);">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-uppercase fw-bold mb-1" style="font-size: 0.8rem; letter-spacing: 1px;">Expirées</h6>
                        <h2 class="mb-0 fw-bold"><?php echo $expiredGaranties; ?></h2>
                    </div>
                    <div class="bg-white bg-opacity-25 rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <i class="fas fa-times-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <span class="fw-bold" style="color: #486a70;">
                    <i class="fas fa-chart-area me-2"></i>Évolution des Créations de Garanties
                </span>
                <select id="timeFilter" class="form-select form-select-sm w-auto fw-bold" style="background-color: #f8f9fa; border-color: #dee2e6; color: #486a70;">
                    <option value="jour">Par Jour (30 derniers)</option>
                    <option value="mois" selected>Par Mois (12 derniers)</option>
                    <option value="annee">Par Année (Historique)</option>
                </select>
            </div>
            <div class="card-body" style="height: 320px;">
                <canvas id="evolutionChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4 mb-md-0">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <span class="fw-bold" style="color: #486a70;">
                    <i class="fas fa-building me-2"></i>Utilisation par Banque
                </span>
                <span class="badge" style="background-color: #486a70;">Total: <?php echo $totalBanques; ?> Banques</span>
            </div>
            <div class="card-body" style="height: 300px;">
                <canvas id="banqueChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <span class="fw-bold" style="color: #486a70;">
                    <i class="fas fa-file-contract me-2"></i>Garanties par Appel d'Offre (Top 8)
                </span>
                <span class="badge" style="background-color: #486a70;">Total: <?php echo $totalAO; ?> A.O.</span>
            </div>
            <div class="card-body" style="height: 300px;">
                <canvas id="aoChart"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0 fw-bold" style="color: #486a70;"><i class="fas fa-list me-2"></i>Dernières Garanties Ajoutées</h5>
                <a href="index.php?page=liste-garanties" class="btn btn-sm text-white shadow-sm" style="background-color: #486a70;">Voir tout</a>
            </div>
            <div class="card-body p-0"> 
                <?php if (!empty($garanties)): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">N° Garantie</th>
                                <th>Appel d'Offre</th>
                                <th>Soumissionnaire</th>
                                <th>Banque/Agence</th>
                                <th>Montant</th>
                                <th>Statut</th>
                                <th class="text-center pe-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($garanties as $row): ?>
                            <tr>
                                <td class="ps-3"><strong style="color: #486a70;"><?php echo htmlspecialchars($row['num_garantie']); ?></strong></td>
                                <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($row['num_app_offre']); ?></span></td>
                                <td class="fw-medium"><?php echo htmlspecialchars($row['nom_entreprise'] ?? 'N/A'); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['nom_banque'] ?? 'N/A'); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($row['agence_nom'] ?? 'N/A'); ?></small>
                                </td>
                                <td class="fw-bold"><?php echo number_format($row['montant_garantie'], 2, ',', ' '); ?> <span class="text-muted fw-normal"><?php echo htmlspecialchars($row['devise'] ?? ''); ?></span></td>
                                <td>
                                    <?php 
                                    $statutL = htmlspecialchars($row['statut'] ?? '');
                                    $bgClass = 'bg-secondary';
                                    if(strtolower($statutL) === 'active') $bgClass = 'bg-success';
                                    if(strtolower($statutL) === 'expirée') $bgClass = 'bg-danger';
                                    if(strtolower($statutL) === 'libérée') $bgClass = 'bg-info';
                                    echo "<span class='badge $bgClass px-2 py-1 shadow-sm'>$statutL</span>";
                                    ?>
                                </td>
                                <td class="text-center pe-3">
                                    <div class="btn-group btn-group-sm shadow-sm" role="group">
                                        <a href="index.php?page=details-garantie&id=<?php echo $row['id']; ?>" class="btn text-white" style="background-color: #486a70;" title="Détails"><i class="fas fa-eye"></i></a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="p-5 text-center text-muted">
                    <i class="fas fa-folder-open fa-3x mb-3 opacity-25"></i>
                    <h5>Aucune garantie trouvée</h5>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    Chart.defaults.font.family = "'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif";
    Chart.defaults.color = '#6c757d';

    // =========================================================
    // 1. CHART: ÉVOLUTION AVEC FILTRE
    // =========================================================
    const dataJour = <?php echo json_encode($dataJour); ?>;
    const dataMois = <?php echo json_encode($dataMois); ?>;
    const dataAnnee = <?php echo json_encode($dataAnnee); ?>;

    const ctxEvol = document.getElementById('evolutionChart').getContext('2d');
    let evolChartInstance = null; // Stocker l'instance du graphique pour la détruire et la recréer

    let gradientFill = ctxEvol.createLinearGradient(0, 0, 0, 300);
    gradientFill.addColorStop(0, 'rgba(72, 106, 112, 0.4)');
    gradientFill.addColorStop(1, 'rgba(72, 106, 112, 0.0)');

    function renderEvolutionChart(filterType) {
        let selectedData = [];
        let timeLabel = '';

        // Choix des données selon le filtre
        if (filterType === 'jour') {
            selectedData = dataJour;
            timeLabel = 'Garanties par Jour';
        } else if (filterType === 'mois') {
            selectedData = dataMois;
            timeLabel = 'Garanties par Mois';
        } else if (filterType === 'annee') {
            selectedData = dataAnnee;
            timeLabel = 'Garanties par Année';
        }

        const labels = selectedData.map(item => item.date_val);
        const counts = selectedData.map(item => item.count);

        // Détruire l'ancien graphique s'il existe
        if (evolChartInstance) {
            evolChartInstance.destroy();
        }

        evolChartInstance = new Chart(ctxEvol, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: timeLabel,
                    data: counts,
                    borderColor: '#486a70',
                    backgroundColor: gradientFill,
                    borderWidth: 3,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#486a70',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.3 // Courbe fluide
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: { display: true, text: `Création de Garanties (${filterType})`, font: { size: 14 } },
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 }, grid: { borderDash: [5, 5] } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // Écouteur pour le menu déroulant (Filtre)
    document.getElementById('timeFilter').addEventListener('change', function(e) {
        renderEvolutionChart(e.target.value);
    });

    // Afficher le graphique 'Mois' par défaut au chargement
    renderEvolutionChart('mois');


    // =========================================================
    // 2. CHART: UTILISATION PAR BANQUE (Barres Horizontales)
    // =========================================================
    const banqueDataRaw = <?php echo json_encode($banqueData); ?>;
    const banqueLabels = banqueDataRaw.map(item => item.nom_banque || 'Inconnu');
    const banqueCounts = banqueDataRaw.map(item => item.count);

    const ctxBanque = document.getElementById('banqueChart').getContext('2d');
    new Chart(ctxBanque, {
        type: 'bar', // Type bar + indexAxis = 'y' crée des barres horizontales
        data: {
            labels: banqueLabels,
            datasets: [{
                label: 'Garanties',
                data: banqueCounts,
                backgroundColor: '#198754', // Vert Sonatrach
                borderRadius: 4,
                barPercentage: 0.7
            }]
        },
        options: {
            indexAxis: 'y', // Convertit le Bar chart en Horizontal Bar Chart
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 } },
                y: { grid: { display: false } }
            }
        }
    });


    // =========================================================
    // 3. CHART: GARANTIES PAR APPEL D'OFFRE (Barres Verticales)
    // =========================================================
    const aoDataRaw = <?php echo json_encode($aoData); ?>;
    const aoLabels = aoDataRaw.map(item => item.num_app_offre || 'Sans A.O');
    const aoCounts = aoDataRaw.map(item => item.count);

    const ctxAo = document.getElementById('aoChart').getContext('2d');
    new Chart(ctxAo, {
        type: 'bar',
        data: {
            labels: aoLabels,
            datasets: [{
                label: 'Nombre de Garanties',
                data: aoCounts,
                backgroundColor: '#ffc107', // Jaune Sonatrach
                borderRadius: 4,
                barPercentage: 0.6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 } },
                x: { grid: { display: false } }
            }
        }
    });

});
</script>