<?php
session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(180deg, #486a70 75%, #2f4858 90%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding-top: 0;
        }
        
        /* Removed background and fixed positioning from top bar */
        .top-bar {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem 0;
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
        }
        
        .top-bar img {
            width: 60px;
        }
        
        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 1rem;
        }
        
        .login-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        
        .login-header {
            background: linear-gradient(180deg, #38595eff 85%, #2f4858 100%);
            padding: 2.5rem 2rem;
            text-align: center;
            color: white;
        }
        
        .login-header h1 {
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0 0 0.5rem 0;
        }
        
        .login-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 0.95rem;
        }
        
        .login-body {
            padding: 2.5rem 2rem;
        }
        
        .form-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
        
        .form-control {
            border: 2px solid #e0e6ed;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .input-group {
            position: relative;
        }
        
        .input-group .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #95a5a6;
            z-index: 10;
        }
        
        .input-group .form-control {
            padding-left: 2.75rem;
        }
        
        .btn-login {
            background: linear-gradient(135deg, #e8772bff 30%, #666f80ff 100%);
            border: none;
            border-radius: 8px;
            padding: 0.875rem;
            font-size: 1rem;
            font-weight: 600;
            color: white;
            width: 100%;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(105, 46, 37, 0.4);
        }
        
        
        
       
        .alert {
            border-radius: 8px;
            border: none;
        }
        
        @media (max-width: 768px) {
            .top-bar img {
                height: 40px;
            }
        }
    </style>
</head>
<body>
    <div class="top-bar">
        <img src="images/logo-sona.svg" alt="SONA Logo">
    </div>
    
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1><i class="fas fa-user-shield"></i> Bienvenue!</h1>
                <p>Connectez-vous à votre compte</p>
            </div>
            
            <div class="login-body">
                <?php
                if (isset($_SESSION['login_error'])) {
                    echo '<div class="alert alert-danger mb-4" role="alert">';
                    echo '<i class="fas fa-exclamation-circle"></i> ' . htmlspecialchars($_SESSION['login_error']);
                    echo '</div>';
                    unset($_SESSION['login_error']);
                }
                
                if (isset($_SESSION['logout_success'])) {
                    echo '<div class="alert alert-success mb-4" role="alert">';
                    echo '<i class="fas fa-check-circle"></i> ' . htmlspecialchars($_SESSION['logout_success']);
                    echo '</div>';
                    unset($_SESSION['logout_success']);
                }
                ?>
                
                <form action="authenticate.php" method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Nom d'utilisateur</label>
                        <div class="input-group">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" class="form-control" id="username" name="username" placeholder="Entrez votre nom d'utilisateur" required>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label">Mot de passe</label>
                        <div class="input-group">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Entrez votre mot de passe" required>
                        </div>
                    </div>
                    
                   
                      
                    
                    <button type="submit" class="btn btn-login">
                        <i class="fas fa-sign-in-alt"></i> Se connecter
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(function(alert) {
                    alert.style.transition = "opacity 0.6s ease";
                    alert.style.opacity = "0";
                    setTimeout(function() {
                        alert.remove();
                    }, 600);
                });
            }, 5000);
        });
    </script>
    


</body>
</html>
