<?php
session_start();
require_once dirname(__DIR__) . '/database.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../login.php');
    exit();
}

$id = $_GET['id'] ?? 0;

if ($id > 0) {

    // 1. Check if garantie exists and get num_garantie
    $check_stmt = $pdo->prepare("SELECT num_garantie FROM garantie_soumission WHERE id = ?");
    $check_stmt->execute([$id]);
    $garantie = $check_stmt->fetch(PDO::FETCH_ASSOC);

    if ($garantie) {
        // 2. Delete related records first (if no ON DELETE CASCADE)
        $tables = ['document', 'amendement', 'liberation', 'authentification'];
        foreach ($tables as $table) {
            $delete = $pdo->prepare("DELETE FROM {$table} WHERE garantie_soumissionID = ?");
            $delete->execute([$id]);
        }

        // 3. Delete the garantie itself
        $stmt = $pdo->prepare("DELETE FROM garantie_soumission WHERE id = ?");
        if ($stmt->execute([$id])) {
            $_SESSION['success'] = "Garantie #{$garantie['num_garantie']} supprimée avec succès";
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression";
        }

    } else {
        $_SESSION['error'] = "Garantie non trouvée";
    }

} else {
    $_SESSION['error'] = "ID invalide";
}

header('Location: ../index.php?page=liste-garanties');
exit();
?>