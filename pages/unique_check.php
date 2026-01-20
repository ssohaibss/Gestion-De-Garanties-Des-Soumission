<?php
require_once dirname(__DIR__) . '/database.php';
// Note: Utilise ta méthode habituelle pour récupérer $pdo (ex: $pdo = getDBConnection(); ou juste require)
if(!isset($pdo)) { $pdo = getDBConnection(); } 

header('Content-Type: application/json');

$config = [
    'pays'        => ['table' => 'pays',           'fields' => ['nom' => 'nom', 'code_pays' => 'code_pays']],
    'devise'      => ['table' => 'devise',         'fields' => ['libelle' => 'libelle', 'code' => 'code']],
    'structure'   => ['table' => 'structure',      'fields' => ['code' => 'code', 'libelle' => 'libelle']],
    'user'        => ['table' => 'utilisateur',    'fields' => ['username' => 'username', 'email' => 'email']],
    'banque'      => ['table' => 'banque',         'fields' => ['code' => 'code', 'nom_banque' => 'nom_banque']],
    'agence'      => ['table' => 'agence',         'fields' => ['code' => 'code', 'nom' => 'nom']],
    'fournisseur' => ['table' => 'soumissionnaire', 'fields' => ['nom' => 'nom_entreprise', 'email' => 'email']],
    'appel_offre' => ['table' => 'appel_offre',    'fields' => ['numero_ao' => 'num_app_offre']],
    'garantie' => ['table' => 'garantie_soumission', 'fields' => ['num_garantie' => 'num_garantie']]
];

// On accepte POST ou GET pour plus de flexibilité selon tes formulaires
$type  = $_REQUEST['type'] ?? ''; 
$field = $_REQUEST['field'] ?? '';
$value = trim($_REQUEST['value'] ?? $_REQUEST['numero_ao'] ?? '');
$current_id = intval($_REQUEST['id'] ?? 0); // Pour exclure l'ID actuel lors d'une modif

// Sécurité : Si le type n'est pas dans la config
if (!isset($config[$type]) || !isset($config[$type]['fields'][$field]) || $value === '') {
    echo json_encode(['valid' => true, 'exists' => false]);
    exit;
}

$tableName  = $config[$type]['table'];
$columnName = $config[$type]['fields'][$field];

try {
    // 1. CAS SPÉCIFIQUE AGENCE (Ton ancienne logique)
    if ($type === 'agence' && $field === 'nom') {
        $adresse = trim($_REQUEST['adresse'] ?? '');
        $banqueID = $_REQUEST['banqueID'] ?? '';

        if ($adresse !== '' && $banqueID !== '') {
            $stmt = $pdo->prepare("SELECT 1 FROM agence WHERE nom = ? AND adresse = ? AND banqueID = ? LIMIT 1");
            $stmt->execute([$value, $adresse, $banqueID]);
            if ($stmt->fetch()) {
                echo json_encode(['valid' => false, 'exists' => true, 'message' => 'Cette agence existe déjà pour cette banque.']);
                exit;
            }
        }
    }

    // 2. VÉRIFICATION STANDARD (Avec exclusion de l'ID actuel pour les modifications)
    $sql = "SELECT 1 FROM $tableName WHERE $columnName = ?";
    $params = [$value];

    if ($current_id > 0) {
        $sql .= " AND id != ?";
        $params[] = $current_id;
    }

    $stmt = $pdo->prepare($sql . " LIMIT 1");
    $stmt->execute($params);
    
    $exists = (bool)$stmt->fetch();

    echo json_encode([
        'valid'  => !$exists, 
        'exists' => $exists,
        'message' => $exists ? 'Cette valeur est déjà utilisée.' : ''
    ]);

} catch (PDOException $e) {
    echo json_encode(['valid' => false, 'message' => 'Erreur technique.']);
}
exit;