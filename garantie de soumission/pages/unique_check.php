<?php
// Remove any HTML or white space before this tag
require_once dirname(__DIR__) . '/database.php';

// Turn off error reporting to screen (prevents PHP warnings from breaking JSON)
error_reporting(0);

if (isset($_POST['field']) && isset($_POST['value'])) {
    $field = $_POST['field']; 
    $value = trim($_POST['value']);

    // Check your database table columns! 
    // Is it 'Nom' or 'nom'? 'code_pays' or 'Code'?
    $column = ($field === 'nom') ? 'Nom' : 'code_pays';

    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM pays WHERE $column = ?");
        $stmt->execute([$value]);
        $exists = $stmt->fetchColumn() > 0;

        header('Content-Type: application/json');
        echo json_encode(['exists' => $exists]);
    } catch (Exception $e) {
        echo json_encode(['exists' => false, 'error' => $e->getMessage()]);
    }
    exit;
}