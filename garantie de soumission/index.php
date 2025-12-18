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
  background-color: #9eaeb5 !important; /* The !important is often needed to override Bootstrap's utilities */
}
.bg-deep-sea-blue:hover {
  background-color: #486a70 !important; /* Darker shade on hover */}

  .ajouter{
    background: linear-gradient(180deg, #486a70 85%, #2f4858 100%);
    color: white;
  }
  .ajouter:hover {
    background: linear-gradient(135deg, #e8772bff 30%, #57606f 100%);
  }
  .edit{
    background: #6cb0bcff;
  }
  .edit:hover{
    background: #4ca0abff;
  }
  .eye{
    background:#77b7d3;
  }
  .eye:hover{
    background:#5aa7b1;
  }

        body {
            overflow-x: hidden;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
.voir:hover {
    background: linear-gradient(135deg, #e8772bff 30%, #57606f 100%);}
        
        /* Removed background and styling from top bar, keeping only logo centering */
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
            color: #cfcfcfff !important;
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
        
        .dropdown-item.active {
            background: #3498db;
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
        
        .content-header h2 {
            color: #2c3e50;
            font-weight: 700;
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
        
        @media (max-width: 768px) {
            #sidebar {
                position: fixed;
                z-index: 1000;
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            #sidebar.show {
                transform: translateX(0);
            }
            
            .top-bar img {
                height: 40px;
            }
        }
    </style>
</head>
<body>
    
    
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block sidebar">
                <!-- Profile Section -->
                 <div class ="pt-2">  <img src="images/logo-sona.svg" alt="Logo" width="60px" > </div>
                <div class="profile-section">
                    
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($userName); ?>&background=6c757d&color=fff&size=80" alt="Profile" class="profile-img">
                    <p class="profile-name"><?php echo htmlspecialchars($userName); ?></p>
                    <p class="profile-role">Administrateur</p>
                </div>
                
                <!-- Navigation Menu -->
                <ul class="nav flex-column mt-3">
                    <li class="nav-item">
                        <a class="nav-link <?php echo (!isset($_GET['page']) || $_GET['page'] == 'dashboard') ? 'active' : ''; ?>" href="index.php?page=dashboard">
                            <i class="fas fa-home"></i>
                            <span>Tableau de bord</span>
                        </a>
                    </li>
                    
                    <!-- Administration Dropdown -->
                    <li class="nav-item">
                        <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" data-bs-toggle="collapse" data-bs-target="#adminSubmenu" aria-expanded="false">
                            <i class="fas fa-cog"></i>
                            <span>Administration</span>
                        </a>
                        <div class="collapse" id="adminSubmenu">
                            <ul class="list-unstyled ps-3">
                                <li>
                                    <a class="dropdown-item <?php echo (isset($_GET['page']) && $_GET['page'] == 'fournisseur') ? 'active' : ''; ?>" href="index.php?page=fournisseur">
                                        <i class="fas fa-truck"></i> Fournisseur
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item <?php echo (isset($_GET['page']) && $_GET['page'] == 'pays') ? 'active' : ''; ?>" href="index.php?page=pays">
                                        <i class="fas fa-flag"></i> Pays
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item <?php echo (isset($_GET['page']) && $_GET['page'] == 'devise') ? 'active' : ''; ?>" href="index.php?page=devise">
                                        <i class="fas fa-dollar-sign"></i> Devise
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item <?php echo (isset($_GET['page']) && $_GET['page'] == 'structure') ? 'active' : ''; ?>" href="index.php?page=structure">
                                        <i class="fas fa-building"></i> Structure
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item <?php echo (isset($_GET['page']) && $_GET['page'] == 'user') ? 'active' : ''; ?>" href="index.php?page=user">
                                        <i class="fas fa-user"></i> Utilisateur
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item <?php echo (isset($_GET['page']) && $_GET['page'] == 'appelle-offre') ? 'active' : ''; ?>" href="index.php?page=appelle-offre">
                                        <i class="fas fa-file-invoice"></i> Appelle d'offre
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item <?php echo (isset($_GET['page']) && $_GET['page'] == 'banque') ? 'active' : ''; ?>" href="index.php?page=banque">
                                        <i class="fas fa-university"></i> Banque
                                    </a>
                                </li>
                                <!-- Added new menu item for Agence -->
                                <li>
                                    <a class="dropdown-item <?php echo (isset($_GET['page']) && $_GET['page'] == 'agence') ? 'active' : ''; ?>" href="index.php?page=agence">
                                        <i class="fas fa-map-marked-alt"></i> Agence
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    
                    <!-- Ajouter une Garantie -->
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

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4" id="content">
                <?php
                if (isset($_SESSION['success'])) {
                    echo '<div class="alert alert-success alert-dismissible fade show mt-3" role="alert">';
                    echo htmlspecialchars($_SESSION['success']);
                    echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
                    echo '</div>';
                    unset($_SESSION['success']);
                }
                
                if (isset($_SESSION['error'])) {
                    echo '<div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">';
                    echo htmlspecialchars($_SESSION['error']);
                    echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
                    echo '</div>';
                    unset($_SESSION['error']);
                }
                
                $page = $_GET['page'] ?? 'dashboard';
                $file = "pages/{$page}.php";
                
                if (file_exists($file)) {
                    include $file;
                } else {
                    echo '<div class="alert alert-danger">Page non trouvée</div>';
                }
                ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
