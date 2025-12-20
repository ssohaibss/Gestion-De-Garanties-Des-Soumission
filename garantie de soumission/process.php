<?php
session_start();
require_once 'database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $form_type = $_POST['form_type'] ?? '';


    switch ($form_type) {
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
            // 1. Capture and Clean
            $nom_pays = trim($_POST['nom'] ?? '');
            $code_pays = strtoupper(trim($_POST['code_pays'] ?? ''));

            // 2. Server-side empty check
            if (empty($nom_pays) || empty($code_pays)) {
                $_SESSION['error'] = "Tous les champs sont obligatoires.";
                header('Location: index.php?page=pays');
                exit;
            }

            try {
                $stmt = $pdo->prepare("INSERT INTO pays (Nom, code_pays) VALUES (?, ?)");
                $stmt->execute([$nom_pays, $code_pays]);
                $_SESSION['success'] = 'Pays ajouté avec succès';
            } catch (PDOException $e) {
                // Check for Unique Constraint Violation
                if ($e->getCode() == 23000) {
                    $_SESSION['error'] = "Erreur : Le pays '$nom_pays' ou le code '$code_pays' existe déjà.";
                } else {
                    $_SESSION['error'] = "Erreur base de données : " . $e->getMessage();
                }
            }
            header('Location: index.php?page=pays');
            exit;
            break;

        case 'devise':
            // 1. Capture and Clean
            $libelle = trim($_POST['libelle'] ?? '');
            $code_devise = strtoupper(trim($_POST['code'] ?? ''));

            // 2. Server-side empty check
            if (empty($libelle) || empty($code_devise)) {
                $_SESSION['error'] = "Tous les champs sont obligatoires.";
                header('Location: index.php?page=devise');
                exit;
            }

            try {
                $stmt = $pdo->prepare("INSERT INTO devise (code, libelle) VALUES (?, ?)");
                $stmt->execute([$code_devise, $libelle]);
                $_SESSION['success'] = 'Devise ajoutée avec succès';
            } catch (PDOException $e) {
                // Check for Unique Constraint Violation
                if ($e->getCode() == 23000) {
                    $_SESSION['error'] = "Erreur : La devise '$libelle' ou le code '$code_devise' existe déjà.";
                } else {
                    $_SESSION['error'] = "Erreur base de données : " . $e->getMessage();
                }
            }
            header('Location: index.php?page=devise');
            exit;
            break;

        case 'structure':
            $code = trim($_POST['code'] ?? '');
            $libelle = trim($_POST['libelle'] ?? '');

            if (empty($code) || empty($libelle)) {
                $_SESSION['error'] = "Tous les champs sont obligatoires.";
                header('Location: index.php?page=structure');
                exit;
            }

            try {
                $stmt = $pdo->prepare("INSERT INTO structure (code, libelle) VALUES (?, ?)");
                $stmt->execute([$code, $libelle]);
                $_SESSION['success'] = 'Structure ajoutée avec succès';
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $_SESSION['error'] = "Erreur : Ce code ou ce libellé existe déjà.";
                } else {
                    $_SESSION['error'] = "Erreur : " . $e->getMessage();
                }
            }
            header('Location: index.php?page=structure');
            exit;
            break;

    case 'user':
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $roleID = $_POST['role'] ?? null;

    if (empty($nom) || empty($email) || empty($password)) {
        $_SESSION['error'] = "Tous les champs sont obligatoires.";
        header('Location: index.php?page=user');
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    try {
        // Préparation de la requête
        $stmt = $pdo->prepare("INSERT INTO utilisateur (email, nom, mot_de_passe, roleID) VALUES (?, ?, ?, ?)");
        $stmt->execute([$email, $nom, $hashedPassword, $roleID]);
        
        $_SESSION['success'] = 'Utilisateur ajouté avec succès';

    } catch (PDOException $e) {
        // Code 23000 = Violation de contrainte (Doublon)
        if ($e->getCode() == 23000) {
            // On vérifie quel champ pose problème dans le message d'erreur
            if (str_contains($e->getMessage(), 'nom')) {
                $_SESSION['error'] = "Erreur : Le nom '$nom' est déjà utilisé.";
            } elseif (str_contains($e->getMessage(), 'email')) {
                $_SESSION['error'] = "Erreur : L'adresse email '$email' est déjà utilisée.";
            } else {
                $_SESSION['error'] = "Erreur : Cet utilisateur existe déjà.";
            }
        } else {
            $_SESSION['error'] = "Erreur technique : " . $e->getMessage();
        }
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
            // Nettoyage des données
            $code_banque = strtoupper(trim($_POST['code'] ?? '')); // Code souvent en majuscules
            $nom_banque = trim($_POST['nom_banque'] ?? '');

            // Vérification des champs vides
            if (empty($code_banque) || empty($nom_banque)) {
                $_SESSION['error'] = "Tous les champs sont obligatoires.";
                header('Location: index.php?page=banque');
                exit;
            }

            try {
                // Préparation et exécution
                $stmt = $pdo->prepare("INSERT INTO banque (code, nom_banque) VALUES (?, ?)");
                $stmt->execute([$code_banque, $nom_banque]);
                
                $_SESSION['success'] = "La banque '$nom_banque' a été ajoutée avec succès.";

            } catch (PDOException $e) {
                // Code d'erreur 23000 : Violation de contrainte d'unicité (Duplicate entry)
                if ($e->getCode() == 23000) {
                    // On vérifie quel champ est en cause pour personnaliser le message
                    if (str_contains($e->getMessage(), 'code')) {
                        $_SESSION['error'] = "Erreur : Le code banque '$code_banque' est déjà utilisé.";
                    } elseif (str_contains($e->getMessage(), 'nom_banque')) {
                        $_SESSION['error'] = "Erreur : Le nom de banque '$nom_banque' existe déjà.";
                    } else {
                        $_SESSION['error'] = "Erreur : Cette banque existe déjà dans la base de données.";
                    }
                } else {
                    // Autre erreur SQL
                    $_SESSION['error'] = "Une erreur technique est survenue : " . $e->getMessage();
                }
            }

            // Redirection vers la page banque
            header('Location: index.php?page=banque');
            exit;
            break;

   case 'agence':
    $code = strtoupper(trim($_POST['code'] ?? ''));
    $nom = trim($_POST['nom'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');
    $banqueID = $_POST['banqueID'] ?? null;

    if (empty($code) || empty($nom) || empty($banqueID)) {
        $_SESSION['error'] = "Champs obligatoires manquants.";
        header('Location: index.php?page=agence');
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO agence (code, nom, adresse, banqueID) VALUES (?, ?, ?, ?)");
        $stmt->execute([$code, $nom, $adresse, $banqueID]);
        $_SESSION['success'] = "Agence ajoutée !";
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $_SESSION['error'] = "Le code agence '$code' est déjà utilisé.";
        } else {
            $_SESSION['error'] = "Erreur SQL : " . $e->getMessage();
        }
    }
    header('Location: index.php?page=agence');
    exit;
    break;

        case 'garantie':
            function nullIfEmpty($value)
            {
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

            if (
                $stmt->execute([
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
                ])
            ) {
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