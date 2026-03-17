<?php
session_start();
require_once 'database.php';

$isJson = (
    isset($_SERVER['HTTP_ACCEPT']) &&
    str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')
);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$form_type = $_POST['form_type'] ?? '';

    
    
    switch($form_type) {
        //                                          soumissionnaire


case 'soumissionnaire':
    $id = intval($_POST['id'] ?? 0);
    $is_update = ($id > 0);

    // NETTOYAGE STRICT
    $nom       = trim(preg_replace('/\s+/', ' ', preg_replace('/[0-9]/', '', $_POST['nom'] ?? '')));
    $email     = strtolower(trim(preg_replace('/\s+/', '', $_POST['email'] ?? '')));
    $adresse   = trim(preg_replace('/\s+/', ' ', $_POST['adresse'] ?? ''));
    $paysID    = $_POST['pays'] ?? null;
    $digits    = preg_replace('/[^0-9]/', '', $_POST['telephone'] ?? '');
    $telephone = '+' . $digits;

    $errors = [];

    // Validations
    if (strlen($nom) < 3) {
        $errors['nom'] = "Le nom d'entreprise doit faire au moins 3 caractères (lettres uniquement).";
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Email invalide.";
    }

    if (strlen($digits) < 8 || strlen($digits) > 15) {
        $errors['telephone'] = "Le numéro doit contenir entre 8 et 15 chiffres.";
    }

    if (empty($adresse)) $errors['adresse'] = "L'adresse est requise.";
    if (empty($paysID)) $errors['pays'] = "Le pays est requis.";

    // Vérification Doublons
    $sql_check = "SELECT nom_entreprise, email, telephone FROM soumissionnaire WHERE (nom_entreprise = ? OR email = ? OR telephone = ?)";
    if ($is_update) $sql_check .= " AND id != ?";

    $stmt = $pdo->prepare($sql_check);
    $params = [$nom, $email, $telephone];
    if ($is_update) $params[] = $id;
    
    $stmt->execute($params);
    while ($row = $stmt->fetch()) {
        if ($row['nom_entreprise'] === $nom) $errors['nom'] = "Ce nom existe déjà.";
        if ($row['email'] === $email) $errors['email'] = "Cet email est déjà utilisé.";
        if ($row['telephone'] === $telephone) $errors['telephone'] = "Ce numéro est déjà utilisé.";
    }

    if (!empty($errors)) {
        echo json_encode(['ok' => false, 'errors' => $errors]);
        exit;
    }

    try {
        if ($is_update) {
            $sql = "UPDATE soumissionnaire SET nom_entreprise=?, adresse=?, telephone=?, email=?, paysID=? WHERE id=?";
            $pdo->prepare($sql)->execute([$nom, $adresse, $telephone, $email, $paysID, $id]);
        } else {
            $sql = "INSERT INTO soumissionnaire (nom_entreprise, adresse, telephone, email, paysID) VALUES (?, ?, ?, ?, ?)";
            $pdo->prepare($sql)->execute([$nom, $adresse, $telephone, $email, $paysID]);
        }
        echo json_encode(['ok' => true]);
    } catch (PDOException $e) {
        echo json_encode(['ok' => false, 'message' => "Erreur technique SQL."]);
    }
    exit;
    break;

case 'delete_soumissionnaire':
    $id = intval($_POST['id'] ?? 0);
    try {
        $stmt = $pdo->prepare("DELETE FROM soumissionnaire WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['ok' => true]);
    } catch (PDOException $e) {
        echo json_encode(['ok' => false, 'message' => "Impossible de supprimer ce soumissionnaire."]);
    }
    exit;
    break;


                                                         // PAYS

case 'pays':
    $id = intval($_POST['id'] ?? 0);
    $is_update = ($id > 0);
    
    // NETTOYAGE STRICT
    $nom  = trim(preg_replace('/\s+/', ' ', preg_replace('/[0-9]/', '', $_POST['nom'] ?? '')));
    $code = strtoupper(trim(preg_replace('/\s+/', '', $_POST['code_pays'] ?? '')));

    $errors = [];
    if (strlen($nom) < 2 || strlen($nom) > 50) $errors['nom'] = "Nom invalide (2 à 50 caractères).";
    if (strlen($code) < 2 || strlen($code) > 3) $errors['code_pays'] = "Le code ISO doit faire 2 ou 3 lettres.";

    // Vérification Doublons
    $sql_check = "SELECT nom, code_pays FROM pays WHERE (LOWER(nom) = LOWER(?) OR code_pays = ?)";
    if ($is_update) $sql_check .= " AND id != ?";
    
    $stmt = $pdo->prepare($sql_check);
    $params = [$nom, $code];
    if ($is_update) $params[] = $id;
    $stmt->execute($params);

    while ($row = $stmt->fetch()) {
        if (strtolower($row['nom']) === strtolower($nom)) $errors['nom'] = "Ce pays existe déjà.";
        if ($row['code_pays'] === $code) $errors['code_pays'] = "Ce code ISO existe déjà.";
    }

    if (!empty($errors)) {
        echo json_encode(['ok' => false, 'errors' => $errors]);
        exit;
    }

    try {
        if ($is_update) {
            $stmt = $pdo->prepare("UPDATE pays SET nom = ?, code_pays = ? WHERE id = ?");
            $stmt->execute([$nom, $code, $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO pays (nom, code_pays) VALUES (?, ?)");
            $stmt->execute([$nom, $code]);
        }
        echo json_encode(['ok' => true]);
    } catch (PDOException $e) {
        echo json_encode(['ok' => false, 'message' => "Erreur technique."]);
    }
    exit;
    break;

case 'delete_pays':
    $id = intval($_POST['id'] ?? 0);
    // Vérification d'intégrité : est-il lié à un soumissionnaire ?
    $check = $pdo->prepare("SELECT 1 FROM soumissionnaire WHERE paysID = ? LIMIT 1");
    $check->execute([$id]);
    if ($check->fetch()) {
        echo json_encode(['ok' => false, 'message' => 'Impossible de supprimer : ce pays est lié à des soumissionnaire.']);
        exit;
    }

    if ($pdo->prepare("DELETE FROM pays WHERE id = ?")->execute([$id])) {
        echo json_encode(['ok' => true]);
    } else {
        echo json_encode(['ok' => false, 'message' => 'Erreur lors de la suppression.']);
    }
    exit;
    break;

                                                         // DEVISE


    case 'devise':
    $id = intval($_POST['id'] ?? 0);
    $is_update = ($id > 0);
    
    // Nettoyage strict
    $libelle = trim(preg_replace('/\s+/', ' ', preg_replace('/[0-9]/', '', $_POST['libelle'] ?? '')));
    $code    = strtoupper(trim(preg_replace('/\s+/', '', $_POST['code'] ?? '')));

    $errors = [];
    if (strlen($code) !== 3) $errors['code'] = "Le code ISO doit faire exactement 3 lettres.";
    if (strlen($libelle) < 3) $errors['libelle'] = "Le libellé est trop court.";

    // Vérification Doublons
    $sql_check = "SELECT libelle, code FROM devise WHERE (LOWER(libelle) = LOWER(?) OR code = ?)";
    if ($is_update) $sql_check .= " AND id != ?";
    
    $stmt = $pdo->prepare($sql_check);
    $params = [$libelle, $code];
    if ($is_update) $params[] = $id;
    $stmt->execute($params);
    
    while ($row = $stmt->fetch()) {
        if (strtolower($row['libelle']) === strtolower($libelle)) $errors['libelle'] = "Ce nom existe déjà.";
        if ($row['code'] === $code) $errors['code'] = "Ce code ISO existe déjà.";
    }

    if (!empty($errors)) {
        echo json_encode(['ok' => false, 'errors' => $errors]);
        exit;
    }

    try {
        if ($is_update) {
            $pdo->prepare("UPDATE devise SET code = ?, libelle = ? WHERE id = ?")->execute([$code, $libelle, $id]);
        } else {
            $pdo->prepare("INSERT INTO devise (code, libelle) VALUES (?, ?)")->execute([$code, $libelle]);
        }
        echo json_encode(['ok' => true]);
    } catch (PDOException $e) {
        echo json_encode(['ok' => false, 'message' => "Erreur technique SQL."]);
    }
    exit;
    break;

case 'delete_devise':
    $id = intval($_POST['id'] ?? 0);
    try {
        $stmt = $pdo->prepare("DELETE FROM devise WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['ok' => true]);
    } catch (PDOException $e) {
        echo json_encode(['ok' => false, 'message' => "Impossible de supprimer : cette devise est utilisée ailleurs."]);
    }
    exit;
    break;
                                                     // STRUCTURE

case 'structure':
    $id = intval($_POST['id'] ?? 0);
    $is_update = ($id > 0);
    
    // 1. NETTOYAGE STRICT DU LIBELLÉ
    // On retire les chiffres
    $libelle = preg_replace('/[0-9]/', '', $_POST['libelle'] ?? '');
    // On remplace les espaces multiples par un seul et on trim (début/fin)
    $libelle = trim(preg_replace('/\s+/', ' ', $libelle));
    
    // 2. NETTOYAGE STRICT DU CODE
    // On force majuscules et on retire TOUT ce qui n'est pas une lettre A-Z
    $code = strtoupper(preg_replace('/[^a-zA-Z]/', '', $_POST['code'] ?? ''));
    
    $errors = [];

    // 3. VALIDATIONS SUR LES DONNÉES NETTOYÉES
    if (empty($libelle) || strlen($libelle) < 3) {
        $errors['libelle'] = "Le libellé doit contenir au moins 3 lettres (sans chiffres).";
    }
    
    if (empty($code) || strlen($code) < 2 || strlen($code) > 6) {
        $errors['code'] = "Le code doit contenir entre 2 et 6 lettres.";
    }

    // 4. VÉRIFICATION DES DOUBLONS (Libellé OU Code)
    // On compare de manière insensible à la casse pour le libellé
    $sql_check = "SELECT libelle, code FROM structure WHERE (LOWER(libelle) = LOWER(?) OR code = ?)";
    if ($is_update) {
        $sql_check .= " AND id != $id";
    }
    
    $stmt = $pdo->prepare($sql_check);
    $stmt->execute([$libelle, $code]);
    
    while ($row = $stmt->fetch()) {
        if (strtolower($row['libelle']) === strtolower($libelle)) {
            $errors['libelle'] = "Ce libellé de structure existe déjà.";
        }
        if ($row['code'] === $code) {
            $errors['code'] = "Ce code acronyme existe déjà.";
        }
    }

    // Renvoi des erreurs au JS
    if (!empty($errors)) {
        echo json_encode(['ok' => false, 'errors' => $errors]);
        exit;
    }

    // 5. INSERTION OU MISE À JOUR
    try {
        if ($is_update) {
            $stmt = $pdo->prepare("UPDATE structure SET libelle = ?, code = ? WHERE id = ?");
            $stmt->execute([$libelle, $code, $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO structure (libelle, code) VALUES (?, ?)");
            $stmt->execute([$libelle, $code]);
        }
        echo json_encode(['ok' => true]);
    } catch (PDOException $e) {
        echo json_encode(['ok' => false, 'message' => "Erreur lors de l'enregistrement : " . $e->getMessage()]);
    }
    exit;
    break;

case 'delete_structure':
    $id = intval($_POST['id'] ?? 0);
    try {
        $stmt = $pdo->prepare("DELETE FROM structure WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['ok' => true]);
    } catch (PDOException $e) {
        echo json_encode(['ok' => false, 'message' => "Impossible de supprimer : cette structure est liée à d'autres données."]);
    }
    exit;
    break;

                                                     // UTILISATEUR


    case 'user':
    $id = intval($_POST['id'] ?? 0);
    $is_update = ($id > 0);
    
    // Nettoyage strict côté serveur
    $nom      = strtoupper(trim(preg_replace('/\s+/', ' ', preg_replace('/[0-9]/', '', $_POST['nom'] ?? ''))));
    $prenom   = trim(preg_replace('/\s+/', ' ', preg_replace('/[0-9]/', '', $_POST['prenom'] ?? '')));
    $username = trim(preg_replace('/\s+/', '', $_POST['username'] ?? ''));
    $email    = strtolower(trim(preg_replace('/\s+/', '', $_POST['email'] ?? '')));
    $password = $_POST['password'] ?? '';
    $role_id  = intval($_POST['role'] ?? 0);

    $errors = [];
    if (strlen($nom) < 2) $errors['nom'] = "Nom trop court.";
    if (strlen($username) < 4) $errors['username'] = "Login trop court (min 4).";
   if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = "Une adresse email valide est requise.";
} elseif (!str_ends_with($email, '@sonatrach.com')) {
    $errors['email'] = "L'adresse email doit être @sonatrach.com.";
}

    // Validation Password (si nouveau ou si rempli en modif)
    if (!$is_update || !empty($password)) {
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&_\-])[A-Za-z\d@$!%*?&_\-]{8,}$/', $password)) {
            $errors['password'] = "Le mot de passe ne respecte pas les critères de sécurité.";
        }
    }

    // Unicité
    $stmt = $pdo->prepare("SELECT email, username FROM utilisateur WHERE (email = ? OR username = ?) AND id != ?");
    $stmt->execute([$email, $username, $id]);
    while ($row = $stmt->fetch()) {
        if ($row['email'] === $email) $errors['email'] = "Email déjà utilisé.";
        if ($row['username'] === $username) $errors['username'] = "Login déjà utilisé.";
    }

    if (!empty($errors)) {
        echo json_encode(['ok' => false, 'errors' => $errors]);
        exit;
    }

    try {
        if ($is_update) {
            if (!empty($password)) {
                $sql = "UPDATE utilisateur SET nom=?, prenom=?, username=?, email=?, mot_de_pass=?, roleID=? WHERE id=?";
                $pdo->prepare($sql)->execute([$nom, $prenom, $username, $email, password_hash($password, PASSWORD_DEFAULT), $role_id, $id]);
            } else {
                $sql = "UPDATE utilisateur SET nom=?, prenom=?, username=?, email=?, roleID=? WHERE id=?";
                $pdo->prepare($sql)->execute([$nom, $prenom, $username, $email, $role_id, $id]);
            }
        } else {
            $sql = "INSERT INTO utilisateur (nom, prenom, username, email, mot_de_pass, roleID) VALUES (?, ?, ?, ?, ?, ?)";
            $pdo->prepare($sql)->execute([$nom, $prenom, $username, $email, password_hash($password, PASSWORD_DEFAULT), $role_id]);
        }
        echo json_encode(['ok' => true]);
    } catch (PDOException $e) {
        echo json_encode(['ok' => false, 'message' => "Erreur technique SQL"]);
    }
    exit;
    break;

case 'delete_user':
    $id_del = intval($_POST['id'] ?? 0);
    $me = intval($_SESSION['user_id'] ?? 0);

    if ($id_del === $me) {
        echo json_encode(['ok' => false, 'message' => "Auto-suppression impossible."]);
        exit;
    }

    // Vérif dernier Admin
    $user = $pdo->prepare("SELECT roleID FROM utilisateur WHERE id = ?");
    $user->execute([$id_del]);
    $u = $user->fetch();
    if ($u && $u['roleID'] == 1) { // 1 = Admin
        $count = $pdo->query("SELECT COUNT(*) FROM utilisateur WHERE roleID = 1")->fetchColumn();
        if ($count <= 1) {
            echo json_encode(['ok' => false, 'message' => "Impossible de supprimer le dernier administrateur."]);
            exit;
        }
    }

    $pdo->prepare("DELETE FROM utilisateur WHERE id = ?")->execute([$id_del]);
    echo json_encode(['ok' => true]);
    exit;
    break;


                                                     // APPEL OFFRE


    case 'appel_offre':
    case 'update_appel_offre':
        $is_update = ($form_type === 'update_appel_offre');
        $id = intval($_POST['id'] ?? 0);

        // 1. Nettoyage : Majuscules et AUCUN espace
        $num_ao = strtoupper(str_replace(' ', '', $_POST['numero_ao'] ?? ''));
        $num_ao = preg_replace('/[^A-Z0-9\/\-]/', '', $num_ao);
        
        $date_em = $_POST['date_emission'] ?? '';
        $deviseID = !empty($_POST['deviseID']) ? intval($_POST['deviseID']) : null;
        
        $errors = [];
        $today = date('Y-m-d');
        $year_now = intval(date('Y'));

        // 2. Validation du numéro (Min 3 caractères)
        if (empty($num_ao)) {
            $errors['numero_ao'] = "Numéro obligatoire.";
        } elseif (strlen($num_ao) < 3) {
            $errors['numero_ao'] = "Le numéro est trop court (min. 3 caract.).";
        }

        // 3. Validation de la date (Pas de 0001, pas de futur)
        $today = date('Y-m-d');
        if (empty($date_em)) {
            $errors['date_emission'] = "Date obligatoire.";
        } else {
            if ($date_em > $today) {
                $errors['date_emission'] = "La date d'émission ne peut pas être dans le futur.";
            }
            // La restriction sur les années passées est bien supprimée ici.
        }

        // 4. Validation Devise
        if (!$deviseID) $errors['deviseID'] = "Sélectionnez une devise.";

        // 5. Unicité
        if (empty($errors)) {
            $sql_check = "SELECT 1 FROM appel_offre WHERE num_app_offre = ?";
            $params_check = [$num_ao];
            if ($is_update) { $sql_check .= " AND id != ?"; $params_check[] = $id; }
            $stmt_check = $pdo->prepare($sql_check);
            $stmt_check->execute($params_check);
            if ($stmt_check->fetch()) $errors['numero_ao'] = "Ce numéro existe déjà.";
        }

        if (!empty($errors)) {
            echo json_encode(['ok' => false, 'errors' => $errors]);
            exit;
        }

        try {
            if ($is_update) {
                $sql = "UPDATE appel_offre SET num_app_offre=?, date_emission=?, deviseID=? WHERE id=?";
                $pdo->prepare($sql)->execute([$num_ao, $date_em, $deviseID, $id]);
            } else {
                $sql = "INSERT INTO appel_offre (num_app_offre, date_emission, deviseID) VALUES (?, ?, ?)";
                $pdo->prepare($sql)->execute([$num_ao, $date_em, $deviseID]);
            }
            echo json_encode(['ok' => true]);
        } catch (Exception $e) {
            echo json_encode(['ok' => false, 'message' => "Erreur SQL lors de l'enregistrement."]);
        }
        break;

    case 'delete_appel_offre':
        $id = intval($_POST['id'] ?? 0);
        try {
            $stmt = $pdo->prepare("DELETE FROM appel_offre WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['ok' => true]);
        } catch (Exception $e) {
            echo json_encode(['ok' => false, 'message' => "Impossible de supprimer : ce dossier est lié à des garanties."]);
        }
        break;
exit;


                                                     // BANQUE


  case 'banque':
    $id = intval($_POST['id'] ?? 0);
    $is_update = ($id > 0);
    
    // Nettoyage strict côté serveur
    $code = strtoupper(preg_replace('/[^a-zA-Z]/', '', $_POST['code'] ?? ''));
    $nom  = trim(preg_replace('/\s+/', ' ', $_POST['nom_banque'] ?? ''));
    
    $errors = [];

    // Validation
    if (strlen($code) < 3 || strlen($code) > 5) {
        $errors['code'] = "Le code doit contenir entre 3 et 5 lettres.";
    }
    if (strlen($nom) < 3) {
        $errors['nom_banque'] = 'Le nom est obligatoire (min. 3 car.).';
    }

    // Unicité Insensible à la casse (Case-Insensitive)
    $sql_check = "SELECT code, nom_banque FROM banque WHERE (code = ? OR LOWER(nom_banque) = LOWER(?))";
    $params = [$code, $nom];
    
    if ($is_update) {
        $sql_check .= " AND id != ?";
        $params[] = $id;
    }
    
    $stmt = $pdo->prepare($sql_check);
    $stmt->execute($params);
    
    while ($row = $stmt->fetch()) {
        if (strtoupper($row['code']) === strtoupper($code)) $errors['code'] = 'Ce code banque existe déjà.';
        if (strtolower($row['nom_banque']) === strtolower($nom)) $errors['nom_banque'] = 'Ce nom de banque existe déjà.';
    }

    if (!empty($errors)) {
        echo json_encode(['ok' => false, 'errors' => $errors]);
        exit;
    }

    try {
        if ($is_update) {
            $pdo->prepare("UPDATE banque SET code = ?, nom_banque = ? WHERE id = ?")->execute([$code, $nom, $id]);
        } else {
            $pdo->prepare("INSERT INTO banque (code, nom_banque) VALUES (?, ?)")->execute([$code, $nom]);
        }
        echo json_encode(['ok' => true]);
    } catch (PDOException $e) {
        echo json_encode(['ok' => false, 'message' => 'Erreur technique en base de données.']);
    }
    exit;

case 'delete_banque':
    $id = intval($_POST['id'] ?? 0);
    try {
        $stmt = $pdo->prepare("DELETE FROM banque WHERE id = ?");
        if ($stmt->execute([$id])) {
            echo json_encode(['ok' => true]);
        } else {
            echo json_encode(['ok' => false, 'message' => "Erreur de suppression."]);
        }
    } catch (PDOException $e) {
        // Capture l'erreur si la banque est liée (Foreign Key constraint)
        echo json_encode(['ok' => false, 'message' => "Cette banque ne peut pas être supprimée car elle est liée à d'autres données (Agences, Comptes, etc.)."]);
    }
    exit;


                                                     // AGENCE

        case 'agence':
    $id = intval($_POST['id'] ?? 0);
    $is_update = ($id > 0);
    
    // Nettoyage strict Espaces + Majuscules
    $code      = strtoupper(preg_replace('/\s+/', '', $_POST['code'] ?? ''));
    $nom       = trim(preg_replace('/\s+/', ' ', $_POST['nom'] ?? ''));
    $adresse   = trim(preg_replace('/\s+/', ' ', $_POST['adresse'] ?? ''));
    $banqueID  = $_POST['banqueID'] ?? '';
    $errors    = [];

    if ($code === '') $errors['code'] = 'Le code agence est obligatoire.';
    elseif (strlen($code) < 3 || strlen($code) > 10) $errors['code'] = "Le code doit faire entre 3 et 10 car.";

    if ($nom === '')     $errors['nom'] = 'Le nom est obligatoire.';
    if ($adresse === '') $errors['adresse'] = 'L\'adresse est obligatoire.';
    if ($banqueID === '') $errors['banqueID'] = 'Veuillez choisir une banque.';

    if (empty($errors)) {
        // Unicité Code
        $sql_code = "SELECT 1 FROM agence WHERE code = ?";
        $params_code = [$code];
        if($is_update) { $sql_code .= " AND id != ?"; $params_code[] = $id; }
        $stmt = $pdo->prepare($sql_code);
        $stmt->execute($params_code);
        if ($stmt->fetch()) $errors['code'] = 'Ce code agence existe déjà.';

        // Unicité Nom (Insensible à la casse et aux espaces nettoyés)
        $sql_nom = "SELECT 1 FROM agence WHERE LOWER(nom) = LOWER(?)";
        $params_nom = [$nom];
        if($is_update) { $sql_nom .= " AND id != ?"; $params_nom[] = $id; }
        $stmt = $pdo->prepare($sql_nom);
        $stmt->execute($params_nom);
        if ($stmt->fetch()) $errors['nom'] = 'Ce nom d\'agence existe déjà.';
    }

    if (!empty($errors)) {
        echo json_encode(['ok' => false, 'errors' => $errors]);
        exit;
    }

    try {
        if ($is_update) {
            $stmt = $pdo->prepare("UPDATE agence SET code=?, nom=?, adresse=?, banqueID=? WHERE id=?");
            $stmt->execute([$code, $nom, $adresse, $banqueID, $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO agence (code, nom, adresse, banqueID) VALUES (?, ?, ?, ?)");
            $stmt->execute([$code, $nom, $adresse, $banqueID]);
        }
        echo json_encode(['ok' => true]);
    } catch (PDOException $e) {
        echo json_encode(['ok' => false, 'message' => 'Erreur SQL : ' . $e->getMessage()]);
    }
    exit;

case 'delete_agence':
    $id = intval($_POST['id'] ?? 0);
    try {
        $stmt = $pdo->prepare("DELETE FROM agence WHERE id = ?");
        if ($stmt->execute([$id])) {
            echo json_encode(['ok' => true]);
        } else {
            echo json_encode(['ok' => false, 'message' => 'Erreur de suppression.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['ok' => false, 'message' => 'L\'agence est liée à d\'autres données.']);
    }
    exit;

                                                         // GARANTIE


case 'garantie':
case 'update_garantie':
    header('Content-Type: application/json');
    $errors = [];
    $id = isset($_POST['id']) ? (int)$_POST['id'] : null;

    // 1. Récupération et Nettoyage
    $num_garantie = trim($_POST['num_garantie'] ?? '');
    $montant = (float)($_POST['montant_garantie'] ?? 0);
    $deviseID = $_POST['deviseID'] ?? '';
    $date_e = $_POST['date_emission'] ?? '';
    $date_x = $_POST['date_expiration'] ?? '';
    $soumissionnaireID = $_POST['soumissionnaireID'] ?? '';
    $agenceID = $_POST['agenceID'] ?? '';
    $structureID = $_POST['structureID'] ?? '';
    $statutID = $_POST['statutID'] ?? '';
    $banqueID = $_POST['banqueID'] ?? '';
    $aoID = !empty($_POST['appel_offreID']) ? $_POST['appel_offreID'] : null;

    $today = date('Y-m-d');

    // 2. VALIDATIONS STRICTES
    if (empty($num_garantie)) $errors['num_garantie'] = "Le numéro est obligatoire.";
    elseif (!is_numeric($num_garantie)) $errors['num_garantie'] = "Le numéro doit être numérique.";
    elseif (strlen($num_garantie) > 20) $errors['num_garantie'] = "Maximum 20 caractères.";
    
    if (empty($deviseID)) $errors['deviseID'] = "La devise est obligatoire.";
    if (empty($soumissionnaireID)) $errors['soumissionnaireID'] = "Le soumissionnaire est obligatoire.";
    if (empty($agenceID)) $errors['agenceID'] = "L'agence est obligatoire.";
    if (empty($structureID)) $errors['structureID'] = "La structure est obligatoire.";
    if (empty($statutID)) $errors['statutID'] = "Le statut est obligatoire.";
    if (empty($banqueID)) $errors['banqueID'] = "La banque est obligatoire.";
    if (empty($aoID)) $errors['appel_offreID'] = "L'appel d'offre est obligatoire.";
    if (empty($montant) || $montant <= 0) $errors['montant_garantie'] = "Montant valide requis.";
 
   if (!$id && (!isset($_FILES['pdf_files']) || $_FILES['pdf_files']['error'] === UPLOAD_ERR_NO_FILE)) {
        $errors['pdf_files'] = "Le document PDF est requis pour toute nouvelle garantie.";
    } elseif (isset($_FILES['pdf_files']) && $_FILES['pdf_files']['error'] === UPLOAD_ERR_OK) {
        $filename = $_FILES['pdf_files']['name'];
        
        // On vérifie si ce nom de fichier existe déjà dans la base
        $sqlCheckDoc = "SELECT id FROM document WHERE nom_document = ?";
        $paramsCheckDoc = [$filename];
        
        // Si c'est une modification, on autorise le même nom SEULEMENT si c'est déjà le document de cette même garantie
        if ($id) {
            $sqlCheckDoc .= " AND garantie_soumissionID != ?";
            $paramsCheckDoc[] = $id;
        }
        
        $checkDoc = $pdo->prepare($sqlCheckDoc);
        $checkDoc->execute($paramsCheckDoc);
        if ($checkDoc->fetch()) {
            $errors['pdf_files'] = "Un document avec ce nom ($filename) existe déjà dans le système. Veuillez renommer votre fichier.";
        }
    }
    if (!empty($errors)) {
    echo json_encode(['ok' => false, 'errors' => $errors]);
    exit;
    }
    

    // Dates
    if (empty($date_e)) $errors['date_emission'] = "Date d'émission requise.";
    elseif ($date_e > $today) $errors['date_emission'] = "La date ne peut pas être future.";

    if (empty($date_x)) $errors['date_expiration'] = "Date d'expiration requise.";
    elseif ($date_x <= $date_e) $errors['date_expiration'] = "Doit être strictement après l'émission.";
    
    // Contrainte Statut / Expiration (Si Active alors non expirée)
    // On suppose ici que ID 1 = Active 
    if ($statutID == "1" && $date_x < $today) {
        $errors['statutID'] = "Une garantie expirée ne peut pas être 'Active'.";
    }

    // 3. Vérification Unicité (si pas d'erreurs précédentes)
    if (empty($errors)) {
        $checkSql = "SELECT id FROM garantie_soumission WHERE num_garantie = ? AND id != ?";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->execute([$num_garantie, $id ?? 0]);
        if ($checkStmt->fetch()) {
            $errors['num_garantie'] = "Ce numéro de garantie est déjà utilisé.";
        }
    }

    if (!empty($errors)) {
        echo json_encode(['ok' => false, 'errors' => $errors]);
        exit;
    }

    // 4. Enregistrement
    try {
        $pdo->beginTransaction();
        
        $params = [
            $num_garantie, $montant, $date_e, $date_x, 
            $soumissionnaireID, $agenceID, $deviseID, $structureID, 
            $aoID, $statutID, (int)$_SESSION['user_id']
        ];

        if ($id) {
            $sql = "UPDATE garantie_soumission SET num_garantie=?, montant_garantie=?, date_emission=?, date_expiration=?, soumissionnaireID=?, agenceID=?, deviseID=?, structureID=?, appel_offreID=?, statutID=?, utilisateurID=? WHERE id=?";
            $params[] = $id;
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $garantie_id = $id;
        } else {
            $sql = "INSERT INTO garantie_soumission (num_garantie, montant_garantie, date_emission, date_expiration, soumissionnaireID, agenceID, deviseID, structureID, appel_offreID, statutID, utilisateurID) VALUES (?,?,?,?,?,?,?,?,?,?,?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $garantie_id = $pdo->lastInsertId();
        }

    // Gestion de l'upload PDF (single file)
if (isset($_FILES['pdf_files']) && $_FILES['pdf_files']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = 'uploads/garanties/';
    
    // Créer le dossier s'il n'existe pas
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $allowed_extensions = ['pdf'];
    $max_file_size = 10 * 1024 * 1024; // 10MB

    // Supprimer les anciens documents si édition
    if ($id) {
        $oldDocs = $pdo->prepare("SELECT chemin_access FROM document WHERE garantie_soumissionID = ? AND type_documentID = 1");
        $oldDocs->execute([$id]);
        foreach ($oldDocs->fetchAll(PDO::FETCH_ASSOC) as $oldDoc) {
            if (file_exists($oldDoc['chemin_access'])) {
                unlink($oldDoc['chemin_access']);
            }
        }
        $pdo->prepare("DELETE FROM document WHERE garantie_soumissionID = ? AND type_documentID = 1")->execute([$id]);
    }

    // Traiter le fichier unique
    $filename = $_FILES['pdf_files']['name'];
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $file_size = $_FILES['pdf_files']['size'];

    if (in_array($extension, $allowed_extensions) && $file_size <= $max_file_size) {
        // Nom unique avec timestamp
        $unique_filename = 'g_' . $garantie_id . '_' . time() . '.pdf';
        $target_file = $upload_dir . $unique_filename;

        if (move_uploaded_file($_FILES['pdf_files']['tmp_name'], $target_file)) {
            // Enregistrer dans la table document
            $sqlDoc = "INSERT INTO document (code, nom_document, chemin_access, garantie_soumissionID, type_documentID) VALUES (?, ?, ?, ?, ?)";
            $stmtDoc = $pdo->prepare($sqlDoc);
            $docCode = 'DOC_' . $garantie_id . '_' . time();
            $stmtDoc->execute([$docCode, $filename, $target_file, $garantie_id, 1]); // Type 1 = GARANTIE
        } else {
            // Si le déplacement échoue, on ajoute une erreur mais on ne bloque pas la transaction
            error_log("Erreur lors du déplacement du fichier PDF pour garantie $garantie_id");
        }
    } else {
        // Fichier invalide, on enregistre dans les logs mais on ne bloque pas
        error_log("Fichier PDF invalide pour garantie $garantie_id: extension=$extension, size=$file_size");
    }
}

        $pdo->commit();
        echo json_encode(['ok' => true]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['ok' => false, 'message' => "Erreur : " . $e->getMessage()]);
    }
    exit;
    break;

// --- SUPPRESSION DE GARANTIE ---
case 'delete_garantie':
    header('Content-Type: application/json');
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if ($id > 0) {
        try {
            // On peut supprimer les documents liés ici si nécessaire
            $pdo->prepare("DELETE FROM document WHERE garantie_soumissionID = ?")->execute([$id]);
            
            $stmt = $pdo->prepare("DELETE FROM garantie_soumission WHERE id = ?");
            if ($stmt->execute([$id])) {
                echo json_encode(['ok' => true]);
            } else {
                echo json_encode(['ok' => false, 'message' => 'Échec de la suppression']);
            }
        } catch (Exception $e) {
            echo json_encode(['ok' => false, 'message' => $e->getMessage()]);
        }
    }

    exit;
    break;

    
                                                     // AMENDEMENT

case 'amendement':
    header('Content-Type: application/json');
    $errors = [];
    
    // 1. DATA RECOVERY & CLEANING
    $garantie_id = intval($_POST['garantie_soumissionID'] ?? 0);
    $num_amendement = trim($_POST['num_amendement'] ?? ''); 
    $date_amendement = trim($_POST['date_amendement'] ?? '');
    $type_amendementID = intval($_POST['type_amendementID'] ?? 0);
    
    // Clean amount: remove spaces/commas and convert to float
    $nouveau_montant_raw = $_POST['nouveau_montant'] ?? '';
    $nouveau_montant = !empty($nouveau_montant_raw) ? floatval(str_replace([' ', ','], ['', '.'], $nouveau_montant_raw)) : null;
    
    $nouvelle_date_expiration = !empty($_POST['nouvelle_date_expiration']) ? trim($_POST['nouvelle_date_expiration']) : null;
    $today = date('Y-m-d');

    // 2. FETCH ORIGINAL DATA FOR COMPARISON
    $original_date_x = null;
    $date_emission = null;
    $typeCode = '';
    
    if ($garantie_id > 0) {
        $stmtG = $pdo->prepare("SELECT date_emission, date_expiration FROM garantie_soumission WHERE id = ?");
        $stmtG->execute([$garantie_id]);
        $rowG = $stmtG->fetch(PDO::FETCH_ASSOC);
        if ($rowG) {
            $date_emission = $rowG['date_emission'];
            $original_date_x = $rowG['date_expiration'];
        }
    }

    if ($type_amendementID > 0) {
        $stmtT = $pdo->prepare("SELECT code FROM type_amendement WHERE id = ?");
        $stmtT->execute([$type_amendementID]);
        $typeCode = $stmtT->fetchColumn();
    }

    // 3. STRICT VALIDATIONS
    if ($garantie_id <= 0) $errors['garantie_soumissionID'] = "Garantie invalide.";
    
    if (empty($num_amendement)) {
        $errors['num_amendement'] = "Le numéro d'amendement est requis.";
    } else {
        $checkStmt = $pdo->prepare("SELECT id FROM amendement WHERE num_amendement = ?");
        $checkStmt->execute([$num_amendement]);
        if ($checkStmt->fetch()) {
            $errors['num_amendement'] = "Ce numéro d'amendement est déjà utilisé dans le système.";
        }}

    if (empty($date_amendement)) {
        $errors['date_amendement'] = "La date d'amendement est requise.";
    } elseif ($date_amendement > $today) {
        $errors['date_amendement'] = "La date ne peut pas être dans le futur.";
    } elseif ($date_emission && $date_amendement <= $date_emission) {
        // NEW CONSTRAINT: Cannot be before Emission
        $errors['date_amendement'] = "La date d'amendement ne peut pas être antérieure à la date d'émission ($date_emission).";
    }elseif ($original_date_x && $date_amendement >= $original_date_x) {
        // NEW CONSTRAINT: Cannot be after original expiration
        $errors['date_amendement'] = "La date d'amendement doit être avant l'ancienne date d'expiration ($original_date_x).";
    }

    if ($type_amendementID <= 0) {
        $errors['type_amendementID'] = "Le type d'amendement est requis.";
    }

    // PDF RESTRICTION: Mandatory and Basic Format Check
    if (!isset($_FILES['amendment_pdf']) || $_FILES['amendment_pdf']['error'] === UPLOAD_ERR_NO_FILE) {
        $errors['amendment_pdf'] = "Le document PDF de l'amendement est obligatoire.";
    } else {
        $filename = $_FILES['amendment_pdf']['name'];
        $file_extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $file_size = $_FILES['amendment_pdf']['size'];
        
        if ($file_extension !== 'pdf') {
            $errors['amendment_pdf'] = "Seuls les fichiers PDF sont autorisés.";
        } elseif ($file_size > 10 * 1024 * 1024) { // 10MB
            $errors['amendment_pdf'] = "Le fichier est trop volumineux (Max 10 Mo).";
        } else {
            // Vérification de l'unicité du nom de fichier
            $checkDoc = $pdo->prepare("SELECT id FROM document WHERE nom_document = ?");
            $checkDoc->execute([$filename]);
            if ($checkDoc->fetch()) {
                $errors['amendment_pdf'] = "Un document avec ce nom existe déjà. Veuillez renommer votre fichier.";
            }
        }
    }

    // CONDITIONAL VALIDATIONS
    if (($typeCode === 'MONTANT' || $typeCode === 'MIXTE') && (empty($nouveau_montant) || $nouveau_montant <= 0)) {
        $errors['nouveau_montant'] = "Le nouveau montant est requis et doit être supérieur à 0.";
    }

    if ($typeCode === 'DATE' || $typeCode === 'MIXTE') {
        if (empty($nouvelle_date_expiration)) {
            $errors['nouvelle_date_expiration'] = "La nouvelle date d'expiration est requise.";
        } elseif ($original_date_x && $nouvelle_date_expiration <= $original_date_x) {
            $errors['nouvelle_date_expiration'] = "La nouvelle date doit être strictement après l'ancienne expiration ($original_date_x).";
        }
    }

    // Uniqueness check for Amendment Number per Guarantee
    if (!empty($num_amendement)) {
    // We removed "AND garantie_soumissionID = ?" to make it a global check
    $checkStmt = $pdo->prepare("SELECT id FROM amendement WHERE num_amendement = ?");
    $checkStmt->execute([$num_amendement]);
    
    if ($checkStmt->fetch()) {
        $errors['num_amendement'] = "Ce numéro d'amendement est déjà utilisé dans le système.";
    }
    }

    if (!empty($errors)) {
        echo json_encode(['ok' => false, 'errors' => $errors]);
        exit;
    }

    // 4. DATABASE PERSISTENCE
    try {
        $pdo->beginTransaction();

        $sql = "INSERT INTO amendement (num_amendement, date_amendement, nouveau_montant, nouvelle_date_expiration, garantie_soumissionID, type_amendementID, utilisateurID) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $num_amendement,
            $date_amendement,
            $nouveau_montant,
            $nouvelle_date_expiration,
            $garantie_id,
            $type_amendementID,
            (int)$_SESSION['user_id']
        ]);
        
        $amendment_id = $pdo->lastInsertId();

        // 5. FILE UPLOAD PROCESSING (Polished & Secure)
        if (isset($_FILES['amendment_pdf']) && $_FILES['amendment_pdf']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/amendements/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $filename = $_FILES['amendment_pdf']['name'];
            // Generate unique filename with hash to avoid predictable names
            $unique_filename = 'a_' . $amendment_id . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.pdf';
            $target_file = $upload_dir . $unique_filename;

            if (move_uploaded_file($_FILES['amendment_pdf']['tmp_name'], $target_file)) {
                // Insert into document table
                $sqlDoc = "INSERT INTO document (code, nom_document, chemin_access, garantie_soumissionID, type_documentID) VALUES (?, ?, ?, ?, ?)";
                $docCode = 'AMD_' . $amendment_id . '_' . time();
                $stmtDoc = $pdo->prepare($sqlDoc);
                $stmtDoc->execute([$docCode, $filename, $target_file, $garantie_id, 2]); // Type 2 = AMENDEMENT
                
                $document_id = $pdo->lastInsertId();

                // Link document to amendment in the junction table
                $pdo->prepare("INSERT INTO document_amendement (documentID, amendementID) VALUES (?, ?)")
                    ->execute([$document_id, $amendment_id]);
            }
        }

        $pdo->commit();
        echo json_encode(['ok' => true]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['ok' => false, 'message' => "Erreur technique : " . $e->getMessage()]);
    }
    exit;
    break;

case 'delete_amendement':
    header('Content-Type: application/json');
    $id = intval($_POST['id'] ?? 0);
    
    if ($id > 0) {
        try {
            $stmt = $pdo->prepare("DELETE FROM amendement WHERE id = ?");
            if ($stmt->execute([$id])) {
                echo json_encode(['ok' => true]);
            } else {
                echo json_encode(['ok' => false, 'message' => 'Échec de la suppression']);
            }
        } catch (Exception $e) {
            echo json_encode(['ok' => false, 'message' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['ok' => false, 'message' => 'ID invalide']);
    }
    exit;
    break;

    //                                          AUTHENTIFICATION

    
case 'authentification':
    header('Content-Type: application/json');
    
    // --- VALIDATION DU FICHIER AVANT TOUT ---
    $errors = [];
    if (!isset($_FILES['authentification_pdf']) || $_FILES['authentification_pdf']['error'] === UPLOAD_ERR_NO_FILE) {
        $errors['authentification_pdf'] = "Le document PDF est obligatoire.";
    } else {
        $filename = $_FILES['authentification_pdf']['name'];
        $checkDoc = $pdo->prepare("SELECT id FROM document WHERE nom_document = ?");
        $checkDoc->execute([$filename]);
        if ($checkDoc->fetch()) {
            $errors['authentification_pdf'] = "Un document avec ce nom existe déjà. Veuillez le renommer.";
        }
    }

    if (!empty($errors)) {
        echo json_encode(['ok' => false, 'errors' => $errors]);
        exit;
    }

    try {
        $pdo->beginTransaction();

        $garantie_id = intval($_POST['garantie_soumissionID']);
        $num_auth    = $_POST['num_authentification'];
        $date_auth   = $_POST['date_authentification'];

        // 1. Insertion dans la table authentification
        $sqlAuth = "INSERT INTO authentification (num_authentification, date_authentification, garantie_soumissionID) 
                    VALUES (?, ?, ?)";
        $stmtAuth = $pdo->prepare($sqlAuth);
        $stmtAuth->execute([$num_auth, $date_auth, $garantie_id]);
        $auth_id = $pdo->lastInsertId();

        // 2. Gestion du Fichier PDF (Exactement comme amendement)
        if (isset($_FILES['authentification_pdf']) && $_FILES['authentification_pdf']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/authentification/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

            $filename = $_FILES['authentification_pdf']['name'];
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            $new_name = 'AUTH_' . $auth_id . '_' . time() . '.' . $extension;
            $target_file = $upload_dir . $new_name;

            if (move_uploaded_file($_FILES['authentification_pdf']['tmp_name'], $target_file)) {
                // Insertion dans la table document (Type 3 pour Authentification)
                $sqlDoc = "INSERT INTO document (code, nom_document, chemin_access, garantie_soumissionID, type_documentID) 
                           VALUES (?, ?, ?, ?, ?)";
                $stmtDoc = $pdo->prepare($sqlDoc);
                $docCode = 'DOC_AUTH_' . $auth_id;
                $stmtDoc->execute([$docCode, $filename, $target_file, $garantie_id, 3]);
                $document_id = $pdo->lastInsertId();

                // Liaison
                $sqlLink = "INSERT INTO document_authentification (documentID, authentificationID) VALUES (?, ?)";
                $pdo->prepare($sqlLink)->execute([$document_id, $auth_id]);
            }
        }

        $pdo->commit();
        echo json_encode(['ok' => true]);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo json_encode(['ok' => false, 'message' => $e->getMessage()]);
    }
    exit;
    break;

case 'liberation':
    header('Content-Type: application/json');
    try {
        $pdo->beginTransaction();

        $garantie_id = intval($_POST['garantie_soumissionID']);
        $num_lib     = trim($_POST['num_liberation'] ?? '');
        $date_lib    = $_POST['date_liberation'];
        $type_libID  = intval($_POST['type_liberationID']);
        $today       = date('Y-m-d');
        
        // --- VALIDATIONS ---
     // 1. Contrainte Date Futur
        if ($date_lib > $today) {
            $errors['date_liberation'] = "La date ne peut pas être dans le futur.";
        }

        // 2. Contrainte Unicité du Fichier PDF
        if (!isset($_FILES['liberation_pdf']) || $_FILES['liberation_pdf']['error'] === UPLOAD_ERR_NO_FILE) {
            $errors['liberation_pdf'] = "Le document PDF est obligatoire.";
        } else {
            $filename = $_FILES['liberation_pdf']['name'];
            $checkDoc = $pdo->prepare("SELECT id FROM document WHERE nom_document = ?");
            $checkDoc->execute([$filename]);
            if ($checkDoc->fetch()) {
                $errors['liberation_pdf'] = "Un document avec ce nom existe déjà. Veuillez le renommer.";
            }
        }

        if (!empty($errors)) {
            echo json_encode(['ok' => false, 'errors' => $errors]);
            exit;
        }
        // -------------------

        $montant_raw = $_POST['montant_libere'] ?? '';
        $montant     = !empty($montant_raw) ? floatval(str_replace([' ', ','], ['', '.'], $montant_raw)) : 0;

        // Insertion
        $sqlLib = "INSERT INTO liberation (num_liberation, montant_libere, date_liberation, garantie_soumissionID, type_liberationID, utilisateurID) 
                    VALUES (?, ?, ?, ?, ?, ?)";
        $stmtLib = $pdo->prepare($sqlLib);
        $stmtLib->execute([$num_lib, $montant, $date_lib, $garantie_id, $type_libID, (int)$_SESSION['user_id']]);
        $lib_id = $pdo->lastInsertId();

        // Mise à jour Statut Garantie -> "Libérée" (ID 3) si TOTALE (Suppose ID Type 1 = Totale)
        // Vous pouvez ajuster cette logique pour ne changer le statut que si c'est une libération Totale
        if ($type_libID == 1) { 
            $sqlStatut = "UPDATE garantie_soumission SET statutID = 3 WHERE id = ?";
            $pdo->prepare($sqlStatut)->execute([$garantie_id]);
        }

        // PDF (Type 3 = Libération)
        if (isset($_FILES['liberation_pdf']) && $_FILES['liberation_pdf']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/liberations/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            $filename = $_FILES['liberation_pdf']['name'];
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            $new_name = 'LIB_' . $lib_id . '_' . time() . '.' . $extension;
            $target_file = $upload_dir . $new_name;

            if (move_uploaded_file($_FILES['liberation_pdf']['tmp_name'], $target_file)) {
                $sqlDoc = "INSERT INTO document (code, nom_document, chemin_access, garantie_soumissionID, type_documentID) VALUES (?, ?, ?, ?, 3)";
                $docCode = 'DLIB_' . $lib_id . '_' . time();
                $pdo->prepare($sqlDoc)->execute([$docCode, $filename, $target_file, $garantie_id]);
                $doc_id = $pdo->lastInsertId();
                $pdo->prepare("INSERT INTO document_liberation (documentID, liberationID) VALUES (?, ?)")->execute([$doc_id, $lib_id]);
            }
        }

        $pdo->commit();
        echo json_encode(['ok' => true]);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo json_encode(['ok' => false, 'message' => $e->getMessage()]);
    }
    exit;
    break;

case 'delete_liberation':
    // Logique standard de suppression (similaire à authentification)
    $id = intval($_POST['id'] ?? 0);
    try {
        $pdo->prepare("DELETE FROM liberation WHERE id = ?")->execute([$id]);
        // NOTE : Les triggers ou la logique SQL doivent gérer le nettoyage des documents si nécessaire
        echo json_encode(['ok' => true]);
    } catch (Exception $e) {
        echo json_encode(['ok' => false, 'message' => "Erreur suppression"]);
    }
    exit;
    
    break;
    header('Content-Type: application/json');
    try {
        $pdo->beginTransaction();

        $garantie_id = intval($_POST['garantie_soumissionID']);
        $num_lib     = trim($_POST['num_liberation'] ?? '');
        $date_lib    = $_POST['date_liberation'];
        $type_libID  = intval($_POST['type_liberationID']);
        
        $montant_raw = $_POST['montant_libere'] ?? '';
        $montant     = !empty($montant_raw) ? floatval(str_replace([' ', ','], ['', '.'], $montant_raw)) : 0;

        // 1. Insertion dans la table liberation
        $sqlLib = "INSERT INTO liberation (num_liberation, montant_libere, date_liberation, garantie_soumissionID, type_liberationID, utilisateurID) 
                    VALUES (?, ?, ?, ?, ?, ?)";
        $stmtLib = $pdo->prepare($sqlLib);
        $stmtLib->execute([$num_lib, $montant, $date_lib, $garantie_id, $type_libID, (int)$_SESSION['user_id']]);
        $lib_id = $pdo->lastInsertId();

        // 2. Mise à jour du statut de la Garantie à "Libérée" (statutID = 3 selon votre BDD)
        $sqlStatut = "UPDATE garantie_soumission SET statutID = 3 WHERE id = ?";
        $pdo->prepare($sqlStatut)->execute([$garantie_id]);

        // 3. Gestion du Fichier PDF
        if (isset($_FILES['liberation_pdf']) && $_FILES['liberation_pdf']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/liberations/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

            $filename = $_FILES['liberation_pdf']['name'];
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            $new_name = 'LIB_' . $lib_id . '_' . time() . '.' . $extension;
            $target_file = $upload_dir . $new_name;

            if (move_uploaded_file($_FILES['liberation_pdf']['tmp_name'], $target_file)) {
                // Type 3 = LIBERATION (selon votre table type_document)
                $sqlDoc = "INSERT INTO document (code, nom_document, chemin_access, garantie_soumissionID, type_documentID) 
                           VALUES (?, ?, ?, ?, ?)";
                $stmtDoc = $pdo->prepare($sqlDoc);
                $docCode = 'DOC_LIB_' . $lib_id;
                $stmtDoc->execute([$docCode, $filename, $target_file, $garantie_id, 3]);
                $document_id = $pdo->lastInsertId();

                // Liaison document_liberation
                $sqlLink = "INSERT INTO document_liberation (documentID, liberationID) VALUES (?, ?)";
                $pdo->prepare($sqlLink)->execute([$document_id, $lib_id]);
            }
        }

        $pdo->commit();
        echo json_encode(['ok' => true]);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo json_encode(['ok' => false, 'message' => $e->getMessage()]);
    }
    exit;
    break;

case 'delete_authentification':
        header('Content-Type: application/json');
        $id = intval($_POST['id'] ?? 0);

        if ($id <= 0) {
            echo json_encode(['ok' => false, 'message' => "ID d'authentification invalide."]);
            exit;
        }

        try {
            $pdo->beginTransaction();

            // 1. Récupérer les informations du document associé pour suppression physique
            $sqlDoc = "SELECT d.id, d.chemin_access 
                       FROM document d
                       JOIN document_authentification da ON d.id = da.documentID
                       WHERE da.authentificationID = ?";
            $stmtDoc = $pdo->prepare($sqlDoc);
            $stmtDoc->execute([$id]);
            $document = $stmtDoc->fetch(PDO::FETCH_ASSOC);

            if ($document) {
                // suppression du fichier sur le disque
                if (file_exists($document['chemin_access'])) {
                    unlink($document['chemin_access']);
                }

                // suppression du lien de document
                $pdo->prepare("DELETE FROM document_authentification WHERE authentificationID = ?")->execute([$id]);
                
                // suppression de l'entrée dans la table document
                $pdo->prepare("DELETE FROM document WHERE id = ?")->execute([$document['id']]);
            }

            // 2. Suppression de l'entrée dans la table authentification
            $stmtDelete = $pdo->prepare("DELETE FROM authentification WHERE id = ?");
            $stmtDelete->execute([$id]);

            $pdo->commit();
            echo json_encode(['ok' => true]);

        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            echo json_encode(['ok' => false, 'message' => "Erreur lors de la suppression : " . $e->getMessage()]);
        }
        exit;
        break;


        default:
            $_SESSION['error'] = 'Type de formulaire non reconnu';
            header('Location: index.php');
            exit;
    }





?>
