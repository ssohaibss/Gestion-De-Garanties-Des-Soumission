<?php
session_start();
require_once 'database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $form_type = $_POST['form_type'] ?? '';
    
    
    switch($form_type) {
        case 'fournisseur':
            $nom = $_POST['nom'] ?? '';
            $code = $_POST['code'] ?? '';
            $email = $_POST['email'] ?? '';
            $telephone = $_POST['telephone'] ?? '';
            $adresse = $_POST['adresse'] ?? '';
            $pays = $_POST['pays'] ?? '';
            
            $stmt = $pdo->prepare("INSERT INTO soumissionnaire (nom_entreprise, adresse, telephone, email, paysID) VALUES (?, ?, ?, ?, ?)");
            
            if ($stmt->execute([$nom, $adresse, $telephone, $email, $pays])) {
                $_SESSION['success'] = 'Fournisseur ajouté avec succès';
            } else {
                $_SESSION['error'] = 'Erreur lors de l\'ajout: ' . $stmt->errorInfo()[2];
            }
            header('Location: index.php?page=fournisseur');
            exit;
            break;
            
        case 'pays':
        $nom_pays = $_POST['nom'] ?? '';
        $code_pays = strtoupper($_POST['code_pays'] ?? ''); // Force uppercase for ISO codes

        try {
        $stmt = $pdo->prepare("INSERT INTO pays (Nom, code_pays) VALUES (?, ?)");
        $stmt->execute([$nom_pays, $code_pays]);
        
        $_SESSION['success'] = 'Pays ajouté avec succès';
        } catch (PDOException $e) {
        // Check if the error code is 23000 (MySQL code for Integrity Constraint Violation)
        if ($e->getCode() == 23000) {
            $_SESSION['error'] = "Erreur : Le pays '$nom_pays' ou le code '$code_pays' existe déjà.";
        } else {
            // Log other types of errors (connection, syntax, etc.)
            $_SESSION['error'] = "Erreur lors de l'ajout : " . $e->getMessage();
        }
        }
    
        header('Location: index.php?page=pays');
        exit;
         break;
            
        case 'devise':
            $code_devise = $_POST['code'] ?? '';
            $libelle = $_POST['libelle'] ?? '';
            
            $stmt = $pdo->prepare("INSERT INTO devise (code, libelle) VALUES (?, ?)");
            
            if ($stmt->execute([$code_devise, $libelle])) {
                $_SESSION['success'] = 'Devise ajoutée avec succès';
            } else {
                $_SESSION['error'] = 'Erreur lors de l\'ajout: ' . $stmt->errorInfo()[2];
            }
            header('Location: index.php?page=devise');
            exit;
            break;
            
        case 'structure':
            $code_structure = $_POST['code'] ?? '';
            $libelle = $_POST['libelle'] ?? '';
            
            $stmt = $pdo->prepare("INSERT INTO structure (code, libelle) VALUES (?, ?)");
            
            if ($stmt->execute([$code_structure, $libelle])) {
                $_SESSION['success'] = 'Structure ajoutée avec succès';
            } else {
                $_SESSION['error'] = 'Erreur lors de l\'ajout: ' . $stmt->errorInfo()[2];
            }
            header('Location: index.php?page=structure');
            exit;
            break;
        
       case 'user':
    $nom = $_POST['nom'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $roleID = $_POST['role'] ?? null;

    // Hash the password before inserting
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO utilisateur (email, nom, mot_de_pass, roleID) VALUES (?, ?, ?, ?)");

    if ($stmt->execute([$email, $nom, $hashedPassword, $roleID])) {
        $_SESSION['success'] = 'Utilisateur ajouté avec succès';
    } else {
        $_SESSION['error'] = 'Erreur lors de l\'ajout: ' . $stmt->errorInfo()[2];
    }
    header('Location: index.php?page=user');
    exit;
    break;

            
        case 'appelle_offre':
            $num_app_offre = $_POST['numero_ao'] ?? '';
            
            $stmt = $pdo->prepare("INSERT INTO appel_offre (num_app_offre) VALUES (?)");
            
            if ($stmt->execute([$num_app_offre])) {
                $_SESSION['success'] = 'Appel d\'offre ajouté avec succès';
            } else {
                $_SESSION['error'] = 'Erreur lors de l\'ajout: ' . $stmt->errorInfo()[2];
            }
            header('Location: index.php?page=appelle-offre');
            exit;
            break;
            
        case 'banque':
            $code_banque = $_POST['code'] ?? '';
            $nom_banque = $_POST['nom_banque'] ?? '';
            
            $stmt = $pdo->prepare("INSERT INTO banque (code, nom_banque) VALUES (?, ?)");
            
            if ($stmt->execute([$code_banque, $nom_banque])) {
                $_SESSION['success'] = 'Banque ajoutée avec succès';
            } else {
                $_SESSION['error'] = 'Erreur lors de l\'ajout: ' . $stmt->errorInfo()[2];
            }
            header('Location: index.php?page=banque');
            exit;
            break;

       case 'agence':
            $code_agence = $_POST['code'] ?? '';
            $nom_agence = $_POST['nom'] ?? '';
            $adresse = $_POST['adresse'] ?? '';
            $banqueID = $_POST['banqueID'] ?? '';

            $stmt = $pdo->prepare("INSERT INTO agence (code, nom, adresse, banqueID) VALUES (?, ?, ?, ?)");

            if ($stmt->execute([$code_agence, $nom_agence, $adresse, $banqueID])) {
                $_SESSION['success'] = 'Agence ajoutée avec succès';
            } else {
                $_SESSION['error'] = 'Erreur lors de l\'ajout : ' . $stmt->errorInfo()[2];
            }

            header('Location: index.php?page=agence');
            exit;
            break;

   case 'garantie':
    function nullIfEmpty($value) {
        return ($value === '' || $value === null) ? null : $value;
    }

    // Cast to proper types based on database schema
    $num_garantie = (int) $_POST['num_garantie'];
    $montant_garantie = (float) $_POST['montant_garantie'];
    $date_emission = $_POST['date_emission'];
    $date_expiration = $_POST['date_expiration'];
    // Date validation
if (strtotime($date_expiration) < strtotime($date_emission)) {
    $_SESSION['error'] = 'La date d\'expiration ne peut pas être antérieure à la date d\'émission';
    header('Location: index.php?page=garantie');
    exit;
}

    $soumissionnaireID = nullIfEmpty($_POST['soumissionnaireID'] ?? null);
    $agenceID = nullIfEmpty($_POST['agenceID'] ?? null);
    $deviseID = nullIfEmpty($_POST['deviseID'] ?? null);
    $structureID = nullIfEmpty($_POST['structureID'] ?? null);
    $appel_offreID = nullIfEmpty($_POST['appel_offreID'] ?? null);
    $statutID = nullIfEmpty($_POST['statutID'] ?? null);

    // Fixed: removed extra dollar sign
    $utilisateurID = (int) $_SESSION['user_id'];

    // Validation
    if (!$agenceID || !$deviseID || !$statutID) {
        $_SESSION['error'] = 'Champs obligatoires manquants (Agence, Devise, Statut)';
        header('Location: index.php?page=garantie');
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO garantie_soumission 
        (num_garantie, montant_garantie, date_emission, date_expiration, 
         soumissionnaireID, agenceID, deviseID, structureID, 
         appel_offreID, statutID, utilisateurID) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
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
        $utilisateurID
    ])) {
        $_SESSION['success'] = 'Garantie ajoutée avec succès';
    } else {
        $_SESSION['error'] = 'Erreur SQL : ' . $stmt->errorInfo()[2];
    }

    header('Location: index.php?page=garantie');
    exit;
    break;
        









            
        default:
            $_SESSION['error'] = 'Type de formulaire non reconnu';
            header('Location: index.php');
            exit;
    }
    
} else {
    $_SESSION['error'] = 'Méthode non autorisée';
    header('Location: index.php');
    exit;
}





?>
