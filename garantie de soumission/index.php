<?php 
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$userName = $_SESSION['name'] ?? 'Admin User';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .bg-deep-sea-blue {
            background-color: #9eaeb5 !important;
        }
        .bg-deep-sea-blue:hover {
            background-color: #486a70 !important;
        }
        .ajouter {
            background: linear-gradient(180deg, #486a70 85%, #2f4858 100%);
            color: white;
        }
        .ajouter:hover {
            background: linear-gradient(135deg, #e8772bff 30%, #57606f 100%);
        }
        .edit { background: #6cb0bcff; }
        .edit:hover { background: #4ca0abff; }
        .eye { background:#77b7d3; }
        .eye:hover { background:#5aa7b1; }

        body {
            overflow-x: hidden;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .voir:hover {
            background: linear-gradient(135deg, #e8772bff 30%, #57606f 100%);
        }
        
        .top-bar {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem 0;
        }
        
        .top-bar img {
            height: 50px;
            width: auto;
        }
        
        #sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #486a70 85%, #2f4858 100%);
        }
        
        .profile-section {
            text-align: center;
            padding: 2rem 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .profile-img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 3px solid #fff;
            margin-bottom: 0.75rem;
            object-fit: cover;
        }
        
        .profile-name {
            color: #fff;
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0;
        }
        
        .profile-role {
            color: #bdc3c7;
            font-size: 0.875rem;
        }
        
        .nav-link {
            color: #cfcfcfff !important;
            padding: 0.75rem 1.25rem;
            margin: 0.25rem 0.5rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .nav-link:hover {
            background: linear-gradient(135deg, #d35400 0%, #57606f 100%);
            transform: translateX(5px);
            border-color: rgba(211, 84, 0, 0.3);
        }
        
        .nav-link.active {
            background: linear-gradient(135deg, #d35400 0%, #57606f 100%);
            color: #fff !important;
        }
        
        .nav-link i {
            width: 20px;
            text-align: center;
        }
        
        .dropdown-toggle::after {
            margin-left: auto;
        }
        
        .dropdown-menu {
            background: #34495e;
            border: none;
            margin-left: 1rem;
            width: calc(100% - 2rem);
        }
        
        .dropdown-item {
            color: #ecf0f1;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            margin: 0.25rem 0;
        }
        
        .dropdown-item:hover {
            background: rgba(255,255,255,0.1);
            color: #fff;
        }
        
        #content {
            padding: 2rem;
            min-height: 100vh;
            background: #faf0de;
        }
        
        .content-header {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #dee2e6;
        }
        
        .card {
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-radius: 10px;
        }
        
        .card-header {
            background: linear-gradient(180deg, #486a70 85%, #2f4858 100%);
            color: white;
            font-weight: 600;
            border-radius: 10px 10px 0 0 !important;
        }
    </style>
</head>
<body>
    
    <div class="container-fluid">
        <div class="row">
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block sidebar">
                <div class ="pt-2"> <img src="images/logo-sona.svg" alt="Logo" width="60px" > </div>
                <div class="profile-section">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($userName); ?>&background=6c757d&color=fff&size=80" alt="Profile" class="profile-img">
                    <p class="profile-name"><?php echo htmlspecialchars($userName); ?></p>
                    <p class="profile-role">Administrateur</p>
                </div>
                
                <ul class="nav flex-column mt-3">
                    <li class="nav-item">
                        <a class="nav-link <?php echo (!isset($_GET['page']) || $_GET['page'] == 'dashboard') ? 'active' : ''; ?>" href="index.php?page=dashboard">
                            <i class="fas fa-home"></i>
                            <span>Tableau de bord</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" data-bs-toggle="collapse" data-bs-target="#adminSubmenu">
                            <i class="fas fa-cog"></i>
                            <span>Administration</span>
                        </a>
                        <div class="collapse <?php echo in_array($_GET['page'] ?? '', ['fournisseur', 'pays', 'devise', 'structure', 'user', 'appelle-offre', 'banque', 'agence']) ? 'show' : ''; ?>" id="adminSubmenu">
                            <ul class="list-unstyled ps-3">
                                <li><a class="dropdown-item" href="index.php?page=fournisseur"><i class="fas fa-truck"></i> Fournisseur</a></li>
                                <li><a class="dropdown-item" href="index.php?page=pays"><i class="fas fa-flag"></i> Pays</a></li>
                                <li><a class="dropdown-item" href="index.php?page=devise"><i class="fas fa-dollar-sign"></i> Devise</a></li>
                                <li><a class="dropdown-item" href="index.php?page=structure"><i class="fas fa-building"></i> Structure</a></li>
                                <li><a class="dropdown-item" href="index.php?page=user"><i class="fas fa-user"></i> Utilisateur</a></li>
                                <li><a class="dropdown-item" href="index.php?page=appelle-offre"><i class="fas fa-file-invoice"></i> Appel d'offre</a></li>
                                <li><a class="dropdown-item" href="index.php?page=banque"><i class="fas fa-university"></i> Banque</a></li>
                                <li><a class="dropdown-item" href="index.php?page=agence"><i class="fas fa-map-marked-alt"></i> Agence</a></li>
                            </ul>
                        </div>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link <?php echo (isset($_GET['page']) && $_GET['page'] == 'garantie') ? 'active' : ''; ?>" href="index.php?page=garantie">
                            <i class="fas fa-shield-alt"></i>
                            <span>Ajouter une Garantie</span>
                        </a>
                    </li>
                    
                    <li class="nav-item mt-auto">
                        <a class="nav-link" href="pages/logout.php">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Déconnexion</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4" id="content">
                <?php
                // Gestion Centralisée des Messages Success/Error
                if (isset($_SESSION['success'])) {
                    echo '<div class="alert alert-success alert-dismissible fade show mt-3" role="alert">';
                    echo '<i class="fas fa-check-circle me-2"></i>' . htmlspecialchars($_SESSION['success']);
                    echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
                    echo '</div>';
                    unset($_SESSION['success']);
                }
                
                if (isset($_SESSION['error'])) {
                    echo '<div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">';
                    echo '<i class="fas fa-exclamation-triangle me-2"></i>' . htmlspecialchars($_SESSION['error']);
                    echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
                    echo '</div>';
                    unset($_SESSION['error']);
                }
                
                $page = $_GET['page'] ?? 'dashboard';
                $file = "pages/{$page}.php";
                
                if (file_exists($file)) {
                    include $file;
                } else {
                    echo '<div class="alert alert-danger mt-3">Page non trouvée</div>';
                }
                ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-fermeture des alertes après 5 secondes
        const alerts = document.querySelectorAll('.alert-dismissible');
        alerts.forEach(function(alertElement) {
            const bsAlert = new bootstrap.Alert(alertElement);
            setTimeout(function() {
                bsAlert.close();
            }, 5000);
        });
    });
    </script>
</body>
</html>