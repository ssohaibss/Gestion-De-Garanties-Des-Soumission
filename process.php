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
        //                                          fournisseur

   case 'fournisseur':
    $id = intval($_POST['id'] ?? 0);
    $is_update = ($id > 0);

    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');
    $paysID = $_POST['pays'] ?? null;
    $digits = preg_replace('/[^0-9]/', '', $_POST['telephone'] ?? '');
    $telephone = '+' . $digits;

    $errors = [];

    // Validations (need to get update to any domaine)
    if (strlen($nom) < 3) $errors['nom'] = "Le nom d'entreprise doit faire au moins 3 caractères.";
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "Email invalide.";
        } elseif (!str_ends_with(strtolower($email), 'gmail.com')) {
            $errors['email'] = "L'email doit se terminer par @gmail.com";
        };
    if (strlen($digits) < 9 ) $errors['telephone'] = "Numéro trop court (min. 12 chiffres).";
    if (strlen($digits) >15 ) $errors['telephone'] = "Numéro trop long (max. 15 chiffres).";
    if (empty($adresse)) $errors['adresse'] = "L'adresse est requise.";
    if (empty($paysID)) $errors['pays'] = "Le pays est requis.";
    if (empty($email)) $errors['email'] = "L'email est requis.";
    if (!preg_match('/^[a-zA-Z\s]+$/', $nom)) $errors['nom'] = "Le nom ne doit contenir que des lettres et des espaces.";

    // Vérification Doublons (Email, Nom, Tel)
    $sql_check = "SELECT nom_entreprise, email, telephone FROM soumissionnaire WHERE (nom_entreprise = ? OR email = ? OR telephone = ?)";
    if ($is_update) $sql_check .= " AND id != $id";

    $stmt = $pdo->prepare($sql_check);
    $stmt->execute([$nom, $email, $telephone]);
    while ($row = $stmt->fetch()) {
        if ($row['nom_entreprise'] === $nom) $errors['nom'] = "Ce nom d'entreprise existe déjà.";
        if ($row['email'] === $email) $errors['email'] = "Cet email est déjà utilisé.";
        if ($row['telephone'] === $telephone) $errors['telephone'] = "Ce numéro est déjà enregistré.";
    }

    if (!empty($errors)) {
        echo json_encode(['ok' => false, 'errors' => $errors]);
        exit;
    }

    try {
        if ($is_update) {
            $sql = "UPDATE soumissionnaire SET nom_entreprise=?, adresse=?, telephone=?, email=?, paysID=? WHERE id=?";
            $pdo->prepare($sql)->execute([$nom, $adresse, $telephone, $email, $paysID, $id]);
            $_SESSION['success'] = "Fournisseur mis à jour.";
        } else {
            $sql = "INSERT INTO soumissionnaire (nom_entreprise, adresse, telephone, email, paysID) VALUES (?, ?, ?, ?, ?)";
            $pdo->prepare($sql)->execute([$nom, $adresse, $telephone, $email, $paysID]);
            $_SESSION['success'] = "Fournisseur ajouté.";
        }
        echo json_encode(['ok' => true]);
    } catch (PDOException $e) {
        echo json_encode(['ok' => false, 'message' => "Erreur technique SQL."]);
    }
    exit;

case 'delete_fournisseur':
    $id = intval($_POST['id'] ?? 0);
    if ($pdo->prepare("DELETE FROM soumissionnaire WHERE id = ?")->execute([$id])) {
        $_SESSION['success'] = "Fournisseur supprimé.";
        echo json_encode(['ok' => true]);
    } else {
        echo json_encode(['ok' => false, 'message' => "Erreur lors de la suppression."]);
    }
    exit;


                                                         // PAYS
