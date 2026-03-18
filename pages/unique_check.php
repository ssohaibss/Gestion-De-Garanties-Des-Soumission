<?php
require_once dirname(__DIR__) . '/database.php';
if(!isset($pdo)) { $pdo = getDBConnection(); } 

header('Content-Type: application/json');

$config = [
    'pays'            => ['table' => 'pays',            'fields' => ['nom' => 'nom', 'code_pays' => 'code_pays']],
    'devise'          => ['table' => 'devise',          'fields' => ['libelle' => 'libelle', 'code' => 'code']],
    'structure'       => ['table' => 'structure',       'fields' => ['code' => 'code', 'libelle' => 'libelle']],
    'user'            => ['table' => 'utilisateur',     'fields' => ['username' => 'username', 'email' => 'email']],
    'banque'          => ['table' => 'banque',          'fields' => ['code' => 'code', 'nom_banque' => 'nom_banque']],
    'agence'          => ['table' => 'agence',          'fields' => ['code' => 'code', 'nom' => 'nom']],
    'soumissionnaire' => ['table' => 'soumissionnaire', 'fields' => ['nom' => 'nom_entreprise', 'email' => 'email', 'telephone' => 'telephone']],
    'appel_offre'     => ['table' => 'appel_offre',     'fields' => ['numero_ao' => 'num_app_offre']],
    
    // Configs essentielles pour tes garanties
    'garantie'         => ['table' => 'garantie_soumission', 'fields' => ['num_garantie' => 'num_garantie']],
    'amendement'       => ['table' => 'amendement',          'fields' => ['num_amendement' => 'num_amendement']],
    'authentification' => ['table' => 'authentification',    'fields' => ['num_authentification' => 'num_authentification', 'date_authentification' => 'date_authentification']]
];

$type  = $_REQUEST['type'] ?? ''; 
$field = $_REQUEST['field'] ?? '';
$value = trim($_REQUEST['value'] ?? $_REQUEST['numero_ao'] ?? '');
// Accepte 'id' ou 'current_id'
$current_id = intval($_REQUEST['id'] ?? $_REQUEST['current_id'] ?? 0);

if (!isset($config[$type]) || !isset($config[$type]['fields'][$field]) || $value === '') {
    echo json_encode(['valid' => true, 'exists' => false]);
    exit;
}

$tableName  = $config[$type]['table'];
$columnName = $config[$type]['fields'][$field];

try {
    if ($type === 'authentification' && $field === 'date_authentification') {
        // Retrieve the guarantee ID sent from your JavaScript
        $garantie_id = intval($_REQUEST['garantie_soumissionID'] ?? $_REQUEST['garantie_id'] ?? 0);
        $today = date('Y-m-d');
        
        // 1. Check: Date cannot be in the future
        if ($value > $today) {
            echo json_encode(['valid' => false, 'exists' => false, 'message' => "The date cannot be in the future."]);
            exit;
        }
        
        // 2. Check: Date cannot be older than the guarantee's emission date
        if ($garantie_id > 0) {
            $stmt = $pdo->prepare("SELECT date_emission FROM garantie_soumission WHERE id = ?");
            $stmt->execute([$garantie_id]);
            $g = $stmt->fetch();
            
            if ($g && $value < $g['date_emission']) {
                echo json_encode([
                    'valid' => false, 
                    'exists' => false, 
                    'message' => "The date cannot be older than the guarantee's emission date (" . date('d/m/Y', strtotime($g['date_emission'])) . ")."
                ]);
                exit;
            }
        }}
        
        // If the date passes all checks, return valid
        echo json_encode(['valid' => true, 'exists' => false, 'message' => '']);
        exit;
    // Cas spécifique agence
    if ($type === 'agence' && $field === 'nom') {
        $adresse = trim($_REQUEST['adresse'] ?? '');
        $banqueID = $_REQUEST['banqueID'] ?? '';
        
        if ($adresse !== '' && $banqueID !== '') {
            $sql_agence = "SELECT 1 FROM agence WHERE nom = ? AND adresse = ? AND banqueID = ?";
            $params_agence = [$value, $adresse, $banqueID];
            
            // LA CORRECTION : Exclusion de l'ID actuel lors de la modification
            if ($current_id > 0) {
                $sql_agence .= " AND id != ?";
                $params_agence[] = $current_id;
            }
            
            $stmt = $pdo->prepare($sql_agence . " LIMIT 1");
            $stmt->execute($params_agence);
            
            if ($stmt->fetch()) {
                echo json_encode(['valid' => false, 'exists' => true, 'message' => 'Cette agence existe déjà pour cette banque.']);
                exit;
            }
        }
    }

    // Vérification standard pour tous les autres champs
    $sql = "SELECT 1 FROM $tableName WHERE $columnName = ?";
    $params = [$value];

    // Exclusion de l'ID actuel pour la vérification standard
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