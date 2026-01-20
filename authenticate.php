<?php
session_start();
require_once 'database.php';


// Sécurité : on n'accepte que le POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit();
}

$login_input = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// Vérification des champs vides
if ($login_input === '' || $password === '') {
    $_SESSION['login_error'] = 'Veuillez remplir tous les champs.';
    header('Location: login.php');
    exit();
}

try {
    // Connexion à la base via ton fichier database.php
    $pdo = getDBConnection(); 

    // Recherche de l'utilisateur par USERNAME
    $stmt = $pdo->prepare(
        "SELECT id, email, username, nom, prenom, mot_de_pass, roleID 
         FROM utilisateur 
         WHERE username = ? LIMIT 1"
    );
    $stmt->execute([$login_input]);
    $user = $stmt->fetch();

    // Vérification : utilisateur trouvé ET mot de passe correct
    if ($user && password_verify($password, $user['mot_de_pass'])) {
        
        // On régénère l'ID pour éviter le vol de session
        session_regenerate_id(true);

        $_SESSION['logged_in'] = true;
        $_SESSION['user_id'] = (int) $user['id']; 
        $_SESSION['username'] = $user['username'];
        
        // On stocke le nom complet pour l'afficher dans le Dashboard
        $_SESSION['full_name'] = $user['nom'] . ' ' . $user['prenom'];
        $_SESSION['role_id'] = $user['roleID'];

        header('Location: index.php');
        exit();
    } else {
        // Identifiants faux
        $_SESSION['login_error'] = 'Nom d\'utilisateur ou mot de passe incorrect.';
        header('Location: login.php');
        exit();
    }

} catch (PDOException $e) {
    // En cas d'erreur SQL (colonne manquante, etc.)
    $_SESSION['login_error'] = 'Erreur technique de connexion au serveur.';
    header('Location: login.php');
    exit();
}