case 'pays':
    $id = intval($_POST['id'] ?? 0);
    $is_update = ($id > 0);
    $nom  = trim($_POST['nom'] ?? '');
    // On garde le code sans espaces et en majuscules
    $code = strtoupper(trim(str_replace(' ', '', $_POST['code_pays'] ?? '')));

    $errors = [];

    if (strlen($nom) < 2) $errors['nom'] = "Le nom est trop court.";
    if (strlen($nom) > 15) $errors['nom'] = "Le nom est trop long (max. 15 caractères).";
    if (strlen($code) < 2) $errors['code_pays'] = "Le code ISO doit faire 2 ou 3 caractères.";
    if (empty($nom)) $errors['nom'] = "Le nom est requis.";
    if (empty($code)) $errors['code_pays'] = "Le code pays est requis.";

    // Vérification Doublons (Utilisation de LOWER pour ignorer la casse en PHP)
    $sql_check = "SELECT nom, code_pays FROM pays WHERE (LOWER(nom) = LOWER(?) OR code_pays = ?)";
    if ($is_update) $sql_check .= " AND id != $id";
    
    $stmt = $pdo->prepare($sql_check);
    $stmt->execute([$nom, $code]);
    while ($row = $stmt->fetch()) {
        // On compare en minuscule pour être sûr de détecter "france" vs "France"
        if (strtolower($row['nom']) === strtolower($nom)) $errors['nom'] = "Ce pays existe déjà (doublon de nom).";
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
        // En cas d'erreur, on renvoie le message SQL précis pour déboguer
        echo json_encode(['ok' => false, 'message' => 'Erreur SQL : ' . $e->getMessage()]);
    }
    exit;

