<?php
session_start();
require_once 'database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit();
}

$nom = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if ($nom === '' || $password === '') {
    $_SESSION['login_error'] = 'Identifiants manquants';
    header('Location: login.php');
    exit();
}



$stmt = $pdo->prepare(
    "SELECT id, email, nom, mot_de_pass 
     FROM utilisateur 
     WHERE nom = ?"
);
$stmt->execute([$nom]);

$user = $stmt->fetch();

if ($user && password_verify($password, $user['mot_de_pass'])) {

    $_SESSION['logged_in'] = true;
    $_SESSION['user_id'] = (int) $user['id']; 
    $_SESSION['username'] = $user['nom'];
    $_SESSION['name'] = $user['nom'];


    header('Location: index.php');
    exit();
}


$_SESSION['login_error'] = 'Nom d\'utilisateur ou mot de passe incorrect';
header('Location: login.php');
exit();
