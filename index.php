<?php 
session_start();

if (isset($_SESSION['user_id'])) {
    require_once 'database.php';
    $db = getDBConnection();
    
    // Vérifier si l'utilisateur existe encore en base de données
    $stmt = $db->prepare("SELECT id FROM utilisateur WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    
    if (!$stmt->fetch()) {
        // Le compte n'existe plus : on détruit la session et on redirige
        session_destroy();
        header("Location: login.php?error=deleted");
        exit();
    }
}

require_once 'includes/functions.php'; 

// Protection de la page
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$userName = $_SESSION['nom_complet'] ?? ($_SESSION['username'] ?? 'Admin User');

// Logique pour le menu déroulant actif
$adminPages = ['fournisseur', 'pays', 'devise', 'structure', 'user', 'appel-offre', 'banque', 'agence'];
$currentPage = $_GET['page'] ?? 'dashboard';
$isAdminMenuOpen = in_array($currentPage, $adminPages);
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
        /* COULEURS ET BOUTONS PERSONNALISÉS */
        .ajouter {
            background: linear-gradient(180deg, #486a70 85%, #2f4858 100%);
            color: white;
            border: none;
        }
        .ajouter:hover {
            background: linear-gradient(135deg, #e8772b 30%, #57606f 100%);
            color: white;
        }
        .edit { background: #6cb0bcff; color: white; }
        .edit:hover { background: #4ca0abff; color: white; }
        .eye { background:#77b7d3; color: white; }
        .eye:hover { background:#5aa7b1; color: white; }

        body {
            overflow-x: hidden;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #faf0de;
        }
        
        /* SIDEBAR STYLING */
        #sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #486a70 85%, #2f4858 100%);
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }
        
        .profile-section {
            text-align: center;
            padding: 1.5rem 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .profile-img {
            width: 70px; height: 70px;
            border-radius: 50%;
            border: 3px solid #fff;
            margin-bottom: 0.75rem;
            object-fit: cover;
        }
        
        .profile-name { color: #fff; font-size: 1rem; font-weight: 600; margin: 0; }
        .profile-role { color: #bdc3c7; font-size: 0.8rem; }
        
        /* NAVIGATION LINKS */
        .nav-link {
            color: #cfcfcfff !important;
            padding: 0.75rem 1.25rem;
            margin: 0.2rem 0.5rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        /* État Hover et Actif (Orange Sona) */
        .nav-link:hover, .nav-link.active, .dropdown-item.active {
            background: linear-gradient(135deg, #e8772b 0%, #57606f 100%) !important;
            color: #fff !important;
            transform: translateX(5px);
        }
        
        .dropdown-menu {
            background: rgba(0, 0, 0, 0.15);
            border: none;
            margin: 0 0.5rem;
            padding: 0;
        }
        
        .dropdown-item {
            color: #ecf0f1;
            padding: 0.6rem 2.5rem;
            font-size: 0.9rem;
            border-radius: 6px;
            transition: 0.2s;
        }
        
        .dropdown-item:hover {
            background: rgba(255,255,255,0.1);
            color: #fff;
        }

        /* MAIN CONTENT */
        #content { padding: 2rem; min-height: 100vh; }
        .content-header {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #dee2e6;
        }
        .card { border: none; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border-radius: 10px; }
        .card-header {
            background: linear-gradient(180deg, #486a70 85%, #2f4858 100%);
            color: white; font-weight: 600;
            border-radius: 10px 10px 0 0 !important;
        }
    </style>
</head>
<body>
    
    <div class="container-fluid">
        <div class="row">
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block sidebar">
                <div class="text-center pt-3">
                    <img src="images/logo-sona.svg" alt="Logo" width="80px">
                </div>
                
                <div class="profile-section">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($userName); ?>&background=e8772b&color=fff" alt="Profile" class="profile-img">
                    <p class="profile-name"><?php echo htmlspecialchars($userName); ?></p>
                    <p class="profile-role">Administrateur</p>
                </div>
                
                <ul class="nav flex-column mt-3">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($currentPage == 'dashboard') ? 'active' : ''; ?>" href="index.php?page=dashboard">
                            <i class="fas fa-home"></i> <span>Tableau de bord</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link dropdown-toggle <?php echo $isAdminMenuOpen ? '' : 'collapsed'; ?>" 
                           href="#" data-bs-toggle="collapse" data-bs-target="#adminSubmenu" 
                           aria-expanded="<?php echo $isAdminMenuOpen ? 'true' : 'false'; ?>">
                            <i class="fas fa-cog"></i> <span>Administration</span>
                        </a>
                        <div class="collapse <?php echo $isAdminMenuOpen ? 'show' : ''; ?>" id="adminSubmenu">
                            <ul class="list-unstyled">
                                <li><a class="dropdown-item <?php echo ($currentPage == 'fournisseur') ? 'active' : ''; ?>" href="index.php?page=fournisseur">Fournisseur</a></li>
                                <li><a class="dropdown-item <?php echo ($currentPage == 'pays') ? 'active' : ''; ?>" href="index.php?page=pays">Pays</a></li>
                                <li><a class="dropdown-item <?php echo ($currentPage == 'devise') ? 'active' : ''; ?>" href="index.php?page=devise">Devise</a></li>
                                <li><a class="dropdown-item <?php echo ($currentPage == 'structure') ? 'active' : ''; ?>" href="index.php?page=structure">Structure</a></li>
                                <li><a class="dropdown-item <?php echo ($currentPage == 'user') ? 'active' : ''; ?>" href="index.php?page=user">Utilisateur</a></li>
                                <li><a class="dropdown-item <?php echo ($currentPage == 'appel-offre') ? 'active' : ''; ?>" href="index.php?page=appel-offre">Appel d'offre</a></li>
                                <li><a class="dropdown-item <?php echo ($currentPage == 'banque') ? 'active' : ''; ?>" href="index.php?page=banque">Banque</a></li>
                                <li><a class="dropdown-item <?php echo ($currentPage == 'agence') ? 'active' : ''; ?>" href="index.php?page=agence">Agence</a></li>
                            </ul>
                        </div>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($currentPage == 'garantie') ? 'active' : ''; ?>" href="index.php?page=garantie">
                            <i class="fas fa-shield-alt"></i> <span>Ajouter Garantie</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="pages/logout.php">
                            <i class="fas fa-sign-out-alt"></i> <span>Déconnexion</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4" id="content">
                <?php 
                $file = "pages/{$currentPage}.php";
                if (file_exists($file)) {
                    include $file;
                } else {
                    echo '<div class="alert alert-danger mt-3">La page "'.htmlspecialchars($currentPage).'" n\'existe pas.</div>';
                }
                ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>