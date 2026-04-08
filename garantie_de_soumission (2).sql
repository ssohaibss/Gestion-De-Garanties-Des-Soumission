-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Apr 08, 2026 at 12:54 AM
-- Server version: 8.0.40
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `garantie_de_soumission`
--

-- --------------------------------------------------------

--
-- Table structure for table `agence`
--

CREATE TABLE `agence` (
  `id` int NOT NULL,
  `nom` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `adresse` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `banqueID` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `agence`
--

INSERT INTO `agence` (`id`, `nom`, `code`, `adresse`, `banqueID`) VALUES
(1, 'Agence Djenane El Malik', 'BEA-0011', 'Rue Djenane El Malik, Hydra, 16035 Alge', 2),
(2, 'Agence Hassi Messaoud', 'BEA-0072', 'Cité du 24 Février, BP 120, Hassi Messaoud, 30500 Ouargla', 2),
(3, 'Agence Arzew', 'BEA-0024', 'Zone Industrielle d\'Arzew, Route du Port, 31200 Oran', 2),
(4, 'Agence Alger Che Guevara', 'BNA-0415', '8, Boulevard Che Guevara, 16000 Alger', 1),
(5, 'Agence Ouargla Centre', 'BNA-0620', 'Avenue de la République, Centre-ville, 30000 Ouargla', 1),
(6, 'Agence Oran Soummam', 'BNA-0312', '04, Boulevard de la Soummam, 31000 Oran', 1),
(7, 'Agence Les Vergers', 'CPA-0105', 'P3F6+859, St Charles, Kouba, 16053 Alger', 3),
(8, 'Agence Hassi R\'Mel', 'CPA-0310', 'Base de Vie Sonatrach, Centre-ville, 03300 Laghouat', 3),
(9, 'Agence Oran Khemisti', 'CPA-0202', 'Rue Mohamed Khemisti, Centre-ville, 31000 Oran', 3);

-- --------------------------------------------------------

--
-- Table structure for table `amendement`
--

CREATE TABLE `amendement` (
  `id` int NOT NULL,
  `num_amendement` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `date_amendement` date NOT NULL,
  `nouveau_montant` decimal(15,2) DEFAULT NULL,
  `nouvelle_date_expiration` date DEFAULT NULL,
  `garantie_soumissionID` int NOT NULL,
  `type_amendementID` int NOT NULL,
  `utilisateurID` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `amendement`
--

INSERT INTO `amendement` (`id`, `num_amendement`, `date_amendement`, `nouveau_montant`, `nouvelle_date_expiration`, `garantie_soumissionID`, `type_amendementID`, `utilisateurID`) VALUES
(1, 'AM/2026/0001', '2026-03-27', 2000.00, '2026-05-25', 3, 3, 1);

-- --------------------------------------------------------

--
-- Table structure for table `appel_offre`
--

CREATE TABLE `appel_offre` (
  `id` int NOT NULL,
  `num_app_offre` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `date_emission` date NOT NULL,
  `deviseID` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appel_offre`
--

INSERT INTO `appel_offre` (`id`, `num_app_offre`, `date_emission`, `deviseID`) VALUES
(1, 'AO/2025/3524', '2025-09-15', 1),
(2, 'AO/2025/3525', '2025-11-20', 3),
(3, 'AO/2026/0001', '2026-01-20', 3),
(4, 'AO/2026/0002', '2026-02-15', 2),
(5, 'AO/2026/0003', '2026-03-10', 2);

-- --------------------------------------------------------

--
-- Table structure for table `authentification`
--

CREATE TABLE `authentification` (
  `id` int NOT NULL,
  `num_authentification` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `date_authentification` date NOT NULL,
  `date_saisie` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `garantie_soumissionID` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `authentification`
--

INSERT INTO `authentification` (`id`, `num_authentification`, `date_authentification`, `date_saisie`, `garantie_soumissionID`) VALUES
(1, 'AU/2026/0001', '2026-01-25', '2026-03-27 20:14:10', 3),
(2, 'AU/2026/0002', '2026-02-20', '2026-03-27 20:14:43', 4),
(3, 'AU/2026/0003', '2026-03-15', '2026-03-27 20:15:18', 5),
(4, 'AU/2026/0004', '2026-03-20', '2026-03-27 20:15:51', 7),
(5, 'AU/2026/0005', '2026-03-25', '2026-03-27 20:18:10', 8);

-- --------------------------------------------------------

--
-- Table structure for table `banque`
--

CREATE TABLE `banque` (
  `id` int NOT NULL,
  `code` varchar(34) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nom_banque` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `banque`
--

INSERT INTO `banque` (`id`, `code`, `nom_banque`) VALUES
(1, 'BNA', 'Banque Nationale d\'Algérie'),
(2, 'BEA', 'Banque Extérieure d\'Algérie'),
(3, 'CPA', 'Crédit Populaire d\'Algérie');

-- --------------------------------------------------------

--
-- Table structure for table `devise`
--

CREATE TABLE `devise` (
  `id` int NOT NULL,
  `code` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `libelle` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `devise`
--

INSERT INTO `devise` (`id`, `code`, `libelle`) VALUES
(1, 'DZD', 'Dinar Algérien'),
(2, 'USD', 'Dollar Américain'),
(3, 'EUR', 'Euro'),
(4, 'TLL', 'TURKEY');

-- --------------------------------------------------------

--
-- Table structure for table `document`
--

CREATE TABLE `document` (
  `id` int NOT NULL,
  `code` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nom_document` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `chemin_access` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `garantie_soumissionID` int NOT NULL,
  `type_documentID` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `document`
--

INSERT INTO `document` (`id`, `code`, `nom_document`, `chemin_access`, `garantie_soumissionID`, `type_documentID`) VALUES
(1, 'DOC_AUTH_1', 'sample-local-pdf - Copy (2).pdf', 'uploads/authentification/AUTH_1_1774642450.pdf', 3, 3),
(2, 'DOC_AUTH_2', 'sample-local-pdf - Copy (3).pdf', 'uploads/authentification/AUTH_2_1774642483.pdf', 4, 3),
(3, 'DOC_AUTH_3', 'sample-local-pdf - Copy (4).pdf', 'uploads/authentification/AUTH_3_1774642518.pdf', 5, 3),
(4, 'DOC_AUTH_4', 'sample-local-pdf - Copy (5).pdf', 'uploads/authentification/AUTH_4_1774642551.pdf', 7, 3),
(5, 'DOC_AUTH_5', 'sample-local-pdf - Copy (6).pdf', 'uploads/authentification/AUTH_5_1774642690.pdf', 8, 3),
(6, 'AMD_1_17746', 'sample-local-pdf - Copy (30).pdf', 'uploads/amendements/a_1_1774642870_251018f3.pdf', 3, 2),
(7, 'DLIB_1_1774', 'sample-local-pdf - Copy (27).pdf', 'uploads/liberations/LIB_1_1774643467.pdf', 3, 3);

-- --------------------------------------------------------

--
-- Table structure for table `document_amendement`
--

CREATE TABLE `document_amendement` (
  `documentID` int NOT NULL,
  `amendementID` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `document_amendement`
--

INSERT INTO `document_amendement` (`documentID`, `amendementID`) VALUES
(6, 1);

-- --------------------------------------------------------

--
-- Table structure for table `document_authentification`
--

CREATE TABLE `document_authentification` (
  `documentID` int NOT NULL,
  `authentificationID` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `document_authentification`
--

INSERT INTO `document_authentification` (`documentID`, `authentificationID`) VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 4),
(5, 5);

-- --------------------------------------------------------

--
-- Table structure for table `document_garantie_soumission`
--

CREATE TABLE `document_garantie_soumission` (
  `documentID` int NOT NULL,
  `garantie_soumissionID` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_liberation`
--

CREATE TABLE `document_liberation` (
  `documentID` int NOT NULL,
  `liberationID` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `document_liberation`
--

INSERT INTO `document_liberation` (`documentID`, `liberationID`) VALUES
(7, 1);

-- --------------------------------------------------------

--
-- Table structure for table `garantie_soumission`
--

CREATE TABLE `garantie_soumission` (
  `id` int NOT NULL,
  `num_garantie` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `montant_garantie` decimal(15,2) NOT NULL,
  `date_emission` date NOT NULL,
  `date_expiration` date NOT NULL,
  `soumissionnaireID` int NOT NULL,
  `agenceID` int NOT NULL,
  `deviseID` int NOT NULL,
  `structureID` int NOT NULL,
  `appel_offreID` int NOT NULL,
  `statutID` int NOT NULL,
  `utilisateurID` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `garantie_soumission`
--

INSERT INTO `garantie_soumission` (`id`, `num_garantie`, `montant_garantie`, `date_emission`, `date_expiration`, `soumissionnaireID`, `agenceID`, `deviseID`, `structureID`, `appel_offreID`, `statutID`, `utilisateurID`) VALUES
(3, 'GS/2026/0001', 20000.00, '2026-01-25', '2026-04-25', 4, 8, 3, 3, 3, 1, 1),
(4, 'GS/2026/0002', 12000.00, '2026-02-20', '2026-05-20', 4, 2, 2, 1, 4, 1, 1),
(5, 'GS/2026/0003', 35000.00, '2026-03-15', '2026-06-15', 2, 5, 2, 2, 5, 1, 1),
(7, 'GS/2026/0004', 25000.00, '2026-03-20', '2026-09-20', 5, 9, 2, 2, 5, 1, 1),
(8, 'GS/2026/0005', 1500000.00, '2026-03-25', '2026-06-25', 1, 4, 1, 1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `liberation`
--

CREATE TABLE `liberation` (
  `id` int NOT NULL,
  `num_liberation` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `montant_libere` decimal(15,2) NOT NULL,
  `date_liberation` date NOT NULL,
  `garantie_soumissionID` int NOT NULL,
  `type_liberationID` int NOT NULL,
  `utilisateurID` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `liberation`
--

INSERT INTO `liberation` (`id`, `num_liberation`, `montant_libere`, `date_liberation`, `garantie_soumissionID`, `type_liberationID`, `utilisateurID`) VALUES
(1, 'LB/2026/0001', 2000.00, '2026-03-27', 3, 2, 1);

-- --------------------------------------------------------

--
-- Table structure for table `pays`
--

CREATE TABLE `pays` (
  `id` int NOT NULL,
  `nom` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `code_pays` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pays`
--

INSERT INTO `pays` (`id`, `nom`, `code_pays`) VALUES
(1, 'Algérie', 'DZ'),
(2, 'France', 'FR'),
(3, 'États-Unis', 'US'),
(4, 'turkey', 'TK'),
(5, 'KOREA', 'KR');

-- --------------------------------------------------------

--
-- Table structure for table `role`
--

CREATE TABLE `role` (
  `id` int NOT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `libelle` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role`
--

INSERT INTO `role` (`id`, `code`, `libelle`) VALUES
(1, 'ADMIN', 'Administrateur'),
(2, 'USER', 'Agent');

-- --------------------------------------------------------

--
-- Table structure for table `soumissionnaire`
--

CREATE TABLE `soumissionnaire` (
  `id` int NOT NULL,
  `nom_entreprise` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `adresse` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `telephone` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `paysID` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `soumissionnaire`
--

INSERT INTO `soumissionnaire` (`id`, `nom_entreprise`, `adresse`, `telephone`, `email`, `paysID`) VALUES
(1, 'ETB TCE', 'Boumerdès', '+213555155777', 'salim@gmail.com', 1),
(2, 'SCP DRGC', 'Chéraga', '+21329732541', 'scp.drgc.dz@gmail.com', 1),
(3, 'ERL El-Nour Travaux', 'Cité 200 Logements, Bâtiment C, Local N°4, Laghouat', '+213661234567', 'elnour.travaux.dz@gmail.com', 1),
(4, 'Apex Drilling Energy Services LLC', '1250 Energy Corridor Blvd, Suite 400, Houston, TX 77079, USA', '+17135550184', 'tenders@apexenergy-usa.com', 3),
(5, 'HydroTech Solutions France', '15 Place de la Défense, 92400 Courbevoie, France', '+33145678900', 'export.mena@hydrotech-france.fr', 2);

-- --------------------------------------------------------

--
-- Table structure for table `statut`
--

CREATE TABLE `statut` (
  `id` int NOT NULL,
  `code` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `libelle` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `statut`
--

INSERT INTO `statut` (`id`, `code`, `libelle`) VALUES
(1, 'ACTIF', 'Active'),
(2, 'EXPIRE', 'Expirée'),
(3, 'LIBERE', 'Libérée'),
(4, 'A_LIBERER', 'À libérer');

-- --------------------------------------------------------

--
-- Table structure for table `structure`
--

CREATE TABLE `structure` (
  `id` int NOT NULL,
  `code` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `libelle` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `structure`
--

INSERT INTO `structure` (`id`, `code`, `libelle`) VALUES
(1, 'AP', 'Administration du Personnel'),
(2, 'TI', 'Technologies de l\'Information'),
(3, 'DRH', 'Direction Ressources Humaines');

-- --------------------------------------------------------

--
-- Table structure for table `type_amendement`
--

CREATE TABLE `type_amendement` (
  `id` int NOT NULL,
  `code` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `libelle` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `type_amendement`
--

INSERT INTO `type_amendement` (`id`, `code`, `libelle`) VALUES
(1, 'MONTANT', 'Modification du montant'),
(2, 'DATE', 'Prolongation de date'),
(3, 'MIXTE', 'Modification montant et date');

-- --------------------------------------------------------

--
-- Table structure for table `type_document`
--

CREATE TABLE `type_document` (
  `id` int NOT NULL,
  `code` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `libelle` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `type_document`
--

INSERT INTO `type_document` (`id`, `code`, `libelle`) VALUES
(1, 'GARANTIE', 'Document de garantie'),
(2, 'AMENDEMENT', 'Document d\'amendement'),
(3, 'LIBERATION', 'Document de libération'),
(4, 'AUTHENTIF', 'Document d\'authentification');

-- --------------------------------------------------------

--
-- Table structure for table `type_liberation`
--

CREATE TABLE `type_liberation` (
  `id` int NOT NULL,
  `code` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `libelle` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `type_liberation`
--

INSERT INTO `type_liberation` (`id`, `code`, `libelle`) VALUES
(1, 'TOTALE', 'Libération totale'),
(2, 'PARTIELLE', 'Libération partielle');

-- --------------------------------------------------------

--
-- Table structure for table `utilisateur`
--

CREATE TABLE `utilisateur` (
  `id` int NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nom` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `prenom` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `mot_de_pass` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `roleID` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `utilisateur`
--

INSERT INTO `utilisateur` (`id`, `email`, `username`, `nom`, `prenom`, `mot_de_pass`, `roleID`) VALUES
(1, 'admin@sonatrach.com', 'admin', 'ADMIN', 'Administrateur', '$2a$12$BStKxMcePHGfPbYVvwvS/uPSRWu8vGWwsvas2e7cvAwWqFcxas21a', 1),
(2, 'mahieddine-cherif@sonatrach.com', 'Cherif', 'MAHIEDDINE', 'Cherif', '$2y$10$TygdhZ3zwZabSHTdVLwgIuFmUUBwJ/48ss/4nZbym/qBAJcntkUbC', 2);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `agence`
--
ALTER TABLE `agence`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD UNIQUE KEY `uniq_agence` (`nom`,`adresse`,`banqueID`),
  ADD KEY `fk_banque_agence` (`banqueID`);

--
-- Indexes for table `amendement`
--
ALTER TABLE `amendement`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `num_amendement` (`num_amendement`),
  ADD KEY `fk_garantie_amendement` (`garantie_soumissionID`),
  ADD KEY `fk_TYPAm_amendement` (`type_amendementID`),
  ADD KEY `fk_utilisateur_amendement` (`utilisateurID`);

--
-- Indexes for table `appel_offre`
--
ALTER TABLE `appel_offre`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `num_app_offre` (`num_app_offre`),
  ADD KEY `fk_devise_appelOffre` (`deviseID`);

--
-- Indexes for table `authentification`
--
ALTER TABLE `authentification`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_garantie_authentification` (`garantie_soumissionID`);

--
-- Indexes for table `banque`
--
ALTER TABLE `banque`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD UNIQUE KEY `nom_banque` (`nom_banque`);

--
-- Indexes for table `devise`
--
ALTER TABLE `devise`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD UNIQUE KEY `libelle` (`libelle`);

--
-- Indexes for table `document`
--
ALTER TABLE `document`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `fk_garantie_document` (`garantie_soumissionID`),
  ADD KEY `fk_TYPDoc_document` (`type_documentID`);

--
-- Indexes for table `document_amendement`
--
ALTER TABLE `document_amendement`
  ADD PRIMARY KEY (`documentID`,`amendementID`),
  ADD KEY `fk_amd_docs` (`amendementID`);

--
-- Indexes for table `document_authentification`
--
ALTER TABLE `document_authentification`
  ADD PRIMARY KEY (`documentID`,`authentificationID`),
  ADD KEY `fk_ath_docs` (`authentificationID`);

--
-- Indexes for table `document_garantie_soumission`
--
ALTER TABLE `document_garantie_soumission`
  ADD PRIMARY KEY (`documentID`,`garantie_soumissionID`),
  ADD KEY `fk_garantie_docs` (`garantie_soumissionID`);

--
-- Indexes for table `document_liberation`
--
ALTER TABLE `document_liberation`
  ADD PRIMARY KEY (`documentID`,`liberationID`),
  ADD KEY `fk_lib_docs` (`liberationID`);

--
-- Indexes for table `garantie_soumission`
--
ALTER TABLE `garantie_soumission`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `num_garantie` (`num_garantie`),
  ADD KEY `fk_soumissionnaire_garantie` (`soumissionnaireID`),
  ADD KEY `fk_agence_garantie` (`agenceID`),
  ADD KEY `fk_devise_garantie` (`deviseID`),
  ADD KEY `fk_structure_garantie` (`structureID`),
  ADD KEY `fk_appel_garantie` (`appel_offreID`),
  ADD KEY `fk_statut_garantie` (`statutID`),
  ADD KEY `fk_utilisateur_garantie` (`utilisateurID`);

--
-- Indexes for table `liberation`
--
ALTER TABLE `liberation`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `num_liberation` (`num_liberation`),
  ADD KEY `fk_garantie_liberation` (`garantie_soumissionID`),
  ADD KEY `fk_TYPLib_liberation` (`type_liberationID`),
  ADD KEY `fk_utilisateur_liberation` (`utilisateurID`);

--
-- Indexes for table `pays`
--
ALTER TABLE `pays`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD UNIQUE KEY `code_pays` (`code_pays`),
  ADD UNIQUE KEY `Nom` (`nom`);

--
-- Indexes for table `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `soumissionnaire`
--
ALTER TABLE `soumissionnaire`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `nom_entreprise` (`nom_entreprise`),
  ADD UNIQUE KEY `telephone` (`telephone`),
  ADD KEY `fk_pays_soumissionnaire` (`paysID`);

--
-- Indexes for table `statut`
--
ALTER TABLE `statut`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `structure`
--
ALTER TABLE `structure`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD UNIQUE KEY `libelle` (`libelle`);

--
-- Indexes for table `type_amendement`
--
ALTER TABLE `type_amendement`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `type_document`
--
ALTER TABLE `type_document`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `type_liberation`
--
ALTER TABLE `type_liberation`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `fk_role_utilisateur` (`roleID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `agence`
--
ALTER TABLE `agence`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `amendement`
--
ALTER TABLE `amendement`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `appel_offre`
--
ALTER TABLE `appel_offre`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `authentification`
--
ALTER TABLE `authentification`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `banque`
--
ALTER TABLE `banque`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `devise`
--
ALTER TABLE `devise`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `document`
--
ALTER TABLE `document`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `garantie_soumission`
--
ALTER TABLE `garantie_soumission`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `liberation`
--
ALTER TABLE `liberation`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pays`
--
ALTER TABLE `pays`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `role`
--
ALTER TABLE `role`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `soumissionnaire`
--
ALTER TABLE `soumissionnaire`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `statut`
--
ALTER TABLE `statut`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `structure`
--
ALTER TABLE `structure`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `type_amendement`
--
ALTER TABLE `type_amendement`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `type_document`
--
ALTER TABLE `type_document`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `type_liberation`
--
ALTER TABLE `type_liberation`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `utilisateur`
--
ALTER TABLE `utilisateur`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `agence`
--
ALTER TABLE `agence`
  ADD CONSTRAINT `fk_banque_agence` FOREIGN KEY (`banqueID`) REFERENCES `banque` (`id`);

--
-- Constraints for table `amendement`
--
ALTER TABLE `amendement`
  ADD CONSTRAINT `fk_garantie_amendement` FOREIGN KEY (`garantie_soumissionID`) REFERENCES `garantie_soumission` (`id`),
  ADD CONSTRAINT `fk_TYPAm_amendement` FOREIGN KEY (`type_amendementID`) REFERENCES `type_amendement` (`id`),
  ADD CONSTRAINT `fk_utilisateur_amendement` FOREIGN KEY (`utilisateurID`) REFERENCES `utilisateur` (`id`);

--
-- Constraints for table `appel_offre`
--
ALTER TABLE `appel_offre`
  ADD CONSTRAINT `fk_devise_appelOffre` FOREIGN KEY (`deviseID`) REFERENCES `devise` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `authentification`
--
ALTER TABLE `authentification`
  ADD CONSTRAINT `fk_garantie_authentification` FOREIGN KEY (`garantie_soumissionID`) REFERENCES `garantie_soumission` (`id`);

--
-- Constraints for table `document`
--
ALTER TABLE `document`
  ADD CONSTRAINT `fk_garantie_document` FOREIGN KEY (`garantie_soumissionID`) REFERENCES `garantie_soumission` (`id`),
  ADD CONSTRAINT `fk_TYPDoc_document` FOREIGN KEY (`type_documentID`) REFERENCES `type_document` (`id`);

--
-- Constraints for table `document_amendement`
--
ALTER TABLE `document_amendement`
  ADD CONSTRAINT `fk_amd_docs` FOREIGN KEY (`amendementID`) REFERENCES `amendement` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_docs_amd` FOREIGN KEY (`documentID`) REFERENCES `document` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `document_authentification`
--
ALTER TABLE `document_authentification`
  ADD CONSTRAINT `fk_ath_docs` FOREIGN KEY (`authentificationID`) REFERENCES `authentification` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_docs_ath` FOREIGN KEY (`documentID`) REFERENCES `document` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `document_garantie_soumission`
--
ALTER TABLE `document_garantie_soumission`
  ADD CONSTRAINT `fk_docs_garantie` FOREIGN KEY (`documentID`) REFERENCES `document` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_garantie_docs` FOREIGN KEY (`garantie_soumissionID`) REFERENCES `garantie_soumission` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `document_liberation`
--
ALTER TABLE `document_liberation`
  ADD CONSTRAINT `fk_docs_lib` FOREIGN KEY (`documentID`) REFERENCES `document` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_lib_docs` FOREIGN KEY (`liberationID`) REFERENCES `liberation` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `garantie_soumission`
--
ALTER TABLE `garantie_soumission`
  ADD CONSTRAINT `fk_agence_garantie` FOREIGN KEY (`agenceID`) REFERENCES `agence` (`id`),
  ADD CONSTRAINT `fk_appel_garantie` FOREIGN KEY (`appel_offreID`) REFERENCES `appel_offre` (`id`),
  ADD CONSTRAINT `fk_devise_garantie` FOREIGN KEY (`deviseID`) REFERENCES `devise` (`id`),
  ADD CONSTRAINT `fk_soumissionnaire_garantie` FOREIGN KEY (`soumissionnaireID`) REFERENCES `soumissionnaire` (`id`),
  ADD CONSTRAINT `fk_statut_garantie` FOREIGN KEY (`statutID`) REFERENCES `statut` (`id`),
  ADD CONSTRAINT `fk_structure_garantie` FOREIGN KEY (`structureID`) REFERENCES `structure` (`id`),
  ADD CONSTRAINT `fk_utilisateur_garantie` FOREIGN KEY (`utilisateurID`) REFERENCES `utilisateur` (`id`);

--
-- Constraints for table `liberation`
--
ALTER TABLE `liberation`
  ADD CONSTRAINT `fk_garantie_liberation` FOREIGN KEY (`garantie_soumissionID`) REFERENCES `garantie_soumission` (`id`),
  ADD CONSTRAINT `fk_TYPLib_liberation` FOREIGN KEY (`type_liberationID`) REFERENCES `type_liberation` (`id`),
  ADD CONSTRAINT `fk_utilisateur_liberation` FOREIGN KEY (`utilisateurID`) REFERENCES `utilisateur` (`id`);

--
-- Constraints for table `soumissionnaire`
--
ALTER TABLE `soumissionnaire`
  ADD CONSTRAINT `fk_pays_soumissionnaire` FOREIGN KEY (`paysID`) REFERENCES `pays` (`id`);

--
-- Constraints for table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD CONSTRAINT `fk_role_utilisateur` FOREIGN KEY (`roleID`) REFERENCES `role` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
