<?php
session_start();

session_unset();
session_destroy();

session_start();
$_SESSION['logout_success'] = 'Vous avez été déconnecté avec succès.';

header('Location: ../login.php');
exit();
?>

