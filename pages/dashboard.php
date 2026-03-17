<?php
require_once 'database.php';
require_once 'includes/functions.php';

// Vérification du rôle admin
$isAdmin = (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1);

// 1. Define a single authoritative date
$today = new DateTimeImmutable('today', new DateTimeZone('UTC'));
$todayStr   = $today->format('Y-m-d');
$in30DaysStr = $today->modify('+30 days')->format('Y-m-d');

// --- KPI CARDS DATA (GARANTIES) ---
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

// --- STATISTIQUES ADMIN ---
if ($isAdmin) {
    $totalUsers = (int) $pdo->query("SELECT COUNT(*) FROM utilisateur")->fetchColumn();
    $totalPays = (int) $pdo->query("SELECT COUNT(*) FROM pays")->fetchColumn();
}

// --- STATISTIQUES GRAPHIQUES ---
// 1. Évolution des Garanties
$dataJour = $pdo->query("
    SELECT DATE_FORMAT(date_emission, '%d/%m') as date_val, COUNT(id) as count 
    FROM garantie_soumission 
    WHERE date_emission >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) 
    GROUP BY date_val 
    ORDER BY MIN(date_emission)
")->fetchAll(PDO::FETCH_ASSOC);

$dataMois = $pdo->query("
    SELECT DATE_FORMAT(date_emission, '%m/%Y') as date_val, COUNT(id) as count 
    FROM garantie_soumission 
    WHERE date_emission >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY date_val 
    ORDER BY MIN(date_emission)
")->fetchAll(PDO::FETCH_ASSOC);

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
    ORDER BY count DESC LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// 3. Statistiques Appel d'Offres (AO)
$totalAO = (int) $pdo->query("SELECT COUNT(*) FROM appel_offre")->fetchColumn();
$aoData = $pdo->query("
    SELECT ao.num_app_offre, COUNT(g.id) as count 
    FROM appel_offre ao 
    LEFT JOIN garantie_soumission g ON ao.id = g.appel_offreID 
    GROUP BY ao.id 
    ORDER BY count DESC LIMIT 6
")->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
    /* Custom Dashboard Styling */
    .dash-header-title { color: #2C3E50; font-weight: 800; letter-spacing: -0.5px; }
    
    .kpi-card {
        border-radius: 16px;
        transition: all 0.3s ease;
        border: 1px solid rgba(0,0,0,0.05);
        background: #fff;
    }
    .kpi-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.08) !important;
    }
    .kpi-icon-wrapper {
        width: 56px; height: 56px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
    }
    .kpi-title { font-size: 0.85rem; font-weight: 600; color: #7f8c8d; text-transform: uppercase; letter-spacing: 1px;}
    .kpi-value { font-size: 2rem; font-weight: 800; color: #2c3e50; margin-bottom: 0; line-height: 1;}

    .admin-stat-card {
        border-radius: 10px;
        background: #fff;
        border: none;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        border-left: 4px solid #e8772b;
        transition: transform 0.2s;
    }
    .admin-stat-card:hover { transform: translateX(5px); }

    .chart-card {
        border-radius: 16px;
        border: 1px solid rgba(0,0,0,0.05);
        box-shadow: 0 4px 20px rgba(0,0,0,0.03);
    }
    .chart-header {
        background: transparent;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        padding: 1.25rem 1.5rem;
    }
    .chart-title { font-weight: 700; color: #34495e; font-size: 1.1rem; margin: 0; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="dash-header-title mb-0"><i class="fas fa-chart-pie me-2 text-primary"></i> Synthèse des Garanties</h2>
        <div class="text-muted fw-medium"><i class="far fa-calendar-alt me-1"></i> <?php echo date('d M Y'); ?></div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-xl-3 col-md-6">
            <div class="card kpi-card shadow-sm h-100 p-3">
                <div class="card-body d-flex justify-content-between align-items-center p-2">
                    <div>
                        <p class="kpi-title mb-2">Total Garanties</p>
                        <h2 class="kpi-value"><?php echo number_format($totalGaranties, 0, ',', ' '); ?></h2>
                    </div>
                    <div class="kpi-icon-wrapper" style="background: rgba(52, 152, 219, 0.1); color: #3498db;">
                        <i class="fas fa-folder-open fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card kpi-card shadow-sm h-100 p-3">
                <div class="card-body d-flex justify-content-between align-items-center p-2">
                    <div>
                        <p class="kpi-title mb-2">Garanties Actives</p>
                        <h2 class="kpi-value"><?php echo number_format($activeGaranties, 0, ',', ' '); ?></h2>
                    </div>
                    <div class="kpi-icon-wrapper" style="background: rgba(46, 204, 113, 0.1); color: #2ecc71;">
                        <i class="fas fa-toggle-on fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card kpi-card shadow-sm h-100 p-3">
                <div class="card-body d-flex justify-content-between align-items-center p-2">
                    <div>
                        <p class="kpi-title mb-2">Expire Bientôt (< 30j)</p>
                        <h2 class="kpi-value"><?php echo number_format($expiringSoon, 0, ',', ' '); ?></h2>
                    </div>
                    <div class="kpi-icon-wrapper" style="background: rgba(241, 196, 15, 0.1); color: #f1c40f;">
                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card kpi-card shadow-sm h-100 p-3">
                <div class="card-body d-flex justify-content-between align-items-center p-2">
                    <div>
                        <p class="kpi-title mb-2">Expirées / Libérées</p>
                        <h2 class="kpi-value"><?php echo number_format($expiredGaranties, 0, ',', ' '); ?></h2>
                    </div>
                    <div class="kpi-icon-wrapper" style="background: rgba(231, 76, 60, 0.1); color: #e74c3c;">
                        <i class="fas fa-times-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($isAdmin): ?>
    <div class="mb-5 p-4 rounded-4" style="background: rgba(116, 166, 207, 0.16); border: 1px dashed rgba(72, 106, 112, 0.3);">
        <h5 class="fw-bold mb-3" style="color: #486a70;"><i class="fas fa-server me-2"></i> Vue d'ensemble du Système</h5>
        <div class="row g-3">
            <div class="col-lg-3 col-sm-6">
                <div class="card admin-stat-card p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1" style="font-size: 0.85rem; font-weight: 600;">UTILISATEURS</h6>
                            <h3 class="mb-0 fw-bold" style="color: #2c3e50;"><?php echo $totalUsers; ?></h3>
                        </div>
                        <i class="fas fa-users fa-2x" style="color: #e8772b; opacity: 0.8;"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="card admin-stat-card p-3" style="border-left-color: #3498db;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1" style="font-size: 0.85rem; font-weight: 600;">BANQUES</h6>
                            <h3 class="mb-0 fw-bold" style="color: #2c3e50;"><?php echo $totalBanques; ?></h3>
                        </div>
                        <i class="fas fa-landmark fa-2x" style="color: #3498db; opacity: 0.8;"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="card admin-stat-card p-3" style="border-left-color: #2ecc71;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1" style="font-size: 0.85rem; font-weight: 600;">APPELS D'OFFRE</h6>
                            <h3 class="mb-0 fw-bold" style="color: #2c3e50;"><?php echo $totalAO; ?></h3>
                        </div>
                        <i class="fas fa-file-contract fa-2x" style="color: #2ecc71; opacity: 0.8;"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="card admin-stat-card p-3" style="border-left-color: #9b59b6;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1" style="font-size: 0.85rem; font-weight: 600;">PAYS ENREGISTRÉS</h6>
                            <h3 class="mb-0 fw-bold" style="color: #2c3e50;"><?php echo $totalPays; ?></h3>
                        </div>
                        <i class="fas fa-globe fa-2x" style="color: #9b59b6; opacity: 0.8;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card chart-card h-100">
                <div class="card-header chart-header d-flex justify-content-between align-items-center">
                    <h5 class="chart-title"><i class="fas fa-chart-area me-2 text-primary"></i> Évolution des Émissions</h5>
                    <select id="timeFilter" class="form-select form-select-sm w-auto rounded-pill shadow-sm" style="border-color: #e2e8f0; cursor: pointer;">
                        <option value="jour">30 Derniers Jours</option>
                        <option value="mois" selected>12 Derniers Mois</option>
                        <option value="annee">Historique Global</option>
                    </select>
                </div>
                <div class="card-body p-4" style="height: 380px;">
                    <canvas id="evolutionChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-5">
            <div class="card chart-card h-100">
                <div class="card-header chart-header d-flex justify-content-between align-items-center">
                    <h5 class="chart-title"><i class="fas fa-landmark me-2 text-success"></i> Répartition par Banque</h5>
                    <span class="badge bg-light text-secondary border rounded-pill px-3">Top 5</span>
                </div>
                <div class="card-body p-4 d-flex justify-content-center" style="height: 350px;">
                    <canvas id="banqueChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card chart-card h-100">
                <div class="card-header chart-header d-flex justify-content-between align-items-center">
                    <h5 class="chart-title"><i class="fas fa-file-signature me-2 text-warning"></i> Garanties par Appel d'Offre</h5>
                    <span class="badge bg-light text-secondary border rounded-pill px-3">Top 6</span>
                </div>
                <div class="card-body p-4" style="height: 350px;">
                    <canvas id="aoChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Global Styling for modern look
    Chart.defaults.font.family = "'Inter', 'Segoe UI', Roboto, Helvetica, Arial, sans-serif";
    Chart.defaults.color = '#95a5a6';
    Chart.defaults.scale.grid.color = 'rgba(0,0,0,0.03)';
    
    // Smooth Tooltips
    const tooltipOptions = {
        backgroundColor: 'rgba(44, 62, 80, 0.9)',
        titleFont: { size: 13, weight: 'bold' },
        bodyFont: { size: 14 },
        padding: 12,
        cornerRadius: 8,
        displayColors: true
    };

    // =========================================================
    // 1. CHART: ÉVOLUTION (Smooth Area Line)
    // =========================================================
    const dataJour = <?php echo json_encode($dataJour); ?>;
    const dataMois = <?php echo json_encode($dataMois); ?>;
    const dataAnnee = <?php echo json_encode($dataAnnee); ?>;

    const ctxEvol = document.getElementById('evolutionChart').getContext('2d');
    let evolChartInstance = null;

    // Create a beautiful fade gradient for the area chart
    let gradientEvol = ctxEvol.createLinearGradient(0, 0, 0, 400);
    gradientEvol.addColorStop(0, 'rgba(52, 152, 219, 0.5)'); 
    gradientEvol.addColorStop(1, 'rgba(52, 152, 219, 0.0)');

    function renderEvolutionChart(filterType) {
        let selectedData = [];
        
        if (filterType === 'jour') selectedData = dataJour;
        else if (filterType === 'mois') selectedData = dataMois;
        else if (filterType === 'annee') selectedData = dataAnnee;

        const labels = selectedData.map(item => item.date_val);
        const counts = selectedData.map(item => item.count);

        if (evolChartInstance) evolChartInstance.destroy();

        evolChartInstance = new Chart(ctxEvol, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Garanties Émises',
                    data: counts,
                    borderColor: '#3498db',
                    backgroundColor: gradientEvol,
                    borderWidth: 3,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#3498db',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.4 // Makes lines smooth and curved
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: tooltipOptions
                },
                scales: {
                    y: { 
                        beginAtZero: true, 
                        ticks: { stepSize: 1, precision: 0, padding: 10 },
                        border: { display: false }
                    },
                    x: { 
                        grid: { display: false },
                        border: { display: false }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
            }
        });
    }

    document.getElementById('timeFilter').addEventListener('change', e => renderEvolutionChart(e.target.value));
    renderEvolutionChart('mois');


    // =========================================================
    // 2. CHART: BANQUES (Modern Doughnut)
    // =========================================================
    const banqueDataRaw = <?php echo json_encode($banqueData); ?>;
    const banqueLabels = banqueDataRaw.map(item => item.nom_banque || 'Inconnu');
    const banqueCounts = banqueDataRaw.map(item => item.count);

    const ctxBanque = document.getElementById('banqueChart').getContext('2d');
    
    // Modern palette
    const bgColors = ['#2ecc71', '#3498db', '#9b59b6', '#f1c40f', '#e67e22', '#95a5a6'];

    new Chart(ctxBanque, {
        type: 'doughnut',
        data: {
            labels: banqueLabels,
            datasets: [{
                data: banqueCounts,
                backgroundColor: bgColors,
                borderWidth: 0,
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%', 
            plugins: {
                legend: { 
                    position: 'right',
                    labels: { usePointStyle: true, padding: 20, font: { size: 12 } }
                },
                tooltip: tooltipOptions
            }
        }
    });


    // =========================================================
    // 3. CHART: APPEL D'OFFRE (Rounded Vertical Bars)
    // =========================================================
    const aoDataRaw = <?php echo json_encode($aoData); ?>;
    const aoLabels = aoDataRaw.map(item => item.num_app_offre || 'Sans A.O');
    const aoCounts = aoDataRaw.map(item => item.count);

    const ctxAo = document.getElementById('aoChart').getContext('2d');
    
    // Gradient for bars
    let gradientAo = ctxAo.createLinearGradient(0, 0, 0, 400);
    gradientAo.addColorStop(0, '#f39c12'); 
    gradientAo.addColorStop(1, '#f1c40f'); 

    new Chart(ctxAo, {
        type: 'bar',
        data: {
            labels: aoLabels,
            datasets: [{
                label: 'Volume de Garanties',
                data: aoCounts,
                backgroundColor: gradientAo,
                borderRadius: 8, 
                borderSkipped: false,
                barPercentage: 0.5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: tooltipOptions
            },
            scales: {
                y: { 
                    beginAtZero: true, 
                    ticks: { stepSize: 1, precision: 0, padding: 10 },
                    border: { display: false }
                },
                x: { 
                    grid: { display: false },
                    border: { display: false }
                }
            }
        }
    });

});
</script>