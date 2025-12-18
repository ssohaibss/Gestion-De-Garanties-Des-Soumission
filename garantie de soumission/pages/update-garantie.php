<?php
session_start();
require_once dirname(__DIR__) . '/database.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit();
}

function nullIfEmpty($value) {
    return ($value === '' || $value === null) ? null : $value;
}

$id = (int) $_POST['id'];
$num_garantie = (int) $_POST['num_garantie'];
$montant_garantie = (float) $_POST['montant_garantie'];
$date_emission = $_POST['date_emission'];
$date_expiration = $_POST['date_expiration'];

// Date validation
if (strtotime($date_expiration) < strtotime($date_emission)) {
    $_SESSION['error'] = 'La date d\'expiration ne peut pas être antérieure à la date d\'émission';
    header('Location: ../index.php?page=modifier-garantie&id=' . $id);
    exit;
}

$soumissionnaireID = nullIfEmpty($_POST['soumissionnaireID'] ?? null);
$agenceID = nullIfEmpty($_POST['agenceID'] ?? null);
$deviseID = nullIfEmpty($_POST['deviseID'] ?? null);
$structureID = nullIfEmpty($_POST['structureID'] ?? null);
$appel_offreID = nullIfEmpty($_POST['appel_offreID'] ?? null);
$statutID = nullIfEmpty($_POST['statutID'] ?? null);

// Validation
if (!$agenceID || !$deviseID || !$statutID) {
    $_SESSION['error'] = 'Champs obligatoires manquants (Agence, Devise, Statut)';
    header('Location: ../index.php?page=modifier-garantie&id=' . $id);
    exit;
}

try {
    // Update garantie
    $stmt = $pdo->prepare("
        UPDATE garantie_soumission 
        SET num_garantie = ?,
            montant_garantie = ?,
            date_emission = ?,
            date_expiration = ?,
            soumissionnaireID = ?,
            agenceID = ?,
            deviseID = ?,
            structureID = ?,
            appel_offreID = ?,
            statutID = ?
        WHERE id = ?
    ");

    if ($stmt->execute([
        $num_garantie,
        $montant_garantie,
        $date_emission,
        $date_expiration,
        $soumissionnaireID,
        $agenceID,
        $deviseID,
        $structureID,
        $appel_offreID,
        $statutID,
        $id
    ])) {
        $_SESSION['success'] = 'Garantie modifiée avec succès';
        header('Location: ../index.php?page=details-garantie&id=' . $id);
    } else {
        $_SESSION['error'] = 'Erreur lors de la modification';
        header('Location: ../index.php?page=modifier-garantie&id=' . $id);
    }
} catch (PDOException $e) {
    $_SESSION['error'] = 'Erreur lors de la modification : ' . $e->getMessage();
    header('Location: ../index.php?page=modifier-garantie&id=' . $id);
}

exit();
?>