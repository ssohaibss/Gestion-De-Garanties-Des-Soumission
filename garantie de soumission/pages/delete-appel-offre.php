<?php
session_start();
require_once dirname(__DIR__) . '/database.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../login.php');
    exit();
}

$id = $_GET['id'] ?? 0;

if ($id > 0) {
    
    // Check if appel d'offre exists and if it has associated garanties
   if (isset($id) && is_numeric($id)) {

    // 1. Get the num_app_offre
    $check_stmt = $pdo->prepare("SELECT num_app_offre FROM appel_offre WHERE id = ?");
    $check_stmt->execute([$id]);
    $ao = $check_stmt->fetchColumn(); // scalar value

    if ($ao) {
        // 2. Count associated garanties
        $garanties_stmt = $pdo->prepare("SELECT COUNT(*) FROM garantie_soumission WHERE appel_offreID = ?");
        $garanties_stmt->execute([$id]);
        $garanties_count = (int) $garanties_stmt->fetchColumn();

        if ($garanties_count > 0) {
            $_SESSION['error'] = "Impossible de supprimer l'appel d'offre '{$ao}' car il est lié à {$garanties_count} garantie(s)";
        } else {
            // 3. Delete the appel d'offre
            $stmt = $pdo->prepare("DELETE FROM appel_offre WHERE id = ?");
            if ($stmt->execute([$id])) {
                $_SESSION['success'] = "Appel d'offre '{$ao}' supprimé avec succès";
            } else {
                $_SESSION['error'] = "Erreur lors de la suppression";
            }
        }

    } else {
        $_SESSION['error'] = "Appel d'offre non trouvé";
    }

} else {
    $_SESSION['error'] = "ID invalide";
}
}
header('Location: ../index.php?page=liste-appels-offre');
exit();
?>