case 'delete_pays':
    $id = intval($_POST['id'] ?? 0);
    // Optionnel : Vérifier si le pays est utilisé par un fournisseur avant de supprimer
    $check = $pdo->prepare("SELECT 1 FROM soumissionnaire WHERE paysID = ? LIMIT 1");
    $check->execute([$id]);
    if ($check->fetch()) {
        echo json_encode(['ok' => false, 'message' => 'Impossible de supprimer : ce pays est lié à des fournisseurs.']);
        exit;
    }

    if ($pdo->prepare("DELETE FROM pays WHERE id = ?")->execute([$id])) {
        $_SESSION['success'] = 'Pays supprimé avec succès';
        echo json_encode(['ok' => true]);
    } else {
        echo json_encode(['ok' => false, 'message' => 'Erreur lors de la suppression.']);
    }
    exit;
                                                         // DEVISE


    case 'devise':
        $id = intval($_POST['id'] ?? 0);
        $is_update = ($id > 0);
        $libelle = trim($_POST['libelle'] ?? '');
        $code    = strtoupper(trim(str_replace(' ', '', $_POST['code'] ?? '')));

        $errors = [];

        // 1. Validation de base
        if (strlen($code) !== 3) {
            $errors['code'] = "Le code doit faire exactement 3 lettres (ex: USD).";
        }
        if (empty($libelle)) {
            $errors['libelle'] = "Le libellé est requis.";
        }

        // 2. Vérification Doublons (Exclure l'ID actuel si modification)
        // On utilise LOWER() pour ignorer la casse (ex: Dollar = dollar)
        $sql_check = "SELECT libelle, code FROM devise WHERE (LOWER(libelle) = LOWER(?) OR code = ?)";
        if ($is_update) {
            $sql_check .= " AND id != $id";
        }
        
        $stmt = $pdo->prepare($sql_check);
        $stmt->execute([$libelle, $code]);
        
        while ($row = $stmt->fetch()) {
            if (strtolower($row['libelle']) === strtolower($libelle)) {
                $errors['libelle'] = "Ce nom de devise existe déjà.";
            }
            if ($row['code'] === $code) {
                $errors['code'] = "Ce code ISO existe déjà.";
            }
        }

        // Si erreurs détectées, on arrête et on renvoie les erreurs
        if (!empty($errors)) {
            echo json_encode(['ok' => false, 'errors' => $errors]);
            exit;
        }

        try {
            if ($is_update) {
                // MISE À JOUR
                $stmt = $pdo->prepare("UPDATE devise SET code = ?, libelle = ? WHERE id = ?");
                $stmt->execute([$code, $libelle, $id]);
            } else {
                // INSERTION
                $stmt = $pdo->prepare("INSERT INTO devise (code, libelle) VALUES (?, ?)");
                $stmt->execute([$code, $libelle]);
            }
            echo json_encode(['ok' => true]);
        } catch (PDOException $e) {
            echo json_encode(['ok' => false, 'message' => 'Erreur technique : ' . $e->getMessage()]);
        }
        exit;
        break;

    case 'delete_devise':
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['ok' => false, 'message' => 'ID invalide.']);
            exit;
        }

        try {
            $stmt = $pdo->prepare("DELETE FROM devise WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['ok' => true]);
        } catch (PDOException $e) {
            echo json_encode(['ok' => false, 'message' => 'Impossible de supprimer : la devise est peut-être liée à d\'autres données.']);
        }
        exit;
        break;


                                                     // STRUCTURE



case 'structure':
        $id = $_POST['id'] ?? '';
        $libelle = trim($_POST['libelle'] ?? '');
        
        // On récupère la saisie
        $code = $_POST['code'] ?? '';
        // NETTOYAGE : Supprime tout ce qui n'est pas une lettre ou un chiffre, puis met en MAJUSCULES
        $code = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $code));
        
        $errors = [];

        if (empty($libelle)) $errors['libelle'] = "Le libellé est requis.";
        if (empty($code)) $errors['code'] = "Le code est requis.";

        if (!empty($errors)) {
            echo json_encode(['ok' => false, 'errors' => $errors]);
            exit;
        }

        try {
            if ($id) {
                $stmt = $pdo->prepare("UPDATE structure SET libelle = ?, code = ? WHERE id = ?");
                $stmt->execute([$libelle, $code, $id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO structure (libelle, code) VALUES (?, ?)");
                $stmt->execute([$libelle, $code]);
            }
            echo json_encode(['ok' => true]);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                echo json_encode(['ok' => false, 'message' => "Erreur : Le code ou le libellé existe déjà."]);
            } else {
                echo json_encode(['ok' => false, 'message' => "Erreur base de données."]);
            }
        }
        break;

                                                     case 'delete_structure':
        $id = $_POST['id'] ?? '';
        if ($id) {
            try {
                $stmt = $pdo->prepare("DELETE FROM structure WHERE id = ?");
                $stmt->execute([$id]);
                echo json_encode(['ok' => true]);
            } catch (PDOException $e) {
                echo json_encode(['ok' => false, 'message' => "Impossible de supprimer : cette structure est liée à d'autres données."]);
            }
        }
        break;

                                                     // UTILISATEUR


    case 'user':
        $id = intval($_POST['id'] ?? 0);
        $is_update = ($id > 0);
        
        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        $role_id = $_POST['role'] ?? ''; 
        
        $errors = [];

        // Validations standards
        if (strlen($nom) < 3) $errors['nom'] = "Minimum 3 caractères.";
        if (strlen($prenom) < 3) $errors['prenom'] = "Minimum 3 caractères.";
        if (strlen($username) < 4) $errors['username'] = "Minimum 4 caractères.";
        if (empty($role_id)) $errors['role'] = "Sélectionnez un rôle.";
        
        // Validation Email @sonatrach.com
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "Email invalide.";
        } elseif (!str_ends_with(strtolower($email), '@sonatrach.com')) {
            $errors['email'] = "L'email doit se terminer par @sonatrach.com";
        }

        // Nouvelle Validation Password simplifiée (Maj, Min, Chiffre, Spécial, Min 8)
        if (!$is_update || !empty($password)) {
            $has_upper   = preg_match('@[A-Z]@', $password);
            $has_lower   = preg_match('@[a-z]@', $password);
            $has_number  = preg_match('@[0-9]@', $password);
            $has_special = preg_match('@[^\w]@', $password); // Tout ce qui n'est pas lettre ou chiffre

            if (!$has_upper || !$has_lower || !$has_number || !$has_special || strlen($password) < 8) {
                $errors['password'] = "Format requis: 8 caractères min, une majuscule, une minuscule, un chiffre et un symbole (ex: Sntr@2025)";
            }
        }

        // Vérification doublons
        $sql_check = "SELECT email, username FROM utilisateur WHERE (email = ? OR username = ?)";
        if ($is_update) $sql_check .= " AND id != $id";
        
        $stmt = $pdo->prepare($sql_check);
        $stmt->execute([$email, $username]);
        while ($row = $stmt->fetch()) {
            if ($row['email'] === $email) $errors['email'] = "Cet email appartient déjà à un autre compte.";
            if ($row['username'] === $username) $errors['username'] = "Ce login est déjà pris.";
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
    $id_to_delete = intval($_POST['id'] ?? 0);
    $current_logged_id = intval($_SESSION['user_id'] ?? 0); // Récupère l'ID de la session

    // PROTECTION 1 : Empêcher l'auto-suppression
    if ($id_to_delete === $current_logged_id ) {
        echo json_encode(['ok' => false, 'message' => "Sécurité : Vous ne pouvez pas supprimer le compte avec lequel vous êtes connecté."]);
        exit;
    }

    // PROTECTION 2 : Empêcher la suppression du dernier administrateur (Optionnel)
    $stmtCheck = $pdo->prepare("SELECT roleID FROM utilisateur WHERE id = ?");
    $stmtCheck->execute([$id_to_delete]);
    $user_to_delete = $stmtCheck->fetch();

    if ($user_to_delete && $user_to_delete['roleID'] == 1) { // On suppose que 1 est l'ID Admin
        $countAdmins = $pdo->query("SELECT COUNT(*) FROM utilisateur WHERE roleID = 1")->fetchColumn();
        if ($countAdmins <= 1) {
            echo json_encode(['ok' => false, 'message' => "Action interdite : Il doit rester au moins un administrateur."]);
            exit;
        }
    }

    if ($id_to_delete > 0) {
        $pdo->prepare("DELETE FROM utilisateur WHERE id = ?")->execute([$id_to_delete]);
        echo json_encode(['ok' => true]);
    } else {
        echo json_encode(['ok' => false, 'message' => "ID invalide"]);
    }
    exit;
        break;


                                                     // APPEL OFFRE


case 'appel_offre':
    case 'update_appel_offre':
        $is_update = ($form_type === 'update_appel_offre');
        $id        = intval($_POST['id'] ?? 0);

        // --- NETTOYAGE DU NUMÉRO (Autorise / - et espace) ---
        $num_ao = strtoupper(trim($_POST['numero_ao'] ?? ''));
        $num_ao = preg_replace('/[^a-zA-Z0-9\/ \-]/', '', $num_ao);
        
        $date_em = $_POST['date_emission'] ?? '';
        
        // Nettoyage montant (enlève espaces et remplace virgule par point)
        $raw_montant = $_POST['montant'] ?? '0';
        $clean_montant = str_replace([' ', ','], ['', '.'], $raw_montant);
        $montant = floatval($clean_montant);
        
        $deviseID = !empty($_POST['deviseID']) ? $_POST['deviseID'] : null;
        $errors = [];
        $today = date('Y-m-d');
        $annee_act = date('Y');

        // --- VALIDATIONS ---
        if (empty($num_ao)) {$errors['numero_ao'] = "Numéro obligatoire.";}
        if (empty($montant)) {$errors['montant'] = "Montant obligatoire.";}
        if (empty($date_em)) {
            $errors['date_emission'] = "Date obligatoire.";
        } else {
            $annee_saisie = date('Y', strtotime($date_em));
            if ($date_em > $today) {
                $errors['date_emission'] = "Date dans le futur interdite.";
            } elseif ($annee_saisie < ($annee_act - 1)) {
                $errors['date_emission'] = "Date trop ancienne.";
            }
        }

        if (!$deviseID) {
            $errors['deviseID'] = "Sélectionnez une devise.";
        } else {
            $stmtD = $pdo->prepare("SELECT code FROM devise WHERE Id = ?");
            $stmtD->execute([$deviseID]);
            $code_devise = $stmtD->fetchColumn();
            
            // Règles de montant minimum selon la devise
            if ($code_devise === 'DZD' && $montant < 200000) {
                $errors['montant'] = "Minimum 200 000 DZD.";
            } elseif ($code_devise !== 'DZD' && $montant < 1400) {
                $errors['montant'] = "Montant trop bas pour cette devise.";
            }
        }

        // --- EXÉCUTION SQL ---
        if (empty($errors)) {
            try {
                if ($is_update) {
                    $sql = "UPDATE appel_offre SET num_app_offre = ?, date_emission = ?, montant = ?, deviseID = ? WHERE id = ?";
                    $pdo->prepare($sql)->execute([$num_ao, $date_em, $montant, $deviseID, $id]);
                } else {
                    $sql = "INSERT INTO appel_offre (num_app_offre, date_emission, montant, deviseID) VALUES (?, ?, ?, ?)";
                    $pdo->prepare($sql)->execute([$num_ao, $date_em, $montant, $deviseID]);
                }
                echo json_encode(['ok' => true]);
            } catch (Exception $e) {
                echo json_encode(['ok' => false, 'message' => "Erreur base de données : " . $e->getMessage()]);
            }
        } else {
            echo json_encode(['ok' => false, 'errors' => $errors]);
        }
        exit;
    break;

    // --- CAS : SUPPRESSION APPEL D'OFFRE ---
    case 'delete_appel_offre':
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $stmt = $pdo->prepare("DELETE FROM appel_offre WHERE id = ?");
                $stmt->execute([$id]);
                echo json_encode(['ok' => true]);
            } catch (Exception $e) {
                echo json_encode(['ok' => false, 'message' => "Erreur lors de la suppression."]);
            }
        } else {
            echo json_encode(['ok' => false, 'message' => "ID invalide."]);
        }
        exit;
    break;            

                                                     // BANQUE


      case 'banque':
    $id = intval($_POST['id'] ?? 0);
    $is_update = ($id > 0);
    $code = strtoupper(trim($_POST['code'] ?? ''));
    $nom  = trim($_POST['nom_banque'] ?? '');
    $errors = [];

    // Validation
    if ($code === '') $errors['code'] = 'Le code est obligatoire.';
    if ($nom === '') $errors['nom_banque'] = 'Le nom est obligatoire.';
    if (strlen($code) > 5) $errors['code'] = "Le code banque est trop long.";
    if (strlen($code) < 3) $errors['code'] = "Le code banque est trop court.";
    if (empty($code)) $errors['code'] = 'Le code est requis.';

    // Unicité
    $sql_check = "SELECT code, nom_banque FROM banque WHERE (code = ? OR nom_banque = ?)";
    if ($is_update) $sql_check .= " AND id != $id";
    
    $stmt = $pdo->prepare($sql_check);
    $stmt->execute([$code, $nom]);
    while ($row = $stmt->fetch()) {
        if ($row['code'] === $code) $errors['code'] = 'Ce code existe déjà.';
        if ($row['nom_banque'] === $nom) $errors['nom_banque'] = 'Ce nom existe déjà.';
    }

    if (!empty($errors)) {
        echo json_encode(['ok' => false, 'errors' => $errors]);
        exit;
    }

    try {
        if ($is_update) {
            $stmt = $pdo->prepare("UPDATE banque SET code = ?, nom_banque = ? WHERE id = ?");
            $stmt->execute([$code, $nom, $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO banque (code, nom_banque) VALUES (?, ?)");
            $stmt->execute([$code, $nom]);
        }
        echo json_encode(['ok' => true]);
    } catch (PDOException $e) {
        echo json_encode(['ok' => false, 'message' => 'Erreur technique.']);
    }
    exit;

case 'delete_banque':
    $id = intval($_POST['id'] ?? 0);
    if ($pdo->prepare("DELETE FROM banque WHERE id = ?")->execute([$id])) {
        echo json_encode(['ok' => true]);
    } else {
        echo json_encode(['ok' => false, 'message' => "Erreur de suppression."]);
    }
    exit;


                                                     // AGENCE


      case 'agence':
    $id = intval($_POST['id'] ?? 0);
    $is_update = ($id > 0);
    $code = strtoupper(trim($_POST['code'] ?? ''));
    $nom = trim($_POST['nom'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');
    $banqueID = $_POST['banqueID'] ?? '';
    $errors = [];

    // Validation
    if ($code === '') $errors['code'] = 'Le code agence est obligatoire.';
    if ($nom === '') $errors['nom'] = 'Le nom de l\'agence est obligatoire.';
    if ($adresse === '') $errors['adresse'] = 'L\'adresse est obligatoire.';
    if ($banqueID === '') $errors['banqueID'] = 'Veuillez choisir une banque.';

    if (empty($errors)) {
        // Unicité du Code
        $sql_code = "SELECT 1 FROM agence WHERE code = ?";
        if($is_update) $sql_code .= " AND id != $id";
        $stmt = $pdo->prepare($sql_code);
        $stmt->execute([$code]);
        if ($stmt->fetch()) $errors['code'] = 'Ce code agence existe déjà.';

        // Unicité ABSOLUE du Nom (comme demandé)
        $sql_nom = "SELECT 1 FROM agence WHERE nom = ?";
        if($is_update) $sql_nom .= " AND id != $id";
        $stmt = $pdo->prepare($sql_nom);
        $stmt->execute([$nom]);
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
        echo json_encode(['ok' => false, 'message' => 'Erreur technique serveur.']);
    }
    exit;

    case 'delete_agence':
    $id = intval($_POST['id'] ?? 0);
    if ($pdo->prepare("DELETE FROM agence WHERE id = ?")->execute([$id])) {
        echo json_encode(['ok' => true]);
    } else {
        echo json_encode(['ok' => false, 'message' => 'Erreur lors de la suppression en base de données.']);
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
    $fournisseurID = $_POST['soumissionnaireID'] ?? '';
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
    if (empty($fournisseurID)) $errors['soumissionnaireID'] = "Le soumissionnaire est obligatoire.";
    if (empty($agenceID)) $errors['agenceID'] = "L'agence est obligatoire.";
    if (empty($structureID)) $errors['structureID'] = "La structure est obligatoire.";
    if (empty($statutID)) $errors['statutID'] = "Le statut est obligatoire.";
    if (empty($banqueID)) $errors['banqueID'] = "La banque est obligatoire.";
    if (empty($aoID)) $errors['appel_offreID'] = "L'appel d'offre est obligatoire.";
    if (empty($montant) || $montant <= 0) $errors['montant_garantie'] = "Montant valide requis.";
    

    // Dates
    if (empty($date_e)) $errors['date_emission'] = "Date d'émission requise.";
    elseif ($date_e > $today) $errors['date_emission'] = "La date ne peut pas être future.";

    if (empty($date_x)) $errors['date_expiration'] = "Date d'expiration requise.";
    elseif ($date_x <= $date_e) $errors['date_expiration'] = "Doit être strictement après l'émission.";
    
    // Contrainte Statut / Expiration (Si Active alors non expirée)
    // On suppose ici que ID 1 = Active (à adapter selon ta table statut)
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
        $params = [
            $num_garantie, $montant, $date_e, $date_x, 
            $fournisseurID, $agenceID, $deviseID, $structureID, 
            $aoID, $statutID, (int)$_SESSION['user_id']
        ];

        if ($id) {
            $sql = "UPDATE garantie_soumission SET num_garantie=?, montant_garantie=?, date_emission=?, date_expiration=?, soumissionnaireID=?, agenceID=?, deviseID=?, structureID=?, appel_offreID=?, statutID=?, utilisateurID=? WHERE id=?";
            $params[] = $id;
        } else {
            $sql = "INSERT INTO garantie_soumission (num_garantie, montant_garantie, date_emission, date_expiration, soumissionnaireID, agenceID, deviseID, structureID, appel_offreID, statutID, utilisateurID) VALUES (?,?,?,?,?,?,?,?,?,?,?)";
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        echo json_encode(['ok' => true]);
    } catch (Exception $e) {
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
            
        default:
            $_SESSION['error'] = 'Type de formulaire non reconnu';
            header('Location: index.php');
            exit;
    }





?